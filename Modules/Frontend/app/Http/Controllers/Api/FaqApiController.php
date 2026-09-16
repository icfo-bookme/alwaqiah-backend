<?php

namespace Modules\Frontend\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Frontend\Services\FaqService;

class FaqApiController extends Controller
{
    protected $faqService;

    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }

    /**
     * GET /api/faqs
     * All active FAQs (for frontend display), ordered by sort_order.
     */
    public function index()
    {
        return response()->json($this->faqService->getFrontendFaqs());
    }
}
