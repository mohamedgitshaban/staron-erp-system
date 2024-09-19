<?php


namespace App\Http\Controllers\hr;

use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\EmployeeRFE;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\hr\LeavingBalanceLogController;
use DateTime;

class EmployeeRFEController extends Controller
{
    protected $user;
    protected $LeavingBalanceLogController;
    public function __construct(LeavingBalanceLogController $LeavingBalanceLogController)
    {
        $this->LeavingBalanceLogController=$LeavingBalanceLogController;
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('sanctum')->user(); // Assign authenticated user to $this->user
            return $next($request);
        });
        }
    public function index()
    {

        $EmployeeRFE = EmployeeRFE::latest()->where("user_id", $this->user->id)->with("user")
            ->get();
            $EmployeeRFE->transform(function ($item, $key) {
                if (in_array($item->request_type, ['Sick Leave', 'Annual Vacation', 'Absent'])) {
                    unset($item->from_ci);
                    unset($item->to_co);
                }
                else{
                    $item->date=$item->from_date;
                    unset($item->from_date);
                    unset($item->to_date);
                }
                return $item;
            });
        if (!$EmployeeRFE->isEmpty()) {
            return response()->json(["data" => $EmployeeRFE, "status" => Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return response()->json(["data" => "there is no Employee RFE", "status" => Response::HTTP_NO_CONTENT], Response::HTTP_OK);
        }
    }
    public function HRindex()
    {

        $EmployeeRFE = EmployeeRFE::latest()->with("user")
            ->get();
        if (!$EmployeeRFE->isEmpty()) {
            return response()->json(["data" => $EmployeeRFE, "status" => Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return response()->json(["data" => "there is no Employee RFE", "status" => Response::HTTP_NO_CONTENT], Response::HTTP_OK);
        }
    }
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'request_type' => 'required|string|in:Sick Leave,Annual Vacation,Absent,Errands,Clock In Excuse,Clock Out Excuse',
            'description' => 'nullable|string',
        ]);

        // Remove 'from_ci' and 'to_co' for Sick Leave, Annual Vacation, Absent
        if (in_array($request->request_type, ['Sick Leave', 'Annual Vacation', 'Absent'])) {
            // Validate only from_date and to_date
            $validatedData->addRules([
                'from_date' => 'required|date|after_or_equal:today',
                'to_date' => 'required|date|after_or_equal:today',
            ]);
        }
        // Remove 'from_date' and 'to_date' for Errands, Clock In Excuse, Clock Out Excuse
        if (in_array($request->request_type, ['Errands', 'Clock In Excuse', 'Clock Out Excuse'])) {
            // Validate only from_ci and to_co
            $validatedData->addRules([
                'date' => 'required|date|after_or_equal:today',
                'from_ci' => 'required|date_format:H:i',
                'to_co' => 'required|date_format:H:i|after:from_ci',
            ]);
        }

        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], Response::HTTP_OK);
        }

        // Retrieve the validated data
        $validatedData = $validatedData->validated();

        // Set 'from_ci' and 'to_co' for Sick Leave, Annual Vacation, Absent
        if (in_array($validatedData['request_type'], ['Sick Leave', 'Annual Vacation', 'Absent'])) {
            $validatedData['from_ci'] = $this->user->clockin;
            $validatedData['to_co'] = $this->user->clockout;
        }

        // Set 'from_date' and 'to_date' for Errands, Clock In Excuse, Clock Out Excuse
        if (in_array($validatedData['request_type'], ['Errands', 'Clock In Excuse', 'Clock Out Excuse'])) {
            $validatedData['from_date'] = $validatedData["date"];
            $validatedData['to_date'] =  $validatedData["date"];
        }

        // Add the authenticated user's ID to the validated data
        $validatedData['user_id'] = $this->user->id;
        if($validatedData['request_type']=="Annual Vacation"){
            $start = new DateTime($validatedData["from_date"]);
            $end = new DateTime($validatedData["to_date"]);

            // Use diff to get the difference between the dates
            $diff = $start->diff($end);
            // Add 1 to include both start and end date in the count
            $dayCount = $diff->days + 1;
            if($this->user->VacationBalance<=$dayCount){
                return response()->json(["data" => "the Vacation Balance not enough for this request", "status" => Response::HTTP_NOT_ACCEPTABLE], Response::HTTP_OK);
            }
        }
        EmployeeRFE::create($validatedData);

        // Return success response
        return response()->json(["data" => "Data added successfully", "status" => Response::HTTP_OK], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = EmployeeRFE::with("user")->find($id);
        unset($user->user_id);
        if ($user != null) {
            return response()->json(["data" => $user, "status" => Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return response()->json(["data" => "There is no user with the provided ID", "status" => Response::HTTP_NOT_FOUND], Response::HTTP_OK);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'request_type' => 'required|string|in:Sick Leave,Annual Vacation,Absent,Errands,Clock In Excuse,Clock Out Excuse',
            'description' => 'nullable|string',
        ]);

        // Remove 'from_ci' and 'to_co' for Sick Leave, Annual Vacation, Absent
        if (in_array($request->request_type, ['Sick Leave', 'Annual Vacation', 'Absent'])) {
            // Validate only from_date and to_date
            $validatedData->addRules([
                'from_date' => 'required|date|after_or_equal:today',
                'to_date' => 'required|date|after_or_equal:today',
            ]);
        }
        // Remove 'from_date' and 'to_date' for Errands, Clock In Excuse, Clock Out Excuse
        if (in_array($request->request_type, ['Errands', 'Clock In Excuse', 'Clock Out Excuse'])) {
            // Validate only from_ci and to_co
            $validatedData->addRules([
                'date' => 'required|date|after_or_equal:today',
                'from_ci' => 'required|date_format:H:i',
                'to_co' => 'required|date_format:H:i|after:from_ci',
            ]);
        }
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors()], "status" => Response::HTTP_UNPROCESSABLE_ENTITY], Response::HTTP_OK);
        } else {
            $Request = EmployeeRFE::find($id);
            if ($Request != null) {
                $validatedData = $validatedData->validated();
                if ($Request->hr_approve == "pending") {
                    if (in_array($validatedData['request_type'], ['Sick Leave', 'Annual Vacation', 'Absent'])) {
                        $validatedData['from_ci'] = $this->user->clockin;
                        $validatedData['to_co'] = $this->user->clockout;
                    }

                    // Set 'from_date' and 'to_date' for Errands, Clock In Excuse, Clock Out Excuse
                    if (in_array($validatedData['request_type'], ['Errands', 'Clock In Excuse', 'Clock Out Excuse'])) {
                        $validatedData['from_date'] = $validatedData["date"];
                        $validatedData['to_date'] =  $validatedData["date"];
                    }

                    $Request->update($validatedData);
                    return response()->json(["data" => "data updated successful", "status" => Response::HTTP_OK], Response::HTTP_OK);
                } else {
                    return response()->json(["data" => "method not allowed", "status" => Response::HTTP_METHOD_NOT_ALLOWED], Response::HTTP_OK);
                }
            } else {
                return response()->json(["data" => "There is no Request with the provided ID", "status" => Response::HTTP_NOT_FOUND], Response::HTTP_OK);
            }
        }
    }
    public function hrapprove($id)
    {
        $Request = EmployeeRFE::find($id);
        if ($Request != null) {
            if ($Request->hr_approve == "pending" && $this->user->hraccess == 1) {
                $validatedData["hr_approve"] = "approved";
                $validatedData["hr_approve_data"] = now();
                $Request->update($validatedData);
                if($Request->request_type=="Annual Vacation"){
                    $this->LeavingBalanceLogController->LeavingBalanceDeductionRequest($Request);
                }
                return response()->json(["data" => "data updated successful", "status" => 200]);
            } else {
                return response()->json(["data" => "method not allowed", "status" => Response::HTTP_METHOD_NOT_ALLOWED], Response::HTTP_OK);
            }
        } else {
            return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
        }
    }
    public function hrreject($id)
    {

        $user = EmployeeRFE::find($id);
        if ($user != null) {
            $validatedData["hr_approve"] = "rejected";
            $user->update($validatedData);
            return response()->json(["data" => "data updated successful", "status" => 200]);
        } else {
            return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
        }
    }
    public function destroy($id)
    {
        $user = EmployeeRFE::find($id);
        if ($user != null) {
            $user->delete();

            return response()->json(["data" => "request delete success", "status" => 202]);
        } else {
            return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
        }
    }
}
