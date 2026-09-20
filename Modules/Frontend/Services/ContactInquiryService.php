<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Frontend\Models\ContactInquiry;
use Yajra\DataTables\DataTables;

class ContactInquiryService
{
    /**
     * Get inquiry data for the DataTable AJAX request.
     */
    public function getContactInquiryDataTable(Request $request)
    {
        $query = ContactInquiry::select(
            'contact_inquiries.id',
            'contact_inquiries.name',
            'contact_inquiries.phone',
            'contact_inquiries.email',
            'contact_inquiries.message',
            'contact_inquiries.status',
            'contact_inquiries.created_at'
        )
            ->orderByDesc('contact_inquiries.created_at')
            ->orderByDesc('contact_inquiries.id');

        // Filter by follow up status (value sent by the status filter dropdown).
        if ($request->status !== null && $request->status !== '') {
            $query->where('contact_inquiries.status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', function (ContactInquiry $inquiry) {
                return '<span class="font-medium text-gray-800" title="'.e($inquiry->name).'">'
                    .e(Str::limit($inquiry->name, 40))
                    .'</span>';
            })
            ->editColumn('phone', function (ContactInquiry $inquiry) {
                return '<a href="tel:'.e($inquiry->phone).'" class="text-[#047354] hover:underline">'
                    .e($inquiry->phone)
                    .'</a>';
            })
            ->editColumn('email', function (ContactInquiry $inquiry) {
                if (! $inquiry->email) {
                    return '&mdash;';
                }

                return '<a href="mailto:'.e($inquiry->email).'" class="text-gray-700 hover:underline">'
                    .e($inquiry->email)
                    .'</a>';
            })
            ->editColumn('message', function (ContactInquiry $inquiry) {
                if (! $inquiry->message) {
                    return '&mdash;';
                }

                return '<span class="text-gray-600" title="'.e($inquiry->message).'">'
                    .e(Str::limit(Str::squish($inquiry->message), 60))
                    .'</span>';
            })
            ->editColumn('status', function (ContactInquiry $inquiry) {
                return $this->statusBadge($inquiry->status);
            })
            ->editColumn('created_at', function (ContactInquiry $inquiry) {
                return $inquiry->created_at
                    ? $inquiry->created_at->format('d M Y H:i')
                    : '&mdash;';
            })
            ->addColumn('action', function (ContactInquiry $inquiry) {
                return view('components.action-buttons', [
                    'id' => $inquiry->id,
                    'feature' => 'inquiryView',
                    'featureTitle' => 'View Full Message',
                    'edit' => 'inquiryEdit',
                    'delete' => 'inquiryDelete',
                ])->render();
            })
            ->rawColumns(['name', 'phone', 'email', 'message', 'status', 'created_at', 'action'])
            ->make(true);
    }

    /**
     * Store a new inquiry — used by the public "contact us" form and by the
     * admin panel when an inquiry is logged manually.
     *
     * New inquiries always start as "new" unless another status is given.
     */
    public function saveInquiry(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $data['status'] = $data['status'] ?? ContactInquiry::STATUS_NEW;

                $inquiry = ContactInquiry::create($data);

                return [
                    'status' => 'success',
                    'message' => 'Thank you! Your message has been received. We will contact you soon.',
                    'inquiry' => $inquiry->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving inquiry: '.$e->getMessage(),
                'inquiry' => null,
            ];
        }
    }

    /**
     * Update an existing inquiry (the follow up status is changed here).
     */
    public function updateInquiry(array $data, int $id): array
    {
        try {
            return DB::transaction(function () use ($data, $id) {
                $inquiry = ContactInquiry::findOrFail($id);

                $data['updated_by'] = $data['updated_by'] ?? auth()->id();

                $inquiry->update($data);

                return [
                    'status' => 'success',
                    'message' => 'Inquiry updated successfully.',
                    'inquiry' => $inquiry->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating inquiry: '.$e->getMessage(),
                'inquiry' => null,
            ];
        }
    }

    /**
     * Get a single inquiry by ID (for the view drawer).
     */
    public function getInquiryById(int $id): array
    {
        try {
            $inquiry = ContactInquiry::with('updatedBy:id,name')->findOrFail($id);

            return [
                'status' => 'success',
                'inquiry' => $inquiry,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Inquiry not found.',
                'inquiry' => null,
            ];
        }
    }

    /**
     * Delete an inquiry record.
     */
    public function deleteInquiry(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $inquiry = ContactInquiry::findOrFail($id);

                $inquiry->delete();

                return [
                    'status' => 'success',
                    'message' => 'Inquiry deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting inquiry: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Counters per follow up status (for the page header cards).
     */
    public function getStatusCounts(): array
    {
        $counts = ContactInquiry::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $statuses = [];

        foreach (ContactInquiry::statuses() as $value => $label) {
            $statuses[$value] = [
                'label' => $label,
                'total' => (int) ($counts[$value] ?? 0),
            ];
        }

        return [
            'status' => 'success',
            'total' => array_sum($counts),
            'statuses' => $statuses,
        ];
    }

    /**
     * Colored badge for a follow up status.
     */
    private function statusBadge(?string $status): string
    {
        $classes = [
            ContactInquiry::STATUS_NEW => 'bg-blue-50 text-blue-700',
            ContactInquiry::STATUS_CONTACTED => 'bg-amber-50 text-amber-700',
            ContactInquiry::STATUS_RESOLVED => 'bg-emerald-50 text-emerald-700',
        ];

        $label = ContactInquiry::statuses()[$status] ?? 'Unknown';

        return '<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium '
            .($classes[$status] ?? 'bg-gray-100 text-gray-700').'">'.e($label).'</span>';
    }
}
