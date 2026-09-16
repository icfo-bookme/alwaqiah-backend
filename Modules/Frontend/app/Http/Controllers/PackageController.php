<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\SavePackageFeaturesRequest;
use Modules\Frontend\Http\Requests\StorePackageRequest;
use Modules\Frontend\Http\Requests\UpdatePackageRequest;
use Modules\Frontend\Services\PackageService;

class PackageController extends Controller
{
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Display packages listing page
     */
    public function index(Request $request)
    {
        return view('frontend::packages.index');
    }

    /**
     * Get package data for DataTable AJAX
     */
    public function dataTable(Request $request)
    {
        return $this->packageService->getPackageDataTable($request);
    }

    /**
     * Store new package
     */
    public function store(StorePackageRequest $request)
    {
        return response()->json($this->packageService->savePackage($request->validated()));
    }

    /**
     * Get single package by ID
     */
    public function show($id)
    {
        return response()->json($this->packageService->getPackageById((int) $id));
    }

    /**
     * Update existing package
     */
    public function update(UpdatePackageRequest $request, $id)
    {
        return response()->json($this->packageService->updatePackage($request->validated(), (int) $id));
    }

    /**
     * Delete package (soft delete with deleted_by tracking)
     */
    public function destroy($id)
    {
        return response()->json($this->packageService->deletePackage((int) $id));
    }

    /**
     * Reorder packages after drag & drop
     */
    public function reorder(Request $request)
    {
        return response()->json($this->packageService->reorderPackages(
            $request->input('order', []),
            (int) $request->input('start', 0)
        ));
    }

    /**
     * Get all features of a package (for the features drawer)
     */
    public function features($id)
    {
        return response()->json($this->packageService->getPackageFeatures((int) $id));
    }

    /**
     * Save (create/update/remove) the features of a package
     */
    public function saveFeatures(SavePackageFeaturesRequest $request, $id)
    {
        return response()->json($this->packageService->savePackageFeatures(
            $request->validated(),
            (int) $id
        ));
    }
}
