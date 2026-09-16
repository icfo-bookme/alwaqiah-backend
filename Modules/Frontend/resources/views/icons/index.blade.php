<x-app-layout>

    <div class="p-4">
        <div
            class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col w-full md:w-1/3">
                <x-form-select label="Status" id="filter_status" class="dt-filter-iconTable">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>

            <div class="w-full md:w-auto flex items-end">
                <button id="resetFilters"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    Reset
                </button>
            </div>
        </div>

        <x-data-table id="iconTable" title="Icon Management" icon="fa-solid fa-icons"
            buttonId="btnAddIcon" buttonText="Add New Icon"
            :columns="['Name', 'Class', 'Keywords', 'Sort', 'Status', 'Created At', 'Action']"
            :ajaxUrl="route('icons.dataTable')"
            :dtColumns="[
                ['data' => 'name', 'orderable' => false],
                ['data' => 'class'],
                ['data' => 'keywords'],
                ['data' => 'sort_order'],
                ['data' => 'is_active'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]" :filters="[
                'is_active' => '#filter_status',
            ]" :exportButtons="true" />
    </div>

    <x-drawer id="icon-drawer" overlayId="icon-overlay" title="Add New Icon" submitOnClick="saveForm()">
        <form id="iconForm">
            <input type="hidden" name="id" id="icon_id">

            <div class="mb-4 animate-fade" style="animation-delay: 150ms;">
                <x-form-input label="Name" name="name" id="name" placeholder="e.g. Hotel" :required="true" />
            </div>

            <div class="mb-4 animate-fade" style="animation-delay: 200ms;">
                <x-form-input label="Font Awesome Class" name="class" id="class"
                    placeholder="e.g. fa-solid fa-hotel" :required="true" oninput="updateIconPreview()" />
            </div>

            <div class="mb-4 animate-fade" style="animation-delay: 250ms;">
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 flex items-center gap-3">
                    <div class="h-11 w-11 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                        <i id="iconPreview" class="fa-solid fa-icons text-xl text-gray-700"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Icon Preview</p>
                        <p id="iconPreviewClass" class="text-xs text-gray-500">fa-solid fa-icons</p>
                    </div>
                </div>
            </div>

            <div class="mb-4 animate-fade" style="animation-delay: 300ms;">
                <x-form-textarea label="Keywords" name="keywords" id="keywords"
                    placeholder="e.g. hotel, room, stay" />
            </div>

            <div class="mb-4 animate-fade" style="animation-delay: 350ms;">
                <x-form-select label="Status" name="is_active" id="is_active" :placeholder="false">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>
        </form>
    </x-drawer>

    @push('scripts')
        <style>
            #iconTable tbody tr {
                cursor: grab;
            }

            #iconTable tbody tr:active {
                cursor: grabbing;
            }

            #iconTable tbody tr.sortable-ghost {
                opacity: 0.4;
                background: #ecfdf5;
            }
        </style>
        <script>
            let isSaving = false;

            function updateIconPreview() {
                const iconClass = $('#class').val().trim() || 'fa-solid fa-icons';
                $('#iconPreview').attr('class', iconClass + ' text-xl text-gray-700');
                $('#iconPreviewClass').text(iconClass);
            }

            function resetIconForm() {
                $('#iconForm')[0].reset();
                $('#icon_id').val('');
                $('#is_active').val('1');
                updateIconPreview();
            }

            function openIconDrawer(mode, icon) {
                resetIconForm();

                if (mode === 'edit' && icon) {
                    $('#icon_id').val(icon.id);
                    $('#name').val(icon.name ?? '');
                    $('#class').val(icon.class ?? '');
                    $('#keywords').val(icon.keywords ?? '');
                    $('#is_active').val(icon.is_active ? '1' : '0');
                    $('#drawerTitle').text('Edit Icon');
                    $('#drawerButtonText').text('Update Icon');
                } else {
                    $('#drawerTitle').text('Add New Icon');
                    $('#drawerButtonText').text('Save Icon');
                }

                updateIconPreview();
                openGlobalDrawer('icon-drawer', 'icon-overlay');
            }

            function iconEdit(id) {
                let showUrl = "{{ route('icons.show', ':id') }}".replace(':id', id);

                $.get(showUrl, function(res) {
                    if (res.status === 'success') {
                        openIconDrawer('edit', res.icon);
                    } else {
                        Swal.fire('Error', res.message || 'Icon not found.', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Failed to communicate with server.', 'error');
                });
            }

            function saveForm() {
                if (isSaving) return;

                const id = $('#icon_id').val();
                isSaving = true;
                $('#saveBtn').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
                $('#drawerButtonText').text('Saving...');

                const payload = {
                    name: $('#name').val().trim(),
                    class: $('#class').val().trim(),
                    keywords: $('#keywords').val().trim(),
                    is_active: $('#is_active').val(),
                };

                let url = id
                    ? "{{ route('icons.update', ':id') }}".replace(':id', id)
                    : "{{ route('icons.store') }}";

                if (id) payload._method = 'PUT';

                $.post(url, payload, function(res) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#drawerButtonText').text(id ? 'Update Icon' : 'Save Icon');

                    if (res.status === 'success' || res.status === true) {
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: res.message || 'Icon saved successfully',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                        closeGlobalDrawer('icon-drawer', 'icon-overlay');
                        $('#iconTable').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message || 'Something went wrong', 'error');
                    }
                }).fail(function(xhr) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#drawerButtonText').text(id ? 'Update Icon' : 'Save Icon');

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

            function iconDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This icon will be soft deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#4b5563',
                    confirmButtonText: 'Yes, delete it!'
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    let deleteUrl = "{{ route('icons.destroy', ':id') }}".replace(':id', id);

                    $.post(deleteUrl, {
                        _method: 'DELETE',
                    }, function(res) {
                        if (res.status === 'success' || res.status === true) {
                            Swal.fire('Deleted!', res.message || 'Icon has been deleted.', 'success');
                            $('#iconTable').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.message || 'Deletion failed.', 'error');
                        }
                    }).fail(function() {
                        Swal.fire('Error', 'Failed to communicate with server.', 'error');
                    });
                });
            }

            $('#resetFilters').on('click', function() {
                $('#filter_status').val('');
                $('.dt-filter-iconTable').trigger('change');
            });

            function initIconSortable() {
                const tbody = document.querySelector('#iconTable tbody');
                if (!tbody || typeof Sortable === 'undefined') return;

                if (tbody._sortableInstance) {
                    tbody._sortableInstance.destroy();
                }

                tbody._sortableInstance = Sortable.create(tbody, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    filter: 'button, a, input, select',
                    onEnd: function() {
                        const table = $('#iconTable').DataTable();
                        const pageStart = table.page.info().start;
                        const order = Array.from(tbody.querySelectorAll('tr'))
                            .map(tr => table.row(tr).data()?.id)
                            .filter(id => id !== undefined);

                        if (order.length === 0) return;

                        $.post("{{ route('icons.reorder') }}", { order: order, start: pageStart }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire({
                                    toast: true,
                                    icon: 'success',
                                    title: res.message || 'Icon order updated',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                });
                            } else {
                                Swal.fire('Error', res.message || 'Order update failed.', 'error');
                            }
                            $('#iconTable').DataTable().ajax.reload(null, false);
                        }).fail(function() {
                            Swal.fire('Error', 'Failed to update order.', 'error');
                            $('#iconTable').DataTable().ajax.reload(null, false);
                        });
                    }
                });
            }

            $(document).ready(function() {
                $(document).on('click', '#btnAddIcon', function(e) {
                    e.preventDefault();
                    openIconDrawer('add');
                });

                $('#iconTable').on('draw.dt', initIconSortable);
                initIconSortable();
            });
        </script>
    @endpush
</x-app-layout>
