<?php

namespace App\Http\Controllers\hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceAssignLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceAssignLogController extends Controller
{
    public function index(){
        $attendanceLogs = AttendanceAssignLog::latest()->with("user")->get();
        if (!$attendanceLogs->isEmpty()) {
            return response()->json(["data" => $attendanceLogs, "status" => Response::HTTP_OK]);
        }
        else {
            return response()->json(["data" => "There is No Data", "status" => Response::HTTP_NO_CONTENT ]);
        }
    }

    public function store()
    {
        // Get the authenticated user's ID
        $userId = Auth::id();

        // Use current date and day of the week
        $date = now()->toDateString();
        $dayOfWeek = now()->dayOfWeek;

        // Check if today is Friday (5) or Saturday (6)
        if ($dayOfWeek == 5 || $dayOfWeek == 6) {
            return; // Do not create a record
        }

        // Check if today is Sunday (0)
        if ($dayOfWeek == 0) {
            // Count records for the current date and user
            $recordCount = AttendanceAssignLog::where('userid', $userId)
                ->where('date', $date)
                ->count();

            // If there are 2 or more records, do not create a new one
            if ($recordCount >= 2) {
                return;
            }
            elseif($recordCount==1){
                $date = Carbon::parse($date)->subDay()->toDateString();
                $attendanceLog = new AttendanceAssignLog();
                $attendanceLog->userid = $userId;
                $attendanceLog->date = $date;
                $attendanceLog->save();
            }
            else{
                $attendanceLog = new AttendanceAssignLog();
                $attendanceLog->userid = $userId;
                $attendanceLog->date = $date;
                $attendanceLog->save();
            }
        }else{
            $existingLog = AttendanceAssignLog::where('userid', $userId)
            ->where('date', $date)
            ->first();
            if (!$existingLog) {
                // Create a new attendance log
                $attendanceLog = new AttendanceAssignLog();
                $attendanceLog->userid = $userId;
                $attendanceLog->date = $date;
                $attendanceLog->save();
            }
        }

        // Check if there is already a record for the same date and user (for other days)



    }


    public function selectAll()
    {
        // Get the authenticated user's ID
        $userId = Auth::id();

        // Retrieve all attendance logs for the authenticated user
        $attendanceLogs = AttendanceAssignLog::where('userid', $userId)->get();

        return response()->json(['message' => 'Attendance logs retrieved successfully', 'data' => $attendanceLogs, "status" => Response::HTTP_OK], 200);
    }

    public function sendAttendanceEmail()
    {
        $date = Carbon::now();
        $dayOfWeek = $date->dayOfWeek;
        $targetDates = [];

        if (in_array($dayOfWeek, [2, 3, 4])) { // Tuesday to Thursday
            $targetDates[] = $date->copy()->subDay()->toDateString();
        } elseif ($dayOfWeek == 0) { // Sunday
            $targetDates[] = $date->copy()->subDays(3)->toDateString(); // Thursday
        } elseif ($dayOfWeek == 1) { // Monday
            $targetDates[] = $date->copy()->subDay()->toDateString(); // Sunday
            $targetDates[] = $date->copy()->subDays(2)->toDateString(); // Saturday
        } else {
            Log::info('No action needed for today.');
            return; // No action needed for other days
        }

        $attendanceLogs = AttendanceAssignLog::whereIn('date', $targetDates)->with('user')->get();

        Mail::send('emails.attendance', ['attendanceLogs' => $attendanceLogs], function ($message) {
            $message->to('mohamed.shaban@staronegypt.com.eg')
                    ->subject('Daily Attendance Log');
        });

        Log::info('Attendance report email sent for dates: ' . implode(', ', $targetDates));
    }

}
