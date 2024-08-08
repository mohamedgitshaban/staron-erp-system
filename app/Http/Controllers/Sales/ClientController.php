<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{

    public function index()
    {
        $data=Client::latest()->where("status","work")->with("assignBy")->get();
        if(!$data->isEmpty()){
            $data->transform(function($item){
                unset($item->asignby);
                return $item;
            });
            return response()->json(["data"=>$data,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"There is No Data","status"=>Response::HTTP_NO_CONTENT ]);

        }
    }
    public function store(Request $request)
    {
        $validate=Validator($request->all(),[
            'name'=>'required|string|max:255',
            'company'=>'required|string|max:255',
            'Job_role'=>'required|string|max:255',
            'phone'=>"required|regex:/^(\+\d{1,2}\s?)?(\d{10,})$/",
            'email'=>'nullable',
            'source'=>'required|in:Mr. Tarek El Behairy,Mr. Hussein El Behairy,Independent Effort,Eng. Eslam Moataz',
            'type'=>'required|in:Project Owner,Main Contractor,Sub Contractor,Consultant',
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $validate["status"]="work";
            $validate["asignby"]=Auth::id();
            Client::create($validate);
            return response()->json(['message' => 'Client created successfully',"status"=>Response::HTTP_CREATED]);
        }
    }

    public function show($id)
    {
        $Client= Client::with("assignBy")->find($id);
        if($Client!=null){
            unset($Client->asignby);
            return response()->json(["data"=>$Client,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"there is no Client","status"=>Response::HTTP_NO_CONTENT ]);
        }
    }

    public function update(Request $request, $id)
    {
        $validate=Validator($request->all(),[
            'name'=>'required|string|max:255',
            'company'=>'required|string|max:255',
            'Job_role'=>'required|string|max:255',
            'phone'=>"required|regex:/^(\+\d{1,2}\s?)?(\d{10,})$/",
            'email'=>'nullable',
            'source'=>'required|in:Mr. Tarek El Behairy,Mr. Hussein El Behairy,Independent Effort,Eng. Eslam Moataz',
            'type'=>'required|in:Project Owner,Main Contractor,Sub Contractor,Consultant',
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $Client= Client::where("id",$id)->update($validate);
            return response()->json(['message' => 'Client updated successfully',"status"=>Response::HTTP_CREATED]);
        }
    }

    public function destroy($id)
    {
        $Client= Client::find($id);
        if($Client!=null){
            if ($Client->status=="not work") {
                $Client->status="work";
            }
            else{
                $Client->status="not work";
            }
            $Client->save();
            return response()->json(['message' => 'Client deleted successfully',"status"=>Response::HTTP_FOUND]); }
        else{
            return response()->json(["data"=>"there is no Client","status"=>Response::HTTP_NO_CONTENT ]);
        }
    }
    public function NewStakeholders(Request $request )
    {
        $validator = Validator::make($request->all(), [
            'filter' => 'required|string',

        ]);
        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $validator->errors(),
                'status'=>Response::HTTP_BAD_REQUEST
            ], Response::HTTP_OK);
        }
       else{
        $validator=$validator->validated();
         if($validator["filter"]=="This Week"){
            $ControlOperationPlan = Client::
            whereBetween('created_at', [now()->subDays(7), now()])
            ->count();
              }
        else if($validator["filter"]=="This Month"){
            $ControlOperationPlan = Client::
            whereMonth('created_at', [ now()->month])
            ->count();

        }
        else if($validator["filter"]=="This Quarter"){
            $ControlOperationPlan = Client::
            whereBetween('created_at', [now()->subDays(120), now()])
            ->count();

        }
        else{
            $ControlOperationPlan = Client::
            whereYear('created_at', [ now()->year])
            ->count();
        }
        if($ControlOperationPlan!=null){

            return response()->json(["data"=>$ControlOperationPlan,"status"=>200]);
        }
        else{
            return response()->json(["data"=>0,"status"=>404]);

        }
       }
    }
    public function StakeholdersReprsinting()
    {
        $ProjectOwner=Client::where("type","Project Owner")->count();
        $MainContractor=Client::where("type","Main Contractor")->count();
        $SubContractor=Client::where("type","Sub Contractor")->count();
        $Consultant=Client::where("type","Consultant")->count();

            return response()->json(["data"=>[
                'ProjectOwner'=>$ProjectOwner,
                'MainContractor'=> $MainContractor,
                'SubContractor'=>$SubContractor,
                 'Consultant'=>$Consultant
            ],"status"=>Response::HTTP_OK]);

    }
}
