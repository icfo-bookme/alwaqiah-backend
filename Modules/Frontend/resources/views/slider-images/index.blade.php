<x-app-layout>

    <div class="p-4">
        <div
            class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            {{-- Status Filter --}}
            <div class="flex flex-col w-full md:w-1/3">
                <x-form-select label="Status" id="filter_status" class="dt-filter-sliderImageTable">
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
        <x-data-table id="sliderImageTable" title="Slider Images" icon="fa-solid fa-images"
            buttonId="btnAddSlider" buttonText="Add New Images"
            :columns="['Image', 'Sort', 'Status', 'Published At', 'Created At', 'Action']"
            :ajaxUrl="route('slider-images.dataTable')"
            :dtColumns="[
                ['data' => 'image', 'orderable' => false, 'searchable' => false],
                ['data' => 'sort_order'],
                ['data' => 'is_active'],
                ['data' => 'published_at'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]" :filters="[
                'is_active' => '#filter_status',
            ]" :exportButtons="true" />
    </div>

    {{-- DRAWER COMPONENT --}}
    <x-drawer id="slider-drawer" overlayId="slider-overlay" title="Add New Slider Images"
        submitOnClick="saveForm()">
        <form id="sliderForm">
            <input type="hidden" name="id" id="slider_id">

            {{-- Multi Image Upload --}}
            <div class="mb-4 animate-fade" style="animation-delay: 150ms;">
                <x-form-input label="Images (multiple select allowed)" name="images" id="images" type="file"
                    :required="true" multiple accept="image/png,image/jpeg,image/webp"
                    onchange="previewSliderImages()" />
            </div>

            {{-- Live Preview --}}
            <div id="sliderPreview" class="flex flex-wrap gap-2 mb-2 animate-fade" style="animation-delay: 200ms;">
            </div>

            {{-- Status (default: Active, editable) --}}
            <div class="mb-4 animate-fade" style="animation-delay: 300ms;">
                <x-form-select label="Status" name="is_active" id="is_active">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>
        </form>
    </x-drawer>

    @push('scripts')
    <style>
        #sliderImageTable tbody tr { cursor: grab; }
        #sliderImageTable tbody tr:active { cursor: grabbing; }
        #sliderImageTable tbody tr.sortable-ghost { opacity: 0.4; background: #ecfdf5; }
    </style>
    <script>
        let isSaving = false;
        let selectedFiles = [];

        function previewThumb(src, onclick) {
            return $(`
                <div class="relative">
                    <img src="${src}" class="h-16 w-24 rounded-md object-cover ring-1 ring-gray-200">
                    <button type="button" onclick="${onclick}"
                        class="absolute -top-2 -right-2 h-6 w-6 rounded-full bg-red-500 text-white
                               flex items-center justify-center shadow hover:bg-red-600 transition-colors">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
            `);
        }

        function previewSliderImages() {
            selectedFiles = Array.from($('#images')[0].files);
            renderSelectedPreview();
        }

        function renderSelectedPreview() {
            const preview = $('#sliderPreview');
            preview.empty();

            selectedFiles.forEach((file, index) => {
                preview.append(previewThumb(URL.createObjectURL(file), `removeSelectedFile(${index})`));
            });
        }

        function removeSelectedFile(index) {
            selectedFiles.splice(index, 1);
            renderSelectedPreview();
        }

        function openSliderDrawer(mode, slider) {
            $('#sliderForm')[0].reset();
            $('#slider_id').val('');
            $('#sliderPreview').empty();
            $('#images').val('');
            selectedFiles = [];

            if (mode === 'edit' && slider) {
                $('#slider_id').val(slider.id);
                $('#is_active').val(slider.is_active ? '1' : '0');

                // Current image with red-cross remove option
                $('#sliderPreview').append(previewThumb(slider.image_url, `removeSliderImage(${slider.id})`));

                $('#drawerTitle').text('Edit Slider Image');
                $('#drawerButtonText').text('Update Slider');
            } else {
                $('#is_active').val('1');
                $('#drawerTitle').text('Add New Slider Images');
                $('#drawerButtonText').text('Save Sliders');
            }

            openGlobalDrawer('slider-drawer', 'slider-overlay');
        }

        function sliderImageEdit(id) {
            let showUrl = "{{ route('slider-images.show', ':id') }}".replace(':id', id);

            $.get(showUrl, function(res) {
                if (res.status === 'success') {
                    openSliderDrawer('edit', res.slider);
                } else {
                    Swal.fire('Error', res.message || 'Slider image not found.', 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Failed to communicate with server.', 'error');
            });
        }

        function saveForm() {
            if (isSaving) return;

            let id = $('#slider_id').val();

            if (!id && selectedFiles.length === 0) {
                Swal.fire('Warning', 'Please select at least one image.', 'warning');
                return;
            }

            isSaving = true;
            $('#saveBtn').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
            $('#drawerButtonText').text('Saving...');

            let formData = new FormData();
            for (const file of selectedFiles) {
                formData.append('images[]', file);
            }
            formData.append('is_active', $('#is_active').val());

            let url = id
                ? "{{ route('slider-images.update', ':id') }}".replace(':id', id)
                : "{{ route('slider-images.store') }}";
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
                    $('#drawerButtonText').text(id ? 'Update Slider' : 'Save Sliders');

                    if (res.status === 'success' || res.status === true) {
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: res.message || 'Slider images saved successfully',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                        closeGlobalDrawer('slider-drawer', 'slider-overlay');
                        $('#sliderImageTable').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message || 'Something went wrong', 'error');
                        $('#drawerButtonText').text(id ? 'Update Slider' : 'Save Sliders');
                    }
                },
                error: function(xhr) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#drawerButtonText').text(id ? 'Update Slider' : 'Save Sliders');

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

        function removeSliderImage(id) {
            Swal.fire({
                title: 'Remove this slider image?',
                text: 'This image will be soft deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, remove it!'
            }).then((r) => {
                if (!r.isConfirmed) return;

                let deleteUrl = "{{ route('slider-images.destroy', ':id') }}".replace(':id', id);

                $.post(deleteUrl, {
                    _method: 'DELETE',
                }, function(res) {
                    if (res.status === 'success' || res.status === true) {
                        Swal.fire('Removed!', res.message || 'Slider image has been removed.', 'success');
                        closeGlobalDrawer('slider-drawer', 'slider-overlay');
                        $('#sliderImageTable').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message || 'Removal failed.', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Failed to communicate with server.', 'error');
                });
            });
        }

        function sliderImageDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This slider image will be soft deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, delete it!'
            }).then((r) => {
                if (r.isConfirmed) {
                    let deleteUrl = "{{ route('slider-images.destroy', ':id') }}".replace(':id', id);

                    $.post(deleteUrl, {
                        _method: 'DELETE',
                    }, function(res) {
                        if (res.status === 'success' || res.status === true) {
                            Swal.fire('Deleted!', res.message || 'Slider image has been deleted.', 'success');
                            $('#sliderImageTable').DataTable().ajax.reload(null, false);
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

            $('.dt-filter-sliderImageTable').trigger('change');
        });

        function initSliderSortable() {
            const tbody = document.querySelector('#sliderImageTable tbody');
            if (!tbody || typeof Sortable === 'undefined') return;

            if (tbody._sortableInstance) {
                tbody._sortableInstance.destroy();
            }

            tbody._sortableInstance = Sortable.create(tbody, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                filter: 'button, a, input, select',
                onEnd: function() {
                    const table = $('#sliderImageTable').DataTable();
                    const pageStart = table.page.info().start;
                    const order = Array.from(tbody.querySelectorAll('tr'))
                        .map(tr => table.row(tr).data()?.id)
                        .filter(id => id !== undefined);

                    if (order.length === 0) return;

                    $.post("{{ route('slider-images.reorder') }}", { order: order, start: pageStart }, function(res) {
                        if (res.status === 'success' || res.status === true) {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: res.message || 'Slider order updated',
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                            });
                        } else {
                            Swal.fire('Error', res.message || 'Order update failed.', 'error');
                        }
                        // Sync DataTables internal data with the new order
                        $('#sliderImageTable').DataTable().ajax.reload(null, false);
                    }).fail(function() {
                        Swal.fire('Error', 'Failed to update order.', 'error');
                        $('#sliderImageTable').DataTable().ajax.reload(null, false);
                    });
                }
            });
        }

        $(document).ready(function() {
            $(document).on('click', '#btnAddSlider', function(e) {
                e.preventDefault();
                openSliderDrawer('add');
            });

            // Enable drag & drop reorder on the slider table (re-init on every draw)
            $('#sliderImageTable').on('draw.dt', initSliderSortable);
            initSliderSortable();
        });
    </script>
    @endpush
</x-app-layout>
