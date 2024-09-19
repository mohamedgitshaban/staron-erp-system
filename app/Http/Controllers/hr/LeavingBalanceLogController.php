<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;
use App\Models\LeavingBalanceLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\hr\UserController;
class LeavingBalanceLogController extends Controller
{
    protected $UserController;
    public function __construct(UserController $UserController ) {
        $this->UserController = $UserController;
    }
    public function index()
    {
        $data=LeavingBalanceLog::latest()->with("user")->get();
        if(!$data->isEmpty()){
            return response()->json(["data"=>$data,"status"=>200]);
        }
        else{

            return response()->json(["data"=>"no data","status"=>404]);
        }

    }


    public function LeavingBalanceDeductionRequest(Request $request){
        $validator = Validator::make($request->all(), [
            'request_type' => 'required|in:Annual Vacation',
            'hr_approve' => 'required|in:approved',
            'from_date' => 'required|date|before_or_equal:to_date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'description' => 'required|string|max:1000',
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], Response::HTTP_OK);
        }
        else{
            $validator=$validator->validated();
            $start=new DateTime($validator["from_date"]);
            $end=new DateTime($validator["to_date"]);
            $interval=DateInterval::createFromDateString("1 day");
            $period= new  DatePeriod($start,$interval,$end);
            // Add 1 to include both start and end date in the count
            $dayCount = 0;
            foreach($period as $balance){
                $LeavingBalance=new LeavingBalanceLog();
                $LeavingBalance->userid=$validator["user_id"];
                $LeavingBalance->date=$balance;
                $LeavingBalance->text=$validator["description"];
                $dayOfWeek = $balance->format('w');
                if ($dayOfWeek == 0 || $dayOfWeek == 4) {
                    $LeavingBalance->amount=2;
                }
                $LeavingBalance->amount=1;
                $LeavingBalance->type="decress";
                $LeavingBalance->save();
                $dayCount+=$LeavingBalance->amount;
            }
            $this->UserController->DecressUserLeavingBalance($validator["user_id"],$dayCount);
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
