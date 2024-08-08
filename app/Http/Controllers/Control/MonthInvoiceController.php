<?php

namespace App\Http\Controllers\Control;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OperationMonthlyScPlan;
use App\Models\OperationActualInvoiceIn;
use App\Models\FinanceActualCollection;
use App\Models\MonthInvoice;
use Carbon\Carbon;
class MonthInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $MonthInvoice=MonthInvoice::latest()->get();
        if(!$MonthInvoice->isEmpty()){
            $MonthInvoice->transform(function ($invoice) {
                $invoice->created_date = Carbon::parse($invoice->created_at)->toDateString();
                $invoice->created_time = Carbon::parse($invoice->created_at)->toTimeString();
                unset($invoice->created_at);
                return $invoice;
            });
            $resultArray = [];
            foreach ($MonthInvoice as $invoice) {
                $month = date('m', strtotime($invoice->created_date)); // Extract month
                $year = date('Y', strtotime($invoice->created_date)); // Extract year
                $OperationMonthlyScPlan = OperationMonthlyScPlan::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->get();
                $OperationActualInvoiceIn = OperationActualInvoiceIn::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();
                $FinanceActualCollection = FinanceActualCollection::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();
                $resultArray[] = [
                    "id" => $invoice->id,
                    "date" => $invoice->created_date,
                    "time" => $invoice->created_time,
                    "invoice" => $invoice->invoicein,
                    "OperationMonthlyScPlan" => $OperationMonthlyScPlan,
                    "OperationActualInvoiceIn" => $OperationActualInvoiceIn,
                    "FinanceActualCollection" => $FinanceActualCollection,
                ];
            }

            return response()->json(["data"=>$resultArray,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is No Data","status"=>404]);

        }
    }


    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [

            'invoicein' => 'required|file',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors(),$request->all()],"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
        }
        $validator = $validatedData->validated();
        $file=$request->file('invoicein');
        $fileName = time().'.'.$file->getClientOriginalExtension();
        $validator["invoicein"]= '/uploads/invoicein/'.$fileName;
        // Move the file to the desired location
        $file->move(public_path('uploads/invoicein'), $fileName);
        // Validation passed, create the user
        MonthInvoice::create($validator);

        return response()->json(['message' => 'Month Invoice created successfully',"status"=> Response::HTTP_OK]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $User=MonthInvoice::find($id);
        if($User!=null){
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no MonthInvoice","status"=>404]);

        }

    }
    public function update(Request $request, $id)
    {
        if ($request->hasFile('invoicein')) {
            $file=$request->file('invoicein');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $validator["invoicein"]='/uploads/invoicein'. $fileName;
            // Move the file to the desired location
            $file->move(public_path('uploads/invoicein'), $fileName);
        }

        // Validation passed, create the user
        MonthInvoice::where('id',$id)->update($validator);

        return response()->json(['message' => 'User updated successfully',"status"=>Response::HTTP_OK], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $User=MonthInvoice::find($id);
        if($User!=null){
            $User->delete();
            return response()->json(["data"=>"month invoice deleted","status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no MonthInvoice","status"=>404]);

        }
    }
}
