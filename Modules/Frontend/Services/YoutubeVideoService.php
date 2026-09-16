<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Models\YoutubeVideo;
use Yajra\DataTables\DataTables;

class YoutubeVideoService
{
    public function getYoutubeVideoDataTable(Request $request)
    {
        $query = YoutubeVideo::select(
            'youtube_videos.id',
            'youtube_videos.title',
            'youtube_videos.slug',
            'youtube_videos.youtube_video_id',
            'youtube_videos.thumbnail',
            'youtube_videos.sort_order',
            'youtube_videos.is_active',
            'youtube_videos.published_at',
            'youtube_videos.created_at'
        )
            ->orderBy('youtube_videos.sort_order')
            ->orderBy('youtube_videos.id');

        if ($request->is_active !== null && $request->is_active !== '') {
            $query->where('youtube_videos.is_active', $request->is_active);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('thumbnail', function (YoutubeVideo $video) {
                return $video->thumbnail
                    ? '<img src="'.e($video->thumbnail).'" alt="'.e($video->title).'" class="h-10 w-16 rounded-md object-cover ring-1 ring-gray-200">'
                    : '&mdash;';
            })
            ->editColumn('is_active', function (YoutubeVideo $video) {
                return statusBadge($video->is_active);
            })
            ->editColumn('published_at', function (YoutubeVideo $video) {
                return $video->published_at
                    ? $video->published_at->format('d M Y H:i')
                    : '&mdash;';
            })
            ->editColumn('created_at', function (YoutubeVideo $video) {
                return $video->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (YoutubeVideo $video) {
                return view('components.action-buttons', [
                    'id' => $video->id,
                    'edit' => 'youtubeVideoEdit',
                    'delete' => 'youtubeVideoDelete',
                ])->render();
            })
            ->rawColumns(['thumbnail', 'is_active', 'published_at', 'action'])
            ->make(true);
    }

    public function saveYoutubeVideo(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                // Auto-extract video ID & default thumbnail from URL.
                $this->prepareVideoData($data);

                // Auto sort_order — one higher than the current maximum.
                $data['sort_order'] = ((int) YoutubeVideo::max('sort_order')) + 1;

                // Auto-set publish timestamp on creation (frontend does not send it).
                $data['published_at'] = now();

                $data['created_by'] = $data['created_by'] ?? auth()->id();
                $data['updated_by'] = $data['updated_by'] ?? auth()->id();

                $video = YoutubeVideo::create($data);

                return [
                    'status' => 'success',
                    'message' => 'Video created successfully.',
                    'video' => $video->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving video: '.$e->getMessage(),
                'video' => null,
            ];
        }
    }

    public function updateYoutubeVideo(array $data, int $id): array
    {
        try {
            return DB::transaction(function () use ($data, $id) {
                $video = YoutubeVideo::findOrFail($id);

                // Auto-extract video ID & default thumbnail from URL.
                $this->prepareVideoData($data);

                $data['updated_by'] = $data['updated_by'] ?? auth()->id();

                $video->update($data);

                return [
                    'status' => 'success',
                    'message' => 'Video updated successfully.',
                    'video' => $video->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating video: '.$e->getMessage(),
                'video' => null,
            ];
        }
    }

    /**
     * Frontend API: all active videos, ordered for display.
     */
    public function getFrontendVideos(): array
    {
        $videos = YoutubeVideo::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'title',
                'slug',
                'youtube_video_id',
                'youtube_url',
                'thumbnail',
                'description',
                'sort_order',
                'published_at',
            ]);

        return [
            'status' => 'success',
            'count' => $videos->count(),
            'videos' => $videos->map(fn (YoutubeVideo $video) => $this->formatFrontendVideo($video))->values()->all(),
        ];
    }

    /**
     * Shape a video for frontend consumption (embed/watch URLs resolved).
     */
    private function formatFrontendVideo(YoutubeVideo $video): array
    {
        return [
            'id' => $video->id,
            'title' => $video->title,
            'slug' => $video->slug,
            'description' => $video->description,
            'youtube_video_id' => $video->youtube_video_id,
            'youtube_url' => $video->youtube_url,
            'embed_url' => 'https://www.youtube.com/embed/'.$video->youtube_video_id,
            'watch_url' => 'https://www.youtube.com/watch?v='.$video->youtube_video_id,
            'thumbnail' => $video->thumbnail,
            'sort_order' => $video->sort_order,
            'published_at' => $video->published_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function prepareVideoData(array &$data): void
    {
        if (empty($data['youtube_video_id']) && ! empty($data['youtube_url'])) {
            $data['youtube_video_id'] = $this->extractYouTubeId($data['youtube_url']);
        }

        if (empty($data['thumbnail']) && ! empty($data['youtube_video_id'])) {
            $data['thumbnail'] = 'https://img.youtube.com/vi/'.$data['youtube_video_id'].'/hqdefault.jpg';
        }
    }

    public function getYoutubeVideoById(int $id): array
    {
        try {
            $video = YoutubeVideo::findOrFail($id);

            return [
                'status' => 'success',
                'video' => $video,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Video not found.',
                'video' => null,
            ];
        }
    }

    public function deleteYoutubeVideo(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $video = YoutubeVideo::findOrFail($id);

                // Soft delete (deleted_at is set, record stays in DB)
                $video->delete();

                return [
                    'status' => 'success',
                    'message' => 'Video deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting video: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Persist the new sort order after drag & drop —
     * each array position becomes the sort_order, offset by the current page.
     */
    public function reorderYoutubeVideos(array $orderedIds, int $start = 0): array
    {
        try {
            if (empty($orderedIds)) {
                return [
                    'status' => 'error',
                    'message' => 'No order provided.',
                ];
            }

            return DB::transaction(function () use ($orderedIds, $start) {
                foreach ($orderedIds as $index => $id) {
                    YoutubeVideo::where('id', (int) $id)->update(['sort_order' => $start + $index + 1]);
                }

                return [
                    'status' => 'success',
                    'message' => 'Video order updated successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating video order: '.$e->getMessage(),
            ];
        }
    }

    private function extractYouTubeId(string $url): ?string
    {
        if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_\-]{6,})#i', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
