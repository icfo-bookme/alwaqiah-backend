<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreFlightRequest;
use Modules\Frontend\Http\Requests\UpdateFlightRequest;
use Modules\Frontend\Services\FlightService;

class FlightController extends Controller
{
    protected $flightService;

    public function __construct(FlightService $flightService)
    {
        $this->flightService = $flightService;
    }

    /**
     * Display flights listing page
     */
    public function index(Request $request)
    {
        return view('frontend::flights.index');
    }

    /**
     * Get flight data for DataTable AJAX
     */
    public function dataTable(Request $request)
    {
        return $this->flightService->getFlightDataTable($request);
    }

    /**
     * Store new flight
     */
    public function store(StoreFlightRequest $request)
    {
        $result = $this->flightService->saveFlight($request->validated());

        return response()->json($result);
    }

    /**
     * Get single flight by ID
     */
    public function show($id)
    {
        $result = $this->flightService->getFlightById((int) $id);

        return response()->json($result);
    }

    /**
     * Update existing flight
     */
    public function update(UpdateFlightRequest $request, $id)
    {
        $result = $this->flightService->updateFlight($request->validated(), (int) $id);

        return response()->json($result);
    }

    /**
     * Reorder flights after drag & drop
     */
    public function reorder(Request $request)
    {
        return response()->json($this->flightService->reorderFlights(
            $request->input('order', []),
            (int) $request->input('start', 0)
        ));
    }

    /**
     * Delete flight (soft delete)
     */
    public function destroy($id)
    {
        $result = $this->flightService->deleteFlight((int) $id);

        return response()->json($result);
    }
}
