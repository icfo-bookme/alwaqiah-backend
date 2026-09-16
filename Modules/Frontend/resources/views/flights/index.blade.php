<x-app-layout>

    <div class="p-4">
        <div
            class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            {{-- Status Filter --}}
            <div class="flex flex-col w-full md:w-1/3">
                <x-form-select label="Status" id="filter_status" class="dt-filter-flightTable">
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
        <x-data-table id="flightTable" title="Flights" icon="fa-solid fa-plane" buttonId="btnAddFlight"
            buttonText="Add New Flight" :columns="['Airline', 'Departure', 'Return', 'Sort', 'Status', 'Created At', 'Action']" :ajaxUrl="route('flights.dataTable')" :dtColumns="[
                ['data' => 'airline_name'],
                ['data' => 'departure_at'],
                ['data' => 'return_at'],
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
    <x-drawer id="flight-drawer" overlayId="flight-overlay" title="Add New Flight" maxWidth="max-w-xl"
        submitOnClick="saveForm()">
        <form id="flightForm">
            <input type="hidden" name="id" id="flight_id">

            {{-- Airline Name --}}
            <div class="mb-4 animate-fade" style="animation-delay: 150ms;">
                <x-form-input label="Airline Name" name="airline_name" id="airline_name" placeholder="e.g. Biman Bangladesh Airlines"
                    :required="true" />
            </div>

            {{-- Airline Logo --}}
            <div class="mb-4 animate-fade" style="animation-delay: 200ms;">
                <x-form-input label="Airline Logo" name="airline_logo" id="airline_logo" type="file"
                    accept="image/png,image/jpeg,image/webp,image/svg+xml" onchange="previewLogo(this)" />
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP or SVG — max 2MB. <span id="logoKeepHint"
                        class="hidden text-gray-500">Leave empty to keep the current logo.</span></p>
                <div id="logoPreview" class="mt-2"></div>
            </div>

            {{-- Flight Number --}}
            <div class="mb-4 animate-fade" style="animation-delay: 250ms;">
                <x-form-input label="Flight Number" name="flight_number" id="flight_number"
                    placeholder="e.g. BG-021" />
            </div>

            {{-- Departure Leg --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 300ms;">
                <x-form-input label="Departure Airport" name="departure_airport" id="departure_airport"
                    placeholder="e.g. DAC" />
                <x-form-input label="Arrival Airport" name="arrival_airport" id="arrival_airport"
                    placeholder="e.g. JED" />
            </div>

            <div class="mb-4 animate-fade" style="animation-delay: 350ms;">
                <x-form-input label="Departure Time" name="departure_at" id="departure_at" type="datetime-local" />
            </div>

            {{-- Return Leg --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 400ms;">
                <x-form-input label="Return Departure Airport" name="return_departure_airport"
                    id="return_departure_airport" placeholder="e.g. JED" />
                <x-form-input label="Return Arrival Airport" name="return_arrival_airport"
                    id="return_arrival_airport" placeholder="e.g. DAC" />
            </div>

            <div class="mb-4 animate-fade" style="animation-delay: 450ms;">
                <x-form-input label="Return Time" name="return_at" id="return_at" type="datetime-local" />
            </div>

            {{-- Status --}}
            <div class="mb-4 animate-fade" style="animation-delay: 500ms;">
                <x-form-select label="Status" name="is_active" id="is_active">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>
        </form>
    </x-drawer>

    @push('scripts')
        <style>
            #flightTable tbody tr { cursor: grab; }
            #flightTable tbody tr:active { cursor: grabbing; }
            #flightTable tbody tr.sortable-ghost { opacity: 0.4; background: #ecfdf5; }
        </style>
        <script>
            let isSaving = false;

            // Convert a stored datetime ("2026-09-19T14:30:00.000000Z" or
            // "2026-09-19 14:30:00") to the datetime-local input format,
            // keeping the stored wall-clock time (no timezone shifting).
            function toDatetimeLocal(value) {
                if (!value) return '';
                let m = String(value).match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/);
                return m ? `${m[1]}-${m[2]}-${m[3]}T${m[4]}:${m[5]}` : '';
            }

            // Local preview of the selected logo file
            function previewLogo(input) {
                $('#logoPreview').empty();
                if (input.files && input.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $('#logoPreview').append(
                            `<div class="flex items-center gap-2">
                                <img src="${e.target.result}" class="h-12 w-12 rounded-lg object-contain ring-1 ring-gray-200 bg-white">
                                <span class="text-xs text-gray-500">${input.files[0].name}</span>
                            </div>`
                        );
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function openFlightDrawer(mode, flight) {
                $('#flightForm')[0].reset();
                $('#flight_id').val('');
                $('#logoPreview').empty();
                $('#logoKeepHint').addClass('hidden');

                if (mode === 'edit' && flight) {
                    $('#flight_id').val(flight.id);
                    $('#airline_name').val(flight.airline_name ?? '');
                    $('#flight_number').val(flight.flight_number ?? '');
                    $('#departure_airport').val(flight.departure_airport ?? '');
                    $('#arrival_airport').val(flight.arrival_airport ?? '');
                    $('#departure_at').val(toDatetimeLocal(flight.departure_at));
                    $('#return_departure_airport').val(flight.return_departure_airport ?? '');
                    $('#return_arrival_airport').val(flight.return_arrival_airport ?? '');
                    $('#return_at').val(toDatetimeLocal(flight.return_at));
                    $('#is_active').val(flight.is_active ? '1' : '0');

                    // Show the current logo (kept unless a new file is chosen)
                    if (flight.airline_logo_url) {
                        $('#logoPreview').append(
                            `<div class="flex items-center gap-2">
                                <img src="${flight.airline_logo_url}" class="h-12 w-12 rounded-lg object-contain ring-1 ring-gray-200 bg-white">
                                <span class="text-xs text-gray-500">Current logo</span>
                            </div>`
                        );
                        $('#logoKeepHint').removeClass('hidden');
                    }

                    $('#drawerTitle').text('Edit Flight');
                    $('#drawerButtonText').text('Update Flight');
                } else {
                    $('#is_active').val('1');
                    $('#drawerTitle').text('Add New Flight');
                    $('#drawerButtonText').text('Save Flight');
                }

                openGlobalDrawer('flight-drawer', 'flight-overlay');
            }

            function flightEdit(id) {
                let showUrl = "{{ route('flights.show', ':id') }}".replace(':id', id);

                $.get(showUrl, function(res) {
                    if (res.status === 'success') {
                        openFlightDrawer('edit', res.flight);
                    } else {
                        Swal.fire('Error', res.message || 'Flight not found.', 'error');
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

                let id = $('#flight_id').val();
                let url = id ?
                    "{{ route('flights.update', ':id') }}".replace(':id', id) :
                    "{{ route('flights.store') }}";

                // FormData so the airline logo file is included
                let formData = new FormData(document.getElementById('flightForm'));
                if (id) formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        isSaving = false;
                        $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        $('#drawerButtonText').text(id ? 'Update Flight' : 'Save Flight');

                        if (res.status === 'success' || res.status === true) {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: res.message || 'Flight saved successfully',
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            });
                            closeGlobalDrawer('flight-drawer', 'flight-overlay');
                            $('#flightTable').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.message || 'Something went wrong', 'error');
                            $('#drawerButtonText').text(id ? 'Update Flight' : 'Save Flight');
                        }
                    },
                    error: function(xhr) {
                        isSaving = false;
                        $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        $('#drawerButtonText').text(id ? 'Update Flight' : 'Save Flight');

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
                    }
                });
            }

            function flightDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This flight will be soft deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#4b5563',
                    confirmButtonText: 'Yes, delete it!'
                }).then((r) => {
                    if (r.isConfirmed) {
                        let deleteUrl = "{{ route('flights.destroy', ':id') }}".replace(':id', id);

                        $.post(deleteUrl, {
                            _method: 'DELETE',
                        }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire('Deleted!', res.message || 'Flight has been deleted.', 'success');
                                $('#flightTable').DataTable().ajax.reload(null, false);
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

                $('.dt-filter-flightTable').trigger('change');
            });

            function initFlightSortable() {
                const tbody = document.querySelector('#flightTable tbody');
                if (!tbody || typeof Sortable === 'undefined') return;

                if (tbody._sortableInstance) {
                    tbody._sortableInstance.destroy();
                }

                tbody._sortableInstance = Sortable.create(tbody, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    filter: 'button, a, input, select',
                    onEnd: function() {
                        const table = $('#flightTable').DataTable();
                        const pageStart = table.page.info().start;
                        const order = Array.from(tbody.querySelectorAll('tr'))
                            .map(tr => table.row(tr).data()?.id)
                            .filter(id => id !== undefined);

                        if (order.length === 0) return;

                        $.post("{{ route('flights.reorder') }}", { order: order, start: pageStart }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire({
                                    toast: true,
                                    icon: 'success',
                                    title: res.message || 'Flight order updated',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                });
                            } else {
                                Swal.fire('Error', res.message || 'Order update failed.', 'error');
                            }
                            // Sync DataTables internal data with the new order
                            $('#flightTable').DataTable().ajax.reload(null, false);
                        }).fail(function() {
                            Swal.fire('Error', 'Failed to update order.', 'error');
                            $('#flightTable').DataTable().ajax.reload(null, false);
                        });
                    }
                });
            }

            $(document).ready(function() {
                $(document).on('click', '#btnAddFlight', function(e) {
                    e.preventDefault();
                    openFlightDrawer('add');
                });

                // Enable drag & drop reorder on the flight table (re-init on every draw)
                $('#flightTable').on('draw.dt', initFlightSortable);
                initFlightSortable();
            });
        </script>
    @endpush
</x-app-layout>
