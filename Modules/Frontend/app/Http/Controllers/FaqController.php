<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreFaqRequest;
use Modules\Frontend\Http\Requests\UpdateFaqRequest;
use Modules\Frontend\Services\FaqService;

class FaqController extends Controller
{
    protected $faqService;

    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }

    /**
     * Display FAQs listing page
     */
    public function index(Request $request)
    {
        return view('frontend::faqs.index');
    }

    /**
     * Get FAQ data for DataTable AJAX
     */
    public function dataTable(Request $request)
    {
        return $this->faqService->getFaqDataTable($request);
    }

    /**
     * Store new FAQ
     */
    public function store(StoreFaqRequest $request)
    {
        $result = $this->faqService->saveFaq($request->validated());

        return response()->json($result);
    }

    /**
     * Get single FAQ by ID
     */
    public function show($id)
    {
        $result = $this->faqService->getFaqById((int) $id);

        return response()->json($result);
    }

    /**
     * Update existing FAQ
     */
    public function update(UpdateFaqRequest $request, $id)
    {
        $result = $this->faqService->updateFaq($request->validated(), (int) $id);

        return response()->json($result);
    }

    /**
     * Reorder FAQs after drag & drop
     */
    public function reorder(Request $request)
    {
        return response()->json($this->faqService->reorderFaqs(
            $request->input('order', []),
            (int) $request->input('start', 0)
        ));
    }

    /**
     * Delete FAQ (soft delete)
     */
    public function destroy($id)
    {
        $result = $this->faqService->deleteFaq((int) $id);

        return response()->json($result);
    }
}
