<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Frontend\Models\Faq;
use Yajra\DataTables\DataTables;

class FaqService
{
    /**
     * Get FAQ data for the DataTable AJAX request.
     */
    public function getFaqDataTable(Request $request)
    {
        $query = Faq::select(
            'faqs.id',
            'faqs.question',
            'faqs.answer',
            'faqs.sort_order',
            'faqs.is_active',
            'faqs.created_at'
        )
            ->orderBy('faqs.sort_order')
            ->orderBy('faqs.id');

        if ($request->is_active !== null && $request->is_active !== '') {
            $query->where('faqs.is_active', $request->is_active);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('question', function (Faq $faq) {
                return '<span class="font-medium text-gray-800" title="'.e($faq->question).'">'
                    .e(Str::limit($faq->question, 60))
                    .'</span>';
            })
            ->editColumn('answer', function (Faq $faq) {
                return '<span class="text-gray-600" title="'.e($faq->answer).'">'
                    .e(Str::limit(strip_tags($faq->answer), 80))
                    .'</span>';
            })
            ->editColumn('is_active', function (Faq $faq) {
                return statusBadge($faq->is_active);
            })
            ->editColumn('created_at', function (Faq $faq) {
                return $faq->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Faq $faq) {
                return view('components.action-buttons', [
                    'id' => $faq->id,
                    'edit' => 'faqEdit',
                    'delete' => 'faqDelete',
                ])->render();
            })
            ->rawColumns(['question', 'answer', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Create a new FAQ — sort_order is auto-assigned (one higher than
     * the current maximum).
     */
    public function saveFaq(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $data['sort_order'] = ((int) Faq::max('sort_order')) + 1;

                $data['created_by'] = $data['created_by'] ?? auth()->id();
                $data['updated_by'] = $data['updated_by'] ?? auth()->id();

                $faq = Faq::create($data);

                return [
                    'status' => 'success',
                    'message' => 'FAQ created successfully.',
                    'faq' => $faq->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving FAQ: '.$e->getMessage(),
                'faq' => null,
            ];
        }
    }

    /**
     * Update an existing FAQ.
     */
    public function updateFaq(array $data, int $id): array
    {
        try {
            return DB::transaction(function () use ($data, $id) {
                $faq = Faq::findOrFail($id);

                $data['updated_by'] = $data['updated_by'] ?? auth()->id();

                $faq->update($data);

                return [
                    'status' => 'success',
                    'message' => 'FAQ updated successfully.',
                    'faq' => $faq->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating FAQ: '.$e->getMessage(),
                'faq' => null,
            ];
        }
    }

    /**
     * Get a single FAQ by ID (for the edit drawer).
     */
    public function getFaqById(int $id): array
    {
        try {
            $faq = Faq::findOrFail($id);

            return [
                'status' => 'success',
                'faq' => $faq,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'FAQ not found.',
                'faq' => null,
            ];
        }
    }

    /**
     * Soft delete a FAQ record.
     */
    public function deleteFaq(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $faq = Faq::findOrFail($id);

                $faq->delete();

                return [
                    'status' => 'success',
                    'message' => 'FAQ deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting FAQ: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Persist the new sort order after drag & drop —
     * each array position becomes the sort_order, offset by the current page.
     */
    public function reorderFaqs(array $orderedIds, int $start = 0): array
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
                    Faq::where('id', (int) $id)->update(['sort_order' => $start + $index + 1]);
                }

                return [
                    'status' => 'success',
                    'message' => 'FAQ order updated successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating FAQ order: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Frontend API: all active FAQs, ordered for display.
     */
    public function getFrontendFaqs(): array
    {
        $faqs = Faq::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'question', 'answer', 'sort_order']);

        return [
            'status' => 'success',
            'count' => $faqs->count(),
            'faqs' => $faqs->map(fn (Faq $faq) => $this->formatFrontendFaq($faq))->values()->all(),
        ];
    }

    /**
     * Shape a FAQ for frontend consumption.
     */
    private function formatFrontendFaq(Faq $faq): array
    {
        return [
            'id' => $faq->id,
            'question' => $faq->question,
            'answer' => $faq->answer,
            'sort_order' => $faq->sort_order,
        ];
    }
}
