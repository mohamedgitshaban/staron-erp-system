<?php

namespace App\Http\Controllers;

use App\Models\ChartAccount;
use App\Models\ChartAccountValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use App\Http\Controllers\Finance\ChartAccountController;

class ChartAccountValidationController extends Controller
{
    protected $chartAccountController;

    public function __construct(ChartAccountController $chartAccountController)
    {
        $this->chartAccountController = $chartAccountController;
    }

    // List all validation requests
    public function index()
    {
        $validationRequests = ChartAccountValidation::with('user')->orderBy('created_at', 'desc')->get();

        $requestsWithDetails = $validationRequests->map(function ($item) {
            $userName = $item->user->name ?? 'Unknown User';
            $userImage = $item->user->profileimage ?? null;
            $hierarchy = $this->decodeHierarchy($item);
            $parentAccountName = optional(ChartAccount::find($item->parent_id))->name ?? 'Root';

            return [
                'id' => $item->id,
                'date' => $item->created_at->format('Y-m-d'),
                'name' => $item->name,
                'request_source' => $item->request_source,
                'requested_by' => $item->requested_by,
                'userName' => $userName,
                'userImage' => $userImage,
                'parent_id' => $item->parent_id,
                'parent_name' => $parentAccountName,
                'hierarchy' => $hierarchy,
                'status' => $item->status,
            ];
        });

        return response()->json([
            'data' => $requestsWithDetails,
            'status' => Response::HTTP_OK,
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_source' => 'required|string|max:255',
            'hierarchy' => 'required|array|min:1',
            'originating_module' => 'required|string|max:255',
        ]);

        $validated['requested_by'] = $request->user()->id;
        $validated['parent_id'] = $this->locateParent($validated['hierarchy']);

        if (is_null($validated['parent_id'])) {
            return response()->json([
                'message' => 'Unable to determine parent account.',
                'status' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        }

        $name = $this->generateAccountName($validated['originating_module'], array_pop($validated['hierarchy']));
        $encodedHierarchy = json_encode($validated['hierarchy']);
        $accountLineage = implode('.', $validated['hierarchy']) . '.' . $name;

        try {
            DB::beginTransaction();

            $validationRequest = ChartAccountValidation::create([
                'name' => $name,
                'request_source' => $validated['request_source'],
                'requested_by' => $validated['requested_by'],
                'parent_id' => $validated['parent_id'],
                'hierarchy' => $encodedHierarchy,
                'lineage' => $accountLineage,
                'status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Validation request submitted successfully.',
                'data' => $validationRequest,
                'status' => Response::HTTP_CREATED,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing validation request: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to submit validation request. Please try again later.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function approve($id)
    {
        $validationRequest = ChartAccountValidation::lockForUpdate()->findOrFail($id);

        if ($validationRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Request has already been processed.',
                'status' => Response::HTTP_CONFLICT,
            ], Response::HTTP_CONFLICT);
        }

        try {
            DB::beginTransaction();

            $hierarchy = $this->decodeHierarchy($validationRequest);
            $parentId = $this->locateParent($hierarchy);

            if (is_null($parentId)) {
                throw new \Exception('Unable to locate or create parent account.');
            }

            $code = $this->chartAccountController->generateAccountCode($parentId);
            $lineage = implode('.', $hierarchy) . '.' . $validationRequest->name;

            // Create the new account
            $finalAccount = $this->chartAccountController->createAccount([
                'name' => $validationRequest->name,
                'parent_id' => $parentId,
                'code' => $code,
                'lineage' => $lineage,
            ]);

            // Update the status of the validation request
            $validationRequest->update(['status' => 'approved']);

            // Update the related MainJournalValidation entry
            $mainJournalValidation = \App\Models\MainJournalValidation::where('debit_validation_id', $validationRequest->id)
                ->orWhere('credit_validation_id', $validationRequest->id)
                ->lockForUpdate()
                ->first();

            if ($mainJournalValidation) {
                $mainJournalValidation->status = 'approved';
                $mainJournalValidation->debit_id = $mainJournalValidation->debit_id ?? $finalAccount->id;
                $mainJournalValidation->credit_id = $mainJournalValidation->credit_id ?? $finalAccount->id;
                $mainJournalValidation->save();

                // Automatically post the journal entry
                $this->postJournalEntry($mainJournalValidation);
            }

            DB::commit();

            return response()->json([
                'message' => 'Account created successfully and journal entry posted.',
                'account' => $finalAccount,
                'status' => Response::HTTP_OK,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving request: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to approve request. Please try again later.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    private function postJournalEntry($mainJournalValidation)
    {
    try {
        DB::beginTransaction();

        $debitAccount = ChartAccount::lockForUpdate()->find($mainJournalValidation->debit_id);
        $creditAccount = ChartAccount::lockForUpdate()->find($mainJournalValidation->credit_id);

        if (!$debitAccount || !$creditAccount) {
            throw new \Exception('Debit or credit account not found.');
        }

        // Create the journal entry
        \App\Models\MainJournal::create([
            'date' => $mainJournalValidation->date,
            'debit_id' => $debitAccount->id,
            'credit_id' => $creditAccount->id,
            'value' => $mainJournalValidation->value,
            'description' => $mainJournalValidation->description,
        ]);

        // Update account balances
        $this->updateAccountBalances($debitAccount, $creditAccount, $mainJournalValidation->value);

        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to post journal entry: ' . $e->getMessage());
        throw new \Exception('Failed to post journal entry.');
    }
    }

    public function updateAccountBalances($debitAccount, $creditAccount, $value)
    {
        // Update debit account balance
        $debitAccount->balance += $value;
        $debitAccount->save();

        // Update credit account balance
        $creditAccount->balance -= $value;
        $creditAccount->save();
    }


    private function decodeHierarchy($validationRequest)
    {
        return json_decode($validationRequest->hierarchy, true) ?? [];
    }

    private function locateParent(array $hierarchy)
    {
        $parentId = null;

        foreach ($hierarchy as $levelName) {
            $existingAccount = ChartAccount::where('name', $levelName)->where('parent_id', $parentId)->first();

            if ($existingAccount) {
                $parentId = $existingAccount->id;
            } else {
                $newAccount = $this->chartAccountController->createAccount([
                    'name' => $levelName,
                    'parent_id' => $parentId,
                ]);

                $parentId = $newAccount->id;
            }
        }

        return $parentId;
    }
}
