<x-app-layout>

    <div class="p-4">
        <div
            class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            {{-- Status Filter --}}
            <div class="flex flex-col w-full md:w-1/3">
                <x-form-select label="Status" id="filter_status" class="dt-filter-youtubeVideoTable">
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
        <x-data-table id="youtubeVideoTable" title="YouTube Videos" icon="fa-brands fa-youtube" buttonId="btnAddVideo"
            buttonText="Add New Video" :columns="['Thumbnail', 'Title', 'Video ID', 'Sort', 'Status', 'Published At', 'Created At', 'Action']" :ajaxUrl="route('youtube-videos.dataTable')" :dtColumns="[
                ['data' => 'thumbnail', 'orderable' => false, 'searchable' => false],
                ['data' => 'title'],
                ['data' => 'youtube_video_id'],
                ['data' => 'sort_order'],
                ['data' => 'is_active'],
                ['data' => 'published_at'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]" :filters="[
                'is_active' => '#filter_status',
            ]"
            :exportButtons="true" />
    </div>

    {{-- DRAWER COMPONENT --}}
    <x-drawer id="youtube-video-drawer" overlayId="youtube-video-overlay" title="Add New Video"
        submitOnClick="saveForm()">
        <form id="youtubeVideoForm">
            <input type="hidden" name="id" id="video_id">

            {{-- Title --}}
            <div class="mb-4 animate-fade" style="animation-delay: 150ms;">
                <x-form-input label="Title" name="title" id="title" placeholder="Video Title" :required="true"
                    oninput="autoSlug()" />
            </div>

            {{-- YouTube URL --}}
            <div class="mb-4 animate-fade" style="animation-delay: 200ms;">
                <x-form-input label="YouTube URL" name="youtube_url" id="youtube_url" :required="true"
                    placeholder="https://www.youtube.com/watch?v=..." oninput="autoVideoInfo()" />
            </div>

            {{-- Video ID & Slug (auto) --}}
            <div class="grid grid-cols-2 gap-3 mb-4 animate-fade" style="animation-delay: 250ms;">
                <x-form-input label="Video ID (Auto)" name="youtube_video_id" id="youtube_video_id"
                    placeholder="Auto from URL" />
                <x-form-input label="Slug (Auto)" name="slug" id="slug" placeholder="Auto from Title" />
            </div>

            {{-- Thumbnail (auto) --}}
            <div class="mb-4 animate-fade" style="animation-delay: 300ms;">
                <x-form-input label="Thumbnail URL (Auto)" name="thumbnail" id="thumbnail"
                    placeholder="Auto from Video" />
            </div>

            {{-- Status (default: Active, editable) --}}
            <div class="mb-4 animate-fade" style="animation-delay: 350ms;">
                <x-form-select label="Status" name="is_active" id="is_active">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>

            {{-- Description --}}
            <div class="mb-4 animate-fade" style="animation-delay: 400ms;">
                <x-form-textarea label="Description" name="description" id="description"
                    placeholder="Short description (optional)" />
            </div>
        </form>
    </x-drawer>

    @push('scripts')
        <style>
            #youtubeVideoTable tbody tr { cursor: grab; }
            #youtubeVideoTable tbody tr:active { cursor: grabbing; }
            #youtubeVideoTable tbody tr.sortable-ghost { opacity: 0.4; background: #ecfdf5; }
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

            // Extract YouTube video ID from any YouTube URL format
            function extractYouTubeId(url) {
                let match = url.match(
                    /(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_\-]+)/i);
                return match ? match[1] : null;
            }

            // Auto-fill Video ID + Thumbnail from YouTube URL
            function autoVideoInfo() {
                let url = $('#youtube_url').val().trim();
                if (!url) return;

                let videoId = extractYouTubeId(url);
                if (videoId) {
                    $('#youtube_video_id').val(videoId);
                    $('#thumbnail').val('https://img.youtube.com/vi/' + videoId + '/hqdefault.jpg');
                }
            }

            function openVideoDrawer(mode, video) {
                $('#youtubeVideoForm')[0].reset();
                $('#video_id').val('');

                if (mode === 'edit' && video) {
                    $('#video_id').val(video.id);
                    $('#title').val(video.title ?? '');
                    $('#youtube_url').val(video.youtube_url ?? '');
                    $('#youtube_video_id').val(video.youtube_video_id ?? '');
                    $('#slug').val(video.slug ?? '');
                    $('#thumbnail').val(video.thumbnail ?? '');
                    $('#description').val(video.description ?? '');
                    $('#is_active').val(video.is_active ? '1' : '0');

                    $('#drawerTitle').text('Edit Video');
                    $('#drawerButtonText').text('Update Video');
                } else {
                    $('#is_active').val('1');
                    $('#drawerTitle').text('Add New Video');
                    $('#drawerButtonText').text('Save Video');
                }

                openGlobalDrawer('youtube-video-drawer', 'youtube-video-overlay');
            }

            function youtubeVideoEdit(id) {
                let showUrl = "{{ route('youtube-videos.show', ':id') }}".replace(':id', id);

                $.get(showUrl, function(res) {
                    if (res.status === 'success') {
                        openVideoDrawer('edit', res.video);
                    } else {
                        Swal.fire('Error', res.message || 'Video not found.', 'error');
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

                let id = $('#video_id').val();
                let url = id ?
                    "{{ route('youtube-videos.update', ':id') }}".replace(':id', id) :
                    "{{ route('youtube-videos.store') }}";
                let method = id ? 'PUT' : 'POST';

                $.post(url, $('#youtubeVideoForm').serialize() + '&_method=' + method, function(res) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#drawerButtonText').text(id ? 'Update Video' : 'Save Video');

                    if (res.status === 'success' || res.status === true) {
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: res.message || 'Video saved successfully',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                        closeGlobalDrawer('youtube-video-drawer', 'youtube-video-overlay');
                        $('#youtubeVideoTable').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message || 'Something went wrong', 'error');
                        $('#drawerButtonText').text(id ? 'Update Video' : 'Save Video');
                    }
                }).fail(function(xhr) {
                    isSaving = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#drawerButtonText').text(id ? 'Update Video' : 'Save Video');

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

            function youtubeVideoDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This video will be soft deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#4b5563',
                    confirmButtonText: 'Yes, delete it!'
                }).then((r) => {
                    if (r.isConfirmed) {
                        let deleteUrl = "{{ route('youtube-videos.destroy', ':id') }}".replace(':id', id);

                        $.post(deleteUrl, {
                            _method: 'DELETE',
                        }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire('Deleted!', res.message || 'Video has been deleted.', 'success');
                                $('#youtubeVideoTable').DataTable().ajax.reload(null, false);
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

                $('.dt-filter-youtubeVideoTable').trigger('change');
            });

            function initVideoSortable() {
                const tbody = document.querySelector('#youtubeVideoTable tbody');
                if (!tbody || typeof Sortable === 'undefined') return;

                if (tbody._sortableInstance) {
                    tbody._sortableInstance.destroy();
                }

                tbody._sortableInstance = Sortable.create(tbody, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    filter: 'button, a, input, select',
                    onEnd: function() {
                        const table = $('#youtubeVideoTable').DataTable();
                        const pageStart = table.page.info().start;
                        const order = Array.from(tbody.querySelectorAll('tr'))
                            .map(tr => table.row(tr).data()?.id)
                            .filter(id => id !== undefined);

                        if (order.length === 0) return;

                        $.post("{{ route('youtube-videos.reorder') }}", { order: order, start: pageStart }, function(res) {
                            if (res.status === 'success' || res.status === true) {
                                Swal.fire({
                                    toast: true,
                                    icon: 'success',
                                    title: res.message || 'Video order updated',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                });
                            } else {
                                Swal.fire('Error', res.message || 'Order update failed.', 'error');
                            }
                            // Sync DataTables internal data with the new order
                            $('#youtubeVideoTable').DataTable().ajax.reload(null, false);
                        }).fail(function() {
                            Swal.fire('Error', 'Failed to update order.', 'error');
                            $('#youtubeVideoTable').DataTable().ajax.reload(null, false);
                        });
                    }
                });
            }

            $(document).ready(function() {
                $(document).on('click', '#btnAddVideo', function(e) {
                    e.preventDefault();
                    openVideoDrawer('add');
                });

                // Enable drag & drop reorder on the video table (re-init on every draw)
                $('#youtubeVideoTable').on('draw.dt', initVideoSortable);
                initVideoSortable();
            });
        </script>
    @endpush
</x-app-layout>
