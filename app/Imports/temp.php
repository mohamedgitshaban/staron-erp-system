<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Complains;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

// Set Saturday as the start of the week
Carbon::setWeekStartsAt(Carbon::SATURDAY);

// Set Friday as the end of the week
Carbon::setWeekEndsAt(Carbon::FRIDAY);
class AttendanceImport implements ToCollection
{

    public function collection(Collection $rows)
    {

        $time="00:00:00";
        foreach ($rows as $row) {

            $deduction=0;
            $user = User::where('hr_code', $row[1])->first();
            if($user){
                $attendance = new Attendance();
                $attendance->user_id = $user->id;
                $attendance->date =  Carbon::createFromFormat('m/d/Y', $row[5])->toDateString();
                $attendance->Must_C_In = filter_var($row[19], FILTER_VALIDATE_BOOLEAN);
                $attendance->Must_C_Out = filter_var($row[20], FILTER_VALIDATE_BOOLEAN);
                if($row[9]){
                    $attendance->Clock_In = Carbon::createFromFormat('H:i:s', $row[9]);
                }
                else{
                    $attendance->Clock_In = Carbon::createFromFormat('H:i:s', $time)->format('H:i:s');

                }
                if($row[10]){
                    $attendance->Clock_Out = Carbon::createFromFormat('H:i:s', $row[10]);
                }
                else{
                    $attendance->Clock_Out = Carbon::createFromFormat('H:i:s', $time)->format('H:i:s');
                }
                $attendance->absent = filter_var($row[15], FILTER_VALIDATE_BOOLEAN);

                if( filter_var($row[15], FILTER_VALIDATE_BOOLEAN)){
                    $complainsData = Complains::join('attendances', function ($join) {
                        $join->on('attendances.user_id', '=', 'complains.user_id')
                            ->whereBetween('attendances.Date', ['complains.start_date', 'complains.end_date']);
                    })->select("complains.id")
                    ->get();
                   if($complainsData){
                    $attendance->exception = $complainsData;
                   }
                   else{
                    $attendance->exception = $row[18];
                   }
                }
                else{
                    if(filter_var($row[19], FILTER_VALIDATE_BOOLEAN)){

                        $clockInTime=Carbon::createFromFormat('H:i:s', $row[9]);
                        $onDutyTime=Carbon::createFromFormat('H:i:s', $row[7]);
                        if ($clockInTime->eq($onDutyTime) || $clockInTime->lt($onDutyTime) || $clockInTime->diffInMinutes($onDutyTime) < 11) {
                            $attendance->normal = "true";
                        }
                        else{
                            $startDate = Carbon::now()->startOfMonth()->toDateString();
                            $endDate = Carbon::now()->endOfMonth()->toDateString();
                            $attendances = Attendance::whereBetween('date', [$startDate, $endDate])->where('late',"!=",null)->where('Absent',"!=",true)->count();
                            if($attendances==1){
                                $deduction+=0.25;
                            }
                            if($attendances==2){
                                $deduction+=0.5;
                            }
                            if($attendances==3){
                                $deduction+=0.75;
                            }
                            if($attendances>3){
                                $deduction+=1;
                            }
                            $attendance->normal = "false";
                        }
                    }
                    else{
                        $deduction+=0.5;
                        $attendance->normal = "false";

                    }
                    if(filter_var($row[20], FILTER_VALIDATE_BOOLEAN)){
                        $clockInTime=Carbon::createFromFormat('H:i:s', $row[10]);
                        $onDutyTime=Carbon::createFromFormat('H:i:s', $row[20]);
                        if ($clockInTime->eq($onDutyTime) || $clockInTime->gt($onDutyTime)) {
                            $attendance->normal = "true";
                        }
                        else{

                            $attendance->normal = "false";
                        }
                    }
                    else{
                        $deduction+=0.5;
                        $attendance->normal = "false";

                    }
                    $attendance->exception = $row[18];
                }
                $attendance->work_time = $row[17];
                $attendance->addetion = 0;
                $attendance->deduction = $deduction;
                $attendance->save();
            }

        }

    }
}
