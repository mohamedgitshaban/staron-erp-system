<?php

namespace App\Http\Controllers\Control;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ControlScPlan;
use App\Models\ToLocation;
use Carbon\Carbon;

class   ControlProcurmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ControlOperationPlan = ControlScPlan::with('ToLocation')->latest()->get();
        if(!$ControlOperationPlan->isEmpty()){
            $ControlOperationPlan->transform(function ($invoice) {
                $invoice->created_date = Carbon::parse($invoice->created_at)->toDateString();
                $invoice->created_time = Carbon::parse($invoice->created_at)->toTimeString();

                unset($invoice->created_at);
                return $invoice;
            });

            return response()->json(["data"=>$ControlOperationPlan,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is No Data","status"=>404]);

        }
    }


    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'Priority' => 'required|in:1,2,3',
            'time' => 'nullable|date_format:H:i',
            'from' => 'required|string',
            'to' => 'required|array|min:1', // Ensure 'to' is required, an array, and has at least one element
            'to.*' => 'required|string', // Ensure each element in 'to' array is required and a string
            'description' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors(),$request->all()],"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
        }
        $validator = $validatedData->validated();
        $validator["source"]="Control Office";
        $validator["cost"]=0;
        $currentDate = Carbon::now();
        if($validator["Priority"]==1){
            $twoDaysLater = $currentDate->addDays(1);

        }
        else if($validator["Priority"]==2){
            $twoDaysLater = $currentDate->addDays(2);

        }
        else if($validator["Priority"]==3){
            $twoDaysLater = $currentDate->addDays(4);

        }
        else{
            $twoDaysLater = $currentDate->addDays(30);

        }
        $validator["Deadline"]= $twoDaysLater->format('Y-m-d');
        $controlScPlan=ControlScPlan::create($validator);
        foreach ($validator['to'] as $toLocation) {
            ToLocation::create([
                'control_sc_plan_id' => $controlScPlan->id,
                'to_location' => $toLocation,
            ]);
        }
        return response()->json(['message' => 'procurment successfully',"status"=> Response::HTTP_OK]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'Priority' => 'required|in:1,2,3',
            'time' => 'nullable|date_format:H:i',
            'from' => 'required|string',
            'to' => 'required|array|min:1', // Ensure 'to' is required, an array, and has at least one element
            'to.*' => 'required|string', // Ensure each element in 'to' array is required and a string
            'description' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors(),$request->all()],"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
        }
        else{
            $validator = $validatedData->validated();
            $controlScPlan = ControlScPlan::find($id);

            if($controlScPlan!=null&&$controlScPlan->status=="pending"){
                $currentDate = Carbon::parse($controlScPlan->created_at->format('Y-m-d'));
                if($validator["Priority"]==1){
                    $twoDaysLater = $currentDate->addDays(1);

                }
                else if($validator["Priority"]==2){
                    $twoDaysLater = $currentDate->addDays(2);

                }
                else if($validator["Priority"]==3){
                    $twoDaysLater = $currentDate->addDays(4);

                }
                else{
                    $twoDaysLater = $currentDate->addDays(30);

                }
                $validator["Deadline"]= $twoDaysLater->format('Y-m-d');
            }
        }
        ToLocation::where("control_sc_plan_id","=",$controlScPlan->id)->delete();
        foreach ($validator['to'] as $toLocation) {
            ToLocation::create([
                'control_sc_plan_id' => $controlScPlan->id,
                'to_location' => $toLocation,
            ]);
        }
        $controlScPlan->update($validator);

        return response()->json($controlScPlan, 200);
    }
    public function reject($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null&&$User->status=="in review"&&$User->source=="Control Office"){
            $User->status="un acceptable";
            $User->save();
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }

    }

    public function AcceptfromOperation($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null){
            $User->status="pending";
            $User->save();
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no ControlOperationPlan","status"=>404]);

        }

    }
        /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null){
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no ControlOperationPlan","status"=>404]);

        }

    }

    public function destroy($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null&&$User->status=="pending"&&$User->source=="Control Office"){
            $User->delete();
            return response()->json(["data"=>"plan deleted","status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }
    }
    public function Approve($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null&&$User->status=="in review"&&$User->source=="Control Office"){
            $User->status="completed";
            
            $User->save();
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }

    }
}
