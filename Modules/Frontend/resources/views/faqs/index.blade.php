<x-app-layout>

    <div class="p-4">
        <div
            class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            {{-- Status Filter --}}
            <div class="flex flex-col w-full md:w-1/3">
                <x-form-select label="Status" id="filter_status" class="dt-filter-faqTable">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
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
        <x-data-table id="faqTable" title="FAQs" icon="fa-solid fa-circle-question" buttonId="btnAddFaq"
            buttonText="Add New FAQ" :columns="['Question', 'Answer', 'Sort', 'Status', 'Created At', 'Action']" :ajaxUrl="route('faqs.dataTable')" :dtColumns="[
                ['data' => 'question'],
                ['data' => 'answer'],
                ['data' => 'sort_order'],
                ['data' => 'is_active'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]" :filters="[
                'is_active' => '#filter_status',
            ]"
            :exportButtons="true" />
    </div>

    {{-- DRAWER COMPONENT --}}
    <x-drawer id="faq-drawer" overlayId="faq-overlay" title="Add New FAQ"
        submitOnClick="saveForm()">
        <form id="faqForm">
            <input type="hidden" name="id" id="faq_id">

            {{-- Question --}}
            <div class="mb-4 animate-fade" style="animation-delay: 150ms;">
                <x-form-input label="Question" name="question" id="question" placeholder="Write the question"
                    :required="true" />
            </div>

            {{-- Answer --}}
            <div class="mb-4 animate-fade" style="animation-delay: 200ms;">
                <x-form-textarea label="Answer" name="answer" id="answer" rows="5"
                    placeholder="Write the answer" required />
            </div>

            {{-- Status (default: Active, editable) --}}
            <div class="mb-4 animate-fade" style="animation-delay: 250ms;">
                <x-form-select label="Status" name="is_active" id="is_active">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>
        </form>
    </x-drawer>

    @push('scripts')
        <style>
            #faqTable tbody tr { cursor: grab; }
            #faqTable tbody tr:active { cursor: grabbing; }
            #faqTable tbody tr.sortable-ghost { opacity: 0.4; background: #ecfdf5; }
        </style>
        <script>
            let isSaving = false;

            function openFaqDrawer(mode, faq) {
                $('#faqForm')[0].reset();
                $('#faq_id').val('');

                if (mode === 'edit' && faq) {
                    $('#faq_id').val(faq.id);
                    $('#question').val(faq.question ?? '');
                    $('#answer').val(faq.answer ?? '');
                    $('#is_active').val(faq.is_active ? '1' : '0');

                    $('#drawerTitle').text('Edit FAQ');
                    $('#drawerButtonText').text('Update FAQ');
                } else {
                    $('#is_active').val('1');
                    $('#drawerTitle').text('Add New FAQ');
                    $('#drawerButtonText').text('Save FAQ');
                }

                openGlobalDrawer('faq-drawer', 'faq-overlay');
            }

            function faqEdit(id) {
                let getUrl = "{{ route('faqs.show', ':id') }}".replace(':id', id);

                $.get(getUrl, function(res) {
                    if (res.status === 'success') {
                        openFaqDrawer('edit', res.faq);
                    } else {
                        Swal.fire('Error', res.message || 'FAQ not found.', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Failed to communicate with server.', 'error');
                });
            }

            function saveForm() {
                if (isSaving) return;
                isSaving = true;
                $('#saveBtn').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
                $('#drawerButtonText').text('Saving...');

                let id = $('#faq_id').val();
                let url = id ?
                    "{{ route('faqs.update', ':id') }}".replace(':id', id) :
                    "{{ route('faqs.store') }}";
                let method = id ? 'PUT' : 'POST';

                $.post(url, $('#faqForm').serialize() + '&_method=' + method, function(res) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#drawerButtonText').text(id ? 'Update FAQ' : 'Save FAQ');

                    if (res.status === 'success' || res.status === true) {
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: res.message || 'FAQ saved successfully',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                        closeGlobalDrawer('faq-drawer', 'faq-overlay');
                        $('#faqTable').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message || 'Something went wrong', 'error');
                        $('#drawerButtonText').text(id ? 'Update FAQ' : 'Save FAQ');
                    }
                }).fail(function(xhr) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#drawerButtonText').text(id ? 'Update FAQ' : 'Save FAQ');

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

            function faqDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This FAQ will be soft deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#4b5563',
                    confirmButtonText: 'Yes, delete it!'
                }).then((r) => {
                    if (r.isConfirmed) {
                        let deleteUrl = "{{ route('faqs.destroy', ':id') }}".replace(':id', id);

                        $.post(deleteUrl, {
                            _method: 'DELETE',
                        }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire('Deleted!', res.message || 'FAQ has been deleted.', 'success');
                                $('#faqTable').DataTable().ajax.reload(null, false);
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

                $('.dt-filter-faqTable').trigger('change');
            });

            function initFaqSortable() {
                const tbody = document.querySelector('#faqTable tbody');
                if (!tbody || typeof Sortable === 'undefined') return;

                if (tbody._sortableInstance) {
                    tbody._sortableInstance.destroy();
                }

                tbody._sortableInstance = Sortable.create(tbody, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    filter: 'button, a, input, select',
                    onEnd: function() {
                        const table = $('#faqTable').DataTable();
                        const pageStart = table.page.info().start;
                        const order = Array.from(tbody.querySelectorAll('tr'))
                            .map(tr => table.row(tr).data()?.id)
                            .filter(id => id !== undefined);

                        if (order.length === 0) return;

                        $.post("{{ route('faqs.reorder') }}", { order: order, start: pageStart }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire({
                                    toast: true,
                                    icon: 'success',
                                    title: res.message || 'FAQ order updated',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                });
                            } else {
                                Swal.fire('Error', res.message || 'Order update failed.', 'error');
                            }
                            // Sync DataTables internal data with the new order
                            $('#faqTable').DataTable().ajax.reload(null, false);
                        }).fail(function() {
                            Swal.fire('Error', 'Failed to update order.', 'error');
                            $('#faqTable').DataTable().ajax.reload(null, false);
                        });
                    }
                });
            }

            $(document).ready(function() {
                $(document).on('click', '#btnAddFaq', function(e) {
                    e.preventDefault();
                    openFaqDrawer('add');
                });

                // Enable drag & drop reorder on the FAQ table (re-init on every draw)
                $('#faqTable').on('draw.dt', initFaqSortable);
                initFaqSortable();
            });
        </script>
    @endpush
</x-app-layout>
