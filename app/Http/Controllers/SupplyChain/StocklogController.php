<?php

namespace App\Http\Controllers\SupplyChain;
use App\Http\Controllers\Controller;
use App\Models\Stocklog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Stock;
use Illuminate\Support\Facades\Validator;

class StocklogController extends Controller
{
    public function index()
    {
        $stockLogs = StockLog::with(['stock', 'supplier', 'salesCrm'])
        ->where("status","!=","completed")->get();
        if(!$stockLogs->isEmpty()){
            return response()->json(["data"=>$stockLogs,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>Response::HTTP_NO_CONTENT ]);

        }
    }
    public function StockLog()
    {
        $stockLogs = StockLog::with(['stock', 'supplier', 'salesCrm'])
        ->where("status","completed")->get();
        if(!$stockLogs->isEmpty()){
            return response()->json(["data"=>$stockLogs,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>Response::HTTP_NO_CONTENT ]);

        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:project,procurment,Returned',
            'stocksid' => 'required|exists:stocks,id',
            'Note' => 'nullable|string',
            'quantity' => 'required|numeric',
            'sales_crmsid' => 'nullable|exists:sales_crms,id',
            'supplyersid' => 'nullable|exists:supplyers,id',
            'file' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        else{
            $data = $validator->validated();
            $stock = Stock::find($data['stocksid']);

            $stockLog = new StockLog();
            $stockLog->type = $data['type'];
            $stockLog->status = 'pending finance';
            $stockLog->stocksid = $data['stocksid'];
            $stockLog->Note = $data['Note'] ?? null;
            $stockLog->quantity = $data['quantity'];
            $stockLog->source = 'control office';
            $stockLog->cost = $data['quantity'] * $stock->priceperunit;
           if($request->file('file')){
            $file=$request->file('file');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $stockLog->file= '/uploads/stockLog/'.$fileName;
            // Move the file to the desired location
            $file->move(public_path('uploads/stockLog'), $fileName);
           }
           else{
            $stockLog->file=null;
           }
        //    $Stock=Stock::find($data['stocksid']);
            if ($data['type'] == 'project' || $data['type'] == 'Returned') {
                $stockLog->sales_crmsid = $data['sales_crmsid'];
                $stockLog->supplyersid = null;
            } else {
                $stockLog->supplyersid = $data['supplyersid'];
                $stockLog->sales_crmsid = null;
            }

            $stockLog->save();

            return response()->json(['message' => 'StockLog created successfully', 'data' => $stockLog], 201);
        }

    }

    public function show($id)
    {
        $stockLog = StockLog::with(['stock', 'supplier', 'salesCrm'])->find($id);

        if (!$stockLog) {
            return response()->json(['message' => 'StockLog not found'], 404);
        }

        return response()->json($stockLog);
    }
    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:project,procurment,Returned',
            'stocksid' => 'required|exists:stocks,id',
            'Note' => 'nullable|string',
            'quantity' => 'required|numeric',
            'sales_crmsid' => 'nullable|exists:sales_crms,id',
            'supplyersid' => 'nullable|exists:supplyers,id',
            'file' => 'nullable|file',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        else{
            $data = $validator->validated();
            $stockLog = StockLog::find($id);
            if($stockLog!=null&&$stockLog->status == 'pending finance'){
                $stock = Stock::find($data['stocksid']);
                $stockLog->type = $data['type'];
                $stockLog->status = 'pending finance';
                $stockLog->stocksid = $data['stocksid'];
                $stockLog->Note = $data['Note'] ?? null;
                $stockLog->quantity = $data['quantity'];
                $stockLog->source = 'control office';
                $stockLog->cost = $data['quantity'] * $stock->priceperunit;
               if($request->file('file')){
                $file=$request->file('file');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $stockLog->file= '/uploads/stockLog/'.$fileName;
                // Move the file to the desired location
                $file->move(public_path('uploads/stockLog'), $fileName);
               }
               else{
                $stockLog->file=null;
               }
            //    $Stock=Stock::find($data['stocksid']);
                if ($data['type'] == 'project' || $data['type'] == 'Returned') {
                    $stockLog->sales_crmsid = $data['sales_crmsid'];
                    $stockLog->supplyersid = null;
                } else {
                    $stockLog->supplyersid = $data['supplyersid'];
                    $stockLog->sales_crmsid = null;
                }

                $stockLog->save();

                return response()->json(['message' => 'StockLog created successfully', 'data' => $stockLog], 201);
            }
            else{
                return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

            }
        }
    }
    public function destroy($id)
    {
        $StockLog = StockLog::find($id);
        if($StockLog!=null)
        {
            $StockLog->delete();
            return response()->json(["data"=>"Data deleted Success","status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }
    }
}
