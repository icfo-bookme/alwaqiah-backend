<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreContactInquiryRequest;
use Modules\Frontend\Http\Requests\UpdateContactInquiryRequest;
use Modules\Frontend\Services\ContactInquiryService;

class ContactInquiryController extends Controller
{
    protected $contactInquiryService;

    public function __construct(ContactInquiryService $contactInquiryService)
    {
        $this->contactInquiryService = $contactInquiryService;
    }

    /**
     * Display the contact inquiries listing page.
     */
    public function index(Request $request)
    {
        return view('frontend::contact-inquiries.index');
    }

    /**
     * Get inquiry data for DataTable AJAX.
     */
    public function dataTable(Request $request)
    {
        return $this->contactInquiryService->getContactInquiryDataTable($request);
    }

    /**
     * Store a new inquiry (admin side entry).
     */
    public function store(StoreContactInquiryRequest $request)
    {
        return response()->json($this->contactInquiryService->saveInquiry($request->validated()));
    }

    /**
     * Get single inquiry by ID.
     */
    public function show($id)
    {
        $result = $this->contactInquiryService->getInquiryById((int) $id);

        return response()->json($result);
    }

    /**
     * Update existing inquiry (follow up status).
     */
    public function update(UpdateContactInquiryRequest $request, $id)
    {
        $result = $this->contactInquiryService->updateInquiry($request->validated(), (int) $id);

        return response()->json($result);
    }

    /**
     * Delete inquiry.
     */
    public function destroy($id)
    {
        $result = $this->contactInquiryService->deleteInquiry((int) $id);

        return response()->json($result);
    }
}
