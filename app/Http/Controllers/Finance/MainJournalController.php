<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\MainJournal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class MainJournalController extends Controller
{
    private $ChartAccountController;

    public function __construct(ChartAccountController $ChartAccountController)
    {
        $this->ChartAccountController = $ChartAccountController;
    }

    public function ledger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accounts' => 'required|array|min:2',
            'accounts.*.id' => 'required|exists:chart_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        }

        $validated = $validator->validated();
        $generalData = [];

        foreach ($validated["accounts"] as $account) {
            $id = $account['id'];
            $entries = MainJournal::where("credit_id", $id)
                ->orWhere("debit_id", $id)
                ->orderBy("date")
                ->get();

            $date = $this->ChartAccountController->getDate($id);
            if (!$date) {
                return response()->json(["data" => "The record must not be a parent of another element", "status" => Response::HTTP_METHOD_NOT_ALLOWED], 200);
            }

            $balance = 0;
            $accountGeneral = [
                [
                    'date' => Carbon::parse($date)->format('Y-m-d'),
                    'description' => "Balance forward",
                    'debit' => 0,
                    'credit' => 0,
                    'balance' => $balance,
                ]
            ];

            $accountGeneral = array_merge($accountGeneral, $entries->map(function ($entry) use ($id, &$balance) {
                if ($entry->debit_id == $id) {
                    $debit = $entry->value;
                    $credit = 0;
                    $balance -= $entry->value;
                } else {
                    $debit = 0;
                    $credit = $entry->value;
                    $balance += $entry->value;
                }

                return [
                    'date' => Carbon::parse($entry->date)->format('Y-m-d'),
                    'description' => $entry->description,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $balance,
                ];
            })->toArray());

            $accountName = $this->ChartAccountController->showName($id);
            $generalData[] = [
                "account_name" => $accountName[0]->name,
                "general_ledger" => $accountGeneral
            ];
        }

        return response()->json(["all_ledger" => $generalData, "status" => Response::HTTP_OK], 200);
    }

    public function trial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accounts' => 'required|array|min:2',
            'accounts.*.id' => 'required|exists:chart_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        }

        $validated = $validator->validated();
        $trialBalance = [];

        foreach ($validated["accounts"] as $account) {
            $id = $account['id'];
            $credit = MainJournal::where('credit_id', $id)
                ->whereIn('debit_id', $validated["accounts"])
                ->sum("value");
            $debit = MainJournal::where('debit_id', $id)
                ->whereIn('credit_id', $validated["accounts"])
                ->sum("value");

            $accountName = $this->ChartAccountController->showName($id);
            $trialBalance[] = [
                "account_name" => $accountName[0]->name,
                "debit" => $debit,
                "credit" => $credit
            ];
        }

        return response()->json(["trial_balance" => $trialBalance, "status" => Response::HTTP_OK], 200);
    }

    public function getAccountHierarchyName($accountId)
    {
        $account = ChartAccount::find($accountId);
        if (!$account) return null;

        $hierarchy = [];
        while ($account) {
            $hierarchy[] = $account->name;
            $account = $account->parent;  // Assuming each ChartAccount has a `parent` relationship
        }
    }

    public function index()
{
    $data = MainJournal::orderBy("date")->get()->groupBy("date");

    if ($data->isEmpty()) {
        return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
    }

    $transformedData = $data->map(function ($items, $date) {
        return [
            'date' => $date,
            'data' => $items->map(function ($item) {
                $debitHierarchy = $this->ChartAccountController->getAccountHierarchyName($item->debit_id);
                $creditHierarchy = $this->ChartAccountController->getAccountHierarchyName($item->credit_id);

                return [
                    'id' => $item->id,
                    'date' => $item->date,
                    'debit_id' => $item->debit_id,
                    'debit_account_description' => $debitHierarchy,
                    'credit_id' => $item->credit_id,
                    'credit_account_description' => $creditHierarchy,
                    'value' => $item->value,
                    'description' => $item->description,
                ];
            })->toArray(),
        ];
    })->values();

    return response()->json(["data" => $transformedData, "status" => Response::HTTP_OK], 200);
}



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'debit_id' => 'required|integer|exists:chart_accounts,id',
            'credit_id' => 'required|integer|exists:chart_accounts,id',
            'value' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        }

        $validated = $validator->validated();
        $validated["date"] = Carbon::now();

        // Generate description based on account type or transaction type
        $transactionDescriptions = [
            'bank' => "Outgoing money from bank to ",
            'purchase' => "Payment made for purchase ",
            'sale' => "Income from sale transaction ",
            'expense' => "Expense recorded for "
        ];

        $accountType = $this->ChartAccountController->getAccountType($validated['debit_id']); // Assume this function exists
        $validated['description'] = $transactionDescriptions[$accountType] ?? "Transaction processed"; // Assign description based on type

        MainJournal::create($validated);

        // Update account balances
        $this->ChartAccountController->GetMainJournalIncrease($validated["credit_id"], $validated["value"]);
        $this->ChartAccountController->GetMainJournalDecrease($validated["debit_id"], $validated["value"]);

        return response()->json(['message' => 'Data created successfully', "status" => Response::HTTP_CREATED]);
    }

    public function show($id)
    {
        $data = MainJournal::with('debitAccount', 'creditAccount')->find($id);
        if ($data) {
            unset($data->created_at, $data->updated_at);
            return response()->json(["data" => $data, "status" => Response::HTTP_OK]);
        }

        return response()->json(["data" => "No data found", "status" => Response::HTTP_NOT_FOUND]);
    }

    public function bankProfile($id)
    {
        return MainJournal::where("credit_id", $id)
            ->orWhere("debit_id", $id)
            ->orderBy("date")
            ->get();
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'debit_id' => 'required|integer|exists:chart_accounts,id',
            'credit_id' => 'required|integer|exists:chart_accounts,id',
            'value' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        }

        $data = MainJournal::find($id);
        if ($data) {
            $validated = $validator->validated();
            $validated["date"] = Carbon::now();

            $accountType = $this->ChartAccountController->getAccountType($validated['debit_id']);
            $validated['description'] = $transactionDescriptions[$accountType] ?? "Transaction processed";

            $data->update($validated);

            $this->ChartAccountController->GetMainJournalIncrease($validated["credit_id"], $validated["value"]);
            $this->ChartAccountController->GetMainJournalDecrease($validated["debit_id"], $validated["value"]);

            return response()->json(['message' => 'Data updated successfully', "status" => Response::HTTP_OK]);
        }

        return response()->json(['message' => 'Data not found', "status" => Response::HTTP_NOT_FOUND]);
    }

    public function destroy($id)
    {
        $data = MainJournal::find($id);
        if ($data) {
            $this->ChartAccountController->GetMainJournalIncrease($data->debit_id, $data->value);
            $this->ChartAccountController->GetMainJournalDecrease($data->credit_id, $data->value);
            $data->delete();

            return response()->json(['message' => 'Data deleted successfully', "status" => Response::HTTP_OK]);
        }

        return response()->json(['message' => 'Data not found', "status" => Response::HTTP_NOT_FOUND]);
    }
}
