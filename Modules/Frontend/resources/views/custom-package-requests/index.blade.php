<x-app-layout>

    <div class="p-4">
        <div
            class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            {{-- Status Filter --}}
            <div class="flex flex-col w-full md:w-1/3">
                <x-form-select label="Status" id="filter_status" class="dt-filter-cprTable">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="contacted">Contacted</option>
                    <option value="processing">Processing</option>
                    <option value="quoted">Quoted</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
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
        <x-data-table id="cprTable" title="Custom Package Requests" icon="fa-solid fa-file-signature"
            buttonId="btnAddCpr" buttonText="Add New Request"
            :columns="['Customer', 'Airline', 'Travel Date', 'Quoted Price', 'Status', 'Received At', 'Action']"
            :ajaxUrl="route('custom-package-requests.dataTable')" :dtColumns="[
                ['data' => 'name'],
                ['data' => 'airline_name'],
                ['data' => 'travel_date'],
                ['data' => 'quoted_price'],
                ['data' => 'status'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]" :filters="[
                'status' => '#filter_status',
            ]" :exportButtons="true" />
    </div>

    {{-- DRAWER COMPONENT --}}
    <x-drawer id="cpr-drawer" overlayId="cpr-overlay" title="Add New Request" maxWidth="max-w-xl"
        submitOnClick="saveCprForm()">
        <form id="cprForm">
            <input type="hidden" name="id" id="cpr_id">

            {{-- Customer Information --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 100ms;">
                <x-form-input label="Name" name="name" id="cpr_name" placeholder="Customer name" :required="true" />
                <x-form-input label="Phone" name="phone" id="cpr_phone" placeholder="e.g. +880 1712-345678"
                    :required="true" />
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 150ms;">
                <x-form-input label="Email" name="email" id="cpr_email" type="email"
                    placeholder="customer@example.com" />
                <x-form-input label="Travel Date" name="travel_date" id="cpr_travel_date" type="date" />
            </div>

            {{-- Airline dropdown (from airlines table) --}}
            <div class="mb-4 animate-fade" style="animation-delay: 200ms;">
                <x-form-select label="Airline" name="airline_id" id="cpr_airline_id">
                    <option value="">Select airline</option>
                </x-form-select>
            </div>

            {{-- Preferred Hotels --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 250ms;">
                <x-form-input label="Makkah Hotel" name="makkah_hotel" id="cpr_makkah_hotel"
                    placeholder="e.g. Fairmont Clock Tower" />
                <x-form-input label="Madinah Hotel" name="madinah_hotel" id="cpr_madinah_hotel"
                    placeholder="e.g. Anwar Al Madinah Mövenpick" />
            </div>

            {{-- Transport & Food --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 300ms;">
                <x-form-input label="Preferred Transport" name="preferred_transport" id="cpr_transport"
                    placeholder="e.g. Bus, Private Car" />
                <x-form-input label="Food Preference" name="food_preference" id="cpr_food"
                    placeholder="e.g. Bangladeshi, Indian" />
            </div>

            {{-- Passenger Information --}}
            <div class="grid grid-cols-4 gap-3 mb-4 animate-fade" style="animation-delay: 350ms;">
                <x-form-input label="Adults" name="adults" id="cpr_adults" type="number" value="1" min="1" />
                <x-form-input label="Children" name="children" id="cpr_children" type="number" value="0" min="0" />
                <x-form-input label="Male" name="male" id="cpr_male" type="number" value="0" min="0" />
                <x-form-input label="Female" name="female" id="cpr_female" type="number" value="0" min="0" />
            </div>

            {{-- Additional Note --}}
            <div class="mb-4 animate-fade" style="animation-delay: 400ms;">
                <x-form-textarea label="Additional Requirements" name="additional_note" id="cpr_note" rows="3"
                    placeholder="Anything else the customer asked for..." />
            </div>

            {{-- Status — only meaningful when updating an existing request.
                 New requests always start as "pending" (handled by the server). --}}
            <div id="cprStatusWrapper" class="mb-4 animate-fade" style="animation-delay: 450ms;">
                <x-form-select label="Status" name="status" id="cpr_status" :placeholder="false">
                    <option value="pending">Pending</option>
                    <option value="contacted">Contacted</option>
                    <option value="processing">Processing</option>
                    <option value="quoted">Quoted</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                </x-form-select>
            </div>

            {{-- Admin fields --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 500ms;">
                <x-form-input label="Quoted Price" name="quoted_price" id="cpr_quoted_price" type="number"
                    step="0.01" min="0" placeholder="e.g. 185000.00" />
                <x-form-input label="Admin Note" name="admin_note" id="cpr_admin_note"
                    placeholder="Internal note" />
            </div>
        </form>
    </x-drawer>

    @push('scripts')
        <script>
            let isSaving = false;
            let cprAirlineOptions = [];

            function cprEsc(value) {
                return $('<div>').text(value ?? '').html();
            }

            // Load active airlines from the database for the dropdown
            function loadCprAirlineOptions(selectedId) {
                return $.get("{{ route('airlines.options') }}", function(res) {
                    cprAirlineOptions = res.status === 'success' ? (res.airlines || []) : [];

                    let options = '<option value="">Select airline</option>';
                    cprAirlineOptions.forEach(function(airline) {
                        const label = airline.code ? `${airline.name} (${airline.code})` : airline.name;
                        options += `<option value="${airline.id}" ${airline.id == selectedId ? 'selected' : ''}>${cprEsc(label)}</option>`;
                    });

                    $('#cpr_airline_id').html(options);
                }).fail(function() {
                    $('#cpr_airline_id').html('<option value="">Select airline</option>');
                });
            }

            function openCprDrawer(mode, request) {
                $('#cprForm')[0].reset();
                $('#cpr_id').val('');

                if (mode === 'edit' && request) {
                    loadCprAirlineOptions(request.airline_id);
                    $('#cpr_id').val(request.id);
                    $('#cpr_name').val(request.name ?? '');
                    $('#cpr_phone').val(request.phone ?? '');
                    $('#cpr_email').val(request.email ?? '');
                    $('#cpr_travel_date').val(request.travel_date ? request.travel_date.slice(0, 10) : '');
                    $('#cpr_makkah_hotel').val(request.makkah_hotel ?? '');
                    $('#cpr_madinah_hotel').val(request.madinah_hotel ?? '');
                    $('#cpr_transport').val(request.preferred_transport ?? '');
                    $('#cpr_food').val(request.food_preference ?? '');
                    $('#cpr_adults').val(request.adults ?? 1);
                    $('#cpr_children').val(request.children ?? 0);
                    $('#cpr_male').val(request.male ?? 0);
                    $('#cpr_female').val(request.female ?? 0);
                    $('#cpr_note').val(request.additional_note ?? '');
                    $('#cpr_status').val(request.status ?? 'pending');
                    $('#cpr_quoted_price').val(request.quoted_price ?? '');
                    $('#cpr_admin_note').val(request.admin_note ?? '');

                    $('#cprDrawerTitle').text('Edit Request');
                    $('#cprDrawerButtonText').text('Update Request');
                } else {
                    loadCprAirlineOptions('');
                    $('#cpr_status').val('pending');
                    $('#cprDrawerTitle').text('Add New Request');
                    $('#cprDrawerButtonText').text('Save Request');
                }

                openGlobalDrawer('cpr-drawer', 'cpr-overlay');
            }

            function customPackageRequestEdit(id) {
                let showUrl = "{{ route('custom-package-requests.show', ':id') }}".replace(':id', id);

                $.get(showUrl, function(res) {
                    if (res.status === 'success') {
                        openCprDrawer('edit', res.custom_package_request);
                    } else {
                        Swal.fire('Error', res.message || 'Request not found.', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Failed to communicate with server.', 'error');
                });
            }

            function saveCprForm() {
                if (isSaving) return;
                isSaving = true;
                $('#saveBtn').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
                $('#cprDrawerButtonText').text('Saving...');

                let id = $('#cpr_id').val();
                let url = id ?
                    "{{ route('custom-package-requests.update', ':id') }}".replace(':id', id) :
                    "{{ route('custom-package-requests.store') }}";
                let method = id ? 'PUT' : 'POST';

                $.post(url, $('#cprForm').serialize() + '&_method=' + method, function(res) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#cprDrawerButtonText').text(id ? 'Update Request' : 'Save Request');

                    if (res.status === 'success' || res.status === true) {
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: res.message || 'Request saved successfully',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                        closeGlobalDrawer('cpr-drawer', 'cpr-overlay');
                        $('#cprTable').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message || 'Something went wrong', 'error');
                    }
                }).fail(function(xhr) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#cprDrawerButtonText').text(id ? 'Update Request' : 'Save Request');

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

            function customPackageRequestDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This request will be soft deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        let url = "{{ route('custom-package-requests.destroy', ':id') }}".replace(':id', id);

                        $.post(url, { _method: 'DELETE' }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire({
                                    toast: true,
                                    icon: 'success',
                                    title: res.message || 'Request has been deleted.',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                });
                                $('#cprTable').DataTable().ajax.reload(null, false);
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

                $('.dt-filter-cprTable').trigger('change');
            });

            $(document).ready(function() {
                $(document).on('click', '#btnAddCpr', function(e) {
                    e.preventDefault();
                    openCprDrawer('add');
                });

                // Open with the newest requests first
                if ($.fn.DataTable.isDataTable('#cprTable')) {
                    $('#cprTable').DataTable().order([
                        [5, 'desc']
                    ]).draw();
                }
            });
        </script>
    @endpush
</x-app-layout>
