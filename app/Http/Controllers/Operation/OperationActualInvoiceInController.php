<?php

namespace App\Http\Controllers\Operation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OperationActualInvoiceIn;
use Carbon\Carbon;
class OperationActualInvoiceInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $OperationActualInvoiceIn=OperationActualInvoiceIn::latest()->get();
        if(!$OperationActualInvoiceIn->isEmpty()){
            $OperationActualInvoiceIn->transform(function($data){
                $data->created_date = Carbon::parse($data->created_at)->toDateString();
                $data->created_time = Carbon::parse($data->created_at)->toTimeString();
                unset($data->created_at);
                return $data;
            });
            return response()->json(["data"=>$OperationActualInvoiceIn,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"There is No Data","status"=>404]);

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
        OperationActualInvoiceIn::create($validator);

        return response()->json(['message' => 'Month Invoice created successfully',"status"=> Response::HTTP_OK]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $User=OperationActualInvoiceIn::find($id);
        if($User!=null){
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no OperationActualInvoiceIn","status"=>404]);

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
        OperationActualInvoiceIn::where('id',$id)->update($validator);

        return response()->json(['message' => 'plan updated successfully',"status"=>Response::HTTP_OK], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $User=OperationActualInvoiceIn::find($id);
        if($User!=null){
            $User->delete();
            return response()->json(["data"=>"plan deleted","status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no OperationActualInvoiceIn","status"=>404]);

        }
    }
}
