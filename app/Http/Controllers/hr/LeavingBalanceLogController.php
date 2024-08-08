<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;
use App\Models\LeavingBalanceLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class LeavingBalanceLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data=LeavingBalanceLog::latest()->get();
        if(!$data->isEmpty()){
            return response()->json(["data"=>$data,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>404]);

        }

    }
    public function store(Request $request)
    {
         $validator = Validator::make($request->all(),[
            'userid' => 'required|exists:users,id',
            "count"=>'required|numeric',
            'requestname' => 'required|string|in:Absent with permission,Vacation balance,Sick leave',
            'text' => 'required|string',
            'type' => 'required|string|in:incress,decress',
            'file' => 'nullable|file',
        ],  [
            'userid.exists' => 'The selected user does not exist.',
            'date.date' => 'The date is not a valid date format.',
            'text.string' => 'The text must be a string.',
            'file.file' => 'The file name must be a file.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY],200 );
        }
        else{
            $validator=$validator->validated();
            if ($request->hasFile('file')) {
                $file=$request->file('file');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $validator["file"]= '/uploads/file/'.$fileName;
                $file->move(public_path('uploads/file'), $fileName);

            }
            else{
                $validator["file"]= "";
            }
            $data=User::find($validator['userid']);
            if($validator['type']=="incress"){
                $data->VacationBalance+=$validator['count'];
            }
            else{
                $data->VacationBalance-=$validator['count'];
            }
            $data->save();
            LeavingBalanceLog::create($validator);
            return response()->json(["data" => "data updated successful", "status" => Response::HTTP_CREATED]);
                }
    }


    public function show($id)
    {
        $data=LeavingBalanceLog::find($id);
        if($data){
            return response()->json(["data"=>$data,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>Response::HTTP_NOT_FOUND]);

        }
    }
    public function showEmployee($id)
    {
        $data=LeavingBalanceLog::latest()->where("userid",$id)->get();
        if(!$data->isEmpty()){
            return response()->json(["data"=>$data,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>Response::HTTP_NOT_FOUND]);

        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
           'userid' => 'required|exists:users,id',
           "count"=>'required|numeric',
           'requestname' => 'required|string|in:Absent with permission,Vacation balance,Sick leave',
           'text' => 'required|string',
           'type' => 'required|string|in:incress,decress',
           'file' => 'nullable|file',
       ],  [
           'userid.exists' => 'The selected user does not exist.',
           'date.date' => 'The date is not a valid date format.',
           'text.string' => 'The text must be a string.',
           'file.file' => 'The file name must be a file.',
       ]);
       if ($validator->fails()) {
           return response()->json(['errors' => $validator->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY],200 );
       }
       else{
            $RequestData=LeavingBalanceLog::find($id);
            if($RequestData){
                $validator=$validator->validated();
                if ($request->hasFile('file')) {
                    $file=$request->file('file');
                    Storage::disk('public')->delete($RequestData->file);
                    $fileName = time().'.'.$file->getClientOriginalExtension();
                    $validator["file"]= '/uploads/file/'.$fileName;
                    $file->move(public_path('uploads/file'), $fileName);
                }
                else{
                    $validator["file"]= $RequestData->file;
                }
                $data=User::find($validator['userid']);
                if($RequestData->type=="incress"){
                    $data->VacationBalance-=$RequestData->count;
                }
                else{
                    $data->VacationBalance+=$RequestData->count;
                }
                if($validator['type']=="incress"){
                    $data->VacationBalance+=$validator['count'];
                }
                else{
                    $data->VacationBalance-=$validator['count'];
                }
                $data->save();
                LeavingBalanceLog::create($validator);
                return response()->json(["data" => "data added successful", "status" => Response::HTTP_ACCEPTED]);
            }
            else{
                return response()->json(["data" => "data not fount", "status" => Response::HTTP_NOT_FOUND]);

            }
            }
   }
    public function destroy( $id)
    {
        $RequestData=LeavingBalanceLog::find($id);
        if($RequestData){
            Storage::disk('public')->delete($RequestData->file);
            $RequestData->delete();
            return response()->json(["data" => "data deleted successful", "status" => Response::HTTP_ACCEPTED]);
        }
        else{
            return response()->json(["data" => "data not fount", "status" => Response::HTTP_NOT_FOUND]);

        }
    }
}
