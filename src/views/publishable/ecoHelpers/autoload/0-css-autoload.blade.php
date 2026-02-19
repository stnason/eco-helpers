{{--
 * The autoload file associated with the eco-helpers config file array
 * entry for 'auto_loaders' => [0 => 'name']
 * Note: Auto-loader 0 is reserved for the global (app pages) css and js.
 --}}
{{-- ###################################################################################################################### --}}
{{-- Autoload[0] -- STATICALLY CALLED CSS FILES.
        Add any entries here that you want to be loaded on every page using the eh-app-template. --}}
{{-- ###################################################################################################################### --}}
    <!-- Favicon icon in public root folder -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ config('app.url') }}/favicon.ico" />
    {{-- Add any additional items here like paths to the Apple icons or whatever you may need on all pages.
    <link rel="apple-touch-icon" href="/docs/5.2/assets/img/favicons/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" href="/docs/5.2/assets/img/favicons/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/docs/5.2/assets/img/favicons/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="/docs/5.2/assets/img/favicons/manifest.json">
    <link rel="mask-icon" href="/docs/5.2/assets/img/favicons/safari-pinned-tab.svg" color="#712cf9">
    <link rel="icon" href="/docs/5.2/assets/img/favicons/favicon.ico">
    <meta name="theme-color" content="#712cf9">
    --}}

    {{-- Bootstrap 5.3 css. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    {{-- Bootstrap 5.3; Note this has to be in the css (top) loader in order for page elements like the navbar to have access. --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>


    {{-- Bootstrap 5.3 icons. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    {{-- eco-helpers multi-level menus. --}}
    {{-- This controls and styles the multilevel menu dropdowns in the navbar. --}}
    <link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/eh-navbar-multilevel.css')}}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">

    {{-- ecoHelpers specific OOTB --}}
    <link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/eh-app-layout-areas.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/eh-app-forms-tables.css')}}">


    {{-- My main site BS override css.
    <link rel="stylesheet" href="{{asset('css/np-main.css')}}"> --}}


    {{-- NOTE: Putting these in the <head> section so they'll be available to any other per-page-js script that may need it. --}}



