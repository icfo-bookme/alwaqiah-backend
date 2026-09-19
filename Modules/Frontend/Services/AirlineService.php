<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Frontend\Models\Airline;
use Yajra\DataTables\DataTables;

class AirlineService
{
    /**
     * Get airline data for the DataTable AJAX request.
     */
    public function getAirlineDataTable(Request $request)
    {
        $query = Airline::select(
            'airlines.id',
            'airlines.name',
            'airlines.code',
            'airlines.logo',
            'airlines.is_active',
            'airlines.sort_order',
            'airlines.created_at'
        )
            ->orderBy('airlines.sort_order')
            ->orderBy('airlines.id');

        if ($request->is_active !== null && $request->is_active !== '') {
            $query->where('airlines.is_active', $request->is_active);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', function (Airline $airline) {
                $logo = $airline->logo
                    ? '<img src="'.$airline->logo_url.'" alt="'.e($airline->name).'" class="h-8 w-8 rounded-full object-contain ring-1 ring-gray-200 bg-white">'
                    : '<span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 ring-1 ring-gray-200"><i class="fa-solid fa-plane text-sm text-gray-500"></i></span>';

                return '<div class="flex items-center gap-2">'
                    .$logo
                    .'<div>'
                    .'<span class="font-medium text-gray-800">'.e($airline->name).'</span>'
                    .($airline->code
                        ? '<span class="block text-xs text-gray-500">'.e($airline->code).'</span>'
                        : '')
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('is_active', function (Airline $airline) {
                return statusBadge($airline->is_active);
            })
            ->editColumn('created_at', function (Airline $airline) {
                return $airline->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Airline $airline) {
                return view('components.action-buttons', [
                    'id' => $airline->id,
                    'edit' => 'airlineEdit',
                    'delete' => 'airlineDelete',
                ])->render();
            })
            ->rawColumns(['name', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Create a new airline record — the logo (if any) is stored
     * and sort_order is auto-assigned (one higher than the current maximum).
     */
    public function saveAirline(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $userId = auth()->id();

                // Store the uploaded logo and keep only its path.
                if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
                    $data['logo'] = $this->storeLogo($data['logo']);
                }

                // Auto sort_order — one higher than the current maximum.
                $data['sort_order'] = ((int) Airline::max('sort_order')) + 1;
                $data['is_active'] = $data['is_active'] ?? true;
                $data['created_by'] = $userId;
                $data['updated_by'] = $userId;

                $airline = Airline::create($data);

                return [
                    'status' => 'success',
                    'message' => 'Airline created successfully.',
                    'airline' => $airline->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving airline: '.$e->getMessage(),
                'airline' => null,
            ];
        }
    }

    /**
     * Update an existing airline record — the stored logo is replaced
     * only when a new file is uploaded.
     */
    public function updateAirline(array $data, int $id): array
    {
        try {
            return DB::transaction(function () use ($data, $id) {
                $airline = Airline::findOrFail($id);

                // Replace the logo only when a new file is uploaded.
                if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
                    $this->deleteLogoFile($airline->logo);
                    $data['logo'] = $this->storeLogo($data['logo']);
                } else {
                    unset($data['logo']);
                }

                $data['updated_by'] = auth()->id();

                $airline->update($data);

                return [
                    'status' => 'success',
                    'message' => 'Airline updated successfully.',
                    'airline' => $airline->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating airline: '.$e->getMessage(),
                'airline' => null,
            ];
        }
    }

    /**
     * Get a single airline by ID (for the edit drawer).
     */
    public function getAirlineById(int $id): array
    {
        try {
            $airline = Airline::findOrFail($id);

            return [
                'status' => 'success',
                'airline' => $airline,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Airline not found.',
                'airline' => null,
            ];
        }
    }

    /**
     * Soft delete an airline record. The stored logo file is kept so the
     * record can still be restored from trash.
     */
    public function deleteAirline(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $airline = Airline::findOrFail($id);

                $airline->delete();

                return [
                    'status' => 'success',
                    'message' => 'Airline deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting airline: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Persist the new sort order after drag & drop —
     * each array position becomes the sort_order, offset by the current page.
     */
    public function reorderAirlines(array $orderedIds, int $start = 0): array
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
                    Airline::where('id', (int) $id)->update(['sort_order' => $start + $index + 1]);
                }

                return [
                    'status' => 'success',
                    'message' => 'Airline order updated successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating airline order: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Lightweight active airline list for dropdowns.
     */
    public function getAirlineOptions(): array
    {
        try {
            $airlines = Airline::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'name', 'code', 'logo']);

            return [
                'status' => 'success',
                'airlines' => $airlines,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error loading airlines: '.$e->getMessage(),
                'airlines' => [],
            ];
        }
    }

    /**
     * Store an uploaded airline logo on the public disk and return the path.
     */
    private function storeLogo(UploadedFile $file): string
    {
        return $file->store('airlines', 'public');
    }

    /**
     * Delete a stored airline logo file from the public disk if it exists.
     */
    private function deleteLogoFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
