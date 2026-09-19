<?php

namespace Modules\Frontend\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Frontend\Services\AirlineService;

class AirlineApiController extends Controller
{
    protected $airlineService;

    public function __construct(AirlineService $airlineService)
    {
        $this->airlineService = $airlineService;
    }

    /**
     * GET /api/airlines
     * All active airlines (for frontend display), ordered by sort_order.
     */
    public function index()
    {
        return response()->json($this->airlineService->getAirlineOptions());
    }
}
