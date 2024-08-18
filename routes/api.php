<?php

use App\Http\Controllers\Finance\ChartAccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\hr\AttendanceController;
use App\Http\Controllers\hr\UserController;
use App\Http\Controllers\hr\PayrollController;
use App\Http\Controllers\hr\WarningLogController;
use App\Http\Controllers\Control\MonthInvoiceController;
use App\Http\Controllers\Control\ControlOperationPlanController;
use App\Http\Controllers\Control\ControlProcurmentController;
use App\Http\Controllers\Control\PackageDataController;
use App\Http\Controllers\Operation\OperationActualInvoiceInController;
use App\Http\Controllers\Operation\OperationAsbuiltController;
use App\Http\Controllers\Operation\OperationProcurmentController;
use App\Http\Controllers\Operation\OperationMonthlyScPlanController;
use App\Http\Controllers\Finance\FinanceReportSubmitionController;
use App\Http\Controllers\Finance\FinanceActualCollectionController;
use App\Http\Controllers\Sales\ClientController;
use App\Http\Controllers\Sales\SalesCrmController;
use App\Http\Controllers\Sales\MeetingLogController;
use App\Http\Controllers\Technical\TechnecalRequestController;
use App\Http\Controllers\hr\EmployeeRFEController;
use App\Http\Controllers\hr\ReqrurmentController;
use App\Http\Controllers\SupplyChain\CategoryController;
use App\Http\Controllers\Finance\FainanceProcurmentController;
use App\Http\Controllers\Control\ControlStocklogController;
use App\Http\Controllers\hr\AttendanceAssignLogController;
use App\Http\Controllers\hr\LeavingBalanceLogController;
use App\Http\Controllers\SupplyChain\StocklogController;
use App\Http\Controllers\SupplyChain\StockController;
use App\Http\Controllers\SupplyChain\SupplyChainProcurmentController;
use App\Http\Controllers\SupplyChain\SupplyerController;
use App\Http\Controllers\Technical\CuttingList\CuttingDataController;
use App\Http\Controllers\Technical\CuttingList\CuttingDataPiecesController;
use App\Http\Controllers\Technical\CuttingList\CuttingDataRequestController;
use App\Http\Controllers\Finance\MainJournalController;
use App\Http\Controllers\Finance\TresuryAccountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix'=>'v1'],function(){
    Route::post('/login', [UserController::class,"login"]);
     Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [UserController::class,"logout"]);
        Route::group(['prefix'=>'user',],function () {
            Route::get('/profile', [UserController::class,"user"]);
            Route::get('/DepartmentEmployee', [UserController::class,"DepartmentEmployee"]);
            Route::post('/updateProfile', [UserController::class,"updateProfile"]);
            Route::get('/getLastLogin', [UserController::class,"getLastLogin"]);
            Route::get('/Attendance', [AttendanceController::class,"PublicAttendance"]);
            Route::group(['prefix'=>'Requestfe',],function () {
                Route::post('/create', [EmployeeRFEController::class,"store"]);
                Route::get('/pending', [EmployeeRFEController::class,"PendingRequest"]);
                Route::get('/aproved', [EmployeeRFEController::class,"AprovedRequest"]);
                Route::get('/rejected', [EmployeeRFEController::class,"RejectedRequest"]);
                Route::get('/', [EmployeeRFEController::class,"index"]);
                Route::get('/{id}', [EmployeeRFEController::class,"show"])->where('id', '[0-9]+');
                Route::post('/{id}/update', [EmployeeRFEController::class,"update"]);
                Route::delete('/{id}', [EmployeeRFEController::class,"destroy"]);

            });
            Route::group(['prefix'=>'Requirements',],function () {
                Route::get('/', [ReqrurmentController::class,"index"]);
                Route::post('/create', [ReqrurmentController::class,"store"]);
                Route::get('/pending', [ReqrurmentController::class,"PendingRequest"]);
                Route::get('/aproved', [ReqrurmentController::class,"AprovedRequest"]);
                Route::get('/rejected', [ReqrurmentController::class,"RejectedRequest"]);
                Route::get('/{id}', [ReqrurmentController::class,"show"])->where('id', '[0-9]+');
                Route::post('/{id}/update', [ReqrurmentController::class,"update"]);
                Route::delete('/{id}', [ReqrurmentController::class,"destroy"]);
            });
        });
        //human Resource
        Route::group(['prefix'=>'humanresource',],function () {
            Route::group(['prefix'=>'employee',],function () {
                Route::get('/', [UserController::class,"index"]);
                Route::post('/superVisor', [UserController::class,"AllSuperVisor"]);
                Route::get('/department', [UserController::class,"department"]);
                Route::get('/{id}', [UserController::class,"show"])->where('id', '[0-9]+');
                Route::post('/create', [UserController::class,"create"]);
                Route::delete('/{id}', [UserController::class,"destroy"])->where('id', '[0-9]+');
                Route::post('/{id}/update', [UserController::class,"update"])->where('id', '[0-9]+');
            });
            Route::group(['prefix'=>'attendance',],function () {
                Route::get('/', [AttendanceController::class,"index"]);
                Route::get('/log', [AttendanceAssignLogController::class,"index"]);
                Route::post('/create', [AttendanceController::class,"store"]);
                Route::get('/{id}/user', [AttendanceController::class,"showUser"])->where('id', '[0-9]+');
                Route::get('/{id}', [AttendanceController::class,"show"])->where('id', '[0-9]+');
                Route::put('/{id}/update', [AttendanceController::class,"updateById"])->where('id', '[0-9]+');
                Route::delete('/{id}', [AttendanceController::class,"destroyByid"])->where('id', '[0-9]+');
                Route::post('/{id}/addetion', [AttendanceController::class,"addetion"])->where('id', '[0-9]+');
                Route::post('/{id}/deduction', [AttendanceController::class,"deduction"])->where('id', '[0-9]+');

            });
            Route::group(['prefix'=>'Requestfe',],function () {
                Route::get('/', [EmployeeRFEController::class,"index"]);
                Route::post('/{id}/hrapprove', [EmployeeRFEController::class,"hrapprove"])->where('id', '[0-9]+');
                Route::post('/{id}/hrreject', [EmployeeRFEController::class,"hrreject"])->where('id', '[0-9]+');
                Route::post('/{id}/adminapprove', [EmployeeRFEController::class,"adminapprove"])->where('id', '[0-9]+');
                Route::post('/{id}/adminreject', [EmployeeRFEController::class,"adminreject"])->where('id', '[0-9]+');
            });
            Route::group(['prefix'=>'Requirements',],function () {
                Route::get('/', [ReqrurmentController::class,"index"]);
                Route::post('/create', [ReqrurmentController::class,"store"]);
                Route::post('/{id}/hrapprove', [ReqrurmentController::class,"hrapprove"])->where('id', '[0-9]+');
                Route::post('/{id}/hrreject', [ReqrurmentController::class,"hrreject"])->where('id', '[0-9]+');
                Route::post('/{id}/adminapprove', [ReqrurmentController::class,"adminapprove"])->where('id', '[0-9]+');
                Route::post('/{id}/adminreject', [ReqrurmentController::class,"adminreject"])->where('id', '[0-9]+');
                Route::get('/{id}', [ReqrurmentController::class,"show"])->where('id', '[0-9]+');
                Route::post('/{id}/update', [ReqrurmentController::class,"update"]);
                Route::delete('/{id}', [ReqrurmentController::class,"destroy"]);
            });
            Route::group(['prefix'=>'payroll',],function () {
                Route::post('/', [PayrollController::class,"index"]);
                Route::post('/create', [PayrollController::class,"store"]);
                Route::get('/{id}', [PayrollController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [PayrollController::class,"destroy"]);
            });
            Route::group(['prefix'=>'inecentivepayroll',],function () {
                Route::get('/', [PayrollController::class,"index"]);
                Route::post('/create', [PayrollController::class,"store"]);
                Route::get('/{id}', [PayrollController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [PayrollController::class,"destroy"]);
            });

            Route::group(['prefix'=>'warninglog',],function () {
                Route::get('/', [WarningLogController::class,"index"]);
                Route::get('/{id}/user', [WarningLogController::class,"employeeWarning"]);
                Route::get('/{id}', [WarningLogController::class,"show"]);
                Route::put('/{id}/update', [WarningLogController::class,"update"]);
                Route::delete('/{id}', [WarningLogController::class,"destroy"]);
                Route::post('/store', [WarningLogController::class,"store"]);
            });
            Route::group(['prefix'=>'leavingbalance',],function () {
                Route::get('/', [LeavingBalanceLogController::class,"index"]);
                Route::post('/store', [LeavingBalanceLogController::class,"store"]);
                Route::put('/{id}/update', [LeavingBalanceLogController::class,"update"]);
                Route::get('/{id}/user', [LeavingBalanceLogController::class,"showEmployee"])->where('id', '[0-9]+');
                Route::get('/{id}', [LeavingBalanceLogController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [LeavingBalanceLogController::class,"destroy"])->where('id', '[0-9]+');

            });
            Route::group(['prefix'=>'target',],function () {
                Route::get('/assignmentattendance', [AttendanceAssignLogController::class,"selectAll"]);

            });
        });
        //sales
        Route::group(['prefix'=>'sales',],function () {
            Route::group(['prefix'=>'Clint',],function () {
                Route::get('/', [ClientController::class,"index"]);
                Route::post('/create', [ClientController::class,"store"]);
                Route::get('/{id}', [ClientController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [ClientController::class,"destroy"]);
                Route::post('/{id}/update', [ClientController::class,"update"]);
            });
            Route::group(['prefix'=>'Crm',],function () {
                Route::get('/', [SalesCrmController::class,"index"]);
                Route::post('/create', [SalesCrmController::class,"store"]);
                Route::get('/{id}', [SalesCrmController::class,"show"])->where('id', '[0-9]+');
                Route::post('/{id}/switch', [SalesCrmController::class,"switch"])->where('id', '[0-9]+');
                Route::delete('/{id}', [SalesCrmController::class,"destroy"]);
                Route::post('/{id}/update', [SalesCrmController::class,"update"]);
                Route::post('/{id}/RFQ', [SalesCrmController::class,"RFQ"]);
                Route::post('/{id}/QutationApprove', [SalesCrmController::class,"QutationApprove"]);
                Route::post('/{id}/QutationReject', [SalesCrmController::class,"QutationReject"]);
                Route::post('/{id}/submitdrafting', [SalesCrmController::class,"submitdrafting"]);
                Route::post('{id}/submitcontract', [SalesCrmController::class,"clintApprove"]);
                Route::post('{id}/clintApprove', [SalesCrmController::class,"clintApprove"]);
                Route::post('{id}/clintReject', [SalesCrmController::class,"clintReject"]);
                Route::post('{id}/clintRecalculation', [SalesCrmController::class,"clintRecalculation"]);
            });

            Route::group(['prefix'=>'Communications',],function () {
                Route::get('/', [MeetingLogController::class,"index"]);
                Route::get('/indexsum', [MeetingLogController::class,"indexsum"]);
                Route::post('/create', [MeetingLogController::class,"store"]);
                Route::get('/{id}', [MeetingLogController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [MeetingLogController::class,"destroy"]);
                Route::post('/{id}/update', [MeetingLogController::class,"update"]);
            });
            Route::group(['prefix'=>'Target',],function () {
                Route::get('/meetingscore', [MeetingLogController::class,"score"]);
                Route::get('/Qutationscore', [SalesCrmController::class,"Qutationscore"]);
                Route::get('/Conversionscore', [SalesCrmController::class,"Conversionscore"]);
            });
            Route::group(['prefix'=>'dashboard',],function () {
                Route::post('/NewStakeholders', [ClientController::class,"NewStakeholders"]);
                Route::get('/StakeholdersReprsinting', [ClientController::class,"StakeholdersReprsinting"]);
                Route::post('/NumberOfProjects', [SalesCrmController::class,"NumberOfProjects"]);
                Route::post('/ValueOfProjects', [SalesCrmController::class,"ValueOfProjects"]);
                Route::post('/NumberOfQutation', [SalesCrmController::class,"NumberOfQutation"]);
                Route::post('/ValueOfQutation', [SalesCrmController::class,"ValueOfQutation"]);
                Route::post('/meetingcount', [MeetingLogController::class,"meetingcount"]);
                Route::post('/callcount', [MeetingLogController::class,"callcount"]);

            });
        });
        //technical
        Route::group(['prefix'=>'technical',],function () {
            Route::group(['prefix'=>'requests',],function () {
                Route::get('/', [TechnecalRequestController::class,"index"]);
                Route::get('/{id}', [TechnecalRequestController::class,"show"]);
                Route::post('{id}/RejectTask', [TechnecalRequestController::class,"RejectTask"]);
                Route::post('{id}/assign', [TechnecalRequestController::class,"assign"]);
                Route::post('{id}/starttask', [TechnecalRequestController::class,"starttask"]);
                Route::post('{id}/submit', [TechnecalRequestController::class,"SendQC"]);
                Route::put('{id}/review', [TechnecalRequestController::class,"ManagerReview"]);
                Route::post('{id}/rejectreview', [TechnecalRequestController::class,"rejectreview"]);
                Route::get('/{id}', [TechnecalRequestController::class,"show"])->where('id', '[0-9]+');
                Route::post('/{id}/update', [TechnecalRequestController::class,"update"]);
            });
            Route::group(['prefix'=>"cuttinglist"],function (){
                Route::group(['prefix'=>"vanity"],function (){
                    Route::get("/",[CuttingDataController::class,"index"]);
                    Route::post("/create",[CuttingDataController::class,"store"]);
                    Route::get("/{id}",[CuttingDataController::class,"show"]);
                    Route::put("/{id}/update",[CuttingDataController::class,"update"]);
                    Route::delete("/{id}",[CuttingDataController::class,"destroy"]);
                    Route::group(['prefix'=>"{id}/pieces"],function (){
                        Route::get("/",[CuttingDataPiecesController::class,"index"]);
                        Route::post("/create",[CuttingDataPiecesController::class,"store"]);
                        Route::get("/{idpieces}",[CuttingDataPiecesController::class,"show"]);
                        Route::put("/{idpieces}/update",[CuttingDataPiecesController::class,"update"]);
                        Route::delete("/{idpieces}",[CuttingDataPiecesController::class,"destroy"]);
                    });
                });

                Route::group(['prefix'=>"request"],function (){
                    Route::get("/",[CuttingDataRequestController::class,"index"]);
                    Route::post("/create",[CuttingDataRequestController::class,"store"]);
                    Route::get("/{id}",[CuttingDataRequestController::class,"show"]);
                    Route::put("/{id}/update",[CuttingDataRequestController::class,"update"]);
                    Route::delete("/{id}",[CuttingDataRequestController::class,"destroy"]);
                });
            });
            Route::group(['prefix'=>'Target',],function () {
                Route::get('/QStarget', [TechnecalRequestController::class,"QStarget"]);
                Route::get('/packagetarget', [TechnecalRequestController::class,"packagetarget"]);


            });
            Route::group(['prefix'=>'dashboard',],function () {
                Route::post('/ totalnumberofqc', [TechnecalRequestController::class,"totalnumberofqc"]);
                Route::post('/totalnumberofpackage', [TechnecalRequestController::class,"totalnumberofpackage"]);
                Route::post('/top5qc', [TechnecalRequestController::class,"top5qc"]);
                Route::post('/top5package', [TechnecalRequestController::class,"top5package"]);
                Route::post('/bottom5qc', [TechnecalRequestController::class,"bottom5qc"]);
                Route::post('/bottom5package', [TechnecalRequestController::class,"bottom5package"]);
                Route::post('/averagequantity', [TechnecalRequestController::class,"averagequantity"]);
                Route::post('/averagepackage', [TechnecalRequestController::class,"averagepackage"]);

            });
        });
        //control
        Route::group(['prefix'=>'control',],function () {

            Route::group(['prefix'=>'monthinvoice',],function () {
                Route::get('/', [MonthInvoiceController::class,"index"]);
                Route::post('/create', [MonthInvoiceController::class,"store"]);
                Route::post('/{id}/update', [MonthInvoiceController::class,"update"]);
                Route::get('/{id}', [MonthInvoiceController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [MonthInvoiceController::class,"destroy"]);


            });
            Route::group(['prefix'=>'operationplan',],function () {
                Route::get('/', [ControlOperationPlanController::class,"index"]);
                Route::post('/create', [ControlOperationPlanController::class,"store"]);
                Route::get('/{id}', [ControlOperationPlanController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [ControlOperationPlanController::class,"destroy"]);
                Route::post('/{id}/update', [ControlOperationPlanController::class,"update"]);
            });
            Route::group(['prefix'=>'procurment',],function () {
                Route::get('/', [ControlProcurmentController::class,"index"]);
                Route::post('/create', [ControlProcurmentController::class,"store"]);
                Route::get('/{id}', [ControlProcurmentController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [ControlProcurmentController::class,"destroy"]);
                Route::put('/{id}/update', [ControlProcurmentController::class,"update"]);
                Route::post('/{id}/AcceptfromOperation', [ControlProcurmentController::class,"AcceptfromOperation"]);
                Route::post('/{id}/aprove', [ControlProcurmentController::class,"Approve"]);
                Route::post('/{id}/reject', [ControlProcurmentController::class,"reject"]);

            });
            Route::group(['prefix'=>'requests',],function () {
                Route::get('/', [PackageDataController::class,"index"]);
                Route::get('{id}/starttask', [PackageDataController::class,"starttask"]);
                Route::post('{id}/complete', [PackageDataController::class,"complete"]);
                Route::post('{id}/RejectTask', [PackageDataController::class,"RejectTask"]);

            });
            // Route::group(['prefix'=>'scTarget',],function () {
            //     Route::get('/QStarget', [ControlScPlanController::class,"QStarget"]);


            // });
            Route::group(['prefix'=>'warehouse',],function () {

                    Route::get('/', [ControlStocklogController::class,"index"]);
                    Route::get('/StockLog', [ControlStocklogController::class,"StockLog"]);
                    Route::post('/create', [ControlStocklogController::class,"store"]);
                    Route::post('/{id}/update', [ControlStocklogController::class,"update"]);
                    Route::get('/{id}', [ControlStocklogController::class,"show"]);
                    Route::delete('/{id}', [ControlStocklogController::class,"destroy"]);
            });
        });
        //warehouse
        Route::group(['prefix'=>'warehouse',],function () {
            Route::group(['prefix'=>'requset',],function () {
                Route::get('/', [StocklogController::class,"index"]);
                Route::get('/StockLog', [StocklogController::class,"StockLog"]);
                Route::get('/{id}', [StocklogController::class,"show"]);
            });

        });
        //supply chain
        Route::group(['prefix'=>'SupplyChain',],function () {
            Route::group(['prefix'=>'supplyer',],function () {
                Route::get('/', [SupplyerController::class,"index"]);
                Route::post('/create', [SupplyerController::class,"store"]);
                Route::post('/{id}/update', [SupplyerController::class,"update"]);
                Route::get('/{id}', [SupplyerController::class,"show"]);
                Route::delete('/{id}', [SupplyerController::class,"destroy"]);
            });
            Route::group(['prefix'=>'category',],function () {
                Route::get('/', [CategoryController::class,"index"]);
                Route::post('/create', [CategoryController::class,"store"]);
                Route::post('/{id}/update', [CategoryController::class,"update"]);
                Route::get('/{id}', [CategoryController::class,"show"]);
                Route::delete('/{id}', [CategoryController::class,"destroy"]);
            });
            Route::group(['prefix'=>'stock',],function () {
                Route::get('/', [StockController::class,"index"]);
                Route::post('/create', [StockController::class,"store"]);
                Route::post('/{id}/update', [StockController::class,"update"]);
                Route::get('/{id}', [StockController::class,"show"]);
                Route::delete('/{id}', [StockController::class,"destroy"]);
            });
            Route::group(['prefix'=>'procurment',],function () {
                Route::get('/', [SupplyChainProcurmentController::class,"index"]);
                Route::get('/{id}', [SupplyChainProcurmentController::class,"show"])->where('id', '[0-9]+');
                Route::post('/{id}/start', [SupplyChainProcurmentController::class,"Start"]);
                Route::post('/{id}/complete', [SupplyChainProcurmentController::class,"Complete"]);
                Route::post('/{id}/RequestForMoney', [SupplyChainProcurmentController::class,"RequestForMoney"]);

                Route::get('/lastdata', [SupplyChainProcurmentController::class,"Latest5"]);
                Route::post('/CompletedProcurements', [SupplyChainProcurmentController::class,"CompletedProcurements"]);
                Route::post('/RejectedProcurements', [SupplyChainProcurmentController::class,"RejectedProcurements"]);
                Route::post('/CostsCollected', [SupplyChainProcurmentController::class,"CostsCollected"]);
                Route::post('/TotalCosts', [SupplyChainProcurmentController::class,"TotalCosts"]);
                Route::post('/priortyrate', [SupplyChainProcurmentController::class,"ProcurementsPriorty"]);
            });
        });
        //operation
        Route::group(['prefix'=>'operation',],function () {
            Route::group(['prefix'=>'Asbuilt',],function () {
                Route::get('/', [OperationAsbuiltController::class,"index"]);
                Route::post('/create', [OperationAsbuiltController::class,"store"]);
                Route::get('/{id}', [OperationAsbuiltController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [OperationAsbuiltController::class,"destroy"]);
                Route::post('/{id}/update', [OperationAsbuiltController::class,"update"]);
            });
            Route::group(['prefix'=>'ActualInvoicein',],function () {
                Route::get('/', [OperationActualInvoiceInController::class,"index"]);
                Route::post('/create', [OperationActualInvoiceInController::class,"store"]);
                Route::get('/{id}', [OperationActualInvoiceInController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [OperationActualInvoiceInController::class,"destroy"]);
                Route::post('/{id}/update', [OperationActualInvoiceInController::class,"update"]);
            });
            Route::group(['prefix'=>'ScPlan',],function () {
                Route::get('/', [OperationMonthlyScPlanController::class,"index"]);
                Route::post('/create', [OperationMonthlyScPlanController::class,"store"]);
                Route::get('/{id}', [OperationMonthlyScPlanController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [OperationMonthlyScPlanController::class,"destroy"]);
                Route::post('/{id}/update', [OperationMonthlyScPlanController::class,"update"]);
            });

            Route::group(['prefix'=>'procurment',],function () {
                Route::get('/', [OperationProcurmentController::class,"index"]);
                Route::post('/create', [OperationProcurmentController::class,"store"]);
                Route::get('/{id}', [OperationProcurmentController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [OperationProcurmentController::class,"destroy"]);
                Route::post('/{id}/update', [OperationProcurmentController::class,"update"]);
                Route::post('/{id}/AcceptfromOperation', [OperationProcurmentController::class,"AcceptfromOperation"]);
                Route::post('/{id}/aprove', [OperationProcurmentController::class,"Approve"]);
                Route::post('/{id}/reject', [OperationProcurmentController::class,"reject"]);            });
        });
        //finance
        Route::group(['prefix'=>'finance',],function () {
            Route::group(['prefix'=>'ReportSubmition',],function () {
                Route::get('/', [FinanceReportSubmitionController::class,"index"]);
                Route::post('/create', [FinanceReportSubmitionController::class,"store"]);
                Route::get('/{id}', [FinanceReportSubmitionController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [FinanceReportSubmitionController::class,"destroy"]);
                Route::post('/{id}/update', [FinanceReportSubmitionController::class,"update"]);
            });
            Route::group(['prefix'=>'ActualCollection',],function () {
                Route::get('/', [FinanceActualCollectionController::class,"index"]);
                Route::post('/create', [FinanceActualCollectionController::class,"store"]);
                Route::get('/{id}', [FinanceActualCollectionController::class,"show"])->where('id', '[0-9]+');
                Route::delete('/{id}', [FinanceActualCollectionController::class,"destroy"]);
                Route::post('/{id}/update', [FinanceActualCollectionController::class,"update"]);
            });
            Route::group(['prefix'=>'procurment',],function () {
                Route::get('/', [FainanceProcurmentController::class,"index"]);
                Route::get('/{id}', [FainanceProcurmentController::class,"show"])->where('id', '[0-9]+');
                Route::post('/{id}/AcceptRequestForNoMoney', [FainanceProcurmentController::class,"AcceptRequestForNoMoney"]);
                Route::post('/{id}/AcceptRequestForMoney', [FainanceProcurmentController::class,"AcceptRequestForMoney"]);
            });

            Route::group(['prefix'=>'chartAccount',],function () {
                Route::get('/parent', [ChartAccountController::class, 'parent']);
                Route::get('/child', [ChartAccountController::class, 'child']);
                Route::get('/all', [ChartAccountController::class, 'all']);
            });
            Route::resource('chartAccount', ChartAccountController::class);
            Route::group(['prefix'=>'mainjournal',],function () {
                Route::post('/trail', [MainJournalController::class, 'trail']);
                Route::post('/lager', [MainJournalController::class, 'lager']);
            });
            Route::resource('mainjournal', MainJournalController::class);

            Route::group(['prefix'=>'TresuryAccount',],function () {
                Route::post('/depit', [TresuryAccountController::class, 'getallrequests']);
                Route::post('/collection', [TresuryAccountController::class, 'getallcollection']);
                Route::post('/ARcollection', [TresuryAccountController::class, 'getARcollection']);
                Route::post('/ApRequest', [TresuryAccountController::class, 'getApRequest']);
                Route::post('/BankChecks', [TresuryAccountController::class, 'getBankChecks']);
                Route::post('/CashflowHistory', [TresuryAccountController::class, 'getCashflowHistory']);
                Route::post('/TresuryRequests', [TresuryAccountController::class, 'getTresuryRequests']);
            });
            Route::resource('TresuryAccount', TresuryAccountController::class);

        });


    });
 });
