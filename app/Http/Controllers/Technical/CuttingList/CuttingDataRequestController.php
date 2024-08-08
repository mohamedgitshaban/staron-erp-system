<?php

namespace App\Http\Controllers\Technical\CuttingList;
use App\Http\Controllers\Controller;
use App\Models\CuttingDataPieces;
use App\Models\CuttingDataRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
class CuttingDataRequestController extends Controller
{
    public function index()
    {
        $data = CuttingDataRequest::with('CuttingData')->orderBy('created_at', 'desc')->get();

        if (!$data->isEmpty()) {
            $data->transform(function ($dataitem) {
                $dataitem->created_date = Carbon::parse($dataitem->created_at)->toDateString();
                $dataitem->created_time = Carbon::parse($dataitem->created_at)->toTimeString();
                unset($dataitem->created_at);
                unset($dataitem->updated_at);

                if ($dataitem->CuttingData) {
                    // $dataitem->CuttingData->type = $this->convertToAsciiChar($dataitem->CuttingData->type);
                    // $dataitem->CuttingData->lasttype = (int)$dataitem->CuttingData->type+64;
                }

                return $dataitem;
            });

            return response()->json(["data" => $data, "status" => 200]);
        } else {
            return response()->json(["data" => "No Data", "status" => 404]);
        }
    }


    public function store(Request $request)
    {
        $validated=validator($request->all(),[
            'cutting_data_id' => 'required|exists:cutting_data,id',
            'hight' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'length' => 'required|numeric|min:0',
        ],[
            'cutting_data_id.required' => 'The cutting data ID field is required.',
            'cutting_data_id.exists' => 'The selected cutting data ID is invalid.',
            'hight.required' => 'The height field is required.',
            'hight.numeric' => 'The height must be a number.',
            'hight.min' => 'The height must be at least 0.',
            'width.required' => 'The width field is required.',
            'width.numeric' => 'The width must be a number.',
            'width.min' => 'The width must be at least 0.',
            'length.required' => 'The length field is required.',
            'length.numeric' => 'The length must be a number.',
            'length.min' => 'The length must be at least 0.',
        ]);
        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors(),"status"=>Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
        else{
            $validated=$validated->validated();
            CuttingDataRequest::create($validated);
            return response()->json(['message' => 'Request created successfully',"status"=>Response::HTTP_CREATED]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
{
    $cuttingDataRequest = CuttingDataRequest::find($id);
    if ($cuttingDataRequest) {
        $cuttingData = $cuttingDataRequest->CuttingData;
        $pieces = CuttingDataPieces::where('cutting_data_id', $cuttingData->id)->get();
        $formattedpieces = [];

        foreach ($pieces as $piece) {
            $temp = [
                "type" => $piece->type,
                "height" => $piece->type == "top" || $piece->type == "bottom" ? $cuttingDataRequest->hight : ($piece->type == "left side" || $piece->type == "right side" ? $cuttingDataRequest->length - 0.4 : ($piece->type == "back side" || $piece->type == "One Door" ? $cuttingDataRequest->length - 0.4 : ($cuttingDataRequest->length - 0.4))),
                "width" => $piece->type == "top" || $piece->type == "bottom" ? $cuttingDataRequest->width : ($piece->type == "left side" || $piece->type == "right side" ? $cuttingDataRequest->hight : ($piece->type == "back side" || $piece->type == "One Door" ? $cuttingDataRequest->width - 0.4 : ($cuttingDataRequest->width - 0.4) / 2)),
                "length" => 0.2
            ];
            $formattedpieces[] = $temp;
        }

        // Sort pieces by area in decreasing order
        usort($formattedpieces, function($a, $b) {
            return ($b['height'] * $b['width']) <=> ($a['height'] * $a['width']);
        });

        // Fit pieces into sheets
        $sheets = $this->fitPiecesIntoSheets($formattedpieces);

        return response()->json(["data" => $sheets, "status" => Response::HTTP_OK]);

    } else {
        return response()->json(["data" => "No data found", "status" => Response::HTTP_NOT_FOUND]);
    }
}
private function fitPiecesIntoSheets($pieces)
{
    $sheets = [];
    $currentSheet = $this->createNewSheet();

    foreach ($pieces as &$piece) {
        $placed = false;

        // Try to place the piece in existing sheets from left to right
        for ($i = 0; $i < count($sheets); $i++) {
            $placed = $this->placePieceInSheet($sheets[$i], $piece);
            if ($placed) {
                break;
            }
        }

        // If the piece could not be placed in existing sheets, try the current sheet
        if (!$placed) {
            $placed = $this->placePieceInSheet($currentSheet, $piece);
        }

        // If the piece still couldn't be placed, create a new sheet
        if (!$placed) {
            $sheets[] = $currentSheet;
            $currentSheet = $this->createNewSheet();
            $this->placePieceInSheet($currentSheet, $piece);
        }
    }

    // Add the last sheet
    $sheets[] = $currentSheet;
    return $sheets;
}

private function createNewSheet()
{
    return [
        'width' => 5,
        'height' => 5,
        'filled' => [],
        'freeSpaces' => [[0, 0, 5, 5]]
    ];
}


private function placePieceInSheet(&$sheet, &$piece)
{
    $overlapsWithAnotherPiece = false;

    // Sort free spaces by x coordinate (leftmost position)
    usort($sheet['freeSpaces'], function ($a, $b) {
        return $a[0] <=> $b[0]; // Ascending order
    });

    for ($i = 0; $i <= 1; $i++) { // Try both orientations
        foreach ($sheet['freeSpaces'] as $index => &$space) {
            if ($piece['width'] <= $space[2] && $piece['height'] <= $space[3]) {
                // Check if the piece overlaps with any other pieces
                $overlapsWithAnotherPiece = false;
                foreach ($sheet['filled'] as $filledPiece) {
                    if ($this->pieceOverlaps($filledPiece, $piece, $space[0], $space[1])) {
                        $overlapsWithAnotherPiece = true;
                        break;
                    }
                }

                if (!$overlapsWithAnotherPiece) {
                    // Place piece
                    $piece['x'] = $space[0];
                    $piece['y'] = $space[1];
                    $sheet['filled'][] = $piece;

                    // Update free spaces
                    $newSpaces = $this->splitFreeSpace($space, $piece);
                    array_splice($sheet['freeSpaces'], $index, 1, $newSpaces);
                    return true;
                }
            }
        }
        $this->rotatePiece($piece);
    }

    return false;
}

private function pieceOverlaps($filledPiece, $piece, $x, $y)
{
    return $filledPiece['x'] <= $x + $piece['width'] &&
           $filledPiece['x'] + $filledPiece['width'] >= $x &&
           $filledPiece['y'] <= $y + $piece['height'] &&
           $filledPiece['y'] + $filledPiece['height'] >= $y;    
}
private function splitFreeSpace($space, $piece)
{
    $newSpaces = [];
    $widthSpace = $space[2];
    $heightSpace = $space[3];

    // Space remaining on the right
    if ($piece['width'] < $widthSpace) {
        $newSpaces[] = [$space[0] + $piece['width'] + 0.02, $space[1], $widthSpace - $piece['width'] - 0.02, $heightSpace];
    }

    // Space remaining below
    if ($piece['height'] < $heightSpace) {
        $newSpaces[] = [$space[0], $space[1] + $piece['height'] + 0.02, $widthSpace, $heightSpace - $piece['height'] - 0.02];
    }

    // Space in the corner
    if ($piece['width'] < $widthSpace && $piece['height'] < $heightSpace) {
        $newSpaces[] = [$space[0] + $piece['width'] + 0.02, $space[1] + $piece['height'] + 0.02, $widthSpace - $piece['width'] - 0.02, $heightSpace - $piece['height'] - 0.02];
    }

    return $newSpaces;
}

private function rotatePiece(&$piece)
{
    $temp = $piece['height'];
    $piece['height'] = $piece['width'];
    $piece['width'] = $temp;
}


    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        $data=CuttingDataRequest::find($id);
        if($data!=null){
            $data->delete();
            return response()->json(["data"=>"data deleted","status"=>Response::HTTP_NOT_FOUND ]);

        }
        else{
            return response()->json(["data"=>"no data","status"=>Response::HTTP_NOT_FOUND ]);
        }
    }
}
