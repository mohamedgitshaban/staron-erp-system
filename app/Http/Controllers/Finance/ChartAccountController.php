<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\ChartAccountValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class ChartAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.role');
    }

/**
 * Summary of index
 * @return mixed|\Illuminate\Http\JsonResponse
 */
/**
 * Summary of index
 * @return mixed|\Illuminate\Http\JsonResponse
 */
public function index()
{
    $data = Cache::remember('chart_accounts_index', 60, function () {
        return ChartAccount::with('childrenRecursive')->whereNull('parent_id')->get();
    });

    return response()->json([
        'data' => !$data->isEmpty() ? $data : "No Data",
        'status' => !$data->isEmpty() ? Response::HTTP_OK : Response::HTTP_NO_CONTENT
    ]);
}

    /**
     * Create a new chart account.
     *
     * @param array $data
     * @return \App\Models\ChartAccount
     * @throws \Exception
     */
    public function createAccount(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:chart_accounts,id',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Validation failed: ' . json_encode($validator->errors()));
        }

        $parentId = $data['parent_id'] ?? null;

        return DB::transaction(function () use ($data, $parentId) {
            $code = $this->generateAccountCode($parentId);
            $lineage = $this->generateAccountLineage($parentId, $data['name']);

            $accountData = [
                'name' => $data['name'],
                'parent_id' => $parentId,
                'code' => $code,
                'account_lineage' => $lineage,
                'balance' => 0,
                'branch' => 0,
                'debit' => 0,
                'credit' => 0,
            ];

            return ChartAccount::create($accountData);
        });
    }

    /**
     * Store a new chart account via HTTP request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $account = $this->createAccount($request->all());
            return response()->json(['data' => $account, 'status' => Response::HTTP_CREATED]);
        } catch (\Exception $e) {
            Log::error('Error storing chart account: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create chart account.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Approve a validation request and create a new account.
     *
     * @param mixed $validationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveValidationRequest($validationId)
    {
        try {
            DB::transaction(function () use ($validationId) {
                $validationRequest = ChartAccountValidation::findOrFail($validationId);

                if ($validationRequest->status !== 'pending') {
                    throw new \Exception('Request has already been processed.');
                }

                $newAccountData = [
                    'name' => $validationRequest->name,
                    'parent_id' => $validationRequest->parent_id,
                ];

                $this->createAccount($newAccountData);
                $validationRequest->update(['status' => 'approved']);
            });

            return response()->json([
                'message' => 'Validation request approved and account created successfully.',
                'status' => Response::HTTP_OK,
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving validation request: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to approve validation request.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate a unique account code based on the parent account.
     *
     * @param mixed $parentId
     * @return string
     */
    public function generateAccountCode($parentId)
    {
        if (is_null($parentId)) {
            return '1';
        }

        $parentAccount = ChartAccount::lockForUpdate()->findOrFail($parentId);
        $parentCode = $parentAccount->code;
        $childCodes = ChartAccount::where('parent_id', $parentId)->pluck('code');

        // Fix the "Only variables should be passed by reference" error
        $lastSegment = $childCodes->map(function ($code) {
            $segments = explode('.', $code);
            return (int) end($segments);
        })->max();

        $newSegment = $lastSegment ? $lastSegment + 1 : 1;

        return "{$parentCode}.{$newSegment}";
    }


    /**
     * Generate the account lineage based on the parent account.
     *
     * @param mixed $parentId
     * @param string $name
     * @return string
     */
    private function generateAccountLineage($parentId, $name)
    {
        if (is_null($parentId)) {
            return $name;
        }

        $parentAccount = ChartAccount::findOrFail($parentId);
        return "{$parentAccount->account_lineage}.{$name}";
    }

    /**
     * Update an existing chart account.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:chart_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'status' => Response::HTTP_UNPROCESSABLE_ENTITY]);
        }

        try {
            DB::transaction(function () use ($request, $id) {
                $account = ChartAccount::lockForUpdate()->find($id);
                $validatedData = $request->validated();

                if ($account->parent_id !== $validatedData['parent_id']) {
                    $this->updateParentBalance($account->parent_id, $validatedData['parent_id'], $account->balance);
                }

                $account->update($validatedData);
            });

            return response()->json(['message' => 'Data updated successfully', 'status' => Response::HTTP_OK]);
        } catch (\Exception $e) {
            Log::error('Error updating chart account: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update chart account.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
