<?php

namespace Modules\Frontend\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Frontend\Http\Requests\StoreCustomPackageRequest;
use Modules\Frontend\Services\CustomPackageRequestService;

class CustomPackageRequestApiController extends Controller
{
    protected $customPackageRequestService;

    public function __construct(CustomPackageRequestService $customPackageRequestService)
    {
        $this->customPackageRequestService = $customPackageRequestService;
    }

    /**
     * POST /api/custom-package-requests
     * Public custom package form submission — no auth middleware.
     */
    public function store(StoreCustomPackageRequest $request)
    {
        $result = $this->customPackageRequestService->saveCustomPackageRequest($request->validated());

        return response()->json($result, $result['status'] === 'success' ? 201 : 500);
    }
}
