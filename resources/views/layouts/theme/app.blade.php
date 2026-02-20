<!DOCTYPE html>

<html lang="es" class="light">
    <!-- BEGIN: Head -->
    <head>
        <meta charset="utf-8">
        <link href="{{ asset('dist/images/logo.svg')}}" rel="shortcut icon">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Sistema FAstFooD">
        <meta name="keywords" content="ventas,sistema,web,envio,comida,restaurante,ordenes,express,pantalla,táctil">
        <meta name="author" content="khipu.com">
        <title>{{ $title ?? 'Facta SaaS' }}</title>
        <meta name="referrer" content="strict-origin-when-cross-origin">
        <!-- BEGIN: CSS Assets-->
        <link rel="stylesheet" href="{{ asset('dist/css/app.css')}}" />
        <link rel="stylesheet" href="{{ asset('css/all.css')}}" />
        <link rel="stylesheet" href="{{ asset('css/snackbar.min.css')}}" />
        <link rel="stylesheet" href="{{ asset('css/snackbar.min.css')}}" />

        <link rel="stylesheet" href="{{ asset('css/mc-calendar.min.css')}}" />
        <script src="{{ asset('js/mc-calendar.min.js') }}"></script>

        <link rel="stylesheet" href="{{ asset('css/apexcharts.css') }}" />
        <script src="{{ asset('js/apexcharts.js') }}"></script>


        {{-- estilos personalizados  --}}
        <style>
            .image-fit>img{
                object-fit: containt !important;
            }

            nav p {
                display: none !important;
            }
        </style>

        <!-- END: CSS Assets-->

        <!-- para livewire ---------------------------->

        <livewire:styles />

        <!-- para livewire ---------------------------->

        <!-- para livewire ---------------------------->
    
    <!-- Google Analytics -->
    @if(env('GOOGLE_ANALYTICS_ID'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_ID') }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ env('GOOGLE_ANALYTICS_ID') }}');
    </script>
    @endif
    <!-- End Google Analytics -->

    </head>
    <!-- END: Head -->
    <body class="main">
            <!-- BEGIN: Mobile Menu -->

                @include('layouts.theme.mobile-menu')
            <!-- END: Mobile Menu -->
            <div class="flex">

            <!-- BEGIN: Side Menu -->
                @include('layouts.theme.sidebar')
            <!-- END: Side Menu -->

            <!-- BEGIN: Content -->
            <div class="content">
                <!-- BEGIN: Top Bar -->
                @include('layouts.theme.topbar')
                <!-- END: Top Bar -->
    {{-- ********************slot nos permite desplegar cafda uno de los componentes en la seccion content --}}
                {{ $slot }}
    {{-- **********************slot nos permite desplegar cafda uno de los componentes en la seccion content --}}
            </div>

            <!-- END: Content -->
        </div>

        <!-- BEGIN: Dark Mode Switcher-->
        {{-- <div data-url="side-menu-dark-dashboard-overview-1.html" class="dark-mode-switcher cursor-pointer shadow-md fixed bottom-0 right-0 box dark:bg-dark-2 border rounded-full w-40 h-12 flex items-center justify-center z-50 mb-10 mr-10">
            <div class="mr-4 text-gray-700 dark:text-gray-300">Dark Mode</div>
            <div class="dark-mode-switcher__toggle border"></div>
        </div> --}}
        <!-- END: Dark Mode Switcher-->
        <script>
            window.livewire_app_url = "{{ request()->root() }}";
        </script>
        <livewire:scripts />
        <!-- BEGIN: JS Assets-->
            @include('layouts.theme.footer')
        <!-- END: JS Assets-->


         <!-- para livewire ---------------------------->



         <!-- para livewire ---------------------------->




      

       
    </body>
</html>
