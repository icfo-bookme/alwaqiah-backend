<x-app-layout>

    <div class="p-4">
        <div
            class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            {{-- Follow Up Status Filter --}}
            <div class="flex flex-col w-full md:w-1/3">
                <x-form-select label="Follow Up Status" id="filter_status" class="dt-filter-inquiryTable">
                    <option value="">All Status</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="resolved">Resolved</option>
                </x-form-select>
            </div>

            {{-- Optional: Reset Button --}}
            <div class="w-full md:w-auto flex items-end">
                <button id="resetFilters"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800
                   rounded-lg transition active:scale-95">
                    Reset
                </button>
            </div>

        </div>
        {{-- REUSABLE DATA-TABLE COMPONENT --}}
        <x-data-table id="inquiryTable" title="Contact Inquiries" icon="fa-solid fa-envelope-open-text"
            buttonId="btnAddInquiry" buttonText="Log Inquiry"
            :columns="['Name', 'Phone', 'Email', 'Message', 'Status', 'Received At', 'Action']"
            :ajaxUrl="route('contact-inquiries.dataTable')" :dtColumns="[
                ['data' => 'name'],
                ['data' => 'phone'],
                ['data' => 'email'],
                ['data' => 'message'],
                ['data' => 'status'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]" :filters="[
                'status' => '#filter_status',
            ]" :exportButtons="true" />
    </div>

    {{-- DRAWER COMPONENT --}}
    <x-drawer id="inquiry-drawer" overlayId="inquiry-overlay" title="Log New Inquiry" maxWidth="max-w-2xl"
        submitOnClick="saveInquiry()">
        <form id="inquiryForm">
            <input type="hidden" name="id" id="inquiry_id">

            {{-- Name & Phone --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 150ms;">
                <x-form-input label="Name" name="name" id="inquiry_name" placeholder="Visitor name"
                    :required="true" />
                <x-form-input label="Phone" name="phone" id="inquiry_phone"
                    placeholder="e.g. +880 1712-345678" :required="true" />
            </div>

            {{-- Email --}}
            <div class="mb-4 animate-fade" style="animation-delay: 200ms;">
                <x-form-input label="Email" name="email" id="inquiry_email" type="email"
                    placeholder="visitor@example.com" />
            </div>

            {{-- Message --}}
            <div class="mb-4 animate-fade" style="animation-delay: 250ms;">
                <x-form-textarea label="Message" name="message" id="inquiry_message" rows="5"
                    placeholder="What did the visitor ask about?" />
            </div>

            {{-- Follow Up Status — only meaningful when updating an existing inquiry.
                 New inquiries always start as "new" (handled by the server). --}}
            <div id="inquiryStatusWrapper" class="mb-4 animate-fade" style="animation-delay: 300ms;">
                <x-form-select label="Follow Up Status" name="status" id="inquiry_status" :placeholder="false">
                    <option value="new" selected>New</option>
                    <option value="contacted">Contacted</option>
                    <option value="resolved">Resolved</option>
                </x-form-select>
            </div>

            {{-- Read only meta, shown in edit mode --}}
            <div id="inquiryMeta"
                class="hidden text-xs text-slate-500 border-t border-slate-200 pt-3 mt-1 animate-fade"
                style="animation-delay: 350ms;">
                <span id="inquiryMetaText"></span>
            </div>
        </form>
    </x-drawer>

    {{-- Page specific CSS (kept out of this file): public/css/contact-inquiries.css --}}
    @push('head')
        <link rel="stylesheet"
            href="{{ asset('css/contact-inquiries.css') }}?v={{ filemtime(public_path('css/contact-inquiries.css')) }}">
    @endpush

    @push('scripts')
        <script>
            let isSaving = false;

            // Escape values before injecting them into popup HTML.
            function inquiryEsc(value) {
                return $('<div>').text(value ?? '').html();
            }

            // 2026-09-17T10:46:18.000000Z -> 17 Sep 2026 10:46
            function inquiryDate(value) {
                if (!value) return '—';

                const date = new Date(value);
                if (isNaN(date.getTime())) return value;

                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const pad = (n) => String(n).padStart(2, '0');

                return pad(date.getDate()) + ' ' + months[date.getMonth()] + ' ' + date.getFullYear()
                    + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
            }

            function openInquiryDrawer(mode, inquiry) {
                $('#inquiryForm')[0].reset();
                $('#inquiry_id').val('');

                if (mode === 'edit' && inquiry) {
                    $('#inquiry_id').val(inquiry.id);
                    $('#inquiry_name').val(inquiry.name ?? '');
                    $('#inquiry_phone').val(inquiry.phone ?? '');
                    $('#inquiry_email').val(inquiry.email ?? '');
                    $('#inquiry_message').val(inquiry.message ?? '');
                    $('#inquiry_status').val(inquiry.status ?? 'new');

                    const updatedBy = inquiry.updatedBy?.name ? inquiry.updatedBy.name : '—';
                    $('#inquiryMetaText').html(
                        'Received: <strong>' + inquiryEsc(inquiryDate(inquiry.created_at)) + '</strong>' +
                        ' &nbsp;&bull;&nbsp; Last updated by: <strong>' + inquiryEsc(updatedBy) + '</strong>'
                    );

                    $('#inquiryStatusWrapper').removeClass('hidden');
                    $('#inquiryMeta').removeClass('hidden');

                    $('#drawerTitle').text('Update Inquiry');
                    $('#drawerButtonText').text('Update Inquiry');
                } else {
                    // A brand new inquiry always starts as "new" (server side default).
                    $('#inquiryStatusWrapper').addClass('hidden');
                    $('#inquiryMeta').addClass('hidden');

                    $('#drawerTitle').text('Log New Inquiry');
                    $('#drawerButtonText').text('Save Inquiry');
                }

                openGlobalDrawer('inquiry-drawer', 'inquiry-overlay');
            }

            function inquiryEdit(id) {
                let getUrl = "{{ route('contact-inquiries.show', ':id') }}".replace(':id', id);

                $.get(getUrl, function(res) {
                    if (res.status === 'success') {
                        openInquiryDrawer('edit', res.inquiry);
                    } else {
                        Swal.fire('Error', res.message || 'Inquiry not found.', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Failed to communicate with server.', 'error');
                });
            }

            function inquiryView(id) {
                let getUrl = "{{ route('contact-inquiries.show', ':id') }}".replace(':id', id);

                $.get(getUrl, function(res) {
                    if (res.status !== 'success') {
                        Swal.fire('Error', res.message || 'Inquiry not found.', 'error');
                        return;
                    }

                    const q = res.inquiry;
                    const updatedBy = q.updatedBy?.name ? q.updatedBy.name : '—';

                    Swal.fire({
                        title: 'Inquiry Details',
                        width: 640,
                        html: '<div class="inquiry-view__meta">' +
                            'Received: <strong>' + inquiryEsc(inquiryDate(q.created_at)) + '</strong>' +
                            ' &nbsp;&bull;&nbsp; Status: <strong>' + inquiryEsc(q.status) + '</strong>' +
                            ' &nbsp;&bull;&nbsp; Last updated by: <strong>' + inquiryEsc(updatedBy) + '</strong>' +
                            '</div>' +
                            '<label class="inquiry-view__label">Name</label>' +
                            '<span class="inquiry-view__value">' + inquiryEsc(q.name) + '</span>' +
                            '<label class="inquiry-view__label">Phone</label>' +
                            '<span class="inquiry-view__value">' + inquiryEsc(q.phone) + '</span>' +
                            '<label class="inquiry-view__label">Email</label>' +
                            '<span class="inquiry-view__value">' + inquiryEsc(q.email || '—') + '</span>' +
                            '<label class="inquiry-view__label">Message</label>' +
                            '<div class="inquiry-view__message">' + inquiryEsc(q.message || '—') + '</div>',
                        confirmButtonText: 'Close',
                        confirmButtonColor: '#047354',
                        showCancelButton: true,
                        cancelButtonText: '<i class="fa fa-pencil"></i> Update',
                        cancelButtonColor: '#4b5563',
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.cancel) {
                            openInquiryDrawer('edit', q);
                        }
                    });
                }).fail(function() {
                    Swal.fire('Error', 'Failed to communicate with server.', 'error');
                });
            }

            function saveInquiry() {
                if (isSaving) return;
                isSaving = true;
                $('#saveBtn').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
                $('#drawerButtonText').text('Saving...');

                let id = $('#inquiry_id').val();
                let url = id ?
                    "{{ route('contact-inquiries.update', ':id') }}".replace(':id', id) :
                    "{{ route('contact-inquiries.store') }}";
                let method = id ? 'PUT' : 'POST';

                $.post(url, $('#inquiryForm').serialize() + '&_method=' + method, function(res) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#drawerButtonText').text(id ? 'Update Inquiry' : 'Save Inquiry');

                    if (res.status === 'success' || res.status === true) {
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: res.message || 'Inquiry saved successfully',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                        closeGlobalDrawer('inquiry-drawer', 'inquiry-overlay');
                        $('#inquiryTable').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message || 'Something went wrong', 'error');
                    }
                }).fail(function(xhr) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#drawerButtonText').text(id ? 'Update Inquiry' : 'Save Inquiry');

                    let errorMsg = 'Server error occurred';
                    if (xhr.responseJSON?.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    } else if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMsg
                    });
                });
            }

            function inquiryDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This inquiry will be permanently deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#4b5563',
                    confirmButtonText: 'Yes, delete it!'
                }).then((r) => {
                    if (r.isConfirmed) {
                        let deleteUrl = "{{ route('contact-inquiries.destroy', ':id') }}".replace(':id', id);

                        $.post(deleteUrl, {
                            _method: 'DELETE',
                        }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire('Deleted!', res.message || 'Inquiry has been deleted.', 'success');
                                $('#inquiryTable').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', res.message || 'Deletion failed.', 'error');
                            }
                        }).fail(function() {
                            Swal.fire('Error', 'Failed to communicate with server.', 'error');
                        });
                    }
                });
            }

            $('#resetFilters').on('click', function() {
                $('#filter_status').val('');

                $('.dt-filter-inquiryTable').trigger('change');
            });

            $(document).ready(function() {
                $(document).on('click', '#btnAddInquiry', function(e) {
                    e.preventDefault();
                    openInquiryDrawer('add');
                });

                // The shared data-table component defaults to ordering by the first
                // column (Name) — inquiries should open with the newest first instead.
                if ($.fn.DataTable.isDataTable('#inquiryTable')) {
                    $('#inquiryTable').DataTable().order([
                        [5, 'desc']
                    ]).draw();
                }
            });
        </script>
    @endpush
</x-app-layout>