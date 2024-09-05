<?php

namespace App\Http\Controllers\Finance\chartofaccountitem;
use Illuminate\Support\Facades\Validator;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Finance\MainJournalController;
use App\Http\Controllers\Finance\TresuryAccountController;
class BankController extends Controller
{
    private $TresuryAccountController;
    private $MainJournalController;

    public function __construct(TresuryAccountController $TresuryAccountController,MainJournalController $MainJournalController)
    {
        $this->TresuryAccountController = $TresuryAccountController;
        $this->MainJournalController = $MainJournalController;
    }
    // i use this static id to get the parent it from the chart of account for all banks
    protected static $Banksid = '120';
    public function index()
    {
        $data = ChartAccount::with('childrenRecursive')->where('parent_id',self::$Banksid)->get();
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        }

        // Get validated data
        $validatedData = $validator->validated();

        // Initialize `parent_id` and `code`
        $parentId = self::$Banksid;
        $code = null;
            // Find the latest record with the same parent_id
            $parent = ChartAccount::where('id', $parentId)->latest()->first();
            $parent->brance = 1;
            $parent->save();
            $child = ChartAccount::where('parent_id', $parentId)->latest()->first();
            if ($child == null) {
                $code = (int)$parent->code .  1;
            } else {
                $code = (int)$child->code + 1;
            }
            // Increment the code based on the parent's code and count


        $validatedData["parent_id"] =$parentId;
        $validatedData["balance"] = 0;
        $validatedData['code'] = $code;
        $validatedData['brance'] = 0;
        $validatedData['debit'] = 0;
        $validatedData['credit'] = 0;
        ChartAccount::create($validatedData);

        return response()->json(['message' => 'Data created successfully', "status" => Response::HTTP_CREATED]);
    }
    public function update(Request $request, $id)
    {

        $validator = validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);
        } else {
            $data = ChartAccount::find($id);
            if ($data != null) {
                if($data->parent_id==self::$Banksid){
                    $validator = $validator->validated();
                $data->update($validator);
                return response()->json(['message' => 'data updated successfully', "status" => Response::HTTP_OK]);
                }
                else{
                    return response()->json(['massage' =>"method not allwoed you must update only banks" ,"status"=>Response::HTTP_METHOD_NOT_ALLOWED],200 );

                }
            }
            else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

            }
        }
    }
    public function show($id){
        $data=ChartAccount::find($id);
        if($data!=null){
            //check if this id is for bank or not
            if($data->parent_id==self::$Banksid){
                $data["mainjournal"]=$this->MainJournalController->BankProfile($id);
                $data["TresuryAccount"]=$this->TresuryAccountController->BankProfile($id);
                return response()->json(["data" => $data, "status" => Response::HTTP_OK], 200);
            }
            else{
                return response()->json(["data" => "there is no bank with this id", "status" => Response::HTTP_NOT_FOUND], 200);

            }

        }
        else{
            return response()->json(["data" => "data not found", "status" => Response::HTTP_NOT_FOUND], 200);

        }
    }
}
