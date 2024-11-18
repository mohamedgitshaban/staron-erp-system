<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\MainJournal;
use App\Models\ChartAccount;
use App\Models\MainJournalValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class MainJournalController extends Controller
{
    protected $ChartOfAccounts;
    public function __construct() {
        $this->ChartOfAccounts = new ChartAccount();
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $mainJournalEntries = MainJournal::with(['debitAccount', 'creditAccount'])
            ->orderBy('date', 'desc')
            ->paginate($perPage);

        $data = $mainJournalEntries->map(function ($entry) {
            // Safely fetch the names of the debit and credit accounts
            $debitAccountName = optional(ChartAccount::find($entry->debit_id))->name ?? 'Unknown Account';
            $creditAccountName = optional(ChartAccount::find($entry->credit_id))->name ?? 'Unknown Account';

        return [
            'id' => $entry->id,
            'date' => Carbon::parse($entry->date)->format('Y-m-d'),
            'debit_account' => $entry->debitAccount->name ?? 'Unknown Account',
            'credit_account' => $entry->creditAccount->name ?? 'Unknown Account',
            'debit_id' => $entry->debit_id,
            'debit_account_name' => $debitAccountName,
            'credit_id' => $entry->credit_id,
            'credit_account_name' => $creditAccountName,
            'value' => $entry->value,
            'description' => $entry->description,
        ];
    });

    return response()->json([
        'data' => $data,
        'status' => Response::HTTP_OK,
        'pagination' => [
            'current_page' => $mainJournalEntries->currentPage(),
            'per_page' => $mainJournalEntries->perPage(),
            'total' => $mainJournalEntries->total(),
        ],
    ]);
}


    public function ledger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accounts' => 'required|array|min:2',
            'accounts.*.id' => 'required|exists:chart_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $validator->validated();
        $generalData = [];

        foreach ($validated['accounts'] as $account) {
            $id = $account['id'];
            $entries = MainJournal::where('credit_id', $id)
                ->orWhere('debit_id', $id)
                ->orderBy('date')
                ->get();

            $balance = 0;
            $accountGeneral = [
                [
                    'date' => now()->format('Y-m-d'),
                    'description' => 'Balance forward',
                    'debit' => 0,
                    'credit' => 0,
                    'balance' => $balance,
                ]
            ];

            $accountGeneral = array_merge($accountGeneral, $entries->map(function ($entry) use ($id, &$balance) {
                $debit = $entry->debit_id == $id ? $entry->value : 0;
                $credit = $entry->credit_id == $id ? $entry->value : 0;
                $balance += ($credit - $debit);

                return [
                    'date' => Carbon::parse($entry->date)->format('Y-m-d'),
                    'description' => $entry->description,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $balance,
                ];
            })->toArray());

            $accountName = $this->getAccountName($id);
            $generalData[] = [
                'account_name' => $accountName,
                'general_ledger' => $accountGeneral,
            ];
        }

        return response()->json(['all_ledger' => $generalData], Response::HTTP_OK);
    }

    public function trial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accounts' => 'required|array|min:2',
            'accounts.*.id' => 'required|exists:chart_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $validator->validated();
        $trialBalance = [];

        foreach ($validated['accounts'] as $account) {
            $id = $account['id'];
            $entries = MainJournal::where('debit_id', $id)
                ->orWhere('credit_id', $id)
                ->selectRaw('SUM(CASE WHEN debit_id = ? THEN value ELSE 0 END) as debit, SUM(CASE WHEN credit_id = ? THEN value ELSE 0 END) as credit', [$id, $id])
                ->first();

            $accountName = $this->getAccountName($id);
            $trialBalance[] = [
                'account_name' => $accountName,
                'debit' => $entries->debit ?? 0,
                'credit' => $entries->credit ?? 0,
            ];
        }

        return response()->json(['trial_balance' => $trialBalance], Response::HTTP_OK);
    }

    public function storeApprovedTransaction($transaction)
    {
        DB::transaction(function () use ($transaction) {
            $debitAccount = ChartAccount::lockForUpdate()->find($transaction->debit_id);
            $creditAccount = ChartAccount::lockForUpdate()->find($transaction->credit_id);

            if (!$debitAccount || !$creditAccount) {
                throw new \Exception('One or both accounts do not exist.');
            }

            MainJournal::create([
                'date' => $transaction->date,
                'debit_id' => $debitAccount->id,
                'credit_id' => $creditAccount->id,
                'value' => $transaction->value,
                'description' => $transaction->description,
            ]);

            $this->updateBalances($debitAccount, $creditAccount, $transaction->value);
        });

        return response()->json(['message' => 'Transaction approved and recorded successfully'], Response::HTTP_CREATED);
    }

    private function updateBalances($debitAccount, $creditAccount, $value)
    {
        $debitAccount->balance += in_array($debitAccount->type, ['asset', 'expense']) ? $value : -$value;
        $debitAccount->save();

        $creditAccount->balance += in_array($creditAccount->type, ['liability', 'equity', 'income']) ? $value : -$value;
        $creditAccount->save();
    }

    private function getAccountName($id)
    {
        return ChartAccount::find($id)->name ?? 'Unknown Account';
    }
}
