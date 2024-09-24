<?php

namespace App\Imports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
class UsersImport implements ToModel, WithStartRow,WithHeadingRow
{
    public function model(array  $row)
    {

        //  DB::transaction(
        //     function () use($row){
                // dd($row);
                $data=User::where("name",$row['supervisor_name'])->first();
                // dd($data);
                if (empty($row["employee_name"])) {
                    // throw new \Exception("Employee name is missing in the row: " . json_encode($row));
                    return;
                }
                // if(is_numeric($row["employment_date"])){
                //     throw new \Exception("Employee name is missing in the row: " . json_encode($row));
                // }

                return new User([
                    'name' => $row["employee_name"],
                    'hr_code' => $row["employee_id"],
                    'date' => is_numeric($row["birth_date"])?Carbon::createFromTimestamp(($row["birth_date"]- 25569) * 86400)->format('Y-m-d'):Carbon::createFromFormat('m/d/Y', $row["birth_date"])->format('Y-m-d'),
                    'address' => $row["address"],
                    'phone' => $row["phone_number"],
                    'email' => $row["email_address"],
                    'password' =>$row["password"], // Hash the password
                    'department' => $row["department"],
                    'Supervisor' => $data ? $data->id : null, // If supervisor exists, assign the ID; else, set to null
                    'job_role' => $row["job_role"],
                    'job_tybe' => $row["job_tybe"],
                    'salary' => $row["salary"],
                    'profileimage' => $row["employee_name"] . '.jpg',
                    'pdf' => $row["employee_name"] . '.zip',
                    'VacationBalance' => $row["vacationbalance"],
                    'MedicalInsurance' => $row["medicalinsurance"],
                    'SocialInsurance' => $row["socialinsurance"],
                    'Trancportation' => $row["trancportation"],
                    'overtime' => !empty($row["overtime_value"]) ? filter_var(true, FILTER_VALIDATE_BOOLEAN) : false, // Set overtime to true if present
                    'overtime_value' => !empty($row["overtime_value"]) ? $row["overtime_value"] : null, // Assign overtime value only if it exists
                    'EmploymentDate' => is_numeric($row["employment_date"])?Carbon::createFromTimestamp(($row["employment_date"]- 25569) * 86400)->format('Y-m-d'):Carbon::createFromFormat('m/d/Y', $row["employment_date"])->format('Y-m-d'),
                    'kpi' => $row["kpi"],
                    'tax' => $row["tax"],
                    'TimeStamp' => $row["time_stamp"],
                    'grade' => $row["grade"],
                    'segment' => $row["segment"],
                    'startwork' => $row["start_working_day"],
                    'endwork' => $row["end_working_day"],
                    'clockin' => $row["clockin"],
                    'clockout' => $row["clockout"],
                ]);
            // }
        // );
    }

    public function startRow(): int
    {
        return 2; // Skip the first row
    }
}

