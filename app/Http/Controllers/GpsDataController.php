<?php

namespace App\Http\Controllers;

use App\Events\GpsLocationEvent;
use App\Services\GpsDataParserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GpsDataController extends Controller
{
    private $parserService;
    private static $latestData = null;

    public function __construct(GpsDataParserService $parserService)
    {
        $this->parserService = $parserService;
    }

    /**
     * Store GPS data and broadcast location update
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|string'
        ]);

        try {
            $parsedData = $this->parserService->parse($request->input('data'));
            self::$latestData = $parsedData;

            // Broadcast the GPS location update
            broadcast(new GpsLocationEvent(
                latitude: $parsedData['latitude'],
                longitude: $parsedData['longitude'],
                deviceId: $request->input('device_id')
            ));

            return response()->json([
                'success' => true,
                'data' => $parsedData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to parse GPS data: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get latest GPS data
     */
    public function getLatest(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => self::$latestData
        ]);
    }
}
