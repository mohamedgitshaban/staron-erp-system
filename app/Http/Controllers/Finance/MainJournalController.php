<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
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
    public function lager($id)  {
        $data = MainJournal::where("credit_id",$id)->orWhere("debit_id",$id)->orderBy("invoice_id")->get();
        if (!$data->isEmpty()) {
            $date=$this->ChartAccountController->getDate($id);
            if(!$date){
                return response()->json(["data" => "the record must not be parent of another element", "status" => Response::HTTP_METHOD_NOT_ALLOWED], 200);
            }
            $balance=0;
            $trilbalance=[
                [
                    'date' => Carbon::parse($date)->format('Y-m-d'),
                    'description' => "Balance forward",
                    'debit' => 0,
                    'credit' => 0,
                    'balance' => $balance,
                ]
            ];
            $trilbalance = array_merge($trilbalance, $data->map(function ($items) use ($id, &$balance) {
                if ($items->debit_id == $id) {
                    $debit = $items->value;
                    $credit = 0;
                    $balance -= $items->value;
                } else {
                    $debit = 0;
                    $credit = $items->value;
                    $balance += $items->value;
                }
                return [
                    'date' => Carbon::parse($items->date)->format('Y-m-d'),
                    'description' => $items->description,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $balance,
                ];
            })->toArray());
            return response()->json(["data" => $trilbalance, "status" => Response::HTTP_OK], 200);

        }
        else{
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);

        }
    }
    public function index()
    {
        $data = MainJournal::orderBy("invoice_id")->get()->groupBy("invoice_group_id");

        if ($data->isEmpty()) {
            return response()->json(["data" => "No Data", "status" => Response::HTTP_NO_CONTENT], 200);
        }

        $transformedData = $data->map(function ($items, $groupId) {
            // Extract year and week number from invoice_group_id
            $year = substr($groupId, 0, 4);
            $weekNumber = substr($groupId, 4, 2);

            // Calculate start and end dates of the week
            $startOfWeek = Carbon::now()->setISODate($year, $weekNumber)->startOfWeek(Carbon::SATURDAY)->toDateString();
            $endOfWeek = Carbon::now()->setISODate($year, $weekNumber)->endOfWeek(Carbon::FRIDAY)->toDateString();

            return [
                'invoice_group_id' => $groupId,
                'start_of_week' => $startOfWeek,
                'end_of_week' => $endOfWeek,
                'data' => $items,
            ];
        })->values();
        return response()->json(["data" => $transformedData, "status" => Response::HTTP_OK], 200);
    }

    public function store(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'debit_id' => 'required|integer|exists:chart_accounts,id',
            'credit_id' => 'required|integer|exists:chart_accounts,id',
            'value' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);

        }
        else{
            $validator=$validator->validated();
            $validator["date"] = Carbon::now();
            $validator["debit_account_description"]=$this->ChartAccountController->GetFullAccountName($validator["debit_id"]);
            $validator["credit_account_description"]=$this->ChartAccountController->GetFullAccountName($validator["credit_id"]);
            $validator["invoice_group_id"]=$validator["date"]->year.$validator["date"]->weekOfYear;
            $validator["invoice_id"]=$validator["date"]->year.$validator["date"]->weekOfYear.(MainJournal::where("invoice_group_id",$validator["invoice_group_id"])->count()+1);
            MainJournal::create($validator);
            $this->ChartAccountController->GetMainJournalIncress( $validator["credit_id"],$validator["value"]);
            $this->ChartAccountController->GetMainJournalDecress( $validator["debit_id"],$validator["value"]);
            return response()->json(['message' => 'Data created successfully', "status" => Response::HTTP_CREATED]);
        }
    }

    public function show($id)
    {
        $data = MainJournal::with('debitAccount','creditAccount')->find($id);
        if($data!=null){

                unset($data->created_at);
                unset($data->updated_at);

            return response()->json(["data"=>$data,"status"=>Response::HTTP_OK]);
        }
        else{
            return response()->json(["data"=>"no data","status"=>Response::HTTP_NOT_FOUND ]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator=Validator::make($request->all(),[
            'debit_id' => 'required|integer|exists:chart_accounts,id',
            'credit_id' => 'required|integer|exists:chart_accounts,id',
            'value' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY], 200);

        }
        else{
            $data=MainJournal::find($id);
            if($data!=null){
                $validator=$validator->validated();
            $validator["date"] = Carbon::now();
            $validator["debit_account_description"]=$this->ChartAccountController->GetFullAccountName($validator["debit_id"]);
            $validator["credit_account_description"]=$this->ChartAccountController->GetFullAccountName($validator["credit_id"]);
            $this->ChartAccountController->GetMainJournalIncress( $validator["debit_id"],$validator["value"]);
            $this->ChartAccountController->GetMainJournalDecress( $validator["credit_id"],$validator["value"]);
            $validator["invoice_group_id"]=$validator["date"]->year.$validator["date"]->weekOfYear;

            $validator["invoice_id"]=$validator["date"]->year.$validator["date"]->weekOfYear.(MainJournal::where("invoice_group_id",$validator["invoice_group_id"])->count()+1);
            $data->update($validator);
            $this->ChartAccountController->GetMainJournalIncress( $validator["credit_id"],$validator["value"]);
            $this->ChartAccountController->GetMainJournalDecress( $validator["debit_id"],$validator["value"]);
            return response()->json(['message' => 'Data updated successfully', "status" => Response::HTTP_CREATED]);
            }
            else{
                return response()->json(['message' => 'data not found', "status" => Response::HTTP_NOT_FOUND]);

            }
        }
    }

    public function destroy($id)
    {
        $data=MainJournal::find($id);
        if($data!=null){

        $this->ChartAccountController->GetMainJournalIncress( $data->debit_id,$data->value);
        $this->ChartAccountController->GetMainJournalDecress($data->credit_id,$data->value);
        $data->delete();
        return response()->json(['message' => 'Data updated successfully', "status" => Response::HTTP_CREATED]);
        }
        else{
            return response()->json(['message' => 'data not found', "status" => Response::HTTP_NOT_FOUND]);

        }
    }
}
