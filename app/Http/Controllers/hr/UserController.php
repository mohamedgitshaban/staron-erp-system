<?php

namespace App\Http\Controllers\hr;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Rules\Weekday;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class UserController extends Controller
{
    public function index()
    {

        $User = User::latest()->get();
        if (!$User->isEmpty()) {
            return response()->json(["data" => $User, "status" => 200]);
        } else {
            return response()->json(["data" => "there is no users", "status" => 404]);
        }
    }
    public function AllSuperVisor(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'department' => 'required|string|max:255',

        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors(), $request->all()], "status" => Response::HTTP_UNAUTHORIZED], Response::HTTP_OK);
        } else {
            $validatedData = $validatedData->validated();
            $User = User::latest()->where("Supervisor", null)->where("department", $validatedData["department"])->get();
            if (!$User->isEmpty()) {
                return response()->json(["data" => $User, "status" => 200]);
            } else {
                return response()->json(["data" => "there is no users", "status" => 404]);
            }
        }
    }
    public function department()
    {

        $authUser = Auth::user();

        // Fetch users in the same department
        $usersInSameDepartment = User::where('department', $authUser->department)->get();
        if (!$usersInSameDepartment->isEmpty()) {
            return response()->json(["data" => $usersInSameDepartment, "status" => 200]);
        } else {
            return response()->json(["data" => "there is no users", "status" => 404]);
        }
    }
    public function DepartmentEmployee()
    {
        $user = Auth::guard('sanctum')->user();

        $User = User::latest()->where("department", $user->department)->get();
        if (!$User->isEmpty()) {
            return response()->json(["data" => $User, "status" => 200]);
        } else {
            return response()->json(["data" => "there is no users", "status" => 404]);
        }
    }
    public function create(Request $request)
    {

        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|min:8',
            'password_confirm' => 'required|same:password',
            'date' => 'required|date',
            'hr_code' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'profileimage' => 'required|image|mimes:jpg,bmp,png,jpeg',
            'salary' => 'required|numeric|min:0',
            'Trancportation' => 'required|numeric|min:0',
            'kpi' => 'required|numeric|min:0',
            'overtime' => 'required|boolean',
            'overtime_value' => 'nullable|required_if:overtime,true|numeric|min:1',
            'tax' => 'required|numeric|min:0',
            'Supervisor' => 'nullable|exists:users,id',
            'EmploymentDate' => 'required|date',
            'MedicalInsurance' => 'required|numeric|min:0',
            'SocialInsurance' => 'required|numeric|min:0',
            'phone' =>  ['required', 'regex:/^01[0-2|5][0-9]{8}$/'],
            'department' => 'required|string|max:255|in:administration,Executive,Human Resources,Technical Office,Sales Office,Operation Office,Control Office,Supply Chain,Markiting,Research & Development,Finance',
            'job_role' => 'required|string|max:255',
            'job_tybe' => 'required|string|in:Full-Time,Part-Time,Internship,Contractor',
            'pdf' => 'required|file|mimes:zip,rar',
            'TimeStamp' => 'required|string|in:Whats App,Office',
            'grade' => 'required|string|in:Executive,Manager,First Staff,Seconed Staff,Third Staff,Fourth Staff,Craftsman,Steward',
            'segment' => 'required|string|in:G & A,COR',
            'startwork' => ['required', 'string', new Weekday],
            'endwork' => ['required', 'string', new Weekday],
            'clockin' => 'required|date_format:H:i:s',
            'clockout' => 'required|after_or_equal:clockin|date_format:H:i:s',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors(), $request->all()], "status" => Response::HTTP_UNAUTHORIZED], Response::HTTP_OK);
        } else {
            $validator = $validatedData->validated();
            $file = $request->file('pdf');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $validator["pdf"] = '/uploads/userdoc/' . $fileName;
            // Move the file to the desired location
            $file->move(public_path('uploads/userdoc'), $fileName);

            $file = $request->file('profileimage');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $validator["profileimage"] = '/uploads/profileimages/' . $fileName;
            // Move the file to the desired location
            $file->move(public_path('uploads/profileimages'), $fileName);

            // Validation passed, create the user
            User::create($validator);

            return response()->json(['message' => 'User created successfully', "status" => Response::HTTP_OK]);
        }
    }
    public function show($id)
    {
        $user = User::with('supervisor')->find($id);
        $presen = 100;
        $Department = 100;
        $overall = 1;
        if ($user != null) {
            if ($user->supervisor != null) {
                $supervisorName = $user->supervisor->name;
            } else {
                $supervisorName = "No Manger";
            }

            // Modify the user object to include the supervisor name
            $user->supervisor_name = $supervisorName;
            $user->presenmargin = $presen;
            $user->Departmentrate = $Department;
            $user->overallrate = $overall;

            return response()->json(["data" => $user, "status" => 202]);
        } else {
            return response()->json(["data" => "There is no user with the provided ID", "status" => 404]);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($id),
                'max:255',
            ],
            'password' => 'required|min:8',
            'password_confirm' => 'required|same:password',
            'date' => 'required|date',
            'hr_code' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'profileimage' => 'required|image|mimes:jpg,bmp,png,jpeg',
            'salary' => 'required|numeric|min:0',
            'Trancportation' => 'required|numeric|min:0',
            'kpi' => 'required|numeric|min:0',
            'overtime' => 'required|boolean',
            'overtime_value' => 'nullable|required_if:overtime,true|numeric|min:1',
            'tax' => 'required|numeric|min:0',
            'Supervisor' => 'nullable|exists:users,id',
            'EmploymentDate' => 'required|date',
            'MedicalInsurance' => 'required|numeric|min:0',
            'SocialInsurance' => 'required|numeric|min:0',
            'phone' =>  ['required', 'regex:/^01[0-2|5][0-9]{8}$/'],
            'department' => 'required|string|max:255|in:administration,Executive,Human Resources,Technical Office,Sales Office,Operation Office,Control Office,Supply Chain,Markiting,Research & Development,Finance',
            'job_role' => 'required|string|max:255',
            'job_tybe' => 'required|string|in:Full-Time,Part-Time,Internship,Contractor',
            'pdf' => 'required|file|mimes:zip,rar',
            'TimeStamp' => 'required|string|in:Whats App,Office',
            'grade' => 'required|string|in:Executive,Manager,First Staff,Seconed Staff,Third Staff,Fourth Staff,Craftsman,Steward',
            'segment' => 'required|string|in:G & A,COR',
            'startwork' => ['required', 'string', new Weekday],
            'endwork' => ['required', 'string', new Weekday],
            'clockin' => 'required|date_format:H:i:s',
            'clockout' => 'required|after_or_equal:clockin|date_format:H:i:s',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors(), $request->all()], "status" => Response::HTTP_UNAUTHORIZED], Response::HTTP_OK);
        } else {
            $validator = $validatedData->validated();
            if ($request->hasFile('profileimage')) {
                $file = $request->file('profileimage');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $validator["profileimage"] = '/uploads/profileimages/' . $fileName;
                $file->move(public_path('uploads/profileimages'), $fileName);
            }
            if ($request->hasFile('pdf')) {
                $file = $request->file('pdf');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $validator["pdf"] = '/uploads/userdoc' . $fileName;
                // Move the file to the desired location
                $file->move(public_path('uploads/userdoc'), $fileName);
            }

            // Validation passed, create the user
            User::where('id', $id)->update($validator);

            return response()->json(['message' => 'User updated successfully', "status" => Response::HTTP_OK], 200);
        }
    }
    public function destroy(Request $request, $id)
    {
        $User = User::find($id);

        if ($User) {
            // $validatedData = $validatedData->validated();
            $User->isemploee = false;
            // $User->Reason=$validatedData["Reason"];
            $User->EmploymentDateEnd = now();
            $User->save();

            return response()->json(['message' => 'User deleted'], 202);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $validator->errors(),
                'status' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_OK);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'error' => 'Invalid Credentials',
                'message' => 'Invalid email or password',
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_OK);
        }

        $user = Auth::user();
        $token = $user->createToken("token")->plainTextToken;
        $cookie = cookie('jwt', $token, 60 * 60);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
            'status' => Response::HTTP_OK
        ])->withCookie($cookie);
    }
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            // 'email' => 'required|email|max:255',
            'date' => 'nullable|date',
            'phone' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'profileimage' => 'nullable|image|mimes:jpg,bmp,png',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $user = Auth::guard('sanctum')->user();
            $validate = $validator->validated();

            if ($request->hasFile('profileimage')) {
                $file = $request->file('profileimage');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/profileimages'), $fileName);
                $user->profileimage = '/uploads/profileimages' . $fileName;
            }
            User::where("id", "=", $user->id)->update($validate);
            return response()->json(['message' => 'User profile updated successfully'], 205);
        }
    }
    public function user(Request $request)
    {

        $user = Auth::guard('sanctum')->user();
        if ($user) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'status' => Response::HTTP_OK
            ], 200);
        }


        return response()->json([
            'success' => false,
            'message' => $request->cookie('jwt'),
            'status' => Response::HTTP_UNAUTHORIZED
        ], 200);
    }
    public function logout()
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be logged in to perform this action.'
            ], 401);
        }
        $userId = Auth::id();
        $user = User::find($userId);
        $user->last_login = now();
        $user->save();
        $cookie = Cookie::forget('jwt');

        return response()->json([
            'message' => 'Success'
        ])->withCookie($cookie);
    }
    public function getLastLogin()
    {
        $userId = Auth::id();
        $user = User::find($userId);

        return response()->json([
            'last_login' => $user->last_login,
        ]);
    }
}
