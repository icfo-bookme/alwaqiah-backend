<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Frontend\Models\Package;
use Modules\Frontend\Models\PackageFeature;
use Yajra\DataTables\DataTables;

class PackageService
{
    /**
     * Get package data for the DataTable AJAX request.
     */
    public function getPackageDataTable(Request $request)
    {
        $query = Package::select(
                'packages.id',
                'packages.title',
                'packages.duration_days',
                'packages.price',
                'packages.thumbnail',
                'packages.package_type',
                'packages.is_featured',
                'packages.is_active',
                'packages.sort_order',
                'packages.created_at'
            )
            ->orderBy('packages.sort_order')
            ->orderBy('packages.id');

        if ($request->is_active !== null && $request->is_active !== '') {
            $query->where('packages.is_active', $request->is_active);
        }

        if ($request->package_type !== null && $request->package_type !== '') {
            $query->where('packages.package_type', $request->package_type);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('thumbnail', function (Package $package) {
                return $package->thumbnail
                    ? '<img src="' . asset('storage/' . $package->thumbnail) . '" alt="' . e($package->title) . '" class="h-10 w-16 rounded-md object-cover ring-1 ring-gray-200">'
                    : '&mdash;';
            })
            ->editColumn('duration_days', function (Package $package) {
                return $package->duration_days . ' Days';
            })
            ->editColumn('price', function (Package $package) {
                return '৳' . number_format((float) $package->price, 2);
            })
            ->editColumn('package_type', function (Package $package) {
                $color = $package->package_type === 'hajj'
                    ? 'bg-amber-100 text-amber-700'
                    : 'bg-emerald-100 text-emerald-700';

                return '<span class="px-2 py-0.5 rounded-full text-xs font-semibold ' . $color . '">'
                    . ucfirst($package->package_type) . '</span>';
            })
            ->editColumn('is_featured', function (Package $package) {
                return $package->is_featured
                    ? '<span class="text-amber-500"><i class="fa-solid fa-star"></i> Featured</span>'
                    : '&mdash;';
            })
            ->editColumn('is_active', function (Package $package) {
                return statusBadge($package->is_active);
            })
            ->editColumn('created_at', function (Package $package) {
                return $package->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Package $package) {
                return view('components.action-buttons', [
                    'id'           => $package->id,
                    'feature'      => 'packageFeatures',
                    'featureTitle' => 'Manage Features',
                    'edit'         => 'packageEdit',
                    'delete'       => 'packageDelete',
                ])->render();
            })
            ->rawColumns(['thumbnail', 'package_type', 'is_featured', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Create a new package — sort_order is auto-assigned
     * (one higher than the current maximum).
     */
    public function savePackage(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                // Defensive slug fallback (Request usually provides it).
                if (empty($data['slug'])) {
                    $data['slug'] = Str::slug($data['title']);
                }

                // Store the uploaded thumbnail and keep only its path.
                if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
                    $data['thumbnail'] = $this->storeThumbnail($data['thumbnail']);
                }

                // Auto sort_order — one higher than the current maximum.
                $data['sort_order'] = ((int) Package::max('sort_order')) + 1;

                $data['created_by'] = auth()->id();
                $data['updated_by'] = auth()->id();

                $package = Package::create($data);

                return [
                    'status'  => 'success',
                    'message' => 'Package created successfully.',
                    'package' => $package->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error creating package: ' . $e->getMessage(),
                'package' => null,
            ];
        }
    }

    /**
     * Update an existing package.
     */
    public function updatePackage(array $data, int $id): array
    {
        try {
            return DB::transaction(function () use ($data, $id) {
                $package = Package::findOrFail($id);

                // Keep the existing slug when none is provided.
                if (empty($data['slug'])) {
                    unset($data['slug']);
                }

                // Replace the thumbnail only when a new file is uploaded.
                if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
                    $this->deleteThumbnailFile($package->thumbnail);
                    $data['thumbnail'] = $this->storeThumbnail($data['thumbnail']);
                } else {
                    unset($data['thumbnail']);
                }

                $data['updated_by'] = auth()->id();

                $package->update($data);

                return [
                    'status'  => 'success',
                    'message' => 'Package updated successfully.',
                    'package' => $package->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error updating package: ' . $e->getMessage(),
                'package' => null,
            ];
        }
    }

    /**
     * Get a single package by ID (for the edit drawer).
     */
    public function getPackageById(int $id): array
    {
        try {
            $package = Package::findOrFail($id);
            return [
                'status'  => 'success',
                'package' => $package,
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Package not found.',
                'package' => null,
            ];
        }
    }

    /**
     * Soft delete a package — records who deleted it (deleted_by).
     */
    public function deletePackage(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $package = Package::findOrFail($id);

                $package->deleted_by = auth()->id();
                $package->save();

                $package->delete(); // Soft delete (deleted_at is set, record stays in DB)

                return [
                    'status'  => 'success',
                    'message' => 'Package deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error deleting package: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Persist the new sort order after drag & drop —
     * each array position becomes the sort_order (1, 2, 3...).
     */
    public function reorderPackages(array $orderedIds): array
    {
        try {
            if (empty($orderedIds)) {
                return [
                    'status'  => 'error',
                    'message' => 'No order provided.',
                ];
            }

            return DB::transaction(function () use ($orderedIds) {
                foreach ($orderedIds as $index => $id) {
                    Package::where('id', (int) $id)->update(['sort_order' => $index + 1]);
                }

                return [
                    'status'  => 'success',
                    'message' => 'Package order updated successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error updating package order: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get all features of a package (for the features drawer).
     */
    public function getPackageFeatures(int $id): array
    {
        try {
            $package = Package::findOrFail($id);

            return [
                'status'   => 'success',
                'package'  => [
                    'id'    => $package->id,
                    'title' => $package->title,
                ],
                'features' => $package->features()->get(['id', 'icon', 'title']),
            ];
        } catch (\Exception $e) {
            return [
                'status'   => 'error',
                'message'  => 'Package not found.',
                'package'  => null,
                'features' => [],
            ];
        }
    }

    /**
     * Sync the features of a package — update the existing rows, create the new
     * ones and soft delete the rows the user removed in the drawer.
     */
    public function savePackageFeatures(array $data, int $id): array
    {
        try {
            return DB::transaction(function () use ($data, $id) {
                $package = Package::findOrFail($id);

                $keptIds = [];

                foreach (array_values($data['features'] ?? []) as $index => $row) {
                    if (empty($row['title'])) {
                        continue;
                    }

                    $attributes = [
                        'icon'       => ! empty($row['icon']) ? $row['icon'] : null,
                        'title'      => $row['title'],
                        'sort_order' => $index + 1,
                        'updated_by' => auth()->id(),
                    ];

                    $featureId = $row['id'] ?? null;
                    $feature   = $featureId
                        ? $package->features()->whereKey($featureId)->first()
                        : null;

                    if ($feature) {
                        $feature->update($attributes);
                    } else {
                        $feature = $package->features()->create(
                            $attributes + ['created_by' => auth()->id()]
                        );
                    }

                    $keptIds[] = $feature->id;
                }

                // Soft delete the features the user removed in the drawer.
                PackageFeature::where('package_id', $package->id)
                    ->whereNotIn('id', $keptIds)
                    ->delete();

                return [
                    'status'   => 'success',
                    'message'  => count($keptIds) . ' feature(s) saved successfully.',
                    'features' => $package->features()->get(['id', 'icon', 'title']),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error saving features: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Store an uploaded thumbnail on the public disk and return the path.
     */
    private function storeThumbnail(UploadedFile $file): string
    {
        return $file->store('packages', 'public');
    }

    /**
     * Delete a stored thumbnail file from the public disk if it exists.
     */
    private function deleteThumbnailFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
