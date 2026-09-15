<x-app-layout>

    <div class="p-4">
        <div
            class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            {{-- Status Filter --}}
            <div class="flex flex-col w-full md:w-1/3">
                <x-form-select label="Status" id="filter_status" class="dt-filter-iconTable">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>

            {{-- Reset Button --}}
            <div class="w-full md:w-auto flex items-end">
                <button id="resetFilters"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800
                   rounded-lg transition active:scale-95">
                    Reset
                </button>
            </div>
        </div>

        {{-- REUSABLE DATA-TABLE COMPONENT --}}
        <x-data-table id="iconTable" title="Icon Library" icon="fa-solid fa-icons" buttonId="btnAddIcon"
            buttonText="Add New Icon"
            :columns="['Icon / Name', 'Class', 'Keywords', 'Sort', 'Status', 'Created At', 'Action']"
            :ajaxUrl="route('icons.dataTable')"
            :dtColumns="[
                ['data' => 'name'],
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

    {{-- DRAWER COMPONENT --}}
    <x-drawer id="icon-drawer" overlayId="icon-overlay" title="Add New Icon" submitOnClick="saveForm()">
        <form id="iconForm">
            <input type="hidden" name="id" id="icon_id">

            {{-- Name --}}
            <div class="mb-4 animate-fade" style="animation-delay: 150ms;">
                <x-form-input label="Name" name="name" id="name" placeholder="Hotel" :required="true" />
            </div>

            {{-- Class + Live Preview --}}
            <div class="mb-4 animate-fade" style="animation-delay: 200ms;">
                <x-form-input label="Font Awesome Class" name="class" id="class"
                    placeholder="fa-solid fa-hotel" :required="true" oninput="previewIconClass()" />
                <p class="mt-1 text-xs text-gray-500">
                    e.g. <code class="bg-gray-100 px-1 rounded">fa-solid fa-hotel</code>,
                    <code class="bg-gray-100 px-1 rounded">fa-solid fa-plane</code>
                </p>

                {{-- Preview --}}
                <div class="mt-3 flex items-center gap-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
                    <span class="text-xs font-semibold text-gray-500">Preview:</span>
                    <i id="iconPreview" class="fa-solid fa-icons text-2xl text-emerald-600"></i>
                </div>
            </div>

            {{-- Keywords --}}
            <div class="mb-4 animate-fade" style="animation-delay: 250ms;">
                <x-form-input label="Keywords" name="keywords" id="keywords"
                    placeholder="hotel, room, stay, accommodation" />
                <p class="mt-1 text-xs text-gray-500">Comma separated — used for searching inside the picker.</p>
            </div>

            {{-- Status --}}
            <div class="mb-4 animate-fade" style="animation-delay: 300ms;">
                <x-form-select label="Status" name="is_active" id="is_active">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </x-form-select>
            </div>
        </form>
    </x-drawer>
