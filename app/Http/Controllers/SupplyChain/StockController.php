<?php
namespace App\Http\Controllers\SupplyChain;
use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Stock::orderBy('created_at', 'desc')->with("category")->get();
        if(!$data->isEmpty()){
            return response()->json(["data"=>$data,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>Response::HTTP_NO_CONTENT ]);

        }
    }

    public function store(Request $request)
    {
        $validate = Validator($request->all(),[
            'categoriesid' => 'required|exists:categories,id',
            'code' => 'required|string|max:255|unique:stocks',
            'color' => 'required|string|max:255',
            'quantity' => 'required|numeric',
            'priceperunit' => 'required|integer',
            'lastpriceforit' => 'required|integer',
            'unit' => 'required|string|max:255',

        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            Stock::create($validate);
        return response()->json(['message' => 'data created successfully',"status"=>Response::HTTP_ACCEPTED]);
    }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $Stock = Stock::find($id);

        if($Stock!=null){
            return response()->json(["data"=>$Stock,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>404]);

        }
    }

    public function update(Request $request, $id)
    {
        $validate = Validator($request->all(),[
           'categoriesid' => 'required|exists:categories,id',
           'code' => [
            'required',
            'string',
            Rule::unique('stocks')->ignore($id),
            'max:255',
        ],
            'color' => 'required|string|max:255',
            'quantity' => 'required|numeric',
            'priceperunit' => 'required|integer',
            'lastpriceforit' => 'required|integer',
            'unit' => 'required|string|max:255',

        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $Category = Stock::find($id);
            if($Category!=null){
                $Category->update($validate);
                return response()->json(['message' => 'data updated successfully',"status"=>Response::HTTP_ACCEPTED]);
            }
            else{
                return response()->json(["data"=>"no data","status"=>404]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $Category = Stock::find($id);
        if($Category!=null){
            $Category->delete();
            return response()->json(["data"=>"Data deleted Success","status"=>202]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>404]);

        }


    }
}
