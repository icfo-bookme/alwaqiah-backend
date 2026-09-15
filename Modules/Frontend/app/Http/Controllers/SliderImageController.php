<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreSliderImageRequest;
use Modules\Frontend\Http\Requests\UpdateSliderImageRequest;
use Modules\Frontend\Services\SliderImageService;

class SliderImageController extends Controller
{
    protected $sliderImageService;

    public function __construct(SliderImageService $sliderImageService)
    {
        $this->sliderImageService = $sliderImageService;
    }

    /**
     * Display slider images listing page
     */
    public function index(Request $request)
    {
        return view('frontend::slider-images.index');
    }

    /**
     * Get slider image data for DataTable AJAX
     */
    public function dataTable(Request $request)
    {
        return $this->sliderImageService->getSliderImageDataTable($request);
    }

    /**
     * Store multiple slider images (batch upload)
     */
    public function store(StoreSliderImageRequest $request)
    {
        return response()->json($this->sliderImageService->saveSliderImages($request->validated()));
    }

    /**
     * Get single slider image by ID
     */
    public function show($id)
    {
        return response()->json($this->sliderImageService->getSliderImageById((int) $id));
    }

    /**
     * Update existing slider image
     */
    public function update(UpdateSliderImageRequest $request, $id)
    {
        return response()->json($this->sliderImageService->updateSliderImage($request->validated(), (int) $id));
    }

    /**
     * Delete slider image (soft delete)
     */
    public function destroy($id)
    {
        return response()->json($this->sliderImageService->deleteSliderImage((int) $id));
    }

    /**
     * Reorder slider images after drag & drop
     */
    public function reorder(Request $request)
    {
        return response()->json($this->sliderImageService->reorderSliderImages(
            $request->input('order', [])
        ));
    }
}
