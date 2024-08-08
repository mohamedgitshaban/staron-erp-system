<?php

namespace App\Http\Controllers\Technical;
use App\Http\Controllers\Controller;
use App\Models\QcApplecation;
use App\Models\qcApplecationItem;
use Illuminate\Http\Request;

class QcApplecationController extends Controller
{
    private $QcApplecationItemController;
    public function __construct(QcApplecationItemController $QcApplecationItemController)
    {
        $this->QcApplecationItemController = $QcApplecationItemController;
    }

    public function store($request)
    {
        QcApplecation::where('technecal_requests_id', $request["id"])->delete();
        foreach($request["qc"] as $applecation){
            $applecation["technecal_requests_id"]=$request["id"];
            $index=QcApplecation::create($applecation);
            $NewItem["items"]=$applecation['items'];
            $NewItem["qc_applecations_id"]=$index->id;
            $this->QcApplecationItemController->store($NewItem);
        }
    }


}
