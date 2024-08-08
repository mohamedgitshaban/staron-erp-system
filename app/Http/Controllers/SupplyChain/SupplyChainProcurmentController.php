<?php

namespace App\Http\Controllers\SupplyChain;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ControlScPlan;
use App\Models\ToLocation;
use App\Models\ScPlanCost;
use Carbon\Carbon;

class   SupplyChainProcurmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ControlOperationPlan = ControlScPlan::with('ToLocation')->latest()->get();
        if(!$ControlOperationPlan->isEmpty()){
            $ControlOperationPlan->transform(function ($invoice) {
                $invoice->created_date = Carbon::parse($invoice->created_at)->toDateString();
                $invoice->created_time = Carbon::parse($invoice->created_at)->toTimeString();

                unset($invoice->created_at);
                return $invoice;
            });

            return response()->json(["data"=>$ControlOperationPlan,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is No Data","status"=>404]);

        }
    }
    // DASHBOARD
    public function Latest5()
    {
        $ControlOperationPlan = ControlScPlan::with('toLocations')->latest()->take(5)->get();
        if(!$ControlOperationPlan->isEmpty()){
            $ControlOperationPlan->transform(function ($invoice) {
                $invoice->created_date = Carbon::parse($invoice->created_at)->toDateString();
                $invoice->created_time = Carbon::parse($invoice->created_at)->toTimeString();

                unset($invoice->created_at);
                return $invoice;
            });

            return response()->json(["data"=>$ControlOperationPlan,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is No Data","status"=>404]);

        }
    }
    public function CompletedProcurements(Request $request )
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
            $ControlOperationPlan = ControlScPlan::
            whereBetween('created_at', [now()->subDays(7), now()])
            ->where("status","completed")
            ->count();
                    }
        else if($validator["filter"]=="This Month"){
            $ControlOperationPlan = ControlScPlan::
            whereMonth('created_at', [ now()->month])
            ->where("status","completed")
            ->count();

        }
        else if($validator["filter"]=="This Quarter"){
            $ControlOperationPlan = ControlScPlan::
            whereBetween('created_at', [now()->subDays(120), now()])
            ->where("status","completed")
            ->count();

        }
        else{
            $ControlOperationPlan = ControlScPlan::
            whereYear('created_at', [ now()->year])
            ->where("status","completed")
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
    public function ProcurementsPriorty(Request $request )
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
            $HighPriorty = ControlScPlan::
            whereBetween('created_at', [now()->subDays(7), now()])
            ->where("Priority",1)
            ->count();
            $MedumPriorty = ControlScPlan::
            whereBetween('created_at', [now()->subDays(7), now()])
            ->where("Priority",2)
            ->count();
            $LowPriorty = ControlScPlan::
            whereBetween('created_at', [now()->subDays(7), now()])
            ->where("Priority",3)
            ->count();
                    }
        else if($validator["filter"]=="This Month"){
            $HighPriorty = ControlScPlan::
            whereMonth('created_at', [ now()->month])
            ->where("Priority",1)
            ->count();
            $MedumPriorty = ControlScPlan::
            whereMonth('created_at', [ now()->month])
            ->where("Priority",2)
            ->count();
            $LowPriorty = ControlScPlan::
            whereMonth('created_at', [ now()->month])
            ->where("Priority",3)
            ->count();


        }
        else if($validator["filter"]=="This Quarter"){

            $HighPriorty = ControlScPlan::
            whereBetween('created_at', [now()->subDays(120), now()])
            ->where("Priority",1)
            ->count();
            $MedumPriorty = ControlScPlan::
            whereBetween('created_at', [now()->subDays(120), now()])
            ->where("Priority",2)
            ->count();
            $LowPriorty = ControlScPlan::
            whereBetween('created_at', [now()->subDays(120), now()])
            ->where("Priority",3)
            ->count();
        }
        else{


            $HighPriorty = ControlScPlan::
            whereYear('created_at', [ now()->year])
            ->where("Priority",1)
            ->count();
            $MedumPriorty = ControlScPlan::
            whereYear('created_at', [ now()->year])
            ->where("Priority",2)
            ->count();
            $LowPriorty = ControlScPlan::
            whereYear('created_at', [ now()->year])
            ->where("Priority",3)
            ->count();
        }


            return response()->json(["data"=>[
                "HighPriorty"=>$HighPriorty,
                "MedumPriorty"=>$MedumPriorty,
                "LowPriorty"=>$LowPriorty,
                "total"=>$HighPriorty+$MedumPriorty+$LowPriorty
            ],"status"=>200]);


       }

    }
    public function RejectedProcurements(Request $request )
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
            $ControlOperationPlan = ControlScPlan::
            whereBetween('created_at', [now()->subDays(7), now()])
            ->where("status","un acceptable")
            ->count();
                    }
        else if($validator["filter"]=="This Month"){
            $ControlOperationPlan = ControlScPlan::
            whereMonth('created_at', [ now()->month])
            ->where("status","un acceptable")
            ->count();

        }
        else if($validator["filter"]=="This Quarter"){
            $ControlOperationPlan = ControlScPlan::
            whereBetween('created_at', [now()->subDays(120), now()])
            ->where("status","un acceptable")
            ->count();

        }
        else{
            $ControlOperationPlan = ControlScPlan::
            whereYear('created_at', [ now()->year])
            ->where("status","un acceptable")
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
    public function CostsCollected(Request $request )
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
            $ControlOperationPlan = ControlScPlan::
            whereBetween('created_at', [now()->subDays(7), now()])
            ->whereIn("status",["Money ready","in review","un acceptable","completed"])
            ->sum("price");
              }
        else if($validator["filter"]=="This Month"){
            $ControlOperationPlan = ControlScPlan::
            whereMonth('created_at', [ now()->month])
            ->whereIn("status",["Money ready","in review","un acceptable","completed"])
            ->sum("price");

        }
        else if($validator["filter"]=="This Quarter"){
            $ControlOperationPlan = ControlScPlan::
            whereBetween('created_at', [now()->subDays(120), now()])
            ->whereIn("status",["Money ready","in review","un acceptable","completed"])
            ->sum("price");

        }
        else{
            $ControlOperationPlan = ControlScPlan::
            whereYear('created_at', [ now()->year])
            ->whereIn("status",["Money ready","in review","un acceptable","completed"])
            ->sum("price");
        }
        if($ControlOperationPlan!=null){

            return response()->json(["data"=>$ControlOperationPlan,"status"=>200]);
        }
        else{
            return response()->json(["data"=>0,"status"=>404]);

        }
       }
    }
    public function TotalCosts(Request $request )
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
            $ControlOperationPlan = ControlScPlan::
            whereBetween('created_at', [now()->subDays(7), now()])
            ->sum("price");
              }
        else if($validator["filter"]=="This Month"){
            $ControlOperationPlan = ControlScPlan::
            whereMonth('created_at', [ now()->month])
            ->sum("price");

        }
        else if($validator["filter"]=="This Quarter"){
            $ControlOperationPlan = ControlScPlan::
            whereBetween('created_at', [now()->subDays(120), now()])
            ->sum("price");

        }
        else{
            $ControlOperationPlan = ControlScPlan::
            whereYear('created_at', [ now()->year])
            ->sum("price");
        }
        if($ControlOperationPlan!=null){

            return response()->json(["data"=>$ControlOperationPlan,"status"=>200]);
        }
        else{
            return response()->json(["data"=>0,"status"=>404]);

        }
       }
    }
    public function Start($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null&&$User->status=="pending"){
            $User->status="in Progress";
            $User->save();
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }
    }
    public function RequestForMoney($id,Request $request)//REQUEST FOR MONEY FROM FINANCE
    {
        $User=ControlScPlan::find($id);
        if($User!=null&&$User->status=="in Progress"){
            $validatedData = Validator::make($request->all(), [
                'price' => 'required|integer',

            ]);
            if ($validatedData->fails()) {
                return response()->json(['errors' => [$validatedData->errors(),$request->all()],"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
            }
            $validator = $validatedData->validated();
            $User->cost=$validator["price"];
            $User->status="Wating Fainance";
            $User->pricestate="pending";

            $User->save();
            ScPlanCost::create([
                'control_sc_plan_id' => $id,
        ]);
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }

    }
    public function Complete($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null&&$User->status=="Money ready"){
            $User->status="in review";
            $User->finishdata=now();

            $User->save();
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }

    }
    public function show($id)
    {
        $User=ControlScPlan::find($id)->with('ToLocation');
        if($User!=null){
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no ControlOperationPlan","status"=>404]);

        }

    }
}
