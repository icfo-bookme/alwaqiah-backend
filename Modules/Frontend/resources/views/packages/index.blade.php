<x-app-layout>

    <div class="p-4">
        <div
            class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            {{-- Status Filter --}}
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Status" id="filter_status" class="dt-filter-packageTable">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>

            {{-- Package Type Filter --}}
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Package Type" id="filter_type" class="dt-filter-packageTable">
                    <option value="">All Types</option>
                    <option value="hajj">Hajj</option>
                    <option value="umrah">Umrah</option>
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
        <x-data-table id="packageTable" title="Package Management" icon="fa-solid fa-kaaba"
            buttonId="btnAddPackage" buttonText="Add New Package"
            :columns="['Thumbnail', 'Title', 'Type', 'Duration', 'Price', 'Featured', 'Sort', 'Status', 'Created At', 'Action']"
            :ajaxUrl="route('packages.dataTable')"
            :dtColumns="[
                ['data' => 'thumbnail', 'orderable' => false, 'searchable' => false],
                ['data' => 'title'],
                ['data' => 'package_type'],
                ['data' => 'duration_days'],
                ['data' => 'price'],
                ['data' => 'is_featured', 'orderable' => false],
                ['data' => 'sort_order'],
                ['data' => 'is_active'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]" :filters="[
                'is_active' => '#filter_status',
                'package_type' => '#filter_type',
            ]" :exportButtons="true" />
    </div>

    {{-- DRAWER COMPONENT --}}
    <x-drawer id="package-drawer" overlayId="package-overlay" title="Add New Package" maxWidth="max-w-2xl"
        submitOnClick="saveForm()">
        <form id="packageForm">
            <input type="hidden" name="id" id="package_id">

            {{-- Title & Slug (auto) --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 150ms;">
                <x-form-input label="Title" name="title" id="title" placeholder="Package Title"
                    :required="true" oninput="autoSlug()" />
                <x-form-input label="Slug (Auto)" name="slug" id="slug" placeholder="Auto from Title" />
            </div>

            {{-- Duration & Price --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 200ms;">
                <x-form-input label="Duration (Days)" name="duration_days" id="duration_days" type="number"
                    min="1" max="365" step="1" placeholder="e.g. 14" :required="true" />
                <x-form-input label="Price" name="price" id="price" type="number" min="0" step="0.01"
                    placeholder="e.g. 150000" :required="true" />
            </div>

            {{-- Price Label & Package Type --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 250ms;">
                <x-form-input label="Price Label" name="price_label" id="price_label"
                    placeholder="e.g. Per Person" />
                <x-form-select label="Package Type" name="package_type" id="package_type" :placeholder="false">
                    <option value="umrah" selected>Umrah</option>
                    <option value="hajj">Hajj</option>
                </x-form-select>
            </div>

            {{-- Thumbnail Upload --}}
            <div class="mb-4 animate-fade" style="animation-delay: 300ms;">
                <x-form-input label="Thumbnail" name="thumbnail" id="thumbnail" type="file"
                    accept="image/png,image/jpeg,image/webp" onchange="previewPackageThumbnail()" />
            </div>

            {{-- Live Preview --}}
            <div id="packageThumbPreview" class="flex flex-wrap gap-2 mb-4 animate-fade"
                style="animation-delay: 320ms;"></div>

            {{-- Featured & Status --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 350ms;">
                <x-form-select label="Featured" name="is_featured" id="is_featured" :placeholder="false">
                    <option value="0" selected>No</option>
                    <option value="1">Yes</option>
                </x-form-select>
                <x-form-select label="Status" name="is_active" id="is_active" :placeholder="false">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>

            {{-- Short Description --}}
            <div class="mb-4 animate-fade" style="animation-delay: 400ms;">
                <x-form-textarea label="Short Description" name="short_description" id="short_description"
                    placeholder="Short summary (optional)" />
            </div>

            {{-- Description --}}
            <div class="mb-4 animate-fade" style="animation-delay: 450ms;">
                <x-form-textarea label="Description" name="description" id="description" rows="5"
                    placeholder="Full package details (optional)" />
            </div>
        </form>
    </x-drawer>

    {{-- FEATURES DRAWER COMPONENT --}}
    <x-drawer id="features-drawer" overlayId="features-overlay" title="Package Features" maxWidth="max-w-2xl"
        submitBtnId="featureSaveBtn" submitBtnText="Save Features" submitOnClick="saveFeatures()">
        <div class="mb-4 animate-fade">
            <p class="text-sm text-slate-500">
                Managing features for:
                <span id="featurePackageTitle" class="font-semibold text-slate-800"></span>
            </p>
        </div>

        <div class="flex items-center justify-between mb-3">
            <label class="font-semibold text-sm text-slate-700">Feature List</label>
            <button type="button" onclick="addFeatureRow()"
                class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-sm font-medium flex items-center gap-1.5 transition active:scale-95">
                <i class="fa fa-plus-circle"></i> Add Feature
            </button>
        </div>

        <div id="featuresContainer" class="space-y-2"></div>

        <p id="featuresEmpty" class="text-sm text-slate-400 italic mt-2">No features added yet.</p>
    </x-drawer>

    @push('scripts')
        <style>
            #packageTable tbody tr {
                cursor: grab;
            }

            #packageTable tbody tr:active {
                cursor: grabbing;
            }

            #packageTable tbody tr.sortable-ghost {
                opacity: 0.4;
                background: #ecfdf5;
            }
        </style>
        <script>
            let isSaving = false;

            // Auto-generate slug from title (supports Bangla letters)
            function autoSlug() {
                let title = $('#title').val().trim();
                if (!title) {
                    $('#slug').val('');
                    return;
                }

                let slug = title
                    .toLowerCase()
                    .replace(/['"`’‘“”]/g, '')
                    .replace(/[^a-z0-9\u0980-\u09FF]+/g, '-')
                    .replace(/^-+|-+$/g, '');

                $('#slug').val(slug);
            }

            // Preview of the thumbnail file selected by the user
            function previewPackageThumbnail() {
                let input = document.getElementById('thumbnail');
                let preview = document.getElementById('packageThumbPreview');
                preview.innerHTML = '';

                if (!input || !input.files || input.files.length === 0) return;
                if (!input.files[0].type.startsWith('image/')) return;

                let img = document.createElement('img');
                img.src = URL.createObjectURL(input.files[0]);
                img.className = 'h-16 w-24 rounded-md object-cover ring-1 ring-gray-200';
                preview.appendChild(img);
            }

            // Show the thumbnail already stored on the server (edit mode)
            function showExistingThumbnail(url) {
                let preview = document.getElementById('packageThumbPreview');
                preview.innerHTML = '';

                if (!url) return;

                let img = document.createElement('img');
                img.src = url;
                img.className = 'h-16 w-24 rounded-md object-cover ring-1 ring-gray-200';
                preview.appendChild(img);
            }

            function openPackageDrawer(mode, pkg) {
                $('#packageForm')[0].reset();
                $('#package_id').val('');
                document.getElementById('packageThumbPreview').innerHTML = '';

                if (mode === 'edit' && pkg) {
                    $('#package_id').val(pkg.id);
                    $('#title').val(pkg.title ?? '');
                    $('#slug').val(pkg.slug ?? '');
                    $('#duration_days').val(pkg.duration_days ?? '');
                    $('#price').val(pkg.price ?? '');
                    $('#price_label').val(pkg.price_label ?? '');
                    $('#package_type').val(pkg.package_type ?? 'umrah');
                    $('#short_description').val(pkg.short_description ?? '');
                    $('#description').val(pkg.description ?? '');
                    $('#is_featured').val(pkg.is_featured ? '1' : '0');
                    $('#is_active').val(pkg.is_active ? '1' : '0');

                    showExistingThumbnail(pkg.thumbnail_url);

                    $('#drawerTitle').text('Edit Package');
                    $('#drawerButtonText').text('Update Package');
                } else {
                    $('#package_type').val('umrah');
                    $('#is_featured').val('0');
                    $('#is_active').val('1');

                    $('#drawerTitle').text('Add New Package');
                    $('#drawerButtonText').text('Save Package');
                }

                openGlobalDrawer('package-drawer', 'package-overlay');
            }

            function packageEdit(id) {
                let showUrl = "{{ route('packages.show', ':id') }}".replace(':id', id);

                $.get(showUrl, function(res) {
                    if (res.status === 'success') {
                        openPackageDrawer('edit', res.package);
                    } else {
                        Swal.fire('Error', res.message || 'Package not found.', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Failed to communicate with server.', 'error');
                });
            }
            function saveForm() {
                if (isSaving) return;

                let id = $('#package_id').val();
                let url = id
                    ? "{{ route('packages.update', ':id') }}".replace(':id', id)
                    : "{{ route('packages.store') }}";

                let formData = new FormData($('#packageForm')[0]);
                formData.append('_method', id ? 'PUT' : 'POST');

                isSaving = true;
                $('#saveBtn').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
                $('#drawerButtonText').text('Saving...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        isSaving = false;
                        $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        $('#drawerButtonText').text(id ? 'Update Package' : 'Save Package');

                        if (res.status === 'success' || res.status === true) {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: res.message || 'Package saved successfully',
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            });
                            closeGlobalDrawer('package-drawer', 'package-overlay');
                            $('#packageTable').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.message || 'Something went wrong', 'error');
                        }
                    },
                    error: function(xhr) {
                        isSaving = false;
                        $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        $('#drawerButtonText').text(id ? 'Update Package' : 'Save Package');

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
            function packageDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This package will be soft deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#4b5563',
                    confirmButtonText: 'Yes, delete it!'
                }).then((r) => {
                    if (r.isConfirmed) {
                        let deleteUrl = "{{ route('packages.destroy', ':id') }}".replace(':id', id);

                        $.post(deleteUrl, {
                            _method: 'DELETE',
                        }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire({
                                    toast: true,
                                    icon: 'success',
                                    title: res.message || 'Package has been deleted.',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                });
                                $('#packageTable').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', res.message || 'Deletion failed.', 'error');
                            }
                        }).fail(function() {
                            Swal.fire('Error', 'Failed to communicate with server.', 'error');
                        });
                    }
                });
            }

            /* ==================== Package Features ==================== */
            let featurePackageId = null;
            let isSavingFeatures = false;

            function escapeHtml(value) {
                return $('<div>').text(value ?? '').html();
            }

            function featureRowTemplate(feature) {
                feature = feature || {};

                const id = feature.id ?? '';
                const icon = escapeHtml(feature.icon);
                const title = escapeHtml(feature.title);

                return `
                    <div class="feature-row flex items-center gap-2 animate-fade" data-id="${id}">
                        <input type="text"
                            class="feature-icon w-1/3 border border-slate-300 rounded-md p-2 bg-white text-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                            placeholder="Icon (fa-solid fa-check)" value="${icon}">
                        <input type="text"
                            class="feature-title flex-1 border border-slate-300 rounded-md p-2 bg-white text-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                            placeholder="Feature title" value="${title}">
                        <button type="button" onclick="removeFeatureRow(this)" title="Remove feature"
                            class="bg-red-500 hover:bg-red-600 text-white px-2.5 py-2 rounded-md transition active:scale-95">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>`;
            }

            function refreshFeaturesEmpty() {
                $('#featuresEmpty').toggle($('#featuresContainer .feature-row').length === 0);
            }

            function addFeatureRow(feature) {
                $('#featuresContainer').append(featureRowTemplate(feature));
                refreshFeaturesEmpty();
            }

            function removeFeatureRow(button) {
                $(button).closest('.feature-row').remove();
                refreshFeaturesEmpty();
            }

            function packageFeatures(id) {
                let url = "{{ route('packages.features', ':id') }}".replace(':id', id);

                $.get(url, function(res) {
                    if (res.status !== 'success') {
                        Swal.fire('Error', res.message || 'Package not found.', 'error');
                        return;
                    }

                    featurePackageId = id;
                    $('#featurePackageTitle').text(res.package?.title ?? '');
                    $('#featuresContainer').empty();

                    (res.features || []).forEach(function(feature) {
                        addFeatureRow(feature);
                    });
                    refreshFeaturesEmpty();

                    openGlobalDrawer('features-drawer', 'features-overlay');
                }).fail(function() {
                    Swal.fire('Error', 'Failed to communicate with server.', 'error');
                });
            }

            function saveFeatures() {
                if (isSavingFeatures || !featurePackageId) return;

                let features = [];
                $('#featuresContainer .feature-row').each(function() {
                    let title = $(this).find('.feature-title').val().trim();
                    if (!title) return;

                    features.push({
                        id: $(this).data('id') || '',
                        icon: $(this).find('.feature-icon').val().trim(),
                        title: title,
                    });
                });

                let $button = $('#featureSaveBtn');
                let $label = $('#features-drawer').find('#drawerButtonText').first();

                isSavingFeatures = true;
                $button.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
                $label.text('Saving...');

                let url = "{{ route('packages.saveFeatures', ':id') }}".replace(':id', featurePackageId);

                $.post(url, {
                    features: features
                }, function(res) {
                    isSavingFeatures = false;
                    $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $label.text('Save Features');

                    if (res.status === 'success' || res.status === true) {
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: res.message || 'Features saved successfully',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                        closeGlobalDrawer('features-drawer', 'features-overlay');
                    } else {
                        Swal.fire('Error', res.message || 'Something went wrong', 'error');
                    }
                }).fail(function(xhr) {
                    isSavingFeatures = false;
                    $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $label.text('Save Features');

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

            $('#resetFilters').on('click', function() {
                $('#filter_status').val('');
                $('#filter_type').val('');

                $('.dt-filter-packageTable').trigger('change');
            });

            function initPackageSortable() {
                const tbody = document.querySelector('#packageTable tbody');
                if (!tbody || typeof Sortable === 'undefined') return;

                if (tbody._sortableInstance) {
                    tbody._sortableInstance.destroy();
                }

                tbody._sortableInstance = Sortable.create(tbody, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    filter: 'button, a, input, select',
                    onEnd: function() {
                        const table = $('#packageTable').DataTable();
                        const order = Array.from(tbody.querySelectorAll('tr'))
                            .map(tr => table.row(tr).data()?.id)
                            .filter(id => id !== undefined);

                        if (order.length === 0) return;

                        $.post("{{ route('packages.reorder') }}", { order: order }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire({
                                    toast: true,
                                    icon: 'success',
                                    title: res.message || 'Package order updated',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                });
                            } else {
                                Swal.fire('Error', res.message || 'Order update failed.', 'error');
                            }
                            // Sync DataTables internal data with the new order
                            $('#packageTable').DataTable().ajax.reload(null, false);
                        }).fail(function() {
                            Swal.fire('Error', 'Failed to update order.', 'error');
                            $('#packageTable').DataTable().ajax.reload(null, false);
                        });
                    }
                });
            }

            $(document).ready(function() {
                $(document).on('click', '#btnAddPackage', function(e) {
                    e.preventDefault();
                    openPackageDrawer('add');
                });

                // Enable drag & drop reorder on the package table (re-init on every draw)
                $('#packageTable').on('draw.dt', initPackageSortable);
                initPackageSortable();
            });
        </script>
    @endpush
</x-app-layout>
