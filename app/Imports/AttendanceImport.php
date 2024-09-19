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

        $time = "00:00";
        $timelogout = "00:00";
        foreach ($rows as $row) {
            $user = User::where('hr_code', $row[1])->first();

            if ($user) {

                $attendance = new Attendance();
                $attendance->user_id = $user->id;
                $attendance->addetion = 0;
                $attendance->note = "";
                $attendance->deduction =  0;
                // date
                if (is_numeric($row[3])) {
                    // Convert the fractional value to a timestamp
                    $timestamp = ($row[3] - 25569) * 86400; // Convert Excel date to Unix timestamp

                    $attendance->date = Carbon::createFromTimestamp($timestamp)->format('Y-m-d');
                } else {
                    $attendance->date = Carbon::createFromFormat('m/d/Y', $row[3])->format('Y-m-d');
                }
                $record = Attendance::where("user_id", $user->id)->where("date", $attendance->date)->first();

                // attend or not
                if (filter_var($row[7], FILTER_VALIDATE_BOOLEAN)) {
                    $attendance->Clock_In = Carbon::createFromFormat('H:i', $time)->format('H:i');
                    $attendance->Clock_Out = Carbon::createFromFormat('H:i', $timelogout)->format('H:i');
                    $attendance->Work_Time = Carbon::createFromFormat('H:i', $time)->format('H:i');
                    $today = Carbon::now()->dayOfWeek;

                    // Check if today is Thursday (4) or Sunday (0)
                    if ($today == Carbon::THURSDAY || $today == Carbon::SUNDAY) {
                        $attendance->deduction += 2;
                    } else {
                        $attendance->deduction += 1;
                    }
                    $attendance->note .= "Absent";
                } else {
                    $attendance->absent = filter_var($row[7], FILTER_VALIDATE_BOOLEAN);

                    if ($row[4]) {
                        $attendance->Must_C_In = 1;
                        if (is_numeric($row[4])) {
                            try {
                                $hours = floor($row[4] * 24); // Convert days to hours
                                $minutes = round(($row[4] * 24 - $hours) * 60); // Convert remaining hours to minutes
                                if ($minutes < 10 && $hours < 10) {
                                    $totaltime = "0" . $hours . ":0" . $minutes;
                                } elseif ($hours < 10) {
                                    $totaltime = "0" . $hours . ":" . $minutes;
                                } elseif ($minutes < 10) {
                                    $totaltime = $hours . ":0" . $minutes;
                                } else {
                                    $totaltime = $hours . ":" . $minutes;
                                }
                                $attendance->Clock_In = Carbon::createFromFormat('H:i', $totaltime)->format('H:i');
                            } catch (\Throwable $th) {
                                dd($attendance->date . " " . $row[1] . " " . $totaltime);
                                throw $th;
                            }
                        } else {
                            $attendance->Clock_In = Carbon::createFromFormat('H:i', $row[4])->format('H:i');
                        }
                    } else {
                        $attendance->Must_C_In = 0;
                        $attendance->deduction += 0.5;
                        $attendance->note .= " Not clock in ";
                        $attendance->Clock_In = Carbon::createFromFormat('H:i', $time)->format('H:i');
                    }

                    if ($row[5]) {
                        $attendance->Must_C_Out = 1;
                        if (is_numeric($row[5])) {
                            $hours = floor($row[5] * 24); // Convert days to hours
                            $minutes = round(($row[5] * 24 - $hours) * 60); // Convert remaining hours to minutes
                            if ($minutes < 10 && $hours < 10) {
                                $totaltime = "0" . $hours . ":0" . $minutes;
                            } elseif ($hours < 10) {
                                $totaltime = "0" . $hours . ":" . $minutes;
                            } elseif ($minutes < 10) {
                                $totaltime = $hours . ":0" . $minutes;
                            } else {
                                $totaltime = $hours . ":" . $minutes;
                            }
                            $attendance->Clock_Out = Carbon::createFromFormat('H:i', $totaltime);
                        } else {
                            $attendance->Clock_Out = Carbon::createFromFormat('H:i', $row[5]);
                        }
                    } else {
                        $attendance->Must_C_Out = 0;
                        $attendance->deduction += 0.5;
                        $attendance->note .= " Not clock out ";
                        $attendance->Clock_Out = Carbon::createFromFormat('H:i', $timelogout)->format('H:i');
                    }
                    $attendance->Work_Time = Carbon::parse($attendance->Clock_In)->diff(Carbon::parse($attendance->Clock_Out))->format('%H:%i');
                }
                //late
                // dd(Carbon::createFromFormat('H:i', $attendance->Clock_In)->greaterThan(Carbon::createFromFormat('H:i', '09:30')));
                if ($row[4] && Carbon::parse('09:10')->lessThan(Carbon::parse($attendance->Clock_In))) {
                    $attendance->note .= " late on clock in ";
                    $startOfMonth = Carbon::createFromFormat('Y-m-d', $attendance->date)->startOfMonth()->subMonth()->day(26)->format('Y-m-d');
                    // Count all attendance records for this user of this month where Clock_In is after 9:30
                    $lateCount = Attendance::where('user_id', $user->id)
                        ->whereBetween('date', [$startOfMonth, $attendance->date])
                        ->where('Clock_In', '>', '09:10')
                        ->count();

                    // dd($lateFormatted);
                    if ($lateCount == 0) {
                        $attendance->deduction += 0; // First time late, no deduction
                    } elseif ($lateCount == 1) {
                        $attendance->deduction += 0.25; // Second time late
                    } elseif ($lateCount == 2) {
                        $attendance->deduction += 0.5; // Third time late
                    } else {
                        $attendance->deduction += 1; // More than three times late
                    }
                }

                if ($attendance->Must_C_Out == 1 && Carbon::parse($attendance->Clock_Out)->lessThan(Carbon::parse('18:00'))) {
                    $attendance->note .= " early clock out ";
                    $attendance->deduction += 0.5;
                }
                if ($row[8]) {
                    if ($row[8] > 0) {
                        $attendance->addetion += $row[8];
                    } else {
                        $attendance->deduction +=  $row[8];
                    }
                }
                if (!$record) {
                    $attendance->save();
                } else {
                    $record->update((array)$attendance);
                }
            }
        }
    }
}
