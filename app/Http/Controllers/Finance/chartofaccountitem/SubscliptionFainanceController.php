<?php



namespace App\Http\Controllers\Finance\chartofaccountitem;
use Illuminate\Support\Facades\Validator;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Finance\MainJournalController;
use App\Http\Controllers\Finance\TresuryAccountController;

class SubscliptionFainanceController extends Controller
{    protected static $Subscliptionid = '165';

    private $TresuryAccountController;
    private $MainJournalController;

    public function __construct(TresuryAccountController $TresuryAccountController,MainJournalController $MainJournalController)
    {
        $this->TresuryAccountController = $TresuryAccountController;
        $this->MainJournalController = $MainJournalController;
    }
    public function index(){
        $data = ChartAccount::with('childrenRecursive')->where('parent_id',self::$Subscliptionid)->get();
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
}
