<?php

namespace App\Http\Controllers\Control;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ControlOperationPlan;
use Carbon\Carbon;
class ControlOperationPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ControlOperationPlan=ControlOperationPlan::latest()->get();
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

            'plan' => 'required|file',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors(),$request->all()],"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
        }
        $validator = $validatedData->validated();
        $file=$request->file('plan');
        $fileName = time().'.'.$file->getClientOriginalExtension();
        $validator["plan"]= '/uploads/plan/'.$fileName;
        // Move the file to the desired location
        $file->move(public_path('uploads/plan'), $fileName);
        // Validation passed, create the user
        ControlOperationPlan::create($validator);

        return response()->json(['message' => 'Month Invoice created successfully',"status"=> Response::HTTP_OK]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $User=ControlOperationPlan::find($id);
        if($User!=null){
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no ControlOperationPlan","status"=>404]);

        }

    }
    public function update(Request $request, $id)
    {
        if ($request->hasFile('plan')) {
            $file=$request->file('plan');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $validator["plan"]='/uploads/plan/'. $fileName;
            // Move the file to the desired location
            $file->move(public_path('uploads/plan'), $fileName);
        }

        // Validation passed, create the user
        ControlOperationPlan::where('id',$id)->update($validator);

        return response()->json(['message' => 'plan updated successfully',"status"=>Response::HTTP_OK], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $User=ControlOperationPlan::find($id);
        if($User!=null){
            $User->delete();
            return response()->json(["data"=>"plan deleted","status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no ControlOperationPlan","status"=>404]);

        }
    }
}
