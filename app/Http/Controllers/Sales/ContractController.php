<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContractController extends Controller
{

    public function store($id,$validation)
    {
        $data["sales_crms_id"]=$id;
        $data["contractstartdate"]=now();
        $data["contractenddate"]=now();
        $data["contractdata"]=$validation["contractdata"];
        $data["contractValue"]=$validation["contractValue"];
        Contract::create($data);
        return response()->json(['message' => 'contract created successfully.',"status"=>Response::HTTP_OK]);

    }



}
