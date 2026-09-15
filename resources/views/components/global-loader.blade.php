{{-- Professional global loader — covers only the main content area (never the sidebar) --}}
<div id="global-loader"
    class="absolute inset-0 z-[80] flex items-center justify-center bg-white backdrop-blur-sm opacity-100 pointer-events-auto transition-opacity duration-300 ease-in-out">
    <div class="flex flex-col items-center gap-5 select-none">

        {{-- Brand spinner: emerald ring with the brand initial --}}
        <div class="relative w-14 h-14">
            <div class="absolute inset-0 rounded-full border-[3px] border-emerald-100"></div>
            <div
                class="absolute inset-0 rounded-full border-[3px] border-transparent border-t-emerald-700 animate-spin">
            </div>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-emerald-800 font-bold text-lg leading-none">A</span>
            </div>
        </div>

        <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-gray-500">Loading...</p>
    </div>
</div>
