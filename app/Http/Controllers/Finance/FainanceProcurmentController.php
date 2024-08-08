<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ControlScPlan;
use App\Models\ToLocation;
use Carbon\Carbon;
use App\Models\ScPlanCost;

class   FainanceProcurmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ControlOperationPlan = ControlScPlan::with('ToLocation')->with("ScPlanCost")->where("pricestate","!=","request")->latest()->get();
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
    public function AcceptRequestForMoney($id,Request $request)//THERE IS MONEY
    {
        $User=ControlScPlan::find($id);
        if($User!=null){
            $User->status="Money ready";
            $User->pricestate="Completed";
            $User->save();
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }

    }
    public function AcceptRequestForNoMoney($id)//THERE IS NO MONEY
    {
        $User=ControlScPlan::find($id);
        if($User!=null){
            $User->pricestate="Money Not ready";
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

    public function show($id)
    {
        $User=ControlScPlan::find($id);
        if($User!=null&&$User->pricestate!="request"){
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED]);

        }

    }
}
