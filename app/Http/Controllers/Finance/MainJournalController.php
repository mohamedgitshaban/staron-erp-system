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
    public function lager(Request $request)  {
        $validator = Validator::make($request->all(), [
            'accounts' => 'required|array|min:2',
            'accounts.*.id' => 'required|exists:chart_accounts,id',
        ]);
        if($validator->fails()){
            return response()->json(["error"=>$validator->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY],200);
        }
        else{
            $validator=$validator->validated();
            $accountgeneral=[];
            foreach($validator["accounts"] as $id){
                $data = MainJournal::where("credit_id",$id)->orWhere("debit_id",$id)->orderBy("invoice_id")->get();

                    $date=$this->ChartAccountController->getDate($id);
                    if(!$date){
                        return response()->json(["data" => "the record must not be parent of another element", "status" => Response::HTTP_METHOD_NOT_ALLOWED], 200);
                    }
                    $balance=0;
                    $accountgeneral=[
                        [
                            'date' => Carbon::parse($date)->format('Y-m-d'),
                            'description' => "Balance forward",
                            'debit' => 0,
                            'credit' => 0,
                            'balance' => $balance,
                        ]
                    ];
                    $accountgeneral = array_merge($accountgeneral, $data->map(function ($items) use ($id, &$balance) {
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
                    $acc_name=$this->ChartAccountController->showName($id);
                    $generaldata[]=[
                        "account_name"=>$acc_name[0]->name,
                        "general_leger"=>$accountgeneral
                    ];

            }
            return response()->json(["all_lager" => $generaldata, "status" => Response::HTTP_OK], 200);


        }
    }
    public function trail(Request $request)  {
      $validator = Validator::make($request->all(), [
            'accounts' => 'required|array|min:2',
            'accounts.*.id' => 'required|exists:chart_accounts,id',
        ]);
        if($validator->fails()){
            return response()->json(["error"=>$validator->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY],200);
        }
        else{
            $validated = $validator->validated();

            $trialBalance = [];
            // dd($validated["accounts"]);

            foreach ($validated["accounts"] as $id) {
                $credit = MainJournal::where('credit_id', $id)
                        ->whereIn('debit_id', $validated["accounts"])
                        ->sum("value");
                $debit = MainJournal::where('debit_id', $id)
                        ->whereIn('credit_id', $validated["accounts"])
                        ->sum("value");
                $acc_name=$this->ChartAccountController->showName($id);
                $trialBalance[] = [
                    "account_name"=>$acc_name[0]->name,
                    "debit"=>$debit,
                    "credit"=>$credit
                ];
            }

            return response()->json(["trial_balance" => $trialBalance, "status" => Response::HTTP_OK], 200);

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
            //check if parent
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
    // add bank profile function to when open the bank page to get all mainjournal (transaction of this bank)
    public function BankProfile($id){
        return MainJournal::where("credit_id",$id)->orWhere("debit_id",$id)->orderBy("invoice_id")->get();
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
