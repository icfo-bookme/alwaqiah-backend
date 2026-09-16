<?php

namespace Modules\Frontend\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Services\PackageService;

class PackageApiController extends Controller
{
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * GET /api/packages
     * All active packages (for frontend display), ordered by sort_order.
     * Optional filter: ?type=hajj|umrah
     */
    public function index(Request $request)
    {
        $type = $request->query('type');

        if ($type !== null && ! in_array($type, ['hajj', 'umrah'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid type. Allowed values: hajj, umrah.',
                'packages' => [],
            ], 422);
        }

        return response()->json($this->packageService->getFrontendPackages($type));
    }
}
