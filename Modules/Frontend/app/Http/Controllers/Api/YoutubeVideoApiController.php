<?php

namespace Modules\Frontend\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Frontend\Services\YoutubeVideoService;

class YoutubeVideoApiController extends Controller
{
    protected $youtubeVideoService;

    public function __construct(YoutubeVideoService $youtubeVideoService)
    {
        $this->youtubeVideoService = $youtubeVideoService;
    }

    /**
     * GET /api/youtube-videos
     * All active videos (for frontend display), ordered by sort_order.
     */
    public function index()
    {
        return response()->json($this->youtubeVideoService->getFrontendVideos());
    }
}
