<?php
namespace App\Http\Controllers\Technical;
use App\Http\Controllers\Controller;
use App\Models\qcApplecationItem;
use Exception;
use Illuminate\Http\Request;

class QcApplecationItemController extends Controller
{
    public function store($request)
    {
        try{
            foreach($request['items'] as $item){
                $item["qc_applecations_id"]=$request["qc_applecations_id"];
                qcApplecationItem::create($item);
            }
        }
        catch(Exception $e){
            dd ($e);
        }
    }

}
