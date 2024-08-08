<?php

namespace App\Http\Controllers\Technical\CuttingList;
use App\Http\Controllers\Controller;
use App\Models\CuttingDataPieces;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CuttingDataPiecesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store($cuttingData , $request)
    {
        $types = ['top', 'bottom', 'left side', 'right side', 'back side'];

        foreach ($types as $type) {
            $cuttingDataPieces = new CuttingDataPieces();
            $cuttingDataPieces->cutting_data_id = $cuttingData->id;
            $cuttingDataPieces->type = $type;
            if($type=="top"||$type=="bottom"){
                $cuttingDataPieces->low_hight = $cuttingData->low_hight;
                $cuttingDataPieces->high_hight = $cuttingData->high_hight;
                $cuttingDataPieces->low_width = $cuttingData->low_width;
                $cuttingDataPieces->high_width = $cuttingData->high_width;
                $cuttingDataPieces->low_length = 0.1;
                $cuttingDataPieces->high_length = 0.2;
            }
            else if ($type=="left side"||$type=="right side") {

                    $cuttingDataPieces->low_hight = $cuttingData->low_length-0.4;
                    $cuttingDataPieces->high_hight = $cuttingData->high_length-0.2;
                    $cuttingDataPieces->low_width = $cuttingData->low_hight;
                    $cuttingDataPieces->high_width = $cuttingData->high_hight;
                    $cuttingDataPieces->low_length = 0.1;
                    $cuttingDataPieces->high_length = 0.2;


            } else {
                $cuttingDataPieces->low_hight = $cuttingData->low_length-0.4;
                $cuttingDataPieces->high_hight = $cuttingData->high_length-0.2;
                $cuttingDataPieces->low_width = $cuttingData->low_hight-0.4;
                $cuttingDataPieces->high_width = $cuttingData->high_hight-0.2;
                $cuttingDataPieces->low_length = 0.1;
                $cuttingDataPieces->high_length = 0.2;
            }

            $cuttingDataPieces->save();
        }
        if ($request["model"]=="One Door"){
            $cuttingDataPieces = new CuttingDataPieces();
            $cuttingDataPieces->cutting_data_id = $cuttingData->id;
            $cuttingDataPieces->type = "One Door";
            $cuttingDataPieces->low_hight = $cuttingData->low_length-0.4;
            $cuttingDataPieces->high_hight = $cuttingData->high_length-0.2;
            $cuttingDataPieces->low_width = $cuttingData->low_hight-0.4;
            $cuttingDataPieces->high_width = $cuttingData->high_hight-0.2;
            $cuttingDataPieces->low_length = 0.1;
            $cuttingDataPieces->high_length = 0.2;
            $cuttingDataPieces->save();

        }
        else{
            $cuttingDataPieces = new CuttingDataPieces();
            $cuttingDataPieces->cutting_data_id = $cuttingData->id;
            $cuttingDataPieces->type = "left Door";
            $cuttingDataPieces->low_hight = $cuttingData->low_length-0.4;
            $cuttingDataPieces->high_hight = $cuttingData->high_length-0.2;
            $cuttingDataPieces->low_width = ($cuttingData->low_hight-0.4)/2;
            $cuttingDataPieces->high_width = ($cuttingData->high_hight-0.2)/2;
            $cuttingDataPieces->low_length = 0.1;
            $cuttingDataPieces->high_length = 0.2;
            $cuttingDataPieces->save();
            $cuttingDataPieces = new CuttingDataPieces();
            $cuttingDataPieces->cutting_data_id = $cuttingData->id;
            $cuttingDataPieces->type = "right Door";
            $cuttingDataPieces->low_hight = $cuttingData->low_length-0.4;
            $cuttingDataPieces->high_hight = $cuttingData->high_length-0.2;
            $cuttingDataPieces->low_width = ($cuttingData->low_hight-0.4)/2;
            $cuttingDataPieces->high_width = ($cuttingData->high_hight-0.2)/2;
            $cuttingDataPieces->low_length = 0.1;
            $cuttingDataPieces->high_length = 0.2;
            $cuttingDataPieces->save();

        }
    }

    public function show($id)
    {
        $data=CuttingDataPieces::find($id);
        if($data!=null){

            return response()->json(["data"=>$data,"status"=>Response::HTTP_OK ]);

        }
        else{
            return response()->json(["data"=>"no data","status"=>Response::HTTP_NOT_FOUND ]);
        }
    }

    public function update(Request $request, CuttingDataPieces $cuttingDataPieces)
    {
        //
    }
    public function destroy($id)
    {
        $data=CuttingDataPieces::find($id);
        if($data!=null){
            $data->delete();
            return response()->json(["data"=>"data deleted","status"=>Response::HTTP_OK ]);

        }
        else{
            return response()->json(["data"=>"no data","status"=>Response::HTTP_NOT_FOUND ]);
        }
    }
}
