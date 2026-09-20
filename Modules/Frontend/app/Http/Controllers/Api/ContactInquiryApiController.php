<?php

namespace Modules\Frontend\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Frontend\Http\Requests\StoreContactInquiryRequest;
use Modules\Frontend\Services\ContactInquiryService;

class ContactInquiryApiController extends Controller
{
    protected $contactInquiryService;

    public function __construct(ContactInquiryService $contactInquiryService)
    {
        $this->contactInquiryService = $contactInquiryService;
    }

    /**
     * POST /api/contact-inquiries
     * Public contact form submission — no auth middleware.
     */
    public function store(StoreContactInquiryRequest $request)
    {
        $result = $this->contactInquiryService->saveInquiry($request->validated());

        return response()->json($result, $result['status'] === 'success' ? 201 : 500);
    }
}
