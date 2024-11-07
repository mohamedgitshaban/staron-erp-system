<?php

namespace App\Http\Controllers;

use App\Models\MainJournalValidation;
use App\Models\ChartOfAccountsValidation;
use App\Models\MainJournal;
use App\Models\ChartAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class MainJournalValidationController extends Controller
{
    // Show all pending requests
    public function index()
    {
        $pendingRequests = MainJournalValidation::where('status', 'pending')->get();

        $pendingRequestsWithImageUri = $pendingRequests->map(function ($item) {
            $user = User::find($item->requested_by);
            $item->userImage = $user ? $user->profileimage : null;
            $item->userName = $user ? $user->name : null;
            return $item;
        });

        return response()->json([
            'data' => $pendingRequestsWithImageUri,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    // Show specific request
    public function show($id)
    {
        $request = MainJournalValidation::findOrFail($id);

        // Append user details
        $user = User::find($request->requested_by);
        $request->userImage = $user ? $user->profileimage : null;
        $request->userName = $user ? $user->name : null;

        return response()->json([
            'data' => $request,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    // Store a new validation request
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'debit_id' => 'required|exists:chart_accounts,id',
        'credit_id' => 'required|exists:chart_accounts,id',
        'value' => 'required|numeric',
        'description' => 'nullable|string',
        'requested_by' => 'required|exists:users,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $validatedData = $request->only([
        'debit_id',
        'credit_id',
        'value',
        'description',
        'requested_by'
    ]);

    // Set the date to today if it’s not provided
    $validatedData['date'] = $request->input('date', now()->toDateString());

    $mainJournalValidation = MainJournalValidation::create($validatedData);

    return response()->json([
        'data' => $mainJournalValidation,
        'status' => Response::HTTP_OK
    ], Response::HTTP_OK);
}


    // Approve a validation request and create a record in the main journal table
    public function approve($id)
    {
        $request = MainJournalValidation::findOrFail($id);

        if ($request->status !== 'pending') {
            return response()->json([
                'message' => 'Request is already processed.',
                'status' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        // Create a new record in the Main Journal table
        $mainJournal = MainJournal::create([
            'date' => $request->date,
            'debit_id' => $request->debit_id,
            'credit_id' => $request->credit_id,
            'value' => $request->value,
            'description' => $request->description
        ]);

        // Update account balances (assuming ChartOfAccounts model has `balance` attribute)
        ChartAccount::where('id', $request->debit_id)->increment('balance', $request->value);
        ChartAccount::where('id', $request->credit_id)->decrement('balance', $request->value);

        // Update the status of the request to "approved"
        $request->status = 'approved';
        $request->save();

        return response()->json([
            'data' => $mainJournal,
            'status' => Response::HTTP_OK,
            'message' => 'Request approved and record created in Main Journal.'
        ], Response::HTTP_OK);
    }
}
