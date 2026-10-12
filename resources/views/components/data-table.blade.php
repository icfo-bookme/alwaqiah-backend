@props([
    'id' => 'customTable',
    'title' => 'Management',
    'icon' => 'fa-solid fa-list',
    'buttonId' => 'btnAddNew',
    'buttonText' => 'Add New',
    'columns' => [],
    'ajaxUrl' => '',
    'dtColumns' => [],
    'exportButtons' => true,
    'filters' => [],
    'reorderable' => false,
])

<div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden">
    {{-- TABLE HEADER --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50/50 gap-4">
        <div class="flex items-center space-x-2.5">
            <i class="{{ $icon }} text-blue-600 text-lg"></i>
            <span class="font-bold text-gray-800 tracking-tight text-base">{{ $title }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-3 justify-end">

            @if ($buttonId)
                <button id="{{ $buttonId }}"
                    class="bg-[#047354] hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm flex items-center gap-2 transition-all duration-200 text-sm font-medium whitespace-nowrap active:scale-95">
                    <i class="fa fa-plus-circle"></i> {{ $buttonText }}
                </button>
            @endif
        </div>
    </div>

    {{-- TABLE BODY --}}
    <div class="p-6">
        <div class="overflow-x-auto">
            <table id="{{ $id }}" class="w-full border-collapse rounded-lg text-sm text-gray-700">
                <thead>
                    <tr>
                        @if ($reorderable)
                            <th class="drag-handle-column" aria-label="Drag to reorder" title="Drag to reorder">↕</th>
                        @endif
                        @foreach ($columns as $column)
                            <th
                                class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider
                                       {{ $column === 'Action' || $column === 'Actions' ? 'text-center' : '' }}">
                                {{ $column }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            const tableId = "{{ $id }}";
            const reorderable = @json($reorderable);
            const tableColumns = {!! json_encode($dtColumns) !!};
            if (reorderable) {
                tableColumns.unshift({
                    data: null,
                    defaultContent: '<span class="drag-handle" title="Drag to reorder" aria-label="Drag to reorder"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>',
                    orderable: false,
                    searchable: false,
                    className: 'drag-handle-column',
                    width: '40px',
                    responsivePriority: 1,
                });
            }
            const exportColumns = (index) => index < tableColumns.length - 1 && (!reorderable || index !== 0);

            function buildAjaxData(filters) {
                return function(d) {
                    Object.keys(filters || {}).forEach(key => {
                        let selector = filters[key];
                        let value = $(selector).val();
                        d[key] = value;
                    });
                }
            }
            let tableButtons = [];

            @if ($exportButtons)
                tableButtons = [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel mr-1.5 text-green-500"></i> Excel',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm',
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa-solid fa-file-pdf mr-1.5 text-red-500"></i> PDF',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm',
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa-solid fa-print mr-1.5 text-gray-500"></i> Print',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm',
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'colvis',
                        columns: reorderable ? ':not(:first-child)' : undefined,
                        text: '<i class="fa-solid fa-table-columns mr-1.5 text-blue-500"></i> Columns',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm'
                    },
                ];
            @endif

            const table = $('#{{ $id }}').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! $ajaxUrl !!}",
                    data: buildAjaxData(@json($filters ?? []))
                },
                columns: tableColumns,
                dom: '<"flex flex-col md:flex-row items-center justify-between gap-4 mb-4"lBf>rt<"flex flex-col md:flex-row items-center justify-between gap-4 mt-4"ip>',
                buttons: tableButtons,
                ScrollX: true,
                responsive: true,
                order: [
                    [reorderable ? 1 : 0, 'desc']
                ],
                language: {
                    search: "",
                    searchPlaceholder: "🔎︎ Search records...",
                    lengthMenu: "Show _MENU_ entries"
                }
            });

            $(document).on('change', '.dt-filter-' + tableId, function() {
                table.ajax.reload();
            });

            @if ($exportButtons)
                setTimeout(function() {
                    if (table.buttons().container().length) {
                        table.buttons().container().appendTo('#{{ $id }}_buttons_container');
                        $('.dt-button').removeClass('dt-button');
                    }
                }, 50);
            @endif
        });
    </script>
@endpush

<style>
    #{{ $id }} .drag-handle-column {
        width: 40px;
        text-align: center;
        white-space: nowrap;
    }

    #{{ $id }} thead th.drag-handle-column {
        font-size: 18px;
        font-weight: 500;
        line-height: 1;
    }

    #{{ $id }} .drag-handle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border: 1px solid #dce9e3;
        border-radius: 9px;
        background: #f0f7f3;
        color: #54816c;
        font-size: 14px;
        transition: color 150ms ease, background-color 150ms ease, border-color 150ms ease, box-shadow 150ms ease;
        cursor: grab;
        touch-action: none;
        user-select: none;
    }

    #{{ $id }} .drag-handle:hover {
        color: #047354;
        background: #e4f3eb;
        border-color: #9fcbb6;
        box-shadow: 0 2px 5px rgb(4 115 84 / 8%);
    }

    #{{ $id }} .drag-handle:active,
    #{{ $id }} .sortable-chosen .drag-handle {
        color: #047354;
        background: #d1fae5;
        border-color: #6ee7b7;
        cursor: grabbing;
    }

    #{{ $id }} tbody tr:nth-child(even) {
        background-color: #f8fafc !important;
    }

    #{{ $id }} tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }

    #{{ $id }} tbody tr {
        transition: all 0.15s ease-in-out;
        border-bottom: 1px solid #13293f;
    }

    #{{ $id }} tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    #{{ $id }} thead th {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        padding: 12px 16px !important;

    }

    #{{ $id }} tbody td {
        padding: 12px 16px !important;
        color: #334155;
        vertical-align: middle;
        border-bottom: 1px solid #ced5df !important;
    }

    .dataTables_filter input {
        border: 1px solid #e2e8f0;
        padding: 0.45rem 0.85rem;
        border-radius: 0.5rem;
        outline: none;
        font-size: 0.875rem;
        transition: all 0.2s;
        width: 320px;
    }

    .dataTables_filter input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    /* dropdown tyle */
    .dataTables_length select {
        border: 1px solid #e2e8f0;
        padding: 0.4rem 0.9rem !important;
        border-radius: 0.5rem;
        outline: none;
        font-size: 0.875rem;
        -webkit-appearance: none;
        /* Chrome, Safari, Opera */
        -moz-appearance: none;
        /* Firefox */
        appearance: none;
        /* Modern Browsers */
    }

    .dataTables_length select::-ms-expand {
        display: none;
    }

    .dataTables_wrapper .dataTables_buttons {
        display: none !important;
    }
</style>
