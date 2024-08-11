<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ChartAccountController extends Controller
{
    public function __construct()
    {
        // Apply the middleware to all actions
        $this->middleware('check.role');
        $this->middleware('check.role');
    }
    public function index()
    {
        $data = ChartAccount::with('childrenRecursive')->whereNull('parent_id')->get();
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'balance' => 'nullable|numeric',
            'parent_id' => 'nullable|exists:chart_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        }

        // Get validated data
        $validatedData = $validator->validated();

        // Initialize `parent_id` and `code`
        $parentId = $validatedData['parent_id'] ?? null;
        $code = null;

        if ($parentId) {
            // Find the latest record with the same parent_id
            $parent = ChartAccount::where('id', $parentId)->latest()->first();
            $parent->brance = 1;
            $parent->save();

            if (array_key_exists('balance', $validatedData)) {
                $this->GetBalanceIncress($parent->id, $validatedData["balance"]);
            } else {
                return response()->json(['errors' => "you must send balance", "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
            }
            $child = ChartAccount::where('parent_id', $parentId)->latest()->first();
            if ($child == null) {
                $code = (int)$parent->code .  1;
            } else {
                $code = (int)$child->code + 1;
            }
            // Increment the code based on the parent's code and count

        } else {
            // If no parent_id, just use the count of records
            $validatedData["balance"] = 0;
            $count = ChartAccount::where('parent_id', $parentId)->count();
            $code = ($count + 1) . "00";
        }

        // Create the new record with the generated code
        $validatedData['code'] = $code;
        $validatedData['brance'] = 0;
        $validatedData['debit'] = 0;
        $validatedData['credit'] = 0;
        ChartAccount::create($validatedData);

        return response()->json(['message' => 'Data created successfully', "status" => Response::HTTP_CREATED]);
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
    public function show($id)
    {
        $data = ChartAccount::with('childrenRecursive')->find($id);
        if ($data != null) {
            $data->transform(function ($account) {
                unset($account->brance);
                return $account;
            });
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
        if ($data != null) {
            $item=ChartAccount::where("parent_id",$id)->get();
            if ($item->isEmpty()) {
                return $data->created_at;
            }
            else{
                return null ;
            }
            } else {
                return null;
            }
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
}
