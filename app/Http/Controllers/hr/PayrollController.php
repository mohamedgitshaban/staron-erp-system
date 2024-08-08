<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;
use Carbon\CarbonTimeZone;
use App\Models\payroll;
use App\Models\Attendance;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Http\Request;

class PayrollController extends Controller
{

    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'date' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors(), 'status' => Response::HTTP_UNAUTHORIZED], Response::HTTP_OK);
        }

        $validatedData = $validation->validated();
        $date = $validatedData['date'];

        // Extract month and year from the date string
        list($year, $month) = explode('-', $date);

        // Create Carbon instances for the first and last day of the month
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Fetch payrolls for the specified month
        $payrolls = Payroll::with('user')
            ->whereBetween('Date', [$startOfMonth, $endOfMonth])
            ->orderBy('TotalLiquidPay')
            ->get();

        if (!$payrolls->isEmpty()) {
            return response()->json(['data' => $payrolls, 'status' => Response::HTTP_OK]);
        } else {
            return response()->json(['data' => 'There is No Data', 'status' => Response::HTTP_NO_CONTENT]);
        }

    }

    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'Date' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ],[
            'Date.regex' => 'The :attribute must be in the format YYYY-MM.',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else{
            $validatedData = $validatedData->validated();
            $Users=User::latest()->get();
            if($Users->isEmpty()){
                return response()->json(["data"=>"there is no emploee ","status"=>Response::HTTP_NO_CONTENT]);
            }
           else{
                list($year, $month) = explode('-', $validatedData["Date"]);

                $date1 =Carbon::createFromDate($year, $month, 1)->startOfDay();
                $date= $date1->toDateString();

                $lastMonth = $date1->copy()->subMonth()->format('Y-m');

                // Calculate the month before last
                $monthBeforeLast = $date1->copy()->subMonths(2)->format('Y-m');

                // Construct the date ranges
                $specificstartDate = Carbon::createFromFormat('Y-m-d', "$monthBeforeLast-26")->startOfDay();
                $specificendDate = Carbon::createFromFormat('Y-m-d', "$lastMonth-25")->endOfDay();
                // dd($specificstartDate . " " . $specificendDate);
                $period = CarbonPeriod::create($specificstartDate, $specificendDate);
                $numberOfDays = iterator_count($period);
                foreach ($Users as $user) {
                $workingDays=0;
                $AttendanceCount = Attendance::where('user_id', $user->id)->where("Absent","0")->whereBetween('Date', [$specificstartDate, $specificendDate])->count();

                $PayrollUser= new payroll();
                $PayrollUser->user_id= $user->id;
                $PayrollUser->Date=$date;
                    if($user->startwork=="Sunday"){
                        Carbon::setWeekendDays([Carbon::FRIDAY, Carbon::SATURDAY]);
                    }
                    else{
                        Carbon::setWeekendDays([Carbon::FRIDAY]);
                    }
                foreach ($period as $perioddate) {
                    // Exclude weekends (Saturday and Sunday)
                    if ($perioddate->isWeekday()) {
                        // Exclude public holidays in Egypt
                            $workingDays+=1;
                    }
                }
                $PayrollUser->workdays=$workingDays;


                $holidays=$numberOfDays-$workingDays;
                $PayrollUser->holidays=$holidays;
                $PayrollUser->attendance=$AttendanceCount;
                // $PayrollUser->PresenceMargin=($AttendanceCount+$holidays)/$numberOfDays;
                $excuses = 0;
                $addetion = Attendance::where('user_id', $user->id)->whereBetween('Date', [$specificstartDate,  $specificendDate])->sum("addetion");
                $deduction = Attendance::where('user_id', $user->id)->whereBetween('Date', [$specificstartDate,  $specificendDate])->sum("deduction");
                $PayrollUser->excuses=$excuses;
                $PayrollUser->additions=$addetion;
                $PayrollUser->deductions=$deduction;
                $performance=1;//
                $dailyrate=($user->salary+$user->kpi)/30;
                $PayrollUser->dailyrate= $dailyrate;

                $paidday=$excuses+ $AttendanceCount+$holidays+$addetion-$deduction;
                $PayrollUser->paiddays=$paidday;
                $PayrollUser->SocialInsurance=$user->SocialInsurance;
                $PayrollUser->MedicalInsurance=$user->MedicalInsurance;
                $PayrollUser->tax=$user->tax;
                // $PayrollUser->performanceRate=$performance;
                $performanceIncentive=$user->kpi*$performance;
                if($AttendanceCount==0){
                    $PayrollUser->TotalLiquidPay=0;
                    $PayrollUser->TotalPay=0;
                    $PayrollUser->paiddays=0;
                }
                else{
                    $PayrollUser->TotalLiquidPay=$paidday*$dailyrate;
                $PayrollUser->TotalPay=($paidday*$dailyrate)-$user->tax+$user->MedicalInsurance+$user->SocialInsurance;
                $PayrollUser->save();

                }

            }
            return response()->json(['message' => "hello"], Response::HTTP_OK);

            }
        }



    }

    // private function isPublicHoliday($date)
    // {


    //     $publicHolidays = [
    //         // Example public holidays in Egypt
    //         '2023-12-25', // Christmas Day
    //     ];

    //     return in_array($date->toDateString(), $publicHolidays);
    // }

    // public function show($id)
    // {
    //     $payroll=payroll::with("user")->find($id);
    //     if($payroll!=null){
    //         return response()->json(["data"=>$payroll,"status"=>Response::HTTP_OK]);
    //     }
    //     else{
    //         return response()->json(["data"=>"there is no excuses","status"=>Response::HTTP_NOT_FOUND ]);

    //     }
    // }
}
