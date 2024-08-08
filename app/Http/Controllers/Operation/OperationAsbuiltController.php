<?php

namespace App\Http\Controllers\Operation;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sales\TechnecalRequestController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OperationAsbuilt;

class OperationAsbuiltController extends Controller
{
    private $TechnecalRequestController;
    public function __construct(TechnecalRequestController $TechnecalRequestController)
    {
        $this->TechnecalRequestController = $TechnecalRequestController;
    }
    public function index()
    {
        $OperationAsbuilt=OperationAsbuilt::latest()->get();
        if(!$OperationAsbuilt->isEmpty()){
            return response()->json(["data"=>$OperationAsbuilt,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"There is No Data","status"=>404]);

        }
    }


    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'technecal_requests_id'=>'required|exists:technecal_requests,id',
            'date' => 'required|date',
            'location' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors(),"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
        }
        $validator = $validatedData->validated();
        OperationAsbuilt::create($validator);
        $this->TechnecalRequestController->Asbuiltstart($validator["technecal_requests_id"]);
        return response()->json(['message' => 'Month Invoice created successfully',"status"=> Response::HTTP_OK]);
    }
    public function starttask($id)
    {
        try{
            $validate["Actualstart"]=now();
            OperationAsbuilt::where("id","=",$id)->update($validate);

            return response()->json(['massage' =>"task started succesful" ,"status"=>200] );

        }catch(\Exception  $e){
            return response()->json(['error' => 'Something went wrong',"status"=>500] );

        }
    }
    public function Sendactualasbuilt(Request $request,$id)
    {
        $validate=Validator($request->all(),[
            'data' => 'required|file',

        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            if ($request->hasFile('data')) {
                $file=$request->file('data');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $validate["data"]= '/uploads/data/'.$fileName;
                $file->move(public_path('uploads/data'), $fileName);
                $validate["Actualend"]=now();
                OperationAsbuilt::where("id","=",$id)->update($validate);

                return response()->json(['message' => 'crm created successfully',"status"=> Response::HTTP_OK]);

            }
        }
    }
    public function show($id)
    {
        $User=OperationAsbuilt::find($id);
        if($User!=null){
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no OperationAsbuilt","status"=>404]);

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
        OperationAsbuilt::where('id',$id)->update($validator);

        return response()->json(['message' => 'plan updated successfully',"status"=>Response::HTTP_OK], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $User=OperationAsbuilt::find($id);
        if($User!=null){
            $User->delete();
            return response()->json(["data"=>"plan deleted","status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no OperationAsbuilt","status"=>404]);

        }
    }
}
