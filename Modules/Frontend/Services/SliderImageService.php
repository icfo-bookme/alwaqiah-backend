<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Frontend\Models\SliderImage;
use Yajra\DataTables\DataTables;

class SliderImageService
{
    /**
     * Get slider image data for the DataTable AJAX request.
     */
    public function getSliderImageDataTable(Request $request)
    {
        $query = SliderImage::select(
            'slider_images.id',
            'slider_images.image',
            'slider_images.sort_order',
            'slider_images.is_active',
            'slider_images.published_at',
            'slider_images.created_at'
        )
            ->orderBy('slider_images.sort_order')
            ->orderBy('slider_images.id');

        if ($request->is_active !== null && $request->is_active !== '') {
            $query->where('slider_images.is_active', $request->is_active);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('image', function (SliderImage $slider) {
                return '<img src="'.asset('storage/'.$slider->image).'" alt="'.e(basename($slider->image)).'" class="h-10 w-16 rounded-md object-cover ring-1 ring-gray-200">';
            })
            ->editColumn('is_active', function (SliderImage $slider) {
                return statusBadge($slider->is_active);
            })
            ->editColumn('published_at', function (SliderImage $slider) {
                return $slider->published_at
                    ? $slider->published_at->format('d M Y H:i')
                    : '&mdash;';
            })
            ->editColumn('created_at', function (SliderImage $slider) {
                return $slider->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (SliderImage $slider) {
                return view('components.action-buttons', [
                    'id' => $slider->id,
                    'edit' => 'sliderImageEdit',
                    'delete' => 'sliderImageDelete',
                ])->render();
            })
            ->rawColumns(['image', 'is_active', 'published_at', 'action'])
            ->make(true);
    }

    /**
     * Upload multiple slider images at once — each selected file
     * becomes its own slider record.
     */
    public function saveSliderImages(array $data): array
    {
        $storedImages = [];

        try {
            foreach ($data['images'] as $file) {
                if ($file instanceof UploadedFile) {
                    $storedImages[] = $this->storeImage($file);
                }
            }

            DB::transaction(function () use ($data, $storedImages) {

                $userId = auth()->id();

                $nextSort = ((int) SliderImage::max('sort_order')) + 1;

                foreach ($storedImages as $path) {
                    SliderImage::create([
                        'image' => $path,
                        'sort_order' => $nextSort++,
                        'is_active' => $data['is_active'] ?? true,
                        'published_at' => now(),
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                }
            });

            return [
                'status' => 'success',
                'message' => count($storedImages).' slider image(s) uploaded successfully.',
            ];
        } catch (\Exception $e) {

            foreach ($storedImages as $path) {
                Storage::disk('public')->delete($path);
            }

            return [
                'status' => 'error',
                'message' => 'Error uploading slider images: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Update a single slider record — replaces the stored image
     * only when a new file is uploaded (old file is deleted).
     */
    public function updateSliderImage(array $data, int $id): array
    {
        try {
            return DB::transaction(function () use ($data, $id) {
                $slider = SliderImage::findOrFail($id);

                if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                    $this->deleteImageFile($slider->image);
                    $data['image'] = $this->storeImage($data['image']);
                }

                $data['updated_by'] = auth()->id();

                $slider->update($data);

                return [
                    'status' => 'success',
                    'message' => 'Slider image updated successfully.',
                    'slider' => $slider->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating slider image: '.$e->getMessage(),
                'slider' => null,
            ];
        }
    }

    /**
     * Get a single slider image by ID (for the edit drawer).
     */
    public function getSliderImageById(int $id): array
    {
        try {
            $slider = SliderImage::findOrFail($id);

            return [
                'status' => 'success',
                'slider' => $slider,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Slider image not found.',
                'slider' => null,
            ];
        }
    }

    /**
     * Soft delete a slider record. The stored file is kept so the
     * record can still be restored from trash.
     */
    public function deleteSliderImage(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $slider = SliderImage::findOrFail($id);

                $slider->delete();

                return [
                    'status' => 'success',
                    'message' => 'Slider image deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting slider image: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Persist the new sort order after drag & drop —
     * each array position becomes the sort_order, offset by the current page.
     */
    public function reorderSliderImages(array $orderedIds, int $start = 0): array
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
                    SliderImage::where('id', (int) $id)->update(['sort_order' => $start + $index + 1]);
                }

                return [
                    'status' => 'success',
                    'message' => 'Slider order updated successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating slider order: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Frontend API: all active slider images, ordered for display.
     */
    public function getFrontendSliders(): array
    {
        $sliders = SliderImage::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'image',
                'alt_text',
                'sort_order',
                'published_at',
            ]);

        return [
            'status' => 'success',
            'count' => $sliders->count(),
            'sliders' => $sliders->map(fn (SliderImage $slider) => $this->formatFrontendSlider($slider))->values()->all(),
        ];
    }

    /**
     * Shape a slider for frontend consumption (full image URL resolved).
     */
    private function formatFrontendSlider(SliderImage $slider): array
    {
        return [
            'id' => $slider->id,
            'alt_text' => $slider->alt_text,
            'image' => $slider->image,
            'image_url' => $slider->image_url,
            'sort_order' => $slider->sort_order,
            'published_at' => $slider->published_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Store an uploaded image on the public disk and return the path.
     */
    private function storeImage(UploadedFile $file): string
    {
        return $file->store('sliders', 'public');
    }

    /**
     * Delete a stored image file from the public disk if it exists.
     */
    private function deleteImageFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
