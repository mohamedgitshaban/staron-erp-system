<?php

namespace App\Http\Controllers\SupplyChain;
use App\Http\Controllers\Controller;
use App\Models\Supplyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
class SupplyerController extends Controller
{
    public function index()
    {
        $data = Supplyer::orderBy('created_at', 'desc')->get();
        if(!$data->isEmpty()){
            return response()->json(["data"=>$data,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>" No Data","status"=>Response::HTTP_NO_CONTENT ]);

        }
    }

    // Store function with validation
    public function store(Request $request)
    {
        $validate = Validator($request->all(),[
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'source' => 'required|string|max:255',
            'additondata' => 'nullable|string|max:255',
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
         Supplyer::create($validate);
        return response()->json(['message' => 'data created successfully',"status"=>Response::HTTP_ACCEPTED]);
    }
    }

    // Update function with validation using id and requested data
    public function update(Request $request, $id)
    {
        $validate = Validator($request->all(),[
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'source' => 'required|string|max:255',
            'additondata' => 'nullable|string|max:255',
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $supplyer = Supplyer::find($id);
            if($supplyer!=null){
                $supplyer->update($validate);
                return response()->json(['message' => 'data updated successfully',"status"=>Response::HTTP_ACCEPTED]);
            }
            else{
                return response()->json(["data"=>"no data","status"=>404]);
            }
        }
    }

    // Show function to get data by id
    public function show($id)
    {
        $supplyer = Supplyer::find($id);

        if($supplyer!=null){
            return response()->json(["data"=>$supplyer,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>404]);

        }
    }

    // Destroy function to delete data by id
    public function destroy($id)
    {
        $supplyer = Supplyer::find($id);
        if($supplyer!=null){
            $supplyer->delete();
            return response()->json(["data"=>"Data deleted Success","status"=>202]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>404]);

        }


    }
}
