<x-app-layout>

    <div class="p-4 space-y-4">

        {{-- Welcome Banner --}}
        <div
            class=" rounded-xl shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gradient-to-r from-[#047354] to-emerald-600">
                    Welcome back, {{ Auth::user()->name }}! 👋
                </h1>
                <p class="text-emerald-900 text-sm mt-1">
                    Here's what's happening with your content today — {{ now()->format('l, d M Y') }}.
                </p>
            </div>
           
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">

            <a href="{{ route('packages.index') }}"
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3 hover:border-emerald-300 hover:shadow transition-all duration-200 group">
                <div
                    class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-100 transition-colors">
                    <i class="fa-solid fa-suitcase-rolling text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800 leading-none">{{ $stats['packages'] }}</p>
                    <p class="text-xs text-gray-500 font-medium mt-1">Packages</p>
                </div>
            </a>

            <a href="{{ route('flights.index') }}"
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3 hover:border-emerald-300 hover:shadow transition-all duration-200 group">
                <div
                    class="w-11 h-11 rounded-lg bg-sky-50 flex items-center justify-center flex-shrink-0 group-hover:bg-sky-100 transition-colors">
                    <i class="fa-solid fa-plane text-sky-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800 leading-none">{{ $stats['flights'] }}</p>
                    <p class="text-xs text-gray-500 font-medium mt-1">Flights</p>
                </div>
            </a>

            <a href="{{ route('slider-images.index') }}"
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3 hover:border-emerald-300 hover:shadow transition-all duration-200 group">
                <div
                    class="w-11 h-11 rounded-lg bg-violet-50 flex items-center justify-center flex-shrink-0 group-hover:bg-violet-100 transition-colors">
                    <i class="fa-solid fa-images text-violet-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800 leading-none">{{ $stats['sliders'] }}</p>
                    <p class="text-xs text-gray-500 font-medium mt-1">Sliders</p>
                </div>
            </a>

            <a href="{{ route('youtube-videos.index') }}"
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3 hover:border-emerald-300 hover:shadow transition-all duration-200 group">
                <div
                    class="w-11 h-11 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 transition-colors">
                    <i class="fa-brands fa-youtube text-red-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800 leading-none">{{ $stats['videos'] }}</p>
                    <p class="text-xs text-gray-500 font-medium mt-1">Videos</p>
                </div>
            </a>

            <a href="{{ route('icons.index') }}"
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3 hover:border-emerald-300 hover:shadow transition-all duration-200 group">
                <div
                    class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 transition-colors">
                    <i class="fa-solid fa-icons text-amber-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800 leading-none">{{ $stats['icons'] }}</p>
                    <p class="text-xs text-gray-500 font-medium mt-1">Icons</p>
                </div>
            </a>

        </div>

        {{-- Recent Items --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Recent Flights --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-gray-50/50">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-plane text-sky-600"></i>
                        <span class="font-bold text-gray-800 text-sm">Recent Flights</span>
                    </div>
                    <a href="{{ route('flights.index') }}"
                        class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">View all →</a>
                </div>

                @if ($recentFlights->isEmpty())
                    <div class="p-8 text-center">
                        <i class="fa-solid fa-plane text-3xl text-gray-300 mb-2"></i>
                        <p class="text-sm text-gray-400">No flights yet.</p>
                        <a href="{{ route('flights.index') }}"
                            class="inline-block mt-2 text-xs font-semibold text-emerald-700 hover:underline">Add your
                            first flight</a>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($recentFlights as $flight)
                            <li class="flex items-center gap-3 px-5 py-3">
                                @if ($flight->airline_logo_url)
                                    <img src="{{ $flight->airline_logo_url }}"
                                        class="w-9 h-9 rounded-lg object-contain ring-1 ring-gray-200 bg-white flex-shrink-0"
                                        alt="{{ $flight->airline_name }}">
                                @else
                                    <span
                                        class="w-9 h-9 rounded-lg bg-gray-100 ring-1 ring-gray-200 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-plane text-gray-400 text-sm"></i>
                                    </span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $flight->airline_name }}
                                        @if ($flight->flight_number)
                                            <span class="text-xs text-gray-400 font-normal">{{ $flight->flight_number }}</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $flight->departure_airport ?? '—' }} <i
                                            class="fa-solid fa-arrow-right-long text-[10px]"></i>
                                        {{ $flight->arrival_airport ?? '—' }}
                                        @if ($flight->departure_at)
                                            · {{ $flight->departure_at->format('d M Y, H:i') }}
                                        @endif
                                    </p>
                                </div>
                                @if ($flight->is_active)
                                    <span
                                        class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 flex-shrink-0">Active</span>
                                @else
                                    <span
                                        class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-600 flex-shrink-0">Inactive</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Recent Packages --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-gray-50/50">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-suitcase-rolling text-emerald-600"></i>
                        <span class="font-bold text-gray-800 text-sm">Recent Packages</span>
                    </div>
                    <a href="{{ route('packages.index') }}"
                        class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">View all →</a>
                </div>

                @if ($recentPackages->isEmpty())
                    <div class="p-8 text-center">
                        <i class="fa-solid fa-suitcase-rolling text-3xl text-gray-300 mb-2"></i>
                        <p class="text-sm text-gray-400">No packages yet.</p>
                        <a href="{{ route('packages.index') }}"
                            class="inline-block mt-2 text-xs font-semibold text-emerald-700 hover:underline">Add your
                            first package</a>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($recentPackages as $package)
                            <li class="flex items-center gap-3 px-5 py-3">
                                @if ($package->thumbnail_url)
                                    <img src="{{ $package->thumbnail_url }}"
                                        class="w-9 h-9 rounded-lg object-cover ring-1 ring-gray-200 bg-white flex-shrink-0"
                                        alt="{{ $package->title }}">
                                @else
                                    <span
                                        class="w-9 h-9 rounded-lg bg-gray-100 ring-1 ring-gray-200 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-image text-gray-400 text-sm"></i>
                                    </span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $package->title }}</p>
                                    <p class="text-xs text-gray-500">
                                        @if ($package->duration_days)
                                            {{ $package->duration_days }} days
                                        @endif
                                        @if ($package->price)
                                            · ৳{{ number_format((float) $package->price, 0) }}
                                        @endif
                                    </p>
                                </div>
                                @if ($package->is_active)
                                    <span
                                        class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 flex-shrink-0">Active</span>
                                @else
                                    <span
                                        class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-600 flex-shrink-0">Inactive</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>

    </div>

</x-app-layout>

