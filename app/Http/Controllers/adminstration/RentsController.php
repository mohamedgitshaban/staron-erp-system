<?php

namespace App\Http\Controllers\adminstration;
use App\Http\Controllers\Controller;

use App\Models\Rents;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
class RentsController extends Controller
{
    public function index()
    {
        $data=Rents::latest()->where("status","pending")->get();
        if(!$data->isEmpty()){

            return response()->json(["data"=>$data,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"There is No Data","status"=>Response::HTTP_NO_CONTENT ]);

        }
    }
    public function store(Request $request)
    {
        $validate=Validator($request->all(),[
            'factories_id' => 'required|exists:factories,id',  // Ensure the utility exists
            'name' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $validate["status"]="pending";
            Rents::create($validate);
            return response()->json(['message' => 'utility created successfully',"status"=>Response::HTTP_CREATED]);
        }
    }

    public function show($id)
    {
        $data= Rents::find($id);
        if($data!=null){
            return response()->json(["data"=>$data,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"there is no utility","status"=>Response::HTTP_NO_CONTENT ]);
        }
    }
    public function update(Request $request, $id)
    {
        $validate=Validator($request->all(),[
            'factories_id' => 'required|exists:factories,id',  // Ensure the utility exists
            'name' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $data= Rents::find($id);
            if($data!=null){
                $data->update($validate);
                return response()->json(['message' => 'utility updated successfully',"status"=>Response::HTTP_CREATED]);
            }
            else{
                return response()->json(["data"=>"there is no utility","status"=>Response::HTTP_NO_CONTENT ]);
            }
        }
    }

    public function destroy($id)
    {

        $data= Rents::find($id);
        if($data!=null){

            $data->delete();
            return response()->json(['message' => 'utility change status successfully',"status"=>Response::HTTP_CREATED]);
        }
        else{
            return response()->json(["data"=>"there is no utility","status"=>Response::HTTP_NO_CONTENT ]);
        }

    }
}
