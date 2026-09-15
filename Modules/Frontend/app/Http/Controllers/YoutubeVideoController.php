<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreYoutubeVideoRequest;
use Modules\Frontend\Http\Requests\UpdateYoutubeVideoRequest;
use Modules\Frontend\Services\YoutubeVideoService;

class YoutubeVideoController extends Controller
{
    protected $youtubeVideoService;

    public function __construct(YoutubeVideoService $youtubeVideoService)
    {
        $this->youtubeVideoService = $youtubeVideoService;
    }

    /**
     * Display YouTube videos listing page
     */
    public function index(Request $request)
    {
        return view('frontend::youtube-videos.index');
    }

    /**
     * Get video data for DataTable AJAX
     */
    public function dataTable(Request $request)
    {
        return $this->youtubeVideoService->getYoutubeVideoDataTable($request);
    }

    /**
     * Store new video
     */
    public function store(StoreYoutubeVideoRequest $request)
    {
        $result = $this->youtubeVideoService->saveYoutubeVideo($request->validated());
        return response()->json($result);
    }

    /**
     * Get single video by ID
     */
    public function show($id)
    {
        $result = $this->youtubeVideoService->getYoutubeVideoById((int) $id);
        return response()->json($result);
    }

    /**
     * Update existing video
     */
    public function update(UpdateYoutubeVideoRequest $request, $id)
    {
        $result = $this->youtubeVideoService->updateYoutubeVideo($request->validated(), (int) $id);
        return response()->json($result);
    }

    /**
     * Reorder videos after drag & drop
     */
    public function reorder(Request $request)
    {
        return response()->json($this->youtubeVideoService->reorderYoutubeVideos(
            $request->input('order', [])
        ));
    }

    /**
     * Delete video (soft delete)
     */
    public function destroy($id)
    {
        $result = $this->youtubeVideoService->deleteYoutubeVideo((int) $id);
        return response()->json($result);
    }
}