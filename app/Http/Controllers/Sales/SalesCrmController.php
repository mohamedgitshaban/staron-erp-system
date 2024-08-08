<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\SalesCrm;
use App\Models\Qutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Http\Controllers\Technical\TechnecalRequestController;
use Illuminate\Support\Facades\Auth;

class SalesCrmController extends Controller
{
    private $TechnecalRequestController;
    private $QutationController;
    private $ContractController;
    public function __construct(TechnecalRequestController $TechnecalRequestController,QutationController $QutationController,ContractController $ContractController)
    {
        $this->TechnecalRequestController = $TechnecalRequestController;
        $this->ContractController = $ContractController;
        $this->QutationController = $QutationController;
    }
    public function gogo($id){
        $salesCrm = SalesCrm::with("client","technecalRequests.qcApplecations.qcApplecationItem","assignedBy.supervisor",)->find($id);
        $latestQuotation = $salesCrm->getLatestQutationAttribute();

        return view('your-view-name', compact('salesCrm','latestQuotation'));
    }
    //all select function
    public function index()
    {
        $authUser = Auth::user();
        $data = SalesCrm::latest()
            ->with("client","assignedBy","assignedBy.supervisor","technecalRequests","qutations","contracts")
            ->orderBy('updated_at')
            ->where("asignby",$authUser->id)
            ->get();
        if (!$data->isEmpty()) {
            $data->transform(function ($item) {
                $item->created_date = Carbon::parse($item->created_at)->toDateString();
                unset($item->created_at);
                unset($item->updated_at);
                return $item;
            });
            return response()->json(["data" => $data, "status" => 200]);
        } else {
            return response()->json(["data" => "No Data", "status" => 404]);
        }
    }
    public function show($id)
    {
        $Client= SalesCrm::with("client","assignedBy","technecalRequests.qcApplecations.qcApplecationItem","qutations","assignedBy.supervisor",)->find($id);
        if($Client!=null){
            return response()->json(["data"=>$Client,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>Response::HTTP_NO_CONTENT ]);
        }
    }
    public function switch(Request $request,$id)
    {
        $validate=Validator($request->all(),[
            'asignby' => 'required|exists:users,id',
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $data= SalesCrm::find($id);
            if($data!=null){
                $data->asignby=$validate["asignby"];
                $data->save();
                return response()->json(["data"=>"asign by switch successfuly","status"=>Response::HTTP_OK]);
            }
            else{
                return response()->json(["data"=>"no data","status"=>Response::HTTP_NO_CONTENT ]);
            }
        }
    }
    //store function
    public function store(Request $request)
    {
        $validate=Validator($request->all(),[
            'clients_id' => 'required|exists:clients,id',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();

            $user = Auth::guard('sanctum')->user();
            if ($user) {
                $validate["status"]="pending client data";
                $validate["asignby"]=$user->id;
                SalesCrm::create($validate);
                return response()->json(['message' => 'data created successfully',"status"=>Response::HTTP_CREATED]);

            }
            return response()->json(['message' => 'lead not created ',"status"=>Response::HTTP_BAD_REQUEST]);

        }
    }
    public function RFQ(Request $request , $id) {
        $validate = Validator($request->all(), [
            'tasbuilt' => 'required|file',
        ]);

        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY]);
        }

        $data = SalesCrm::find($id);

        if ($data) {
            if ($data->status == "pending client data" || $data->status == "Request for Information") {
                $file = $request->file('tasbuilt');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/tasbuilt'), $fileName);
                $data->tasbuilt = '/uploads/tasbuilt/' . $fileName;
                $data->status = "pending technical Assign";
                $data->save();

                $this->TechnecalRequestController->store($id);

                return response()->json(['message' => 'successfully', "status" => Response::HTTP_OK]);
            } else {
                return response()->json(["data" => "method not allowed", "status" => Response::HTTP_METHOD_NOT_ALLOWED], 200);
            }
        } else {
            return response()->json(["data" => "no data", "status" => Response::HTTP_NOT_FOUND], 200);
        }
    }
    public function QutationReject($id,Request $request){
        $validation=Validator::make($request->all(),[
            "reason"=>"required|string",
        ]);
        if($validation->fails()){
            return response()->json(['errors' => $validation->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);

        }
        else{
            $data=SalesCrm::find($id);
        if($data){

            if($data->status=="pending qutation approve"){
                $validation=$validation->validated();
                $data->status="qutation back";
                $data->save();
                $this->QutationController->reject($id,$validation["reason"]);
                $this->TechnecalRequestController->QutationReject($id,$validation["reason"]);
                return response()->json(['message' => 'qutation Rejected successfully',"status"=>Response::HTTP_CREATED]);

            }
            else{
                return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED ]);

            }
        }
        else{
            return response()->json(["data" => "No Data", "status" => 404]);

        }
        }
    }
    public function QutationApprove($id){
        $data=SalesCrm::find($id);
        if($data){

            if($data->status=="pending qutation approve"){

                $data->status="pending qutation Drafting";
                $data->save();
                return $this->QutationController->Inprogress($id);

            }
            else{
                return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED ]);

            }
        }
        else{
            return response()->json(["data" => "No Data", "status" => 404]);

        }

    }
    public function submitdrafting($id , Request $request){
        $validation=Validator::make($request->all(),[
            "file"=>"required|file"
        ]);
        if($validation->fails()){
            return response()->json(['messege' => $validation->errors(),"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );

        }
        else{

            $data=SalesCrm::find($id);
            if($data){
                if($data->status=="pending qutation Drafting"){
                    $file=$request->file('file');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $validator["file"]= '/uploads/drafting/'.$fileName;
                // Move the file to the desired location
                $file->move(public_path('uploads/drafting'), $fileName);
                $data->status="pending client Approve";
                $data->save();
                return $this->QutationController->Submitqutation($id,$validator["file"]);

                }
                else{
                    return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED ]);
                }
            }
            else{
                return response()->json(["data" => "No Data", "status" => 404]);

            }

        }
    }
    public function clintApprove($id,Request $request){
        $validation=validator::make($request->all(),[
            "contractdata"=>"required|file",
            "contractValue"=>"required|numeric"
        ]);
        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $data=SalesCrm::find($id);
            if($data){
                if ($data->status=="pending client Approve") {
                    $validation=$validation->validated();
                    $data->status="completed";
                    $data->save();
                    return $this->ContractController->store($id,$validation);
                } else {
                    return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED ]);
                }

            }
        else{
            return response()->json(["data" => "No Data", "status" => 404]);

        }
        }
    }
    public function clintReject($id,Request $request){
        $validation=Validator::make($request->all(),[
            "reason"=>"required|string",
        ]);
        if($validation->fails()){
            return response()->json(['errors' => $validation->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);

        }
        $data=SalesCrm::find($id);
        if($data){
            if ($data->status=="pending client Approve") {
                $validation=$validation->validated();
                $data->status="rejected";
                $data->reason=$validation["reason"];
                $data->save();
                return response()->json(['message' => 'project rejected successfuly.',"status"=>Response::HTTP_OK], 200);

            } else {
                return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED ]);
            }

        }
        else{
            return response()->json(["data" => "No Data", "status" => 404]);

        }
    }
    public function clintRecalculation($id,Request $request){
        $validation=Validator::make($request->all(),[
            "reason"=>"required|string",
        ]);
        if($validation->fails()){
            return response()->json(['errors' => $validation->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $data=SalesCrm::find($id);
        if($data){

            if($data->status=="pending client Approve"){
                $validation=$validation->validated();
                $data->status="Re-calculation";
                $data->save();
                $this->QutationController->reject($id,$validation["reason"]);
                $this->TechnecalRequestController->QutationRcalc($id,$validation["reason"]);
                return response()->json(['message' => 'qutation Re-calculation successfully',"status"=>Response::HTTP_CREATED]);

            }
            else{
                return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED ]);
            }
        }
        else{
            return response()->json(["data" => "No Data", "status" => 404]);

        }
        }
    }
    public function update(Request $request, $id)
    {
        $validate=Validator($request->all(),[
            'clients_id' => 'required|exists:clients,id',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            SalesCrm::where("id","=",$id)->update($validate);
            return response()->json(['message' => 'Client created successfully',"status"=>Response::HTTP_CREATED]);
        }
    }
    public function destroy($id , Request $request)
    {
        $validate=Validator::make($request->all(),[
            'reason'=>"required|string"
        ]);
        if($validate->fails()){
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);

        }
        else{
            $SalesCrm=SalesCrm::find($id);
            if($SalesCrm!=null&&$SalesCrm->status!="completed"){
                $validate=$validate->validated();
                $SalesCrm->status="rejected";
                $SalesCrm->reason=$validate["reason"];
                $SalesCrm->save();
                return response()->json(["data"=>$SalesCrm,"status"=>Response::HTTP_OK],200);
            }
            else{
                return response()->json(["data"=>"Method Not Allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED ],200);
            }
        }
    }

}
