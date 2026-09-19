<x-app-layout>

    <div class="p-4">
        <div
            class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            {{-- Status Filter --}}
            <div class="flex flex-col w-full md:w-1/3">
                <x-form-select label="Status" id="filter_status" class="dt-filter-airlineTable">
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
        <x-data-table id="airlineTable" title="Airlines" icon="fa-solid fa-plane-departure" buttonId="btnAddAirline"
            buttonText="Add New Airline" :columns="['Airline', 'Sort', 'Status', 'Created At', 'Action']" :ajaxUrl="route('airlines.dataTable')" :dtColumns="[
                ['data' => 'name'],
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
    <x-drawer id="airline-drawer" overlayId="airline-overlay" title="Add New Airline" maxWidth="max-w-xl"
        submitOnClick="saveAirlineForm()">
        <form id="airlineForm">
            <input type="hidden" name="id" id="airline_id">

            {{-- Name --}}
            <div class="mb-4 animate-fade" style="animation-delay: 150ms;">
                <x-form-input label="Airline Name" name="name" id="name" placeholder="e.g. Biman Bangladesh Airlines"
                    :required="true" />
            </div>

            {{-- Code --}}
            <div class="mb-4 animate-fade" style="animation-delay: 200ms;">
                <x-form-input label="Code" name="code" id="code" placeholder="e.g. BG" />
                <p class="text-xs text-gray-400 mt-1">IATA code — e.g. BG, SV, EK.</p>
            </div>

            {{-- Logo --}}
            <div class="mb-4 animate-fade" style="animation-delay: 250ms;">
                <x-form-input label="Logo" name="logo" id="logo" type="file"
                    accept="image/png,image/jpeg,image/webp,image/svg+xml" onchange="previewAirlineLogo(this)" />
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP or SVG — max 2MB. <span id="airlineLogoKeepHint"
                        class="hidden text-gray-500">Leave empty to keep the current logo.</span></p>
                <div id="airlineLogoPreview" class="mt-2"></div>
            </div>

            {{-- Status --}}
            <div class="mb-4 animate-fade" style="animation-delay: 300ms;">
                <x-form-select label="Status" name="is_active" id="airline_is_active">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>
        </form>
    </x-drawer>

    @push('scripts')
        <style>
            #airlineTable tbody tr { cursor: grab; }
            #airlineTable tbody tr:active { cursor: grabbing; }
            #airlineTable tbody tr.sortable-ghost { opacity: 0.4; background: #ecfdf5; }
        </style>
        <script>
            let isSavingAirline = false;

            // Local preview of the selected logo file
            function previewAirlineLogo(input) {
                $('#airlineLogoPreview').empty();
                if (input.files && input.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $('#airlineLogoPreview').append(
                            `<div class="flex items-center gap-2">
                                <img src="${e.target.result}" class="h-12 w-12 rounded-lg object-contain ring-1 ring-gray-200 bg-white">
                                <span class="text-xs text-gray-500">${input.files[0].name}</span>
                            </div>`
                        );
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function openAirlineDrawer(mode, airline) {
                $('#airlineForm')[0].reset();
                $('#airline_id').val('');
                $('#airlineLogoPreview').empty();
                $('#airlineLogoKeepHint').addClass('hidden');

                if (mode === 'edit' && airline) {
                    $('#airline_id').val(airline.id);
                    $('#name').val(airline.name ?? '');
                    $('#code').val(airline.code ?? '');
                    $('#airline_is_active').val(airline.is_active ? '1' : '0');

                    // Show the current logo (kept unless a new file is chosen)
                    if (airline.logo_url) {
                        $('#airlineLogoPreview').append(
                            `<div class="flex items-center gap-2">
                                <img src="${airline.logo_url}" class="h-12 w-12 rounded-lg object-contain ring-1 ring-gray-200 bg-white">
                                <span class="text-xs text-gray-500">Current logo</span>
                            </div>`
                        );
                        $('#airlineLogoKeepHint').removeClass('hidden');
                    }

                    $('#airlineDrawerTitle').text('Edit Airline');
                    $('#airlineDrawerButtonText').text('Update Airline');
                } else {
                    $('#airline_is_active').val('1');
                    $('#airlineDrawerTitle').text('Add New Airline');
                    $('#airlineDrawerButtonText').text('Save Airline');
                }

                openGlobalDrawer('airline-drawer', 'airline-overlay');
            }

            function airlineEdit(id) {
                let showUrl = "{{ route('airlines.show', ':id') }}".replace(':id', id);

                $.get(showUrl, function(res) {
                    if (res.status === 'success') {
                        openAirlineDrawer('edit', res.airline);
                    } else {
                        Swal.fire('Error', res.message || 'Airline not found.', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Failed to communicate with server.', 'error');
                });
            }

            function saveAirlineForm() {
                if (isSavingAirline) return;
                isSavingAirline = true;
                $('#saveBtn').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
                $('#airlineDrawerButtonText').text('Saving...');

                let id = $('#airline_id').val();
                let url = id ?
                    "{{ route('airlines.update', ':id') }}".replace(':id', id) :
                    "{{ route('airlines.store') }}";

                // FormData so the logo file is included
                let formData = new FormData(document.getElementById('airlineForm'));
                if (id) formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        isSavingAirline = false;
                        $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        $('#airlineDrawerButtonText').text(id ? 'Update Airline' : 'Save Airline');

                        if (res.status === 'success' || res.status === true) {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: res.message || 'Airline saved successfully',
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            });
                            closeGlobalDrawer('airline-drawer', 'airline-overlay');
                            $('#airlineTable').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.message || 'Something went wrong', 'error');
                            $('#airlineDrawerButtonText').text(id ? 'Update Airline' : 'Save Airline');
                        }
                    },
                    error: function(xhr) {
                        isSavingAirline = false;
                        $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        $('#airlineDrawerButtonText').text(id ? 'Update Airline' : 'Save Airline');

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

            function airlineDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This airline will be soft deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        let url = "{{ route('airlines.destroy', ':id') }}".replace(':id', id);

                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function(res) {
                                if (res.status === 'success' || res.status === true) {
                                    Swal.fire({
                                        toast: true,
                                        icon: 'success',
                                        title: res.message || 'Airline has been deleted.',
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true,
                                    });
                                    $('#airlineTable').DataTable().ajax.reload(null, false);
                                } else {
                                    Swal.fire('Error', res.message || 'Deletion failed.', 'error');
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = 'Deletion failed.';
                                if (xhr.responseJSON?.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error', errorMsg, 'error');
                            }
                        });
                    }
                });
            }

            $('#resetFilters').on('click', function() {
                $('#filter_status').val('');

                $('.dt-filter-airlineTable').trigger('change');
            });

            function initAirlineSortable() {
                const tbody = document.querySelector('#airlineTable tbody');
                if (!tbody || typeof Sortable === 'undefined') return;

                if (tbody._sortableInstance) {
                    tbody._sortableInstance.destroy();
                }

                tbody._sortableInstance = Sortable.create(tbody, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    filter: 'button, a, input, select',
                    onEnd: function() {
                        const table = $('#airlineTable').DataTable();
                        const pageStart = table.page.info().start;
                        const order = Array.from(tbody.querySelectorAll('tr'))
                            .map(tr => table.row(tr).data()?.id)
                            .filter(id => id !== undefined);

                        if (order.length === 0) return;

                        $.post("{{ route('airlines.reorder') }}", { order: order, start: pageStart }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire({
                                    toast: true,
                                    icon: 'success',
                                    title: res.message || 'Airline order updated',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                });
                            } else {
                                Swal.fire('Error', res.message || 'Order update failed.', 'error');
                            }
                            // Sync DataTables internal data with the new order
                            $('#airlineTable').DataTable().ajax.reload(null, false);
                        }).fail(function() {
                            Swal.fire('Error', 'Failed to update order.', 'error');
                            $('#airlineTable').DataTable().ajax.reload(null, false);
                        });
                    }
                });
            }

            $(document).ready(function() {
                $(document).on('click', '#btnAddAirline', function(e) {
                    e.preventDefault();
                    openAirlineDrawer('add');
                });

                // Enable drag & drop reorder on the airline table (re-init on every draw)
                $('#airlineTable').on('draw.dt', initAirlineSortable);
                initAirlineSortable();
            });
        </script>
    @endpush
</x-app-layout>
