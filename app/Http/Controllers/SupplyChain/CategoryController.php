<?php

namespace App\Http\Controllers\SupplyChain;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $data = Category::orderBy('created_at', 'desc')->get();
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

        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
         Category::create($validate);
        return response()->json(['message' => 'data created successfully',"status"=>Response::HTTP_ACCEPTED]);
    }
    }

    // Update function with validation using id and requested data
    public function update(Request $request, $id)
    {
        $validate = Validator($request->all(),[
            'name' => 'required|string|max:255',
            
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $Category = Category::find($id);
            if($Category!=null){
                $Category->update($validate);
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
        $Category = Category::find($id);

        if($Category!=null){
            return response()->json(["data"=>$Category,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>404]);

        }
    }

    // Destroy function to delete data by id
    public function destroy($id)
    {
        $Category = Category::find($id);
        if($Category!=null){
            $Category->delete();
            return response()->json(["data"=>"Data deleted Success","status"=>202]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>404]);

        }


    }
}
