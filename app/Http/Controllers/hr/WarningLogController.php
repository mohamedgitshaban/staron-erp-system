<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WarningLog;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator ;
use Carbon\Carbon;

class WarningLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data=User::select('id', 'name',"hr_code","department", 'profileimage')
        ->latest()->with("latestWarningLog")->get();
        if(!$data->isEmpty()){
            return response()->json(["data"=>$data,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>404]);

        }
    }

    public function store(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'userid' => 'nullable|exists:users,id',
            'level' => 'required|integer|in:1,2,3,4',
            'text' => 'nullable|string',
        ],[
            'userid.exists' => 'The selected user does not exist.',
            'level.required' => 'The level is required.',
            'level.integer' => 'The level must be an integer.',
            'date.date' => 'The date must be a valid date format.',
            'text.string' => 'The text must be a string.',
        ]);
        if($validator->fails()){
            return response()->json(["error"=>$validator->errors()],200);
        }
        else{
            $validator=$validator->validated();
            $validator["date"]=now();
            WarningLog::Create($validator);
            return response()->json([
                "messege"=>"data created successfuly",
                "status"=>Response::HTTP_CREATED,
            ],Response::HTTP_CREATED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data=WarningLog::find($id);
        if($data){
            $data->userid=$data->user;
            $data->created_date = Carbon::parse($data->created_at)->toDateString();
            unset($data->created_at);
            unset($data->updated_at);
            unset($data->userid);
            return response()->json([
                "data"=>$data,
                "status"=>Response::HTTP_OK,
            ],Response::HTTP_OK);
        }
        else{
            return response()->json([
                "messege"=>"no data",
                "status"=>Response::HTTP_NOT_FOUND,
            ],Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function employeeWarning($id)
    {
        $user= User::select('id', 'name', 'profileimage')->find($id);
        $data=WarningLog::where("userid",$id)->get();
        if(!$data->isEmpty()&&$user){
            $data->transform(function($item){
                $item->created_date = Carbon::parse($item->created_at)->toDateString();
                unset($item->created_at);
                unset($item->updated_at);
                return $item;
            });
            return response()->json([
                "data"=>[
                    "user"=>$user,
                    "data"=>$data,
                ],
                "status"=>Response::HTTP_ACCEPTED,
            ],Response::HTTP_ACCEPTED);
        }
        else{
            return response()->json([
                "messege"=>"There is no data with this user id",
                "status"=>Response::HTTP_NOT_FOUND,
            ],Response::HTTP_NOT_FOUND);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator=Validator::make($request->all(),[
            'userid' => 'nullable|exists:users,id',
            'level' => 'required|integer|in:1,2,3,4',
            'text' => 'nullable|string',
        ],[
            'userid.exists' => 'The selected user does not exist.',
            'level.required' => 'The level is required.',
            'level.integer' => 'The level must be an integer.',
            'date.date' => 'The date must be a valid date format.',
            'text.string' => 'The text must be a string.',
        ]);
        if($validator->fails()){
            return response()->json(["error"=>$validator->errors()],200);
        }
        else{
            $validator=$validator->validated();
            $data=WarningLog::find($id);
            if($data){
                $data->update($validator);

            return response()->json([
                "messege"=>"data updated successfuly",
                "status"=>Response::HTTP_RESET_CONTENT,
            ],Response::HTTP_RESET_CONTENT);
        }
        else{
            return response()->json([
                "messege"=>"no data",
                "status"=>Response::HTTP_NOT_FOUND,
            ],Response::HTTP_NOT_FOUND);

        }
    }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data=WarningLog::find($id);
        if($data){
            $data->delete();
            return response()->json([
                "messege"=>"data deleted sucessfully",
                "status"=>Response::HTTP_ACCEPTED,
            ],Response::HTTP_ACCEPTED);
        }
        else{
            return response()->json([
                "messege"=>"no data",
                "status"=>Response::HTTP_NOT_FOUND,
            ],Response::HTTP_NOT_FOUND);
        }

    }
}
