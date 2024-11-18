<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Finance\ChartAccountController;
use App\Models\MainJournalValidation;
use App\Models\ChartAccount;
use App\Models\MainJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use App\Models\ChartAccountValidation;

class MainJournalValidationController extends Controller
{
    /**
     * Summary of transactionMatrix
     * @var array
     */
    protected $transactionMatrix = [
        'HR' => [
            'C1' => ['debit' => 'Salaries Expense', 'credit' => 'Cash'],
            'C2' => ['debit' => 'Employee Benefits Expense', 'credit' => 'Accounts Payable'],
        ],
        'SupplyChain' => [
            'C1' => ['debit' => 'New Material Inventory', 'credit' => 'Accounts Payable'],
            'C2' => ['debit' => 'Accounts Payable', 'credit' => 'New Material Inventory'],
        ],
        'Sales' => [
            'C1' => ['debit' => 'Accounts Receivable', 'credit' => 'Revenue'],
        ],
        'Finance' => [
            'C1' => ['debit' => 'Bank Account', 'credit' => 'Liquidity Account'],
            'C2' => ['debit' => 'Bank Account', 'credit' => 'Cash'],
        ],
    ];

    protected $chartAccountController;
    protected $chartAccountValidationController;

    /**
     * Constructor with Dependency Injection.
     *
     * @param ChartAccountController $chartAccountController
     * @param ChartAccountValidationController $chartAccountValidationController
     */
    public function __construct(
        ChartAccountController $chartAccountController,
        ChartAccountValidationController $chartAccountValidationController
    ) {
        $this->chartAccountController = $chartAccountController;
        $this->chartAccountValidationController = $chartAccountValidationController;
    }

    /**
     * Summary of index
     * @return \Illuminate\Http\JsonResponse
     */

    public function index()
    {
        $requests = MainJournalValidation::with(['user', 'debitAccount', 'creditAccount'])->get();

        $requestsWithDetails = $requests->map(function ($item): array {
            return [
                'id' => $item->id,
                'date' => $item->date,
                'debit_id' => $item->debit_id,
                'credit_id' => $item->credit_id,
                'value' => $item->value,
                'description' => $item->description,
                'requested_by' => $item->requested_by,
                'status' => $item->status,
                'transaction_type' => $item->transaction_type,
                'request_source' => $item->request_source,
                'userImage' => $item->user->profileimage ?? '/uploads/defabult.png',
                'userName' => $item->user->name ?? 'Unknown User',
                'debit_account_name' => $item->debitAccount->name ?? 'Unknown Account',
                'credit_account_name' => $item->creditAccount->name ?? 'Unknown Account',
            ];
        });

        return response()->json([
            'data' => $requestsWithDetails,
            'status' => Response::HTTP_OK,
        ], Response::HTTP_OK);
    }

    /**
     * Store a new validation request.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|string|max:255',
            'request_source' => 'required|string|max:255',
            'debit_id' => 'nullable|exists:chart_accounts,id',
            'debit_account_name' => 'nullable|string|max:255',
            'credit_id' => 'nullable|exists:chart_accounts,id',
            'credit_account_name' => 'nullable|string|max:255',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'requested_by' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validatedData = $validator->validated();
        $validatedData['date'] = now()->toDateString();
        $validatedData['status'] = 'pending';

        // Set default account names if not provided and no IDs are given
        /**
         * this naming needs to be handled more acurate than this
         */
        if (empty($validatedData['debit_id'])) {
            $validatedData['debit_account_name'] = $validatedData['debit_account_name'] ?? "Debit Account for {$validatedData['transaction_type']}";
        }

        if (empty($validatedData['credit_id'])) {
            $validatedData['credit_account_name'] = $validatedData['credit_account_name'] ?? "Credit Account for {$validatedData['transaction_type']}";
        }

        // Ensure that both debit_id and debit_account_name are not provided simultaneously
        if (!empty($validatedData['debit_id']) && !empty($validatedData['debit_account_name'])) {
            return response()->json([
                'message' => 'You cannot provide both debit_id and debit_account_name. Please choose one.',
                'status' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!empty($validatedData['credit_id']) && !empty($validatedData['credit_account_name'])) {
            return response()->json([
                'message' => 'You cannot provide both credit_id and credit_account_name. Please choose one.',
                'status' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        }

        // Handle new material creation request for debit account
        // Handle new material creation request for debit account
        if (empty($validatedData['debit_id']) && !empty($validatedData['debit_account_name'])) {
            $existingDebitAccount = ChartAccount::where('name', $validatedData['debit_account_name'])->first();
            if ($existingDebitAccount) {
                $validatedData['debit_id'] = $existingDebitAccount->id;
            } else {
                // Set the status to pending account creation and do not create the account validation request yet
                $validatedData['status'] = 'pending account creation';
                $validatedData['debit_account_name'] = $validatedData['debit_account_name'];
                Log::info("Pending account creation for new debit account: {$validatedData['debit_account_name']}");
            }
        }

        // Handle new material creation request for credit account
        if (empty($validatedData['credit_id']) && !empty($validatedData['credit_account_name'])) {
            $existingCreditAccount = ChartAccount::where('name', $validatedData['credit_account_name'])->first();
            if ($existingCreditAccount) {
                $validatedData['credit_id'] = $existingCreditAccount->id;
            } else {
                // Set the status to pending account creation and do not create the account validation request yet
                $validatedData['status'] = 'pending account creation';
                $validatedData['credit_account_name'] = $validatedData['credit_account_name'];
                Log::info("Pending account creation for new credit account: {$validatedData['credit_account_name']}");
            }
        }


        try {
            DB::beginTransaction();
            $mainJournalValidation = MainJournalValidation::create($validatedData);
            DB::commit();

            return response()->json([
                'data' => $mainJournalValidation,
                'status' => Response::HTTP_CREATED,
                'message' => 'Validation request submitted successfully.',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing validation request: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to submit validation request. Please try again later.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Approve a validation request.
     */
    public function approve($id)
    {
        $request = MainJournalValidation::lockForUpdate()->findOrFail($id);

        if ($request->status !== 'pending') {
            return response()->json([
                'message' => 'Request already processed.',
                'status' => Response::HTTP_CONFLICT,
            ], Response::HTTP_CONFLICT);
        }

        try {
            DB::beginTransaction();

            $department = $request->request_source;
            $transactionType = $request->transaction_type;

            if (!isset($this->transactionMatrix[$department][$transactionType])) {
                return response()->json([
                    'message' => 'Invalid transaction type for the specified request source.',
                    'status' => Response::HTTP_BAD_REQUEST,
                ], Response::HTTP_BAD_REQUEST);
            }

            $transaction = $this->transactionMatrix[$department][$transactionType];
            $debitAccountName = $transaction['debit'];
            $creditAccountName = $transaction['credit'];

            $debitAccount = ChartAccount::lockForUpdate()->where('name', $debitAccountName)->first();
            $creditAccount = ChartAccount::lockForUpdate()->where('name', $creditAccountName)->first();

            $this->handleMissingAccount($request, 'debit', $debitAccount, 'Assets');
            $this->handleMissingAccount($request, 'credit', $creditAccount, 'Liabilities');

            if ($request->status === 'pending account creation') {
                $request->save();
                DB::commit();
                return response()->json([
                    'message' => 'Account validation request created. Waiting for account creation.',
                    'status' => Response::HTTP_OK,
                ], Response::HTTP_OK);
            }

            if ($this->validateAccounts($debitAccount, $creditAccount)) {
                $this->chartAccountValidationController->finalizeApproval($request, $debitAccount, $creditAccount);
            } else {
                return response()->json([
                    'message' => 'Missing debit or credit account. Approval cannot proceed.',
                    'status' => Response::HTTP_BAD_REQUEST,
                ], Response::HTTP_BAD_REQUEST);
            }

            $request->save();
            DB::commit();

            return response()->json([
                'message' => 'Processed successfully.',
                'status' => Response::HTTP_OK,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Approval error for request ID {$id}: " . $e->getMessage());
            return response()->json([
                'message' => 'Approval failed. Please try again later.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function handleMissingAccount($request, $type, $account, $defaultCategory)
    {
        if ($account) {
            return;
        }

        try {
            // Start a transaction for creating the validation request
            DB::beginTransaction();

            // Create the account validation request
            $validation = $this->createAccountValidationRequest(ucfirst($type), $request, $defaultCategory);

            // Ensure the validation record is persisted before committing
            if (is_null($validation) || !$validation->exists) {
                DB::rollBack();
                throw new \Exception("Failed to create account validation request for {$type} account.");
            }

            // Commit the transaction to ensure the validation record is saved
            DB::commit();

            // Verify that the validation record exists in the database
            $validationCheck = ChartAccountValidation::find($validation->id);
            if (!$validationCheck) {
                throw new \Exception("Validation record with ID {$validation->id} does not exist in `chart_account_validations`.");
            }

            // Check the status of the validation record
            if ($validationCheck->status !== 'pending') {
                throw new \Exception("Validation record status is not 'pending'. Current status: {$validationCheck->status}.");
            }

            // Update the MainJournalValidation request with the validation ID
            if ($type === 'debit') {
                $request->debit_validation_id = $validationCheck->id;
            } elseif ($type === 'credit') {
                $request->credit_validation_id = $validationCheck->id;
            }

            $request->status = 'pending account creation';

            // Save the MainJournalValidation request
            $request->save();
            Log::info("Updated MainJournalValidation request with {$type} validation ID: {$validationCheck->id} (Request ID: {$request->id}).");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in handleMissingAccount: " . $e->getMessage());
            throw new \Exception("Unable to handle missing {$type} account.");
        }
    }




    private function validateAccounts($debitAccount, $creditAccount)
    {
        return $debitAccount && $creditAccount;
    }

    private function createAccountValidationRequest($type, $request, $defaultCategory)
    {
        try {
            // Retrieve the account name from the request or use a default name
            $name = $type === 'debit'
                ? ($request->debit_account_name ?? "Debit Account for {$request->transaction_type}")
                : ($request->credit_account_name ?? "Credit Account for {$request->transaction_type}");

            if (!$name) {
                throw new \Exception("Account name is required for creating a validation request.");
            }

            // Check if the account already exists
            $existingAccount = ChartAccount::where('name', $name)->first();
            if ($existingAccount) {
                Log::info("Existing {$type} account found: {$existingAccount->id} (Request ID: {$request->id}).");
                return $existingAccount;
            }

            // Get the parent ID based on the default category (e.g., Assets, Liabilities)
            $parentId = $this->getParentAccountId($defaultCategory);

            // Create a validation request for the new account
            $accountValidation = ChartAccountValidation::create([
                'name' => $name,
                'parent_id' => $parentId,
                'requested_by' => $request->requested_by,
                'hierarchy' => json_encode([$defaultCategory]),
                'request_source' => "{$request->request_source}_{$request->transaction_type}",
                'status' => 'pending',
            ]);

            Log::info("Created account validation request (ID: {$accountValidation->id}) for missing {$type} account (Request ID: {$request->id}).");

            return $accountValidation;
        } catch (\Exception $e) {
            Log::error("Failed to create account validation request: " . $e->getMessage());
            throw new \Exception("Unable to create account validation request.");
        }
    }


    private function getParentAccountId($category)
    {
        try {
            // Find the root parent account based on the category name (e.g., 'Assets', 'Liabilities')
            $parentAccount = ChartAccount::where('name', $category)->whereNull('parent_id')->first();

            if (!$parentAccount) {
                Log::error("Parent account for category '{$category}' not found.");
                throw new \Exception("Parent account for category '{$category}' not found.");
            }

            return $parentAccount->id;
        } catch (\Exception $e) {
            Log::error("Error in getParentAccountId: " . $e->getMessage());
            throw new \Exception("Unable to determine parent account ID for category {$category}.");
        }
    }
}
