{{--
 * The auto-load file associated with the eco-helpers config file array
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

{{-- This controls and styles the multilevel menu dropdowns in the navbar. --}}
<link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/eh-navbar-multilevel.css')}}">

{{-- Sticky footer control. Force the footer to the bottom of the viewport or page - whichever is farther. --}}
<link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/eh-sticky-footer-navbar.css')}}">

{{-- Original Laravel Fonts
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet"> --}}

{{-- Latest compiled Bootstrap 5.3 minified CSS
        Local or CDN --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

{{-- Font Awesome
        Local or CDN --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

