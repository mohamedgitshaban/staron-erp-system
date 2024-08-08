<?php


namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;
use App\Models\Reqrurment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReqrurmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $user;

    public function PendingRequest()
    {
        $this->user = Auth::guard('sanctum')->user();
        $Reqrurment=Reqrurment::latest()
        ->with("user")
        ->where("adminstatus","pending")
        ->orWhere("adminstatus","ask")
        ->where("hrstatus","<>","rejected")
        ->get()->filter(function ($item) {
            return $item->user->department ===$this->user->department;
        });

        if(!$Reqrurment->isEmpty()){
            $Reqrurment->transform(function($data){
                $data->created_date = Carbon::parse($data->created_at)->toDateString();
                $data->created_time = Carbon::parse($data->created_at)->toTimeString();
                unset($data->created_at);
                unset($data->updated_at);
                return $data;
            });
            return response()->json(["data"=>$Reqrurment,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is no Employee RFE","status"=>404]);

        }
    }
    public function AprovedRequest()
    {
        $this->user = Auth::guard('sanctum')->user();
        $Reqrurment=Reqrurment::latest()
        ->with("user")
        ->where("adminstatus","approved")
        ->where("hrstatus","approved")
        ->get()->filter(function ($item) {
            return $item->user->department ===$this->user->department;
        });
        if(!$Reqrurment->isEmpty()){
            $Reqrurment->transform(function($data){
                $data->created_date = Carbon::parse($data->created_at)->toDateString();
                $data->created_time = Carbon::parse($data->created_at)->toTimeString();
                unset($data->created_at);
                unset($data->updated_at);

                return $data;
            });
            return response()->json(["data"=>$Reqrurment,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is no Employee RFE","status"=>404]);

        }
    }
    public function RejectedRequest()
    {
        $this->user = Auth::guard('sanctum')->user();
        $Reqrurment=Reqrurment::latest()
        ->with("user")
        ->where("adminstatus","rejected")
        ->orWhere("adminstatus","rejected")
        ->get()->filter(function ($item) {
            return $item->user->department ===$this->user->department;
        });
        if(!$Reqrurment->isEmpty()){
            $Reqrurment->transform(function($data){
                $data->created_date = Carbon::parse($data->created_at)->toDateString();
                $data->created_time = Carbon::parse($data->created_at)->toTimeString();
                unset($data->created_at);
                unset($data->updated_at);

                return $data;
            });
            return response()->json(["data"=>$Reqrurment,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is no Employee RFE","status"=>404]);

        }
    }
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors()],"status"=>Response::HTTP_BAD_REQUEST],Response::HTTP_OK );
        }
        else{
            $this->user = Auth::guard('sanctum')->user();
            $validatedData=$validatedData->validated();
            $validatedData["asignby"]=$this->user->id;
            Reqrurment::create($validatedData);
            return response()->json(["data" => "data added successful", "status" =>Response::HTTP_CREATED]);

        }
    }

    public function show($id)
    {
        $user=Reqrurment::with("user")->find($id);
        unset($user->user_id);
        if ($user != null) {
                $user->created_date = Carbon::parse($user->created_at)->toDateString();
                $user->created_time = Carbon::parse($user->created_at)->toTimeString();

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
            'title' => 'required|string',
            'description' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors()],"status"=>Response::HTTP_BAD_REQUEST],Response::HTTP_OK );
        }
        else{
            $user=Reqrurment::find($id);
            if ($user != null) {
                $validatedData=$validatedData->validated();
                $user->update($validatedData);
                return response()->json(["data" => "data updated successful", "status" => 200]);

         } else {
             return response()->json(["data" => "There is no Request with the provided ID", "status" => 404]);
         }
        }
    }
    public function hrapprove($id)
    {

            $user=Reqrurment::find($id);
            if ($user != null) {
                $validatedData["hrstatus"]="approved";
                $validatedData["adminstatus"]="pending";
                $user->update($validatedData);
                return response()->json(["data" => "data updated successful", "status" => 200]);

         } else {
             return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
         }

    }
    public function hrreject($id)
    {

            $user=Reqrurment::find($id);
            if ($user != null) {
                $validatedData["hrstatus"]="rejected";
                $user->update($validatedData);
                return response()->json(["data" => "data updated successful", "status" => 200]);

         } else {
             return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
         }

    }
    public function adminapprove($id)
    {

            $user=Reqrurment::find($id);
            if ($user != null) {
                $validatedData["adminstatus"]="approved";
                $validatedData["status"]="pending";
                $user->update($validatedData);
                return response()->json(["data" => "data updated successful", "status" => 200]);

         } else {
             return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
         }

    }
    public function adminreject($id)
    {

            $user=Reqrurment::find($id);
            if ($user != null) {
                $validatedData["adminstatus"]="rejected";
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
        $user=Reqrurment::find($id);
        if ($user != null) {
               $user->delete();

            return response()->json(["data" => "request delete success", "status" => 202]);
        } else {
            return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
        }
    }
}
