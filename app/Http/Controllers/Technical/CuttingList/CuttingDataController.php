<?php

namespace App\Http\Controllers\Technical\CuttingList;

use App\Http\Controllers\Controller;
use App\Models\CuttingData;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class CuttingDataController extends Controller
{
    private $CuttingDataPiecesController;

    public function __construct(CuttingDataPiecesController $CuttingDataPiecesController)
    {
        $this->CuttingDataPiecesController = $CuttingDataPiecesController;
    }

    public function index()
    {
        $data = CuttingData::with("CuttingDataPieces")->orderBy('created_at', 'desc')->get();

        if ($data->isEmpty()) {
            return response()->json(["data" => "There is No Data", "status" => 404]);
        }

        $data->transform(function ($data) {
            $data->created_date = Carbon::parse($data->created_at)->toDateString();
            $data->created_time = Carbon::parse($data->created_at)->toTimeString();
            unset($data->created_at);
            $data->type = $this->convertToAsciiChar($data->type);
            return $data;
        });

        return response()->json(["data" => $data, "status" => 200]);
    }

    private function convertToAsciiChar($type)
    {
        return chr($type + 64);
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'required|integer',
            'low_hight' => 'required|numeric|min:0',
            'high_hight' => 'required|numeric|min:0',
            'low_width' => 'required|numeric|min:0',
            'high_width' => 'required|numeric|min:0',
            'low_length' => 'required|numeric|min:0',
            'high_length' => 'required|numeric|min:0',
        ], [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'type.required' => 'The type field is required.',
            'type.integer' => 'The type must be an integer.',
            'low_hight.required' => 'The low height field is required.',
            'low_hight.numeric' => 'The low height must be a number.',
            'low_hight.min' => 'The low height must be at least 0.',
            'high_hight.required' => 'The high height field is required.',
            'high_hight.numeric' => 'The high height must be a number.',
            'high_hight.min' => 'The high height must be at least 0.',
            'low_width.required' => 'The low width field is required.',
            'low_width.numeric' => 'The low width must be a number.',
            'low_width.min' => 'The low width must be at least 0.',
            'high_width.required' => 'The high width field is required.',
            'high_width.numeric' => 'The high width must be a number.',
            'high_width.min' => 'The high width must be at least 0.',
            'low_length.required' => 'The low length field is required.',
            'low_length.numeric' => 'The low length must be a number.',
            'low_length.min' => 'The low length must be at least 0.',
            'high_length.required' => 'The high length field is required.',
            'high_length.numeric' => 'The high length must be a number.',
            'high_length.min' => 'The high length must be at least 0.',
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors(), "status" => Response::HTTP_UNPROCESSABLE_ENTITY]);
        }

        $validated = $validated->validated();
        $cuttingData = CuttingData::create($validated);
        $this->CuttingDataPiecesController->store($cuttingData, $validated);

        return response()->json(['message' => 'Cutting data created successfully', "status" => Response::HTTP_CREATED]);
    }

    public function show($id)
    {
        $data = CuttingData::find($id);

        if ($data) {
            return response()->json(["data" => $data, "status" => Response::HTTP_OK]);
        }

        return response()->json(["data" => "No data found", "status" => Response::HTTP_NOT_FOUND]);
    }

    public function update(Request $request, $id)
    {
        // Update logic here
    }

    public function destroy($id)
    {
        $data = CuttingData::find($id);

        if ($data) {
            $data->delete();
            return response()->json(["data" => "Data deleted", "status" => Response::HTTP_OK]);
        }

        return response()->json(["data" => "No data found", "status" => Response::HTTP_NOT_FOUND]);
    }
}


