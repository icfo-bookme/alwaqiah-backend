<?php

namespace Modules\Frontend\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Frontend\Services\FlightService;

class FlightApiController extends Controller
{
    protected $flightService;

    public function __construct(FlightService $flightService)
    {
        $this->flightService = $flightService;
    }

    /**
     * GET /api/flights
     * All active flights (for frontend display), ordered by sort_order.
     */
    public function index()
    {
        return response()->json($this->flightService->getFrontendFlights());
    }
}
