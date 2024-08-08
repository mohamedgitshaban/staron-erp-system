<?php

namespace App\Http\Controllers\Operation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ControlScPlan;
use Carbon\Carbon;
use App\Models\ToLocation;

class OperationProcurmentController extends Controller
{
    public function index()
    {
        $ControlOperationPlan=ControlScPlan::where("source","=",'Operation Office')->with('ToLocation')->latest()->get();
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
            return response()->json(["data"=>"There is No Data","status"=>404]);

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
        $validator["status"]="pending control";
        $validator["source"]="Operation Office";
        $validator["cost"]=0;
        $currentDate = Carbon::now();
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
        return response()->json(['message' => 'procurment created successfully',"status"=> Response::HTTP_OK]);
    }
    public function reject($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null&&$User->source=="Operation Office"&&$User->status=="in review"){
            $User->status="un acceptable";
            $User->save();
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }

    }
    public function Approve($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null&&$User->status=="in review"&&$User->source=="Operation Office"){
            $User->status="completed";
            $User->save();
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }

    }
    public function show($id)
    {
        $User = ControlScPlan::with('ToLocation')->find($id);

        if ($User != null) {
            // Pluck only the 'to_location' values from the ToLocation collection
            $toLocations = $User->ToLocation->pluck('to_location')->toArray();

            // Replace the original to_location collection with the array of strings
            $User->to_location = $toLocations;
            $User->time=Carbon::createFromFormat('H:i:s', $User->time)->format('H:i');
            // Optionally remove the original ToLocation relationship data to clean up the response
            unset($User->ToLocation);

            return response()->json(["data" => $User, "status" => 202]);
        } else {
            return response()->json(["data" => "There is no procurement", "status" => 404]);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'Priority' => 'required|in:1,2,3',
            'time' => 'nullable|date_format:H:i',
            'from' => 'required|string',
            'to_location' => 'required|array|min:1', // Ensure 'to' is required, an array, and has at least one element
            'to_location.*' => 'required|string', // Ensure each element in 'to' array is required and a string
            'description' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors(),$request->all()],"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
        }
        else{
            $validator = $validatedData->validated();
            $controlScPlan = ControlScPlan::find($id);

            if($controlScPlan!=null&&$controlScPlan->status=="pending control"){
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
        foreach ($validator['to_location'] as $toLocation) {
            ToLocation::create([
                'control_sc_plan_id' => $controlScPlan->id,
                'to_location' => $toLocation,
            ]);
        }
        $controlScPlan->update($validator);

        return response()->json(['message' => 'procurment updated successfully',"status"=> Response::HTTP_OK]);
    }
    public function destroy($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null&&$User->status=="pending control"&&$User->source=="Operation Office"){
            $User->delete();
            return response()->json(["data"=>"procurment deleted","status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }
    }
}
