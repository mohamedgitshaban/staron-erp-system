<?php

namespace App\Http\Controllers\Control;
use App\Http\Controllers\Controller;
use App\Models\PackageData;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
class PackageDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $PackageData = PackageData::with('technecalRequest.salesCrm.client')->latest()->get();
        if(!$PackageData->isEmpty()){
            $PackageData->transform(function ($invoice) {
                $invoice->created_date = Carbon::parse($invoice->created_at)->toDateString();
                $invoice->created_time = Carbon::parse($invoice->created_at)->toTimeString();

                unset($invoice->created_at);
                return $invoice;
            });
            return response()->json(["data"=>$PackageData,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is No Data","status"=>404]);

        }

    }
    public function starttask($id)
    {
        try{
            $validate["Packedgestatus"]="in progress";
            $validate["Packedgestartdate"]=now();
            PackageData::where("id","=",$id)->update($validate);
            return response()->json(['massage' =>"task started succesful" ,"status"=>200] );

        }catch(\Exception  $e){
            return response()->json(['error' => 'Something went wrong',"status"=>500] );

        }
    }
    public function RejectTask(Request $request,$id)
    {
        $validator=Validator($request->all(),[

            'status' => 'required|string',
            'reason' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => [$validator->errors(),$request->all()],"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
        }
        else{
            try{
                $validator=$validator->Validated();

                    PackageData::where("id","=",$id)->update($validator);

                return response()->json(['massage' =>"task Rejected succesful" ,"status"=>200] );

            }catch(\Exception  $e){
                return response()->json(['error' => 'Something went wrong',"status"=>500] );

            }
        }
    }
    public function complete(Request $request,$id)
    {
        $validate=Validator($request->all(),[
            'Packedgedata' => 'required|file',

        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            if ($request->hasFile('Packedgedata')) {
                $file=$request->file('Packedgedata');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $validate["Packedgedata"]= '/uploads/Packedgedata/'.$fileName;
                $file->move(public_path('uploads/Packedgedata'), $fileName);
                $validate["Packedgestatus"]="completed";
                // $validate["Qutationstatus"]="in progress";

                $validate["Packedgeenddate"]=now();
                PackageData::where("id","=",$id)->update($validate);
                return response()->json(['message' => 'crm created successfully',"status"=> Response::HTTP_OK]);

            }
        }
    }
    public function update(Request $request, PackageData $packageData)
    {
        //
    }
    public function store($id)
    {
        $validator["technecal_requests_id"]=$id;
        PackageData::create($validator);
    }
}
