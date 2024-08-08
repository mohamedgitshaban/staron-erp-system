<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FinanceReportSubmition;
use Carbon\Carbon;
class FinanceReportSubmitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $FinanceReportSubmition=FinanceReportSubmition::latest()->get();

        if(!$FinanceReportSubmition->isEmpty()){
            $FinanceReportSubmition->transform(function($data){
                $data->created_date = Carbon::parse($data->created_at)->toDateString();
                $data->created_time = Carbon::parse($data->created_at)->toTimeString();
                unset($data->created_at);
                return $data;
            });
            return response()->json(["data"=>$FinanceReportSubmition,"status"=>200]);
        }
        else{
            return response()->json(["data"=>"there is no Finance Report Submition","status"=>404]);

        }
    }


    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [

            'SubmitionType' => 'required|string',
            'file' => 'required|file',
        ]);
        if ($validatedData->fails()) {
            return response()->json(['errors' => [$validatedData->errors()],"status"=>Response::HTTP_UNAUTHORIZED],Response::HTTP_OK );
        }
       else{
        $validator = $validatedData->validated();

        if ($validator['SubmitionType']=='Income Statement') {
            $file=$request->file('file');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $validator["report1"]= '/uploads/report1/'.$fileName;
            $file->move(public_path('uploads/report1'), $fileName);
        }
        if ($validator['SubmitionType']=='Cash Flow') {
            $file=$request->file('file');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $validator["report2"]= '/uploads/report2/'.$fileName;
            $file->move(public_path('uploads/report2'), $fileName);

        }
        if ($validator['SubmitionType']=='G & A') {
            $file=$request->file('file');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $validator["report3"]= '/uploads/report3/'.$fileName;
            $file->move(public_path('uploads/report3'), $fileName);

        }
        if ($validator['SubmitionType']=='Revenue') {
            $file=$request->file('file');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $validator["report4"]= '/uploads/report4/'.$fileName;
            $file->move(public_path('uploads/report4'), $fileName);

        }
        if ($validator['SubmitionType']=='COGS') {
            $file=$request->file('file');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $validator["report5"]= '/uploads/report5/'.$fileName;
            $file->move(public_path('uploads/report5'), $fileName);
        }
        unset($validator['SubmitionType']);
        unset($validator['file']);

        $month = date('m', strtotime(now())); // Extract month
                $year = date('Y', strtotime(now())); // Extract year
                $FinanceReportSubmition = FinanceReportSubmition::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->first();
                    if ($FinanceReportSubmition === null) {
                        FinanceReportSubmition::create($validator);
                    } else {
                        $FinanceReportSubmition->update($validator);

                    }
       }
        return response()->json(['message' => 'Finance Report Submition created successfully',"status"=> Response::HTTP_OK]);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $User=FinanceReportSubmition::find($id);
        if($User!=null){
            return response()->json(["data"=>$User,"status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no MonthInvoice","status"=>404]);

        }
    }

    public function update(Request $request, $id)
    {
        if ($request->hasFile('report1')||$request->hasFile('report2')||$request->hasFile('report3')||$request->hasFile('report4')||$request->hasFile('report5')) {
            if ($request->hasFile('report1')) {
                $file=$request->file('report1');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $validator["report1"]= '/uploads/report1/'.$fileName;
                $file->move(public_path('uploads/report1'), $fileName);

            }
            if ($request->hasFile('report2')) {
                $file=$request->file('report2');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $validator["report2"]= '/uploads/report2/'.$fileName;
                $file->move(public_path('uploads/report2'), $fileName);

            }
            if ($request->hasFile('report3')) {
                $file=$request->file('report3');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $validator["report3"]= '/uploads/report3/'.$fileName;
                $file->move(public_path('uploads/report3'), $fileName);

            }
            if ($request->hasFile('report4')) {
                $file=$request->file('report4');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $validator["report4"]= '/uploads/report4/'.$fileName;
                $file->move(public_path('uploads/report4'), $fileName);

            }
            if ($request->hasFile('report5')) {
                $file=$request->file('report5');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $validator["report5"]= '/uploads/report5/'.$fileName;
                $file->move(public_path('uploads/report5'), $fileName);

            }

            FinanceReportSubmition::where('id',$id)->update($validator);

        }
        return response()->json(['message' => 'report updated successfully',"status"=>Response::HTTP_OK], 200);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $FinanceReportSubmition=FinanceReportSubmition::find($id);
        if($FinanceReportSubmition!=null){
            $FinanceReportSubmition->delete();
            return response()->json(["data"=>"Finance Report Submition deleted","status"=>202]);
        }
        else{
            return response()->json(["data"=>"there is no Finance Report Submition","status"=>404]);

        }
        //
    }
}
