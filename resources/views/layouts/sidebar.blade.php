<div class="nav-tooltip" id="navTooltip"></div>
<aside id="sidebar" class="w-48 bg-white border-r border-gray-200 flex flex-col flex-shrink-0 z-30 overflow-hidden">
    <!-- Logo -->
    <div class="h-[60px] bg-[#047354] flex items-center justify-between px-3.5 border-b border-blue-300 flex-shrink-0">
        <div class="flex items-center gap-2 overflow-hidden">
            <!-- A icon circle matching Alwaqiah Hajj Kafel -->
            <div
                class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-700 to-emerald-800 flex items-center justify-center flex-shrink-0 shadow-sm">
                <span class="text-white font-bold text-sm leading-none">A</span>
            </div>
            <span class="logo-text font-semibold text-gray-50 text-[20px] whitespace-nowrap">Alwaqiah</span>
        </div>
    </div>

    <!-- Search Box -->
    <div id="searchBox" class="px-2.5 py-1.5 border-b border-gray-100 flex-shrink-0">
        <div
            class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3  focus-within:border-blue-400 focus-within:ring-1 focus-within:ring-blue-100 transition-all">
            <i class="fas fa-search text-gray-50 text-xs flex-shrink-0"></i>
            <input id="sidebarSearch" type="text" placeholder="Search menus..."
                class="bg-transparent border-0 outline-none focus:outline-none focus:ring-0 text-[13px] text-gray-700 placeholder-gray-400 w-full" />
            <button id="searchClear" class="hidden text-gray-50 hover:text-gray-500 transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <p id="searchEmptyMsg" class="hidden text-[11px] text-gray-400 mt-1.5 px-1">No matching items found</p>
    </div>

    <!-- Navigation -->
    <nav id="sideNav" class="flex-1 overflow-y-auto overflow-x-hidden py-1.5 px-2">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
            class=" nav-item flex items-center gap-2.5 px-2 py-1 rounded-lg text-sm font-medium transition-colors duration-150 mb-1 
    {{ request()->routeIs('dashboard') ? 'bg-[#047354] text-white active' : 'text-gray-500 ' }}"
            data-label="Dashboard">
            <i class="fas fa-th-large w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label font-semibold">Dashboard</span>
        </a>

        <!-- YouTube Videos -->
        <a href="{{ route('youtube-videos.index') }}"
            class="nav-item flex items-center gap-2.5 px-2 py-1 rounded-lg text-gray-500  text-sm font-medium transition-colors duration-150 mb-1 
    {{ request()->routeIs('youtube-videos.index') ? 'bg-[#047354] text-white active' : 'text-gray-500 ' }}"
            data-label="Videos">
            <i class="fa-brands fa-youtube w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label font-semibold">Videos</span>
        </a>

        <!-- Sliders -->
        <a href="{{ route('slider-images.index') }}"
            class="nav-item flex items-center gap-2.5 px-2 py-1 rounded-lg text-gray-500  text-sm font-medium transition-colors duration-150 mb-1 
    {{ request()->routeIs('slider-images.index') ? 'bg-[#047354] text-white active' : 'text-gray-500 ' }}"
            data-label="Sliders">
            <i class="fa-solid fa-images w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label font-semibold">Sliders</span>
        </a>

        <!-- Packages -->
        <a href="{{ route('packages.index') }}"
            class="nav-item flex items-center gap-2.5 px-2 py-1 rounded-lg text-gray-500  text-sm font-medium transition-colors duration-150 mb-1 
    {{ request()->routeIs('packages.index') ? 'bg-[#047354] text-white active' : 'text-gray-500 ' }}"
            data-label="Packages">
            <i class="fa-solid fa-suitcase-rolling w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label font-semibold">Packages</span>
        </a>

        <!-- Flights -->
        <a href="{{ route('flights.index') }}"
            class="nav-item flex items-center gap-2.5 px-2 py-1 rounded-lg text-gray-500  text-sm font-medium transition-colors duration-150 mb-1 
    {{ request()->routeIs('flights.index') ? 'bg-[#047354] text-white active' : 'text-gray-500 ' }}"
            data-label="Flights">
            <i class="fa-solid fa-plane w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label font-semibold">Flights</span>
        </a>

     

        <!-- FAQs -->
        <a href="{{ route('faqs.index') }}"
            class="nav-item flex items-center gap-2.5 px-2 py-1 rounded-lg text-gray-500  text-sm font-medium transition-colors duration-150 mb-1 
    {{ request()->routeIs('faqs.index') ? 'bg-[#047354] text-white active' : 'text-gray-500 ' }}"
            data-label="FAQs">
            <i class="fa-solid fa-circle-question w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label font-semibold">FAQs</span>
        </a>

           <!-- Icons -->
        <a href="{{ route('icons.index') }}"
            class="nav-item flex items-center gap-2.5 px-2 py-1 rounded-lg text-gray-500  text-sm font-medium transition-colors duration-150 mb-1 
    {{ request()->routeIs('icons.index') ? 'bg-[#047354] text-white active' : 'text-gray-500 ' }}"
            data-label="Icons">
            <i class="fa-solid fa-icons w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label font-semibold">Icons</span>
        </a>

    </nav>
</aside>
