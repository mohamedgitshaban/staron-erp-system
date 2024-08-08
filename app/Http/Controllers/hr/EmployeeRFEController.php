<?php


namespace App\Http\Controllers\hr;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\EmployeeRFE;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeRFEController extends Controller
{
    protected $user;
    public function index()
    {
        $this->user = Auth::guard('sanctum')->user();
        $EmployeeRFE=EmployeeRFE::latest()->
        with("user")
        ->get()->filter(function ($item) {
            return $item->user->department ===$this->user->department;
        });
        if(!$EmployeeRFE->isEmpty()){
            $EmployeeRFE->transform(function($data){
                $data->fromdate = Carbon::parse($data->from_date)->toDateString();
                $data->todate = Carbon::parse($data->to_date)->toDateString();
                $data->created_date = Carbon::parse($data->created_at)->toDateString();
                $data->created_time = Carbon::parse($data->created_at)->toTimeString();
                unset($data->created_at);
                unset($data->from_date);
                unset($data->to_date);
                unset($data->updated_at);

                return $data;
            });
            return response()->json(["data"=>$EmployeeRFE,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is no Employee RFE","status"=>404]);

        }
    }

    public function PendingRequest()
    {
        $this->user = Auth::guard('sanctum')->user();
        $EmployeeRFE=EmployeeRFE::latest()->
        with("user")
        ->where("admin_approve","pending")
        ->orWhere("admin_approve","ask")
        ->where("hr_approve","<>","rejected")
        ->get()->filter(function ($item) {
            return $item->user->department ===$this->user->department;
        });
        if(!$EmployeeRFE->isEmpty()){
            $EmployeeRFE->transform(function($data){
                $data->fromdate = Carbon::parse($data->from_date)->toDateString();
                $data->todate = Carbon::parse($data->to_date)->toDateString();
                $data->created_date = Carbon::parse($data->created_at)->toDateString();
                $data->created_time = Carbon::parse($data->created_at)->toTimeString();
                unset($data->created_at);
                unset($data->from_date);
                unset($data->to_date);
                unset($data->updated_at);

                return $data;
            });
            return response()->json(["data"=>$EmployeeRFE,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is no Employee RFE","status"=>404]);

        }
    }
    public function AprovedRequest()
    {
        $this->user = Auth::guard('sanctum')->user();

        $EmployeeRFE=EmployeeRFE::latest()->
        with("user")
        ->where("admin_approve","approved")
        ->where("hr_approve","approved")
        ->get()->filter(function ($item) {
            return $item->user->department ===$this->user->department;
        });
        if(!$EmployeeRFE->isEmpty()){
            $EmployeeRFE->transform(function($data){
                $data->fromdate = Carbon::parse($data->from_date)->toDateString();
                $data->todate = Carbon::parse($data->to_date)->toDateString();
                $data->created_date = Carbon::parse($data->created_at)->toDateString();
                $data->created_time = Carbon::parse($data->created_at)->toTimeString();
                unset($data->created_at);
                unset($data->from_date);
                unset($data->to_date);
                unset($data->updated_at);

                return $data;
            });
            return response()->json(["data"=>$EmployeeRFE,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is no Employee RFE","status"=>404]);

        }
    }
    public function RejectedRequest()
    {
        $this->user = Auth::guard('sanctum')->user();
        $EmployeeRFE=EmployeeRFE::latest()->
        with("user")
        ->where("admin_approve","rejected")
        ->orWhere("hr_approve","rejected")
        ->get()->filter(function ($item) {
            return $item->user->department ===$this->user->department;
        });
        if(!$EmployeeRFE->isEmpty()){
            $EmployeeRFE->transform(function($data){
                $data->fromdate = Carbon::parse($data->from_date)->toDateString();
                $data->todate = Carbon::parse($data->to_date)->toDateString();
                $data->created_date = Carbon::parse($data->created_at)->toDateString();
                $data->created_time = Carbon::parse($data->created_at)->toTimeString();
                unset($data->created_at);
                unset($data->from_date);
                unset($data->to_date);
                unset($data->updated_at);

                return $data;
            });
            return response()->json(["data"=>$EmployeeRFE,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is no Employee RFE","status"=>404]);

        }
    }
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'request_type' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'from_ci' => 'required|date_format:H:i',
            'to_co' => 'required|date_format:H:i',
            'user_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors()],"status"=>Response::HTTP_BAD_REQUEST],Response::HTTP_OK );
        }
        else{
            $validatedData=$validatedData->validated();
            $user=User::find($validatedData["user_id"]);
            $this->user = Auth::guard('sanctum')->user();

            if($this->user->id==$user->Supervisor||$this->user->id==$user->id){
                EmployeeRFE::create($validatedData);
                return response()->json(["data" => "data added successful", "status" => 200]);
            }
            else{
                return response()->json(["data" => "Not Acceptable", "status" => 406]);
            }


        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user=EmployeeRFE::with("user")->find($id);
        unset($user->user_id);
        if ($user != null) {
                $user->fromdate = Carbon::parse($user->from_date)->toDateString();
                $user->todate = Carbon::parse($user->to_date)->toDateString();
                $user->created_date = Carbon::parse($user->created_at)->toDateString();
                $user->created_time = Carbon::parse($user->created_at)->toTimeString();
                unset($user->from_date);
                unset($user->to_date);
                unset($user->created_at);
                unset($user->updated_at);

            return response()->json(["data" => $user, "status" => 202]);
        } else {
            return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'request_type' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'from_ci' => 'required|date_format:H:i',
            'to_co' => 'required|date_format:H:i',
            'user_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors()],"status"=>Response::HTTP_BAD_REQUEST],Response::HTTP_OK );
        }
        else{
            $Request=EmployeeRFE::find($id);
            if ($Request != null)
             {
                $validatedData=$validatedData->validated();
                $user=User::find($validatedData["user_id"]);
                $this->user = Auth::guard('sanctum')->user();
                if($this->user->id==$user->Supervisor||$this->user->id==$user->id){
                $Request->update($validatedData);
                return response()->json(["data" => "data updated successful", "status" => 205]);
                }
                else{
                    return response()->json(["data" => "Not Acceptable", "status" => 406]);
                }

            }
            else {
                return response()->json(["data" => "There is no Request with the provided ID", "status" => 404]);
            }
        }
    }
    public function hrapprove($id)
    {

            $user=EmployeeRFE::find($id);
            if ($user != null) {
                $validatedData["hr_approve"]="approved";
                $validatedData["admin_approve"]="pending";
                $user->update($validatedData);
                return response()->json(["data" => "data updated successful", "status" => 200]);

         } else {
             return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
         }

    }
    public function hrreject($id)
    {

            $user=EmployeeRFE::find($id);
            if ($user != null) {
                $validatedData["hr_approve"]="rejected";
                $user->update($validatedData);
                return response()->json(["data" => "data updated successful", "status" => 200]);

         } else {
             return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
         }

    }
    public function adminapprove($id)
    {

            $user=EmployeeRFE::find($id);
            if ($user != null) {
                $validatedData["admin_approve"]="approved";
                $user->update($validatedData);
                return response()->json(["data" => "data updated successful", "status" => 200]);

         } else {
             return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
         }

    }
    public function adminreject($id)
    {

            $user=EmployeeRFE::find($id);
            if ($user != null) {
                $validatedData["admin_approve"]="rejected";
                $user->update($validatedData);
                return response()->json(["data" => "data updated successful", "status" => 200]);

         } else {
             return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
         }

    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user=EmployeeRFE::find($id);
        if ($user != null) {
               $user->delete();

            return response()->json(["data" => "request delete success", "status" => 202]);
        } else {
            return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
        }
    }
}
