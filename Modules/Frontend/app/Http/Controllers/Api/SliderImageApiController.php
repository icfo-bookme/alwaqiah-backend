<?php

namespace Modules\Frontend\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Frontend\Services\SliderImageService;

class SliderImageApiController extends Controller
{
    protected $sliderImageService;

    public function __construct(SliderImageService $sliderImageService)
    {
        $this->sliderImageService = $sliderImageService;
    }

    /**
     * GET /api/sliders
     * All active slider images (for frontend display), ordered by sort_order.
     */
    public function index()
    {
        return response()->json($this->sliderImageService->getFrontendSliders());
    }
}
