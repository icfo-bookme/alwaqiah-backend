<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Models\Icon;
use Yajra\DataTables\DataTables;

class IconService
{
    /**
     * Get icon data for the DataTable AJAX request.
     */
    public function getIconDataTable(Request $request)
    {
        $query = Icon::select(
                'icons.id',
                'icons.name',
                'icons.class',
                'icons.keywords',
                'icons.sort_order',
                'icons.is_active',
                'icons.created_at'
            )
            ->orderBy('icons.sort_order')
            ->orderBy('icons.id');

        if ($request->is_active !== null && $request->is_active !== '') {
            $query->where('icons.is_active', $request->is_active);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', function (Icon $icon) {
                return '<div class="flex items-center gap-2">'
                    . '<i class="' . e($icon->class) . ' text-lg text-gray-700 w-6 text-center"></i>'
                    . '<span>' . e($icon->name) . '</span>'
                    . '</div>';
            })
            ->editColumn('class', function (Icon $icon) {
                return '<code class="px-2 py-1 rounded bg-gray-100 text-xs text-gray-700">' . e($icon->class) . '</code>';
            })
            ->editColumn('keywords', function (Icon $icon) {
                return $icon->keywords ? e($icon->keywords) : '&mdash;';
            })
            ->editColumn('is_active', function (Icon $icon) {
                return statusBadge($icon->is_active);
            })
            ->editColumn('created_at', function (Icon $icon) {
                return $icon->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Icon $icon) {
                return view('components.action-buttons', [
                    'id'     => $icon->id,
                    'edit'   => 'iconEdit',
                    'delete' => 'iconDelete',
                ])->render();
            })
            ->rawColumns(['name', 'class', 'keywords', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Create a new icon record.
     */
    public function saveIcon(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $userId = auth()->id();

                // Auto sort_order — one higher than the current maximum.
                $data['sort_order'] = ((int) Icon::max('sort_order')) + 1;
                $data['is_active']  = $data['is_active'] ?? true;
                $data['created_by'] = $userId;
                $data['updated_by'] = $userId;

                $icon = Icon::create($data);

                return [
                    'status'  => 'success',
                    'message' => 'Icon created successfully.',
                    'icon'    => $icon->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error saving icon: ' . $e->getMessage(),
                'icon'    => null,
            ];
        }
    }

    /**
     * Update an existing icon record.
     */
    public function updateIcon(array $data, int $id): array
    {
        try {
            return DB::transaction(function () use ($data, $id) {
                $icon = Icon::findOrFail($id);

                unset($data['sort_order']); // Order is managed by drag & drop
                $data['updated_by'] = auth()->id();

                $icon->update($data);

                return [
                    'status'  => 'success',
                    'message' => 'Icon updated successfully.',
                    'icon'    => $icon->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error updating icon: ' . $e->getMessage(),
                'icon'    => null,
            ];
        }
    }

    /**
     * Get a single icon by ID (for the edit drawer).
     */
    public function getIconById(int $id): array
    {
        try {
            return [
                'status' => 'success',
                'icon'   => Icon::findOrFail($id),
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Icon not found.',
                'icon'    => null,
            ];
        }
    }

    /**
     * Soft delete an icon record.
     */
    public function deleteIcon(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $icon = Icon::findOrFail($id);

                $icon->delete();

                return [
                    'status'  => 'success',
                    'message' => 'Icon deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error deleting icon: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Persist the new sort order after drag & drop —
     * each array position becomes the sort_order (1, 2, 3...).
     */
    public function reorderIcons(array $orderedIds): array
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
                    Icon::where('id', (int) $id)->update(['sort_order' => $index + 1]);
                }

                return [
                    'status'  => 'success',
                    'message' => 'Icon order updated successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error updating icon order: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get all active icons as a lightweight list for the icon picker.
     */
    public function getIconOptions(): array
    {
        try {
            $icons = Icon::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'name', 'class', 'keywords']);

            return [
                'status' => 'success',
                'icons'  => $icons,
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error loading icons: ' . $e->getMessage(),
                'icons'   => [],
            ];
        }
    }
}
