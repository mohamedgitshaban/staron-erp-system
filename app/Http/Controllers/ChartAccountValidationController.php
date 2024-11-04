<?php

namespace App\Http\Controllers;

use App\Models\ChartAccount;
use App\Models\ChartAccountValidation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class ChartAccountValidationController extends Controller
{
    /**
     * List all pending validation requests.
     */
    public function index()
    {
        $pendingRequests = ChartAccountValidation::where('status', 'pending')->get();

        // Use the `map()` method to modify the collection directly
        $pendingRequestsWithImageUri = $pendingRequests->map(function ($item) {
            $user = User::find($item->requested_by);
            $item->userImage = $user ? $user->profileimage : null; // Add the image or set null if the user is not found
            return $item;
        });

        // Display the modified collection for debugging
        // dd($pendingRequestsWithImageUri);

        // Return the modified collection as a JSON response
        return response()->json([
            'data' => $pendingRequestsWithImageUri,
        ], Response::HTTP_OK);
    }


    /**
     * Show details of a specific validation request.
     */
    public function show($id)
    {
        $request = ChartAccountValidation::findOrFail($id);

        return response()->json([
            'data' => $request,
        ], Response::HTTP_OK);
    }

    /**
     * Store a new account validation request.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'request_source' => 'required|string|max:255',
        'hierarchy' => 'required|array|min:1', // Ensure hierarchy is a non-empty array
    ]);

    $validated['requested_by'] = Auth::id();
    $validated['parent_id'] = $this->determineParentId($validated['requested_by'], $validated['request_source']);

    if (is_null($validated['parent_id'])) {
        return response()->json([
            'message' => 'Unable to determine parent account. Please ensure the department and request source are configured correctly.',
        ], Response::HTTP_BAD_REQUEST);
    }

    // Extract the last item in the hierarchy as the name
    $name = array_pop($validated['hierarchy']);

    // Debugging output to check JSON encoding
    $encodedHierarchy = json_encode($validated['hierarchy']);
    // dd($encodedHielrarchy); // Check if this outputs valid JSON

    $validationRequest = ChartAccountValidation::create([
        'name' => $name,
        'request_source' => $validated['request_source'],
        'requested_by' => $validated['requested_by'],
        'parent_id' => $validated['parent_id'],
        'hierarchy' => $encodedHierarchy, // Properly encode the hierarchy as JSON
        'status' => 'pending',
    ]);

    return response()->json([
        'message' => 'Account creation request submitted successfully.',
        'data' => $validationRequest,
    ], Response::HTTP_CREATED);
}


    /**
     * Determine the parent ID based on user department and request source.
     */
    private function determineParentId($requestedBy, $requestSource)
    {
        $user = User::findOrFail($requestedBy);
        $department = $user->department;

        $parentMap = [
            'Supply Chain' => [
                'source' => 'Supply Chain Component',
                'parent_id' => 71,
            ],
            'Finance' => [
                'source' => 'Finance Component',
                'parent_id' => 70,
            ],
            'Human Resource' => [
                'source' => 'Human Resource',
                'parent_id' => 70,
            ],
        ];

        $parentId = $parentMap[$department]['parent_id'] ?? null;

        return $parentId ?? ChartAccount::where('name', 'General Ledger')->value('id');
    }

    /**
     * Approve a validation request and create the account with the full hierarchy.
     */
    public function approve($id)
    {
        $validationRequest = ChartAccountValidation::findOrFail($id);

        if ($validationRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Request has already been processed.',
            ], Response::HTTP_CONFLICT);
        }

        // Decode and validate the hierarchy
        $hierarchy = $this->decodeHierarchy($validationRequest);
        if (is_null($hierarchy)) {
            return response()->json([
                'message' => 'Invalid hierarchy data.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Process each level in the hierarchy
        $parentId = $validationRequest->parent_id;
        foreach ($hierarchy as $levelName) {
            $parentId = $this->findOrCreateAccount($levelName, $parentId);
        }

        // Create the final account
        $finalAccount = ChartAccount::create([
            'name' => $validationRequest->name,
            'parent_id' => $parentId,
            'code' => $this->generateAccountCode($parentId),
            'account_lineage' => $this->generateAccountLineage($parentId, $validationRequest->name),
            'debit' => 0,
            'credit' => 0,
            'balance' => 0,
            'branch' => false,
        ]);

        // Mark as approved
        $validationRequest->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Account created successfully along with any missing parent accounts.',
            'account' => $finalAccount,
        ], Response::HTTP_OK);
    }

    /**
     * Decode hierarchy JSON and handle errors.
     */
    private function decodeHierarchy($validationRequest)
    {
        $hierarchy = json_decode($validationRequest->hierarchy, true);
        return is_array($hierarchy) ? $hierarchy : null;
    }

    /**
     * Helper method to find or create an account level in the hierarchy.
     */
    private function findOrCreateAccount($name, $parentId = null)
    {
        $existingAccount = ChartAccount::where('name', $name)
                                       ->where('parent_id', $parentId)
                                       ->first();

        if ($existingAccount) {
            return $existingAccount->id;
        }

        $newAccount = ChartAccount::create([
            'name' => $name,
            'parent_id' => $parentId,
            'code' => $this->generateAccountCode($parentId),
            'account_lineage' => $this->generateAccountLineage($parentId, $name),
            'debit' => 0,
            'credit' => 0,
            'balance' => 0,
            'branch' => true,
        ]);

        return $newAccount->id;
    }

    /**
     * Reject a validation request with a reason.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $validationRequest = ChartAccountValidation::findOrFail($id);

        if ($validationRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Request has already been processed.',
            ], Response::HTTP_CONFLICT);
        }

        $validationRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return response()->json([
            'message' => 'Account creation request rejected successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Generate the account code based on parent account.
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
     * Generate the account lineage based on parent account and name.
     */
    private function generateAccountLineage($parentId, $name)
    {
        $parentAccount = ChartAccount::findOrFail($parentId);
        $parentLineage = $parentAccount->account_lineage;

        return $parentLineage . '.' . $name;
    }
}
