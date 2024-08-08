<?php

namespace App\Http\Controllers\Technical;
use App\Http\Controllers\Controller;
use App\Models\SalesCrm;
use App\Http\Controllers\Sales\QutationController;
use App\Models\TechnecalRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TechnecalRequestController extends Controller
{
    // private $SalesCrmController;
    private $QcApplecationController;
    private $QutationController;
    public function __construct(QutationController $QutationController,QcApplecationController $QcApplecationController)
    {
        // $this->SalesCrmController = $SalesCrmController;
        $this->QutationController = $QutationController;
        $this->QcApplecationController = $QcApplecationController;
    }
    public function index()
    {
        $authUser = Auth::user();
        // dd($authUser->id);
        if($authUser->Supervisor==1||$authUser->Supervisor==null){
            $User=TechnecalRequest::with("salesCrm.client","User.supervisor","qcApplecations")->orderBy('technecal_requests.created_at', 'desc')->get();
        }
        else{
            $User=TechnecalRequest::with("salesCrm.client","User.supervisor","qcApplecations")->where("asign_for_user",$authUser->id)->orderBy('technecal_requests.created_at', 'desc')->get();

        }
        $User->transform(function($data){
            $data->created_date = Carbon::parse($data->created_at)->toDateString();
            $data->created_time = Carbon::parse($data->created_at)->toTimeString();
            unset($data->created_at);
            return $data;
        });
        if(!$User->isEmpty()){
            return response()->json(["data"=>$User,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"There is No Data","status"=>404]);

        }

    }
    public function store($id)
    {
        $validator["qcstartdate"]=now();
        $validator["qcstatus"]="pending assign";
        $TechnecalRequest=TechnecalRequest::select("id","sales_crms_id")->where("sales_crms_id","=",$id)->get();
        if (!$TechnecalRequest->empty()) {

            TechnecalRequest::where("sales_crms_id","=",$id)->update($validator);
            // $this->SalesCrmController->updatestatus($id,"pending technical team");
        }
        else{
        $validator["sales_crms_id"]=$id;
         TechnecalRequest::create($validator);
        }
    }
    public function assign($id,Request $request)
    {
        $validate = Validator::make($request->all(), [
            'asign_for_user' =>  'required|exists:users,id',
        ]);


        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        try{
            $data=TechnecalRequest::find($id);

            if($data){
                $validate=$validate->validated();

                if($data->qcstatus=="pending assign"&& $data->asign_for_user==null){
                    $data->qcstatus = "pending";
                    $data->asign_for_user = $validate["asign_for_user"];
                    $salesCrm = $data->salesCrm;
                    $salesCrm->status = "pending technical team";

                    $data->save();
                    $salesCrm->save(); // Save the updated salesCrm status
                    return response()->json(['massage' =>"task assigned succesful" ,"status"=>200] );

                }
                else{
                    return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

                }
            }
            else{
                return response()->json(['massage' =>"no data" ,"status"=>Response::HTTP_NOT_FOUND],200 );

            }

        }catch(\Exception  $e){
            return response()->json(['error' => 'Something went wrong',"status"=>500] );

        }
    }
    public function starttask($id)
    {
        try{
            $data=TechnecalRequest::find($id);

            if($data){
                if($data->qcstatus=="pending"||$data->qcstatus=="reject back"||$data->qcstatus=="Qutation Back"){
                    $authUser = Auth::user();
                   if($authUser->id==$data->asign_for_user){
                    $data->qcstatus = "in progress";
                    $salesCrm = $data->salesCrm;
                    $salesCrm->status = "technical team in progress";

                    $data->save();
                    $salesCrm->save(); // Save the updated salesCrm status
                    return response()->json(['massage' =>"task started succesful" ,"status"=>200] );
                   }
                   else{
                    return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

                   }

                }
                else{
                    return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

                }
            }
            else{
                return response()->json(['massage' =>"no data" ,"status"=>Response::HTTP_NOT_FOUND],200 );

            }

        }catch(\Exception  $e){
            return response()->json(['error' => 'Something went wrong',"status"=>500] );

        }
    }
    public function SendQC(Request $request,$id)
    {
        $TechnecalRequest=TechnecalRequest::find($id);
        if ($TechnecalRequest) {
            $authUser = Auth::user();
        if (($authUser->id==$TechnecalRequest->asign_for_user)||($authUser->id==$TechnecalRequest->User->Supervisor)) {
            if($authUser->Supervisor==1||$authUser->Supervisor==null){
                return $this->Completed($request,$id,$TechnecalRequest);
            }
            else{
                return $this->reviewRequest($request,$id);
            }
        }
        else{
            return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

        }
        }
        else{
            return response()->json(['massage' =>"no data" ,"status"=>Response::HTTP_NOT_FOUND],200 );
        }
    }
    public function show($id) {
        $Client= TechnecalRequest::with("salesCrm.client","User.supervisor","qcApplecations.qcApplecationItem")->find($id);
        if($Client!=null){
            unset($Client->asignby);
            $Client->qc=$Client->qcApplecations;
            unset($Client->qcApplecations);
            return response()->json(["data"=>$Client,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"there is no Client","status"=>Response::HTTP_NO_CONTENT ]);
        }
    }
    private function Completed($request,$id,$TechnecalRequest){
        $validate = Validator::make($request->all(), [
            'qcdata' => 'nullable|file',
            "totalcost"=>"nullable|numeric|min:1",
            "TotalProjectSellingPrice"=>"required|numeric|min:0",
            "ProjectGrossMargin"=>"required|numeric|min:0",
            'qc' => 'required|array|min:1',
            'qc.*.name' => 'required|string|max:255',
            'qc.*.totalcost' => 'nullable|numeric|min:0',
            'qc.*.grossmargen' => 'nullable|numeric|min:0',
            'qc.*.salingprice' => 'nullable|numeric|min:0',
            'qc.*.items' => 'required|array|min:1',
            'qc.*.items.*.stockid' => 'required|string|max:255',
            'qc.*.items.*.price' => 'nullable|numeric|min:0',
            'qc.*.items.*.quantity' => 'required|numeric|min:0',
            'qc.*.items.*.description' => 'nullable|string',
        ]);


        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
             if($TechnecalRequest->qcstatus=="in progress"||$TechnecalRequest->qcstatus=="Re-calculation"){
              if ($request->hasFile('qcdata')) {
                 $file=$request->file('qcdata');
                 $fileName = time().'.'.$file->getClientOriginalExtension();
                 $TechnecalRequest->qcdata= '/uploads/qcdata/'.$fileName;
                 $file->move(public_path('uploads/qcdata'), $fileName);
                 }
                 $Applecation["id"]=$TechnecalRequest->id;
                 $Applecation["qc"]=$validate["qc"];
                 $this->QcApplecationController->store($Applecation);
                 $TechnecalRequest->totalprice=$validate["totalcost"];
                 $TechnecalRequest->qcstatus="completed";
                 $TechnecalRequest->qcenddate=now();
                 $salesCrm = $TechnecalRequest->salesCrm;
                 $salesCrm->status = "pending qutation approve";
                 $salesCrm->save();
                 $TechnecalRequest->save();
                 $qutation["sales_crms_id"]=$salesCrm->id;
                 $qutation["TotalProjectSellingPrice"]=$validate["TotalProjectSellingPrice"];
                 $qutation["ProjectGrossMargin"]=$validate["ProjectGrossMargin"];
                 $this->QutationController->store(new Request($qutation));

                 return response()->json(['message' => 'quantity servay created successfully',"status"=> Response::HTTP_OK]);
             }
             else{
                 return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED ]);

             }
        }
    }
    private function reviewRequest($request,$id)
    {
        $validate = Validator::make($request->all(), [
            'qcdata' => 'nullable|file',
            'qc' => 'required|array|min:1',
            'qc.*.name' => 'required|string|max:255',
            'qc.*.items' => 'required|array|min:1',
            'qc.*.items.*.stockid' => 'required|string|max:255',
            'qc.*.items.*.quantity' => 'nullable|numeric|min:0',
            'qc.*.items.*.description' => 'nullable|string',
        ]);


        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $TechnecalRequest=TechnecalRequest::find($id);
            if($TechnecalRequest!=null){
             if($TechnecalRequest->qcstatus=="in progress"){
              if ($request->hasFile('qcdata')) {
                 $file=$request->file('qcdata');
                 $fileName = time().'.'.$file->getClientOriginalExtension();
                 $TechnecalRequest->qcdata= '/uploads/qcdata/'.$fileName;
                 $file->move(public_path('uploads/qcdata'), $fileName);
                 }
                 $Applecation["id"]=$TechnecalRequest->id;
                 $Applecation["qc"]=$validate["qc"];
                 $TechnecalRequest->totalprice=$this->QcApplecationController->store($Applecation);
                 $TechnecalRequest->qcstatus="in review";
                 $TechnecalRequest->qcenddate=now();
                 $TechnecalRequest->save();
                 return response()->json(['message' => 'quantity servay created successfully',"status"=> Response::HTTP_OK]);
             }
             else{
                 return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED ]);

             }
        }}
    }
    public function ManagerReview(Request $request,$id)
    {
        $validate = Validator::make($request->all(), [
            "totalcost"=>"nullable|numeric|min:1",
            "TotalProjectSellingPrice"=>"required|numeric|min:0",
            "ProjectGrossMargin"=>"required|numeric|min:0",
            'qc' => 'required|array|min:1',
            'qc.*.name' => 'required|string|max:255',
            'qc.*.totalcost' => 'nullable|numeric|min:0',
            'qc.*.grossmargen' => 'nullable|numeric|min:0',
            'qc.*.salingprice' => 'nullable|numeric|min:0',
            'qc.*.items' => 'required|array|min:1',
            'qc.*.items.*.stockid' => 'required|string|max:255',
            'qc.*.items.*.price' => 'nullable|numeric|min:0',
            'qc.*.items.*.quantity' => 'required|numeric|min:0',
            'qc.*.items.*.description' => 'nullable|string',
        ]);


        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $TechnecalRequest=TechnecalRequest::find($id);

            $validate=$validate->validated();
             if($TechnecalRequest->qcstatus=="in review"||$TechnecalRequest->qcstatus=="Re-calculation"){
                 $Applecation["id"]=$TechnecalRequest->id;
                 $Applecation["qc"]=$validate["qc"];
                 $this->QcApplecationController->store($Applecation);
                 $TechnecalRequest->totalprice=$validate["totalcost"];
                 $TechnecalRequest->qcstatus="completed";
                 $TechnecalRequest->qcenddate=now();
                 $salesCrm = $TechnecalRequest->salesCrm;
                 $salesCrm->status = "pending qutation approve";
                 $salesCrm->save();
                 $TechnecalRequest->save();
                 $qutation["sales_crms_id"]=$salesCrm->id;
                 $qutation["TotalProjectSellingPrice"]=$validate["TotalProjectSellingPrice"];
                 $qutation["ProjectGrossMargin"]=$validate["ProjectGrossMargin"];
                  $this->QutationController->store(new Request($qutation));

                 return response()->json(['message' => 'quantity servay created successfully',"status"=> Response::HTTP_OK]);
             }
             else{
                 return response()->json(["data"=>"method not allowed","status"=>Response::HTTP_METHOD_NOT_ALLOWED ]);

             }
        }
    }
    public function RejectTask(Request $request,$id)
    {
        $validator=Validator($request->all(),[

            'qcstatus' => 'required|string|in:out of scope,request for information',
            'reason' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => [$validator->errors(),$request->all()],"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
        }
        else{
            try{
                $validator=$validator->Validated();
                $data=TechnecalRequest::find($id);

                if($data){
                    if($data->qcstatus=="pending assign"){
                        $data->qcstatus=$validator["reason"];
                        $data->qcstatus="rejected";
                        $validate["status"]=$validator["qcstatus"];
                        SalesCrm::where("id",$data->sales_crms_id)->update($validate);
                        $data->save();
                        return response()->json(['massage' =>"task rejected succesful" ,"status"=>200] );

                    }
                    else{
                        return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

                    }
                }
                else{
                    return response()->json(['massage' =>"no data" ,"status"=>Response::HTTP_NOT_FOUND],200 );

                }
            }catch(\Exception  $e){
                return response()->json(['error' => $e,"status"=>500] );

            }
        }
    }
    public function rejectreview(Request $request,$id)
    {
        $validator=Validator($request->all(),[

            // 'qcstatus' => 'required|string|in:out of scope,Request for Information',
            'reason' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => [$validator->errors(),$request->all()],"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
        }
        else{
            try{
                $validator=$validator->Validated();
                $data=TechnecalRequest::find($id);
                $authUser = Auth::user();

                if($data){
                    if(($data->qcstatus=="in review"||$data->qcstatus=="Qutation Back")&&$authUser->id==$data->User->supervisor->id){
                        $data->qcstatus=$validator["reason"];
                        $data->qcstatus="reject back";
                        $data->save();
                        return response()->json(['massage' =>"task rejected succesful" ,"status"=>200] );

                    }
                    else{
                        return response()->json(['massage' =>"method not allwoed" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

                    }
                }
                else{
                    return response()->json(['massage' =>"no data" ,"status"=>Response::HTTP_NOT_FOUND],200 );

                }
            }catch(\Exception  $e){
                return response()->json(['error' => $e,"status"=>500] );

            }
        }
    }
    public function QutationReject($id , $validation){
        $latestQutation = TechnecalRequest::where('sales_crms_id', $id)->latest()->first();

        if ($latestQutation) {
            $latestQutation->qcstatus = 'Qutation Back';
            $latestQutation->reason = $validation;
            $latestQutation->save();
            return response()->json(['message' => 'Qutation rejected successfully.']);
        }

        return response()->json(['message' => 'No Qutation found for the provided sales_crms_id.',"status"=>Response::HTTP_NOT_FOUND], 200);
    }
    public function QutationRcalc($id , $validation){
        $latestQutation = TechnecalRequest::where('sales_crms_id', $id)->latest()->first();

        if ($latestQutation) {
            $latestQutation->qcstatus = 'Re-calculation';
            $latestQutation->reason = $validation;
            $latestQutation->save();
            return response()->json(['message' => 'Qutation rejected successfully.']);
        }

        return response()->json(['message' => 'No Qutation found for the provided sales_crms_id.',"status"=>Response::HTTP_NOT_FOUND], 200);
    }
}
