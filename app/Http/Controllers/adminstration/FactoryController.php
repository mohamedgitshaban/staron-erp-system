<?php

namespace App\Http\Controllers\adminstration;
use App\Http\Controllers\Controller;

use App\Models\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
class FactoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $data=Factory::latest()->where("factory_status","active")->get();
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
            'factory_name' => 'required|string|max:255',
            'factory_type' => 'required|in:own,rent',
            // 'factory_status' => 'required|in:active,not active',
            'factory_location' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
            'start_date' => 'required|date',
            'factory_contract_file' => 'required|file|mimes:pdf,doc,docx|max:2048', // Example for file upload validation

        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $validate["factory_status"]="active";
            Factory::create($validate);
            return response()->json(['message' => 'Factory created successfully',"status"=>Response::HTTP_CREATED]);
        }
    }

    public function show($id)
    {
        $data= Factory::find($id);
        if($data!=null){
            return response()->json(["data"=>$data,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"there is no Factory","status"=>Response::HTTP_NO_CONTENT ]);
        }
    }

    public function update(Request $request, $id)
    {
        $validate=Validator($request->all(),[
            'factory_name' => 'required|string|max:255',
            'factory_type' => 'required|in:own,rent',
            'factory_location' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
            'start_date' => 'required|date',
            'factory_contract_file' => 'required|file|mimes:pdf,doc,docx|max:2048', // Example for file upload validation
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $data= Factory::find($id);
            if($data!=null){
                $data->update($validate);
                return response()->json(['message' => 'Factory updated successfully',"status"=>Response::HTTP_CREATED]);
            }
            else{
                return response()->json(["data"=>"there is no Factory","status"=>Response::HTTP_NO_CONTENT ]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        $data= Factory::find($id);
        if($data!=null){
            $data->factory_status='not active';
            $data->save();
            return response()->json(['message' => 'Factory change status successfully',"status"=>Response::HTTP_CREATED]);
        }
        else{
            return response()->json(["data"=>"there is no Factory","status"=>Response::HTTP_NO_CONTENT ]);
        }
    }
}
