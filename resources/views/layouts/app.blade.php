<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Alwaqiah Hajj Kafel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200..800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />

    @stack('head')
</head>

<body class="bg-[#e4ebf1] font-sans antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Navigation -->
        @include('layouts.navigation')

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 relative flex flex-col">

            <div class="flex-1 w-full">
                @include('components.global-loader')
                {{ $slot }}
            </div>

        </main>
    </div>



    <!-- JS LIBRARIES -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Sidebar JS -->
    <script src="{{ asset('js/sidebar.js') }}"></script>

    <!-- CSRF Setup -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <!-- Global Loader Handling (professional: always fades out, never gets stuck) -->
    <script>
        (function() {
            'use strict';

            const loader = document.getElementById('global-loader');
            if (!loader) return;

            let pendingAjax = 0;

            // DataTables already show their own "processing" overlay — don't double up
            function isDataTableRequest() {
                const hasDataTable = typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.tables().length > 0;
                return hasDataTable ||
                    $('.dataTables_processing:visible').length > 0 ||
                    $('body').hasClass('dt-custom-loading');
            }

            function showLoader() {
                loader.classList.remove('opacity-0', 'pointer-events-none', 'invisible');
                loader.classList.add('opacity-100', 'pointer-events-auto');
            }

            function hideLoader() {
                loader.classList.add('opacity-0', 'pointer-events-none');
                // Fully detach from the page once the fade transition has finished
                window.setTimeout(function() {
                    if (loader.classList.contains('opacity-0')) {
                        loader.classList.add('invisible');
                    }
                }, 320);
            }

            // Show / hide around global AJAX activity (skips DataTable requests)
            $(document).on('ajaxStart', function() {
                if (isDataTableRequest()) return;
                pendingAjax++;
                showLoader();
            });

            $(document).on('ajaxStop', function() {
                if (isDataTableRequest()) return;
                pendingAjax = Math.max(0, pendingAjax - 1);
                if (pendingAjax === 0) hideLoader();
            });

            // Always end the loader once the page has fully loaded.
            // This is the key fix: pages without AJAX (e.g. the Dashboard) now also hide it.
            function hideAfterPageLoad() {
                window.setTimeout(function() {
                    if (pendingAjax === 0) hideLoader();
                }, 250);
            }

            if (document.readyState === 'complete') {
                hideAfterPageLoad();
            } else {
                $(window).on('load', hideAfterPageLoad);
            }

            // Safety net: never let the loader stay visible for more than ~4s
            window.setTimeout(hideLoader, 4000);
        })();
    </script>

    @stack('scripts')

</body>

</html>
