<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\MeetingLog;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MeetingLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $User=MeetingLog::latest()->with('client', 'assignedBy')->get();
        if(!$User->isEmpty()){
            return response()->json(["data"=>$User,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"There is No Data","status"=>404]);

        }
    }
    public function indexsum()
{
    $Users = Client::withCount([
        'MeetingLog as meeting_count' => function ($query) {
            $query->where('type', 'meeting');
        },
        'MeetingLog as phone_call_count' => function ($query) {
            $query->where('type', 'phone call');
        },
        'salesCrms as completed_sales_count' => function ($query) {
            $query->where('status', 'completed');
        },
        'salesCrms as incomplete_sales_count' => function ($query) {
            $query->where('status', '!=', 'completed');
        },
    ])->latest()->with("assignBy")
    ->get([
        'id', 'name', 'source', 'type'
    ]);

    if ($Users->isNotEmpty()) {
        return response()->json(["data" => $Users, "status" => 200]);
    } else {
        return response()->json(["data" => "There is No Data", "status" => 404]);
    }
}

    public function store(Request $request)
    {
        $validate=Validator($request->all(),[
            'clients_id'=>'required|exists:clients,id',
            'type'=>'required|string|max:255|in:meeting,Phone Call',
            'reason'=>'required|string|max:255',
            'result'=>'nullable|string|max:255',
            'status'=>'required|string|max:255',
            'date' => 'required|date',
            'nextactivity' => 'required|date',
            'time' => 'required|date_format:H:i',

        ]);
        $validate->sometimes('status', 'in:Attended,Re-Scheduled,No Show', function ($input) {
            return $input->type === 'meeting';
        });

        $validate->sometimes('status', 'in:Answered,Call Back,Canceled', function ($input) {
            return $input->type === 'Phone Call';
        });
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            $validate["asignby"]=Auth::id();
            MeetingLog::create($validate);
            return response()->json(['message' => 'Client created successfully',"status"=>Response::HTTP_CREATED]);

        }

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $Client= MeetingLog::find($id);
        if($Client!=null){
            return response()->json(["data"=>$Client,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"there is no Call log","status"=>Response::HTTP_NO_CONTENT ]);
        }
    }

    public function update(Request $request,  $id)
    {
        $validate=Validator($request->all(),[
            'clients_id'=>'required|exists:clients,id',
            'type'=>'required|string|max:255|in:meeting,Phone Call',
            'reason'=>'required|string|max:255',
            'result'=>'nullable|string|max:255',
            'status'=>'required|string|max:255',
            'date' => 'required|date',
            'nextactivity' => 'required|date',
            'time' => 'required|date_format:H:i',

        ]);
        $validate->sometimes('status', 'in:Attended,Re-Scheduled,No Show', function ($input) {
            return $input->type === 'meeting';
        });

        $validate->sometimes('status', 'in:Answered,Call Back,Canceled', function ($input) {
            return $input->type === 'Phone Call';
        });
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validate=$validate->validated();
            MeetingLog::where("id","=",$id)->update($validate);
            return response()->json(['message' => 'Client created successfully',"status"=>Response::HTTP_RESET_CONTENT]);

        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $MeetingLog=MeetingLog::find($id);
        if($MeetingLog!=null){
            $MeetingLog->delete();
            return response()->json(["data"=>$MeetingLog,"status"=>Response::HTTP_OK],200);
        }
        else{
            return response()->json(["data"=>"there is no call log","status"=>Response::HTTP_NO_CONTENT ],404);
        }
    }
    function score(){
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $meetingLogCount = MeetingLog::whereBetween('created_at', [$startDate, $endDate])->count();
        return response()->json(["data"=>[
            "attend"=>$meetingLogCount,
            "score"=>$meetingLogCount/40*100,
        ],"status"=>Response::HTTP_OK]);

    }
    public function meetingcount(Request $request){
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
            $ControlOperationPlan = MeetingLog::
            where("type","meeting")->
            whereBetween('created_at', [now()->subDays(7), now()])
            ->count();
              }
        else if($validator["filter"]=="This Month"){
            $ControlOperationPlan = MeetingLog::
            where("type","meeting")->
            whereMonth('created_at', [ now()->month])
            ->count();

        }
        else if($validator["filter"]=="This Quarter"){
            $ControlOperationPlan = MeetingLog::
            where("type","meeting")->
            whereBetween('created_at', [now()->subDays(120), now()])
            ->count();

        }
        else{
            $ControlOperationPlan = MeetingLog::
            where("type","meeting")->
            whereYear('created_at', [ now()->year])
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
    public function callcount(Request $request){
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
            $ControlOperationPlan = MeetingLog::
            where("type","Phone Call")->
            whereBetween('created_at', [now()->subDays(7), now()])
            ->count();
              }
        else if($validator["filter"]=="This Month"){
            $ControlOperationPlan = MeetingLog::
            where("type","Phone Call")->
            whereMonth('created_at', [ now()->month])
            ->count();

        }
        else if($validator["filter"]=="This Quarter"){
            $ControlOperationPlan = MeetingLog::
            where("type","Phone Call")->
            whereBetween('created_at', [now()->subDays(120), now()])
            ->count();

        }
        else{
            $ControlOperationPlan = MeetingLog::
            where("type","Phone Call")->
            whereYear('created_at', [ now()->year])
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
}
