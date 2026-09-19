<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Models\CustomPackageRequest;
use Yajra\DataTables\DataTables;

class CustomPackageRequestService
{
    /**
     * Get request data for the DataTable AJAX request.
     */
    public function getCustomPackageRequestDataTable(Request $request)
    {
        $query = CustomPackageRequest::select(
            'custom_package_requests.id',
            'airlines.name as airline_name',
            'custom_package_requests.name',
            'custom_package_requests.phone',
            'custom_package_requests.email',
            'custom_package_requests.travel_date',
            'custom_package_requests.adults',
            'custom_package_requests.children',
            'custom_package_requests.status',
            'custom_package_requests.quoted_price',
            'custom_package_requests.created_at'
        )
            ->leftJoin('airlines', 'airlines.id', '=', 'custom_package_requests.airline_id')
            ->orderByDesc('custom_package_requests.created_at')
            ->orderByDesc('custom_package_requests.id');

        // Filter by status (value sent by the status filter dropdown).
        if ($request->status !== null && $request->status !== '') {
            $query->where('custom_package_requests.status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            // Global search on "airline_name" must hit airlines.name, not a
            // column on the base table (aliased join column).
            ->filterColumn('airline_name', function ($query, $keyword) {
                $query->where('airlines.name', 'like', "%{$keyword}%");
            })
            // Column header sorting on "airline_name".
            ->orderColumn('airline_name', 'airlines.name $1')
            ->editColumn('name', function (CustomPackageRequest $request) {
                return '<div>'
                    .'<span class="font-medium text-gray-800">'.e($request->name).'</span>'
                    .'<span class="block text-xs text-gray-500">'
                    .'<a href="tel:'.e($request->phone).'" class="hover:underline text-[#047354]">'.e($request->phone).'</a>'
                    .($request->email ? ' &middot; '.e($request->email) : '')
                    .'</span>'
                    .'</div>';
            })
            ->editColumn('airline_name', function (CustomPackageRequest $request) {
                return $request->airline_name ?: '&mdash;';
            })
            ->editColumn('travel_date', function (CustomPackageRequest $request) {
                if (! $request->travel_date) {
                    return '&mdash;';
                }

                return '<span class="text-gray-700">'.$request->travel_date->format('d M Y').'</span>'
                    .'<span class="block text-xs text-gray-500">'.$request->adults.' adult'
                    .($request->adults > 1 ? 's' : '')
                    .', '.$request->children.' children</span>';
            })
            ->editColumn('quoted_price', function (CustomPackageRequest $request) {
                return $request->quoted_price !== null
                    ? '<span class="font-medium text-gray-800">'.number_format((float) $request->quoted_price, 2).'</span>'
                    : '&mdash;';
            })
            ->editColumn('status', function (CustomPackageRequest $request) {
                return $this->statusBadge($request->status);
            })
            ->editColumn('created_at', function (CustomPackageRequest $request) {
                return $request->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (CustomPackageRequest $request) {
                return view('components.action-buttons', [
                    'id' => $request->id,
                    'edit' => 'customPackageRequestEdit',
                    'delete' => 'customPackageRequestDelete',
                ])->render();
            })
            ->rawColumns(['name', 'airline_name', 'travel_date', 'quoted_price', 'status', 'created_at', 'action'])
            ->make(true);
    }

    /**
     * Store a new request — used by the public custom package form and by the
     * admin panel when a request is logged manually.
     */
    public function saveCustomPackageRequest(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $data['status'] = $data['status'] ?? CustomPackageRequest::STATUS_PENDING;
                $data['created_by'] = auth()->id();
                $data['updated_by'] = auth()->id();

                $customPackageRequest = CustomPackageRequest::create($data);

                return [
                    'status' => 'success',
                    'message' => 'Custom package request submitted successfully.',
                    'custom_package_request' => $customPackageRequest,
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving request: '.$e->getMessage(),
                'custom_package_request' => null,
            ];
        }
    }

    /**
     * Update an existing request (status, quote, admin note etc).
     */
    public function updateCustomPackageRequest(array $data, int $id): array
    {
        try {
            return DB::transaction(function () use ($data, $id) {
                $customPackageRequest = CustomPackageRequest::findOrFail($id);

                $data['updated_by'] = auth()->id();

                $customPackageRequest->update($data);

                return [
                    'status' => 'success',
                    'message' => 'Request updated successfully.',
                    'custom_package_request' => $customPackageRequest->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating request: '.$e->getMessage(),
                'custom_package_request' => null,
            ];
        }
    }

    /**
     * Get a single request by ID (for the edit drawer).
     */
    public function getCustomPackageRequestById(int $id): array
    {
        try {
            $customPackageRequest = CustomPackageRequest::with('airline:id,name,code')->findOrFail($id);

            return [
                'status' => 'success',
                'custom_package_request' => $customPackageRequest,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Request not found.',
                'custom_package_request' => null,
            ];
        }
    }

    /**
     * Delete a request record (soft delete).
     */
    public function deleteCustomPackageRequest(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $customPackageRequest = CustomPackageRequest::findOrFail($id);

                $customPackageRequest->delete();

                return [
                    'status' => 'success',
                    'message' => 'Request deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting request: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Colored badge for a request status.
     */
    private function statusBadge(?string $status): string
    {
        $classes = [
            CustomPackageRequest::STATUS_PENDING    => 'bg-blue-50 text-blue-700',
            CustomPackageRequest::STATUS_CONTACTED  => 'bg-amber-50 text-amber-700',
            CustomPackageRequest::STATUS_PROCESSING => 'bg-indigo-50 text-indigo-700',
            CustomPackageRequest::STATUS_QUOTED     => 'bg-purple-50 text-purple-700',
            CustomPackageRequest::STATUS_CONFIRMED  => 'bg-emerald-50 text-emerald-700',
            CustomPackageRequest::STATUS_CANCELLED  => 'bg-red-50 text-red-700',
        ];

        $label = CustomPackageRequest::statuses()[$status] ?? 'Unknown';

        return '<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium '
            .($classes[$status] ?? 'bg-gray-100 text-gray-700').'">'.e($label).'</span>';
    }
}
