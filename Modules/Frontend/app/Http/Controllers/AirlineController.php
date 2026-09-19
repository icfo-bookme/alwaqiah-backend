<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreAirlineRequest;
use Modules\Frontend\Http\Requests\UpdateAirlineRequest;
use Modules\Frontend\Services\AirlineService;

class AirlineController extends Controller
{
    protected $airlineService;

    public function __construct(AirlineService $airlineService)
    {
        $this->airlineService = $airlineService;
    }

    /**
     * Display airlines listing page
     */
    public function index(Request $request)
    {
        return view('frontend::airlines.index');
    }

    /**
     * Get airline data for DataTable AJAX
     */
    public function dataTable(Request $request)
    {
        return $this->airlineService->getAirlineDataTable($request);
    }

    /**
     * Store new airline
     */
    public function store(StoreAirlineRequest $request)
    {
        $result = $this->airlineService->saveAirline($request->validated());
        return response()->json($result);
    }

    /**
     * Get single airline by ID
     */
    public function show($id)
    {
        $result = $this->airlineService->getAirlineById((int) $id);
        return response()->json($result);
    }

    /**
     * Update existing airline
     */
    public function update(UpdateAirlineRequest $request, $id)
    {
        $result = $this->airlineService->updateAirline($request->validated(), (int) $id);
        return response()->json($result);
    }

    /**
     * Reorder airlines after drag & drop
     */
    public function reorder(Request $request)
    {
        return response()->json($this->airlineService->reorderAirlines(
            $request->input('order', []),
            (int) $request->input('start', 0)
        ));
    }

    /**
     * Delete airline (soft delete)
     */
    public function destroy($id)
    {
        $result = $this->airlineService->deleteAirline((int) $id);
        return response()->json($result);
    }

    /**
     * Lightweight active airline list for dropdowns.
     */
    public function options()
    {
        return response()->json($this->airlineService->getAirlineOptions());
    }
}
