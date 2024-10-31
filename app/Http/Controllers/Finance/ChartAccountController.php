<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\ChartAccount;
use App\Models\ChartAccountValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ChartAccountController extends Controller
{
    public function __construct()
    {
        // Apply the middleware to all actions
        $this->middleware('check.role');
    }

    public function index()
    {
        $data = ChartAccount::with('childrenRecursive')->whereNull('parent_id')->get();
        return response()->json([
            "data" => !$data->isEmpty() ? $data : "No Data",
            "status" => !$data->isEmpty() ? Response::HTTP_OK : Response::HTTP_NO_CONTENT
        ], 200);
    }

    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:chart_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        }

        $validatedData = $validator->validated();
        $parentId = $validatedData['parent_id'] ?? null;

        // Fetch the parent account and set as branch if it becomes a parent
        if ($parentId) {
            $parent = ChartAccount::find($parentId);
            $parent->brance = 1;
            $parent->save();
        }

        // Generate code and lineage for the new account
        $code = $this->generateAccountCode($parentId);
        $lineage = $this->generateAccountLineage($parentId, $validatedData['name']);

        $validatedData['code'] = $code;
        $validatedData['account_lineage'] = $lineage;
        $validatedData['balance'] = 0;
        $validatedData['brance'] = 0;
        $validatedData['debit'] = 0;
        $validatedData['credit'] = 0;

        ChartAccount::create($validatedData);

        return response()->json(['message' => 'Account created successfully', "status" => Response::HTTP_CREATED]);
    }

    /**
     * Generates account code based on parent account
     */
    private function generateAccountCode($parentId)
    {
        if ($parentId === null) {
            throw new \Exception("Parent ID is required to generate the account code.");
        }

        $parentAccount = ChartAccount::findOrFail($parentId);
        $parentCode = $parentAccount->code;
        $childAccounts = ChartAccount::where('parent_id', $parentId)->pluck('code');

        $lastSegments = $childAccounts->map(function ($code) {
            $segments = explode('.', $code);
            return (int) end($segments);
        });

        $newSegment = $lastSegments->isEmpty() ? 1 : $lastSegments->max() + 1;
        return $parentCode . '.' . $newSegment;
    }

    /**
     * Generates account lineage based on parent account and name
     */
    private function generateAccountLineage($parentId, $name)
    {
        $parentAccount = ChartAccount::findOrFail($parentId);
        return $parentAccount->account_lineage . '.' . $name;
    }

    public function approveValidationRequest($validationId)
    {   
        $validationRequest = ChartAccountValidation::findOrFail($validationId);

        if ($validationRequest->status !== 'pending') {
            return response()->json(['message' => 'Request has already been processed.', "status" => Response::HTTP_CONFLICT], 200);
        }

        $newAccountData = [
            'name' => $validationRequest->name,
            'parent_id' => $validationRequest->parent_id,
            'requested_by' => $validationRequest->requested_by,
        ];

        $this->store(new Request($newAccountData));
        $validationRequest->approve();

        return response()->json(['message' => 'Validation request approved and account created successfully.', "status" => Response::HTTP_OK]);
    }

    public function all()
    {
        $data = ChartAccount::latest()->get();
        if (!$data->isEmpty()) {
            $data->transform(function ($account) {
                unset($account->brance);
                return $account;
            });
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);
        } else {
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
    }
    public function parent()
    {
        $data = ChartAccount::latest()->where("brance", 1)->orderBy('parent_id')->get();

        if (!$data->isEmpty()) {
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);
        } else {
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
    }
    public function child()
    {
        $data = ChartAccount::latest()->where("brance", 0)->orderBy('parent_id')->get();

        if (!$data->isEmpty()) {
            return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);
        } else {
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }
    }


    private function GetBalanceIncress($id, $balance)
    {
        $data = ChartAccount::find($id);
        $data->balance += $balance;
        $data->save();
        if ($data->parent_id != null) {
            $this->GetBalanceIncress($data->parent_id, $balance);
        }
    }
    private function GetBalanceDecress($id, $balance)
    {
        $data = ChartAccount::find($id);
        $data->balance -= $balance;
        $data->save();
        if ($data->parent_id != null) {
            $this->GetBalanceDecress($data->parent_id, $balance);
        }
    }
    public function GetMainJournalIncress($id, $balance)
    {
        $data = ChartAccount::find($id);
        $data->balance += $balance;
        $data->credit += $balance;
        $data->save();
        if ($data->parent_id != null) {
            $this->GetMainJournalIncress($data->parent_id, $balance);
        }
    }

    public function GetMainJournalDecress($id, $balance)
    {
        $data = ChartAccount::find($id);
        $data->balance -= $balance;
        $data->debit += $balance;
        $data->save();
        if ($data->parent_id != null) {
            $this->GetMainJournalDecress($data->parent_id, $balance);
        }
    }

    public function GetFullAccountName($id)
    {
        $data = ChartAccount::find($id);
        if ($data->parent_id != null) {
                    //  dd($data->parent_id);

            return $this->GetFullAccountName($data->parent_id) . "-" .$data->name ;
        } else {
            return $data->name;
        }
    }
    public function allunplanedfees()
    {
        $data = ChartAccount::with('childrenRecursive')->find(130);
        if ($data != null) {

                unset($account->brance);

            return response()->json(["data" => $data, "status" => Response::HTTP_OK]);
        } else {
            return response()->json(["data" => "no data", "status" => Response::HTTP_NOT_FOUND]);
        }
    }
    public function show($id)
    {
        $data = ChartAccount::with('childrenRecursive')->find($id);
        if ($data != null) {

                unset($account->brance);

            return response()->json(["data" => $data, "status" => Response::HTTP_OK]);
        } else {
            return response()->json(["data" => "no data", "status" => Response::HTTP_NOT_FOUND]);
        }
    }
    public function showName($id)
    {
        $acc = ChartAccount::find($id);

        if ($acc) {
            return $acc;
        }

        return null;
    }
    public function getDate($id)
    {
        $data = ChartAccount::find($id);
        if ($data && ChartAccount::where('parent_id', $id)->doesntExist()) {
            // dd($data[0]->created_at);
            return $data[0]->created_at;
        }
        return null;
    }


    public function update(Request $request, $id)
    {

        $validator = validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:chart_accounts,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        } else {
            $data = ChartAccount::find($id);
            if ($data != null) {
                $validator = $validator->validated();
                if ($data->parent_id == $validator["parent_id"]) {
                    $data->update($validator);
                } else {

                    // Initialize `parent_id` and `code`
                    $parentId = $validator['parent_id'] ?? null;
                    $code = null;

                    if ($parentId) {
                        // Find the latest record with the same parent_id
                        $parent = ChartAccount::where('id', $parentId)->latest()->first();
                        $parent->brance = 1;
                        $child = ChartAccount::where('parent_id', $parentId)->latest()->first();
                        if ($child == null) {
                            $code = (int)$parent->code .  1;
                        } else {
                            $code = (int)$child->code + 1;
                        }
                        // Increment the code based on the parent's code and count

                    } else {
                        // If no parent_id, just use the count of records
                        $count = ChartAccount::where('parent_id', $parentId)->count();
                        $code = ($count) . "00";
                    }

                    // Create the new record with the generated code
                    $validator['code'] = $code;
                    $oldParentId = $data->parent_id;
                    $data->update($validator);
                    if ($oldParentId != null) {
                        $updateoldbrance = ChartAccount::where('parent_id', $oldParentId)->get();
                        $this->GetBalanceDecress($oldParentId, $data->balance);
                        $this->GetBalanceIncress($validator['parent_id'], $data->balance);
                        if ($updateoldbrance->isEmpty()) {
                            ChartAccount::where("id", $oldParentId)->update([
                                "brance" => 0
                            ]);
                        }
                    }
                }
                return response()->json(['message' => 'data updated successfully', "status" => Response::HTTP_OK]);
            }
        }
    }

    public function destroy($id)
    {
        $data = ChartAccount::find($id);
        if ($data != null) {
            $data->delete();
            return response()->json(["data" => "data deleted successfully", "status" => Response::HTTP_OK], 200);
        }
        return response()->json(["data" => "Method Not Allowed", "status" => Response::HTTP_METHOD_NOT_ALLOWED], 200);
    }


    public function getLeafAccounts() {
        $data = ChartAccount::doesntHave('childrenRecursive')->get();
        if($data->isEmpty()) {
            return response()->json([
                "data" => "No Content",
                "status" => Response::HTTP_NO_CONTENT
            ],200);
        }
        return response()->json([
            "data" => $data,
            "status"=> Response::HTTP_OK
        ],200);
    }

   public function totalParentAccountsBalance()
    {
        // Fetch all parent accounts (where parent_id is null)
        $accounts = DB::table('chart_accounts')->where('parent_id', null)->get();

        // Check if accounts are empty
        if ($accounts->isEmpty()) {
            return response()->json([
                "data" => "No Content Found",
                "status" => Response::HTTP_NO_CONTENT
            ], 200);
        }

        // Sum the balances of the accounts
        $accountsBalance = $accounts->sum('balance');

        // Return the total balance
        return response()->json([
            "data" => $accountsBalance,
            "status" => Response::HTTP_OK
        ], 200);
    }
    public function findSiblingAccounts(Request $request)
    {
        $id = $request->get('id');

        // Find the original account
        $originAccount = ChartAccount::find($id);

        if (!$originAccount) {
            return response()->json([
                "data" => "Account not found",
                "status" => Response::HTTP_NOT_FOUND
            ], 200);
        }

        // Find sibling accounts with the same parent ID, excluding the original account itself
        $siblings = ChartAccount::where('parent_id', $originAccount->parent_id)
                    ->where('id', '!=', $id)
                    ->get();

        if ($siblings->isEmpty()) {
            return response()->json([
                "data" => "No sibling accounts found",
                "status" => Response::HTTP_NO_CONTENT
            ], 200);
        }

        return response()->json([
            "data" => $siblings,
            "status" => Response::HTTP_OK
        ], 200);
    }


}
