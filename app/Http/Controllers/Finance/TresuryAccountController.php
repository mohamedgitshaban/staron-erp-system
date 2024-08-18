<?php
namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\TresuryAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TresuryAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data=TresuryAccount::latest()->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getallrequests()
    {
        $data=TresuryAccount::latest()->where("type","depit")->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getallcollection()
    {
        $data=TresuryAccount::latest()->where("type","collection")->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getARcollection()
    {
        $data = TresuryAccount::latest()
                ->where('type', 'collection')
                ->whereDate('collection_date', '>', Carbon::today())
                ->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getApRequest()
    {
        $data = TresuryAccount::latest()
                ->where('type', 'depit')
                ->whereDate('collection_date', '>', Carbon::today())
                ->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getCashflowHistory()
    {
        $data=TresuryAccount::latest()->whereDate('collection_date', '<', Carbon::today())->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function getBankChecks()
    {
        $data=TresuryAccount::latest()->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }

    public function getTresuryRequests()
    {
        $data=TresuryAccount::latest()->get();
        if($data->isEmpty()){
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
        else{
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
    }
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(),[
            'debit_id' => 'required|exists:chart_accounts,id|integer',
            'debit_account_description' => 'required|string|max:255',
            'credit_id' => 'required|exists:chart_accounts,id|integer',
            'credit_account_description' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'value' => 'required|integer|min:1',
            'collection_date' => 'required|date|after:today',
            'collection_type' => 'required|string|max:100',
            'type' => 'required|string|max:50',
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data=TresuryAccount::find($id);
        if($data!=null){
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);

        }
        else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TresuryAccount $tresuryAccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TresuryAccount $tresuryAccount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data=TresuryAccount::find($id);
        if($data!=null){
            $data->delete();
            return response()->json(["data" => "data deleted succesfuly", "status" => Response::HTTP_OK], 200);

        }
        else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }
}
