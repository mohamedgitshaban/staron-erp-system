<?php

namespace App\Http\Controllers\hr;

use App\Http\Controllers\Controller;
// use App\Http\Controllers\Controller\hr\AttendanceAssignLogController;
use App\Models\Attendance;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Imports\AttendanceImport;
use App\Models\User;
use App\Models\Payroll;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    private $AttendanceAssignLogController;
    public function __construct(AttendanceAssignLogController $AttendanceAssignLogController)
    {
        $this->AttendanceAssignLogController = $AttendanceAssignLogController;
    }
    public function index()
    {
        $attendances = Attendance::latest()->with('user')
            ->get();
        if (!$attendances->isEmpty()) {
            return response()->json(["data" => $attendances, "status" => Response::HTTP_OK]);
        }
        else {
            return response()->json(["data" => "There is No Data", "status" => Response::HTTP_NO_CONTENT ]);
        }
    }
    public function PublicAttendance()
    {
        $user = Auth::guard('sanctum')->user();

        $attendances = Attendance::with("user")->get()->filter(function ($item) use ($user) {
            return $item->user->department === $user->department;
        });;
        $attendances=$attendances->values();
        if (!$attendances->isEmpty()) {

            return response()->json(["data" => $attendances, "status" => Response::HTTP_OK]);
        }
        else {
            return response()->json(["data" => "There is No Data", "status" => Response::HTTP_NO_CONTENT ]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY],200 );
        }

        $validator=$validator->validated();
        Excel::import(new AttendanceImport, $validator["file"]);
        // $this->AttendanceAssignLogController->store();
        return response()->json(['message' => "create success","status"=> Response::HTTP_OK],200);
       
    }

    public function updateByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $file = $request->file('file');
        if ($file) {
            Attendance::whereBetween('Date', [$request->start_date, $request->end_date])->delete();
            Excel::import(new AttendanceImport, $file);
            return response()->json(['message' => 'Attendance updated successfully'], Response::HTTP_RESET_CONTENT );
        }
        return response()->json(['message' => 'file not found'], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
    public function updateById(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'Date' => 'required|date',
            'Clock_In' => 'required',
            'Clock_Out' => 'required',
            'Must_C_In' => 'required',
            'Must_C_Out' => 'required',
            'Absent' => 'required',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $validator->errors(),
                'status' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_OK);
        }

        // Retrieve the validated data
        $validatedData = $validator->validated();

        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json(['message' => 'Attendance not found'], Response::HTTP_NOT_FOUND);
        }
        $attendance->note = "";
          // Parse time with multiple formats
        $carbonTime1 = $this->parseTime($validatedData["Clock_In"]);
        $carbonTime2 = $this->parseTime($validatedData["Clock_Out"]);

        if (!$carbonTime1 || !$carbonTime2) {
            return response()->json([
                'error' => 'Invalid Clock_In or Clock_Out format',
                'status' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_OK);
        }
            // Calculate work time in hours and minutes
            $diffInMinutes = $carbonTime1->diffInMinutes($carbonTime2);
            $hours = intdiv($diffInMinutes, 60);
            $minutes = $diffInMinutes % 60;

            $attendance->Clock_Out = $carbonTime2;
            if ($attendance->Must_C_Out == 1 && !(Carbon::parse($attendance->Clock_In)->lessThan(Carbon::parse('16:45'))) && Carbon::parse($carbonTime2)->lessThan(Carbon::parse('16:45'))) {
                $att->note .= "early clock out updated";
                $att->deduction += 0.5;
            }
            elseif($attendance->Must_C_Out == 1 && (Carbon::parse($attendance->Clock_In)->lessThan(Carbon::parse('16:45'))) && !(Carbon::parse($carbonTime2)->lessThan(Carbon::parse('16:45'))))
        {  $attendance->note .= "early clock out updated";
            $attendance->deduction -= 0.5;
        }
            $attendance->Clock_In = $carbonTime1;

        if($attendance->Absent==0&&$validatedData['Absent']=="true"){
            $attendance->deduction -= 1;
            $attendance->Absent = 1 ;
        }
        elseif ($attendance->Absent==1&&$validatedData['Absent']!="true") {
            # code...
            $attendance->deduction += 1;
            $attendance->Absent = 0 ;
        }
        else{

        }
        $attendance->Absent = $validatedData['Absent']=="true"? 1 : 0; // Convert boolean to integer
         // Reset note

        if ($validatedData['Must_C_In'] && !$attendance->Must_C_In) {
            $attendance->Must_C_In = true;
            $attendance->note .= "must clock in updated; ";
            $attendance->deduction -= 0.5;
        }

        if ($validatedData['Must_C_Out'] && !$attendance->Must_C_Out) {
            $attendance->Must_C_Out = true;
            $attendance->note .= "must clock out updated; ";
            $attendance->deduction -= 0.5;
        }

        $User = User::find($validatedData["user_id"]);
        $isLateNow = $carbonTime1->gt(Carbon::parse('09:30'));

        if (!$validatedData['Absent']) {
            // Get all attendance records for the current month where Absent is false
            $startOfMonth = Carbon::createFromFormat('Y-m-d', $attendance->Date)->startOfMonth()->toDateString();
            $endOfMonth = Carbon::createFromFormat('Y-m-d', $attendance->Date)->endOfMonth()->toDateString();
            $allAttendance = Attendance::where('user_id', $attendance->user_id)
                ->whereBetween('Date', [$startOfMonth, $endOfMonth])
                ->where('Absent', false)
                ->orderBy('Date')
                ->get();

            $lateCount = 0;
            foreach ($allAttendance as $att) {
                $att->note = ""; // Reset note for each record

                if (Carbon::parse($att->Clock_In)->gt(Carbon::parse('09:30'))) {
                    $att->note .= "late on clock in; ";
                    if ($lateCount == 0) {
                        $att->deduction = 0; // First time late, no deduction
                    } elseif ($lateCount == 1) {
                        $att->deduction = 0.25; // Second time late
                    } elseif ($lateCount == 2) {
                        $att->deduction = 0.5; // Third time late
                    } else {
                        $att->deduction = 1; // More than three times late
                    }
                    $lateCount++;
                } else {
                    $att->deduction = 0; // Reset deduction if not late
                }

                if (!$att->Must_C_In) {
                    $att->deduction += 0.5;
                    $att->note .= "must clock in not met; ";
                }
                if (!$att->Must_C_Out) {
                    $att->deduction += 0.5;
                    $att->note .= "must clock out not met; ";
                }

                // Early clock out check
                if ($att->Must_C_Out == 1 && Carbon::parse($att->Clock_Out)->lessThan(Carbon::parse('16:45'))) {
                    $att->note .= "early clock out updated";
                    $att->deduction += 0.5;
                }

                $att->save();
            }
        }

        // Update the current attendance record
        $attendance->Work_Time = sprintf("%02d:%02d", $hours, $minutes);
        $attendance->save();

        // Check and delete payroll record if it exists
        $firstDayOfMonth = Carbon::createFromFormat('Y-m-d', $attendance->Date)->startOfMonth()->toDateString();
        $payroll = Payroll::where('user_id', $attendance->user_id)->whereDate('Date', $firstDayOfMonth)->first();
        if ($payroll) {
            $payroll->delete();
            // Call store function for this user only
            $payrollController = new PayrollController();
            $payrollController->storeForUser($attendance->user_id, $firstDayOfMonth);
        }

        return response()->json(['message' => "Update successful", "status" => Response::HTTP_OK], 200);
    }
    public function show($id)
    {
        $Attendance = Attendance::find($id);
        if ($Attendance != null) {
            return response()->json(["data" => $Attendance, "status" => Response::HTTP_OK]);
        } else {
            return response()->json(["data" => "there is no Attendance", "status" => Response::HTTP_NO_CONTENT]);
        }
    }
    public function showUser($id)
    {
        $data = Attendance::where("user_id",$id)->with('user')
        ->get();
        if(!$data->isEmpty()){
            $data->transform(function($data){
                $data->created_date = Carbon::parse($data->created_at)->toDateString();
                $data->created_time = Carbon::parse($data->created_at)->toTimeString();
                unset($data->created_at);
                return $data;
            });
            return response()->json(["data"=>$data,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"There is No Data","status"=>404]);

        }
    }
    public function addetion(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [

            'addetion' => 'required|numeric',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $validator=$validator->validated();
        $Attendance = Attendance::find($id);
        if ($Attendance != null) {
            $Attendance->addetion += $validator["addetion"];
            $Attendance->save();
            return response()->json(["data" => $Attendance, "status" => Response::HTTP_OK]);
        } else {
            return response()->json(["data" => "there is no Attendance", "status" => Response::HTTP_NO_CONTENT]);
        }

    }
    public function deduction(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'deduction' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $validator=$validator->validated();

        $Attendance = Attendance::find($id);
        if ($Attendance != null) {
            $Attendance->deduction += $validator["deduction"];
            $Attendance->save();
            return response()->json(["data" => $Attendance, "status" => Response::HTTP_OK]);
        } else {
            return response()->json(["data" => "there is no Attendance", "status" => Response::HTTP_NO_CONTENT]);
        }
    }
    public function destroyByid($id)
    {
        $Attendance = Attendance::find($id);

        if ($Attendance) {
            $Attendance->delete();

            return response()->json(['message' => 'Attendance deleted'], Response::HTTP_OK);
        } else {
            return response()->json(['message' => 'Attendance not found'], Response::HTTP_NO_CONTENT);
        }
    }

    public function destroyByRangeDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Attendance::whereBetween('Date', [$request->start_date, $request->end_date])->delete();

        return response()->json(['message' => 'Attendance deleted successfully'], Response::HTTP_OK);
    }
}
