<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;

use App\Models\Qutation;
use App\Models\SalesCrm;
use Illuminate\Http\Request;
use App\Mail\SendQutation;
use App\Mail\SendDrafting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
class QutationController extends Controller
{

    public function Submitqutation($id,$validation)
    {
        $latestQutation = Qutation::where('sales_crms_id', $id)->latest()->first();

        if ($latestQutation) {
            $latestQutation->Qutationstatus = 'pending client approve';
            // $latestQutation->Qutationenddate = now();
            $latestQutation->Qutationdata = $validation;
            $latestQutation->save();
            return response()->json(['message' => 'drafting uploaded successfuly.',"status"=>Response::HTTP_OK], 200);

        }

        return response()->json(['message' => 'No Qutation found for the provided sales_crms_id.',"status"=>Response::HTTP_NOT_FOUND], 200);
    }
    public function Inprogress($id)
    {
        $latestQutation = Qutation::where('sales_crms_id', $id)->latest()->first();

        if ($latestQutation) {
            $latestQutation->Qutationstatus = 'in progress';
            $salesCrm = SalesCrm::with("client","technecalRequests.qcApplecations.qcApplecationItem","assignedBy.supervisor",)->find($id);
            $latestQuotation = $salesCrm->getLatestQutationAttribute();
            Mail::to("mohamed.shaban@staronegypt.com.eg")->send(new SendDrafting($salesCrm,$latestQuotation));
            $latestQutation->save();
            return response()->json(['message' => 'Qutation in progress successfully.',"status"=>Response::HTTP_OK], 200);
        }

        return response()->json(['message' => 'No Qutation found for the provided sales_crms_id.',"status"=>Response::HTTP_NOT_FOUND], 200);
    }
    public function reject($id,$validation)
    {
        $latestQutation = Qutation::where('sales_crms_id', $id)->latest()->first();

        if ($latestQutation) {
            $latestQutation->Qutationstatus = 'reject';
            $latestQutation->reason = $validation;
            $latestQutation->save();
            return response()->json(['message' => 'Qutation rejected successfully.']);
        }

        return response()->json(['message' => 'No Qutation found for the provided sales_crms_id.',"status"=>Response::HTTP_NOT_FOUND], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data["Qutationstartdate"] = now();
        Qutation::create($data);

        $salesCrm = SalesCrm::with("client","technecalRequests.qcApplecations.qcApplecationItem","assignedBy.supervisor",)->find($request->input('sales_crms_id'));

        if ($salesCrm) {
            $salesCrm = SalesCrm::with("client","technecalRequests.qcApplecations.qcApplecationItem","assignedBy.supervisor",)->find($data["sales_crms_id"]);
            $latestQuotation = $salesCrm->getLatestQutationAttribute();
            Mail::to("mohamed.shaban@staronegypt.com.eg")->send(new SendQutation($salesCrm,$latestQuotation));
        } else {
            return response()->json(['error' => 'Sales CRM not found'], 404);
        }
    }

}
