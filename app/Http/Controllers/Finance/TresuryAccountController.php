<?php
namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\TresuryAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TresuryAccountController extends Controller
{
    private $ChartAccountController;
    private $MainJournalController;

    public function __construct(ChartAccountController $ChartAccountController,MainJournalController $MainJournalController)
    {
        $this->ChartAccountController = $ChartAccountController;
        $this->MainJournalController = $MainJournalController;
    }
    public function index()
    {
        $data=TresuryAccount::latest()->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getallrequests()
    {
        $data=TresuryAccount::latest()->where("type","outcome")->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getallcollection()
    {
        $data=TresuryAccount::latest()->where("type","income")->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getARcollection()
    {
        $data = TresuryAccount::latest()
                ->where('type', 'income')
                ->where('status', '!=', "complete")
                ->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getApRequest()
    {
        $data = TresuryAccount::latest()
                ->where('type', 'outcome')
                ->where('status', '!=', "complete")
                ->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getCashflowHistory()
    {
        $data=TresuryAccount::latest()->whereDate('collection_date', '<', Carbon::today())->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getBankChecks()
    {
        $data=TresuryAccount::latest()->where("collection_type","Bank Check")->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }

    public function getTresuryRequests()
    {
        $data=TresuryAccount::latest()->where("collection_type","Cash")->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'debit_id' => 'required|exists:chart_accounts,id',
            'credit_id' => 'required|exists:chart_accounts,id',
            'description' => 'required|string|max:500',
            'value' => 'required|integer|min:1',
            'type' => 'required|string|max:50|in:income,outcome',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);

        }
        else{
            $validator=$validator->validated();
            $validator["debit_account_description"]=$this->ChartAccountController->GetFullAccountName($validator["debit_id"]);
            $validator["credit_account_description"]=$this->ChartAccountController->GetFullAccountName($validator["credit_id"]);
            $validator["status"]="pending";
            TresuryAccount::create($validator);
            return response()->json(['message' => 'Data created successfully', "status" => Response::HTTP_CREATED]);
        }

    }
    public function inprogress(Request $request,$id){
        $validator = Validator::make($request->all(),[
            'collection_date' => 'required|date|after:today',
            'check_collect' => 'nullable|date|after_or_equal:collection_date',
            'collection_type' => 'required|string|max:100|in:Bank Transfer,Bank Check,Cash',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);

        }
        else{
            $validator=$validator->validated();
            $data=TresuryAccount::find($id);
            if($data!=null){
                if ($validator['collection_type'] === 'Bank Check' && is_null($validator['check_collect'])) {
                    return response()->json(['message' => 'Check collect date is required for Bank Check type', "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
                }
                if($data->status=="pending"||$data->status=="check reject"){
                    $validator["status"]="in progress";
                    $data->update($validator);
                    return response()->json(['message' => 'data updated successfully', "status" => Response::HTTP_OK]);

                }
                else{
                    return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

                }
            }
            else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

            }
        }
    }
    public function bankapprove($id){
        $data=TresuryAccount::find($id);
        if($data!=null){
            if(($data->status=="in progress"&&$data->collection_type=="Bank Transfer")||($data->status=="Pending Transfare Approve")){
                if (Carbon::parse($data->collection_date)->greaterThanOrEqualTo(Carbon::today())){

                $validator["status"]="complete";
                $data->update($validator);
                $this->MainJournalController->store(new Request($data));
                return response()->json(['message' => 'data updated successfully', "status" => Response::HTTP_OK]);
                }
                else{
                    return response()->json(['message' => "Method will terminate until {$data->collection_date}", "status" => Response::HTTP_METHOD_NOT_ALLOWED], 200);

                }
            }
            else{
                return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

            }
        }
        else{
        return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }
    public function AccountsrepresentativeApprove($id){
        $data=TresuryAccount::find($id);
        if($data!=null){
            if($data->status=="in progress"){
                if (Carbon::parse($data->collection_date)->greaterThanOrEqualTo(Carbon::today())){
                    if($data->collection_type=="Cash"){
                        $validator["status"]="pending tresury Approve";
                    }
                    elseif($data->collection_type=="Bank Check"){
                        $validator["status"]="pending Account Approve";
                    }
                    else{
                        return response()->json(['message' => 'data updated successfully', "status" => Response::HTTP_OK]);

                    }
                    $data->update($validator);


                }
                else{
                    return response()->json(['message' => "Method will terminate until {$data->collection_date}", "status" => Response::HTTP_METHOD_NOT_ALLOWED], 200);

                }

            }
            else{
                return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

            }
        }
        else{
        return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }
    public function tresuryApprove($id){
        $data=TresuryAccount::find($id);
        if($data!=null){
            if($data->status=="pending tresury Approve"){
                $validator["status"]="complete";
                $data->update($validator);
                $this->MainJournalController->store(new Request($data));
                return response()->json(['message' => 'data updated successfully', "status" => Response::HTTP_OK]);

            }
            else{
                return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

            }
        }
        else{
        return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }

    public function AccountApprove(Request $request,$id){

        $data=TresuryAccount::find($id);
        if($data!=null){
            if($data->status=="pending Account Approve"){
                $validator["status"]="pending check collection";
                $data->update($validator);
                $this->MainJournalController->store(new Request($data));
                return response()->json(['message' => 'data updated successfully', "status" => Response::HTTP_OK]);

            }
            else{
                return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

            }
        }
        else{
        return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }
    public function show($id)
    {
        $data=TresuryAccount::find($id);
        if($data!=null){
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
        else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }
    public function update(Request $request,  $id)
    {
        $validator = Validator::make($request->all(),[
            'debit_id' => 'required|exists:chart_accounts,id',
            'credit_id' => 'required|exists:chart_accounts,id',
            'description' => 'required|string|max:500',
            'value' => 'required|integer|min:1',
            'type' => 'required|string|max:50|in:income,outcome',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        }
        else{
            $validator=$validator->validated();
            $data=TresuryAccount::find($id);
            if($data!=null){
                if($data->status=="pending"){
                    $validator["debit_account_description"]=$this->ChartAccountController->GetFullAccountName($validator["debit_id"]);
                    $validator["credit_account_description"]=$this->ChartAccountController->GetFullAccountName($validator["credit_id"]);
                    $data->update($validator);
                    return response()->json(['message' => 'data updated successfully', "status" => Response::HTTP_OK]);

                }
                else{
                    return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

                }
            }
            else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

            }
        }

    }
    public function cancelled($id)
    {
        $data=TresuryAccount::find($id);
        if($data!=null){
            $data->status="cancelled";
            $data->save();
            return response()->json(["data" => "data deleted succesfuly", "status" => Response::HTTP_OK], 200);

        }
        else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }
    public function checkreject($id)
    {
        $data=TresuryAccount::find($id);
        if($data!=null){
            $data->status="check reject";
            $data->save();
            return response()->json(["data" => "data deleted succesfuly", "status" => Response::HTTP_OK], 200);

        }
        else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }
    public function checkcollect($id,Request $request)
    {

        $validator = Validator::make($request->all(),[
            'type' => 'required|string|max:50|in:Bank Transfer,Cash',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        }
        else{
            $validator=$validator->validated();
        $data=TresuryAccount::find($id);
        if($data!=null){
           if($data->status=="pending check collection"){
            if($validator["type"]=="Bank Transfer"){
                $data->status="Pending Transfare Approve";
                $data->save();
            }
            else{
                $data->status="pending tresury Approve";
                $data->save();
            }
            return response()->json(["data" => "data deleted succesfuly", "status" => Response::HTTP_OK], 200);

           }
            else{

            }
        }
        else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
        }
    }
    public function destroy($id)
    {
        $data=TresuryAccount::find($id);
        if($data!=null){
            $data->delete();
            return response()->json(["data" => "data deleted succesfuly", "status" => Response::HTTP_OK], 200);

        }
        else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }
}
