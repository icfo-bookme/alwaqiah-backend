<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreCustomPackageRequest;
use Modules\Frontend\Http\Requests\UpdateCustomPackageRequest;
use Modules\Frontend\Services\CustomPackageRequestService;

class CustomPackageRequestController extends Controller
{
    protected $customPackageRequestService;

    public function __construct(CustomPackageRequestService $customPackageRequestService)
    {
        $this->customPackageRequestService = $customPackageRequestService;
    }

    /**
     * Display the custom package requests listing page.
     */
    public function index(Request $request)
    {
        return view('frontend::custom-package-requests.index');
    }

    /**
     * Get request data for DataTable AJAX.
     */
    public function dataTable(Request $request)
    {
        return $this->customPackageRequestService->getCustomPackageRequestDataTable($request);
    }

    /**
     * Store a new request (admin side entry).
     */
    public function store(StoreCustomPackageRequest $request)
    {
        return response()->json($this->customPackageRequestService->saveCustomPackageRequest($request->validated()));
    }

    /**
     * Get single request by ID.
     */
    public function show($id)
    {
        return response()->json($this->customPackageRequestService->getCustomPackageRequestById((int) $id));
    }

    /**
     * Update existing request (status, quote, admin note).
     */
    public function update(UpdateCustomPackageRequest $request, $id)
    {
        return response()->json($this->customPackageRequestService->updateCustomPackageRequest(
            $request->validated(),
            (int) $id
        ));
    }

    /**
     * Delete request (soft delete).
     */
    public function destroy($id)
    {
        return response()->json($this->customPackageRequestService->deleteCustomPackageRequest((int) $id));
    }
}
