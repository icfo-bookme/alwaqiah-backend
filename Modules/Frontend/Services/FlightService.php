<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Models\Flight;
use Yajra\DataTables\DataTables;

class FlightService
{
    /**
     * Get flight data for the DataTable AJAX request.
     */
    public function getFlightDataTable(Request $request)
    {
        $query = Flight::select(
            'flights.id',
            'flights.airline_id',
            'airlines.name as airline_name',
            'airlines.code as airline_code',
            'airlines.logo as airline_logo',
            'flights.flight_number',
            'flights.departure_airport',
            'flights.arrival_airport',
            'flights.departure_at',
            'flights.return_departure_airport',
            'flights.return_arrival_airport',
            'flights.return_at',
            'flights.is_active',
            'flights.sort_order',
            'flights.created_at'
        )
            ->join('airlines', 'airlines.id', '=', 'flights.airline_id')
            ->orderBy('flights.sort_order')
            ->orderBy('flights.id');

        if ($request->is_active !== null && $request->is_active !== '') {
            $query->where('flights.is_active', $request->is_active);
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
            ->editColumn('airline_name', function (Flight $flight) {
                // Aliases selected from the joined airlines table.
                $logo = $flight->airline_logo
                    ? '<img src="'.asset('storage/'.$flight->airline_logo).'" alt="'.e($flight->airline_name).'" class="h-8 w-8 rounded-full object-contain ring-1 ring-gray-200 bg-white">'
                    : '<span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 ring-1 ring-gray-200"><i class="fa-solid fa-plane text-sm text-gray-500"></i></span>';

                return '<div class="flex items-center gap-2">'
                    .$logo
                    .'<div>'
                    .'<span class="font-medium text-gray-800">'.e($flight->airline_name).'</span>'
                    .($flight->flight_number
                        ? '<span class="block text-xs text-gray-500">'.e($flight->flight_number).'</span>'
                        : '')
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('departure_at', function (Flight $flight) {
                if (! $flight->departure_airport && ! $flight->arrival_airport && ! $flight->departure_at) {
                    return '&mdash;';
                }

                return '<span class="text-gray-700">'.e($flight->departure_airport ?? '&mdash;')
                    .' <i class="fa-solid fa-arrow-right-long text-xs text-gray-400"></i> '
                    .e($flight->arrival_airport ?? '&mdash;').'</span>'
                    .'<span class="block text-xs text-gray-500">'
                    .($flight->departure_at ? $flight->departure_at->format('d M Y, H:i') : '&mdash;')
                    .'</span>';
            })
            ->editColumn('return_at', function (Flight $flight) {
                if (! $flight->return_departure_airport && ! $flight->return_arrival_airport && ! $flight->return_at) {
                    return '&mdash;';
                }

                return '<span class="text-gray-700">'.e($flight->return_departure_airport ?? '&mdash;')
                    .' <i class="fa-solid fa-arrow-right-long text-xs text-gray-400"></i> '
                    .e($flight->return_arrival_airport ?? '&mdash;').'</span>'
                    .'<span class="block text-xs text-gray-500">'
                    .($flight->return_at ? $flight->return_at->format('d M Y, H:i') : '&mdash;')
                    .'</span>';
            })
            ->editColumn('is_active', function (Flight $flight) {
                return statusBadge($flight->is_active);
            })
            ->editColumn('created_at', function (Flight $flight) {
                return $flight->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Flight $flight) {
                return view('components.action-buttons', [
                    'id' => $flight->id,
                    'edit' => 'flightEdit',
                    'delete' => 'flightDelete',
                ])->render();
            })
            ->rawColumns(['airline_name', 'departure_at', 'return_at', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Create a new flight record — sort_order is auto-assigned
     * (one higher than the current maximum).
     */
    public function saveFlight(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $userId = auth()->id();

                // Auto sort_order — one higher than the current maximum.
                $data['sort_order'] = ((int) Flight::max('sort_order')) + 1;
                $data['is_active'] = $data['is_active'] ?? true;
                $data['created_by'] = $userId;
                $data['updated_by'] = $userId;

                $flight = Flight::create($data);

                return [
                    'status' => 'success',
                    'message' => 'Flight created successfully.',
                    'flight' => $flight->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving flight: '.$e->getMessage(),
                'flight' => null,
            ];
        }
    }

    /**
     * Update an existing flight record.
     */
    public function updateFlight(array $data, int $id): array
    {
        try {
            return DB::transaction(function () use ($data, $id) {
                $flight = Flight::findOrFail($id);

                $data['updated_by'] = auth()->id();

                $flight->update($data);

                return [
                    'status' => 'success',
                    'message' => 'Flight updated successfully.',
                    'flight' => $flight->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating flight: '.$e->getMessage(),
                'flight' => null,
            ];
        }
    }

    /**
     * Get a single flight by ID (for the edit drawer).
     */
    public function getFlightById(int $id): array
    {
        try {
            $flight = Flight::findOrFail($id);

            return [
                'status' => 'success',
                'flight' => $flight,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Flight not found.',
                'flight' => null,
            ];
        }
    }

    /**
     * Soft delete a flight record. The stored logo file is kept so the
     * record can still be restored from trash.
     */
    public function deleteFlight(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $flight = Flight::findOrFail($id);

                $flight->delete();

                return [
                    'status' => 'success',
                    'message' => 'Flight deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting flight: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Persist the new sort order after drag & drop —
     * each array position becomes the sort_order, offset by the current page.
     */
    public function reorderFlights(array $orderedIds, int $start = 0): array
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
                    Flight::where('id', (int) $id)->update(['sort_order' => $start + $index + 1]);
                }

                return [
                    'status' => 'success',
                    'message' => 'Flight order updated successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating flight order: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Frontend API: all active flights, ordered for display.
     */
    public function getFrontendFlights(): array
    {
        $flights = Flight::with('airline')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return [
            'status' => 'success',
            'count' => $flights->count(),
            'flights' => $flights->map(fn (Flight $flight) => $this->formatFrontendFlight($flight))->values()->all(),
        ];
    }

    /**
     * Shape a flight for frontend consumption (airline data + datetimes resolved).
     */
    private function formatFrontendFlight(Flight $flight): array
    {
        return [
            'id' => $flight->id,
            'airline_id' => $flight->airline_id,
            'airline_name' => $flight->airline?->name,
            'airline_code' => $flight->airline?->code,
            'airline_logo' => $flight->airline?->logo,
            'airline_logo_url' => $flight->airline?->logo_url,
            'flight_number' => $flight->flight_number,
            'departure_airport' => $flight->departure_airport,
            'arrival_airport' => $flight->arrival_airport,
            'departure_at' => $flight->departure_at?->format('Y-m-d H:i:s'),
            'return_departure_airport' => $flight->return_departure_airport,
            'return_arrival_airport' => $flight->return_arrival_airport,
            'return_at' => $flight->return_at?->format('Y-m-d H:i:s'),
            'sort_order' => $flight->sort_order,
        ];
    }
}
