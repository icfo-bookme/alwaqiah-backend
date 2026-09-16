<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreIconRequest;
use Modules\Frontend\Http\Requests\UpdateIconRequest;
use Modules\Frontend\Services\IconService;

class IconController extends Controller
{
    protected $iconService;

    public function __construct(IconService $iconService)
    {
        $this->iconService = $iconService;
    }

    /**
     * Display the icons listing page.
     */
    public function index(Request $request)
    {
        return view('frontend::icons.index');
    }

    /**
     * Get icon data for DataTable AJAX.
     */
    public function dataTable(Request $request)
    {
        return $this->iconService->getIconDataTable($request);
    }

    /**
     * Store a new icon.
     */
    public function store(StoreIconRequest $request)
    {
        return response()->json($this->iconService->saveIcon($request->validated()));
    }

    /**
     * Get a single icon by ID.
     */
    public function show($id)
    {
        return response()->json($this->iconService->getIconById((int) $id));
    }

    /**
     * Update an existing icon.
     */
    public function update(UpdateIconRequest $request, $id)
    {
        return response()->json($this->iconService->updateIcon($request->validated(), (int) $id));
    }

    /**
     * Soft delete an icon.
     */
    public function destroy($id)
    {
        return response()->json($this->iconService->deleteIcon((int) $id));
    }

    /**
     * Persist the new order after drag & drop.
     */
    public function reorder(Request $request)
    {
        $ids = $request->input('order', []);

        return response()->json($this->iconService->reorderIcons(
            $ids,
            (int) $request->input('start', 0)
        ));
    }

    /**
     * Lightweight icon list for the icon picker.
     */
    public function options()
    {
        return response()->json($this->iconService->getIconOptions());
    }
}
