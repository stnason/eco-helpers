{{--
///////////////////////////////////////////////////////////////////////////////////////
WARNING!
This is the base template for all pages so be very mindful (think twice)
about making any changes to this file.
    -- Most changes are done through the views/eco-configure folder.

This pages main function is to block out a "standard" html page that you "extend" in
individual page templates:

- HTML document type and base page layout.
- Includes the standard HTML header (standard_html_head.blade.php) in the <head> section.
    - logo
    - Menus (if any) - configured in views/eco-configure
    - standard css
    - any auto loader css (config/eco-helpers; views/eco-configure

- Includes the standard footer (standard_footer.blade.php) in the <head> section.
    - content setup in views/eco-configure
    - standard css
    - any auto loader js (config/eco-helpers; views/eco-configure

- Includes 9 standard page display "areas":
    - Banner
    - Option-Block
    - Icon
    - Name (Title)
    - Description
    - Linkbar
    - Dynamic Heading
    - System Flash
    - Attention Message

    - These are all configurable at runtime through YourPageController.php using the Layout class.

    base.blade.php provivdes extendible content "blocks" - which is where you are going to create your own page
    content:


    - base_head     - Anything else you need in the <head> section will come from your page extention.
                    - This is often used for per page <style> assignments.
    - base_body     - The whole body of page - WITHOUT any doctype declarations or head section.
    - base_js       - Any javascript needed for a single page.

///////////////////////////////////////////////////////////////////////////////////////
// A note on BUTTONS:
The standard button area must be contained within each CRUD <form>

<form class="form-crud" method="post" action="{{ $form['layout']['form_action'] }}">
    @csrf
    @method($form['layout']['form_method'] ?? 'PATCH')
    {{-- ########################################################################
    {{-- Build out the BUTTON area and enumerate over any possible buttons ######
    {!! $control::buttonAreaHTML($form['buttons']) !!}
    {{-- ########################################################################

    Remember that content for the id="system-page-buttons" is built out in the Controls@buttonAreaHTML() call.
///////////////////////////////////////////////////////////////////////////////////////
--}}
@inject('config', 'ScottNason\EcoHelpers\Classes\ehConfig')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{--
        Include all of the standard meta tags, base and auto-loaded css (configure in views/eco-configure).
        --}}
    @if(View::exists('ecoHelpers.'.$config::get('layout.html_head_file')))
        @include('ecoHelpers.'.$config::get('layout.html_head_file'))
    @endif

    {{-- Include the CSS and auto-loader mechanism. --}}
    @include('ecoHelpers::core.eh-css-loader')

    {{-- The <title> (or 'name') of the page. --}}
    @if (!empty($form['layout']['name']['content']))
        <title>{{ $form['layout']['name']['content'] }}</title>
    @else
        <title>{{ config('app.name', 'eco-helpers') }}</title>
    @endif

    {{-- Yield block used by other templates to add additional <head> items.
            Commonly for per page <style> elements. --}}
    @yield('base_head')

</head>
<body>
@if(!isset($form['layout']))
    {{ dd("Missing layout array: You must run Layout::initLayout() and pass to the the template as form[layout]") }}
@endif

{{-- The per user notification url. --}}
<script type="application/javascript">
    var notification_url = '{{config('app.url')}}'+'/notifications';
</script>

{{-- The per user notificatoin message modal. --}}
@include('ecoHelpers::core.eh-message-modal')

{{--
    You may have the need to add other elements to your base template. (dropzones, modals, user notifcations, etc.)
    --}}
@if(View::exists('ecoHelpers.'.$config::get('layout.app_add_ins_file')))
    @include('ecoHelpers.'.$config::get('layout.app_add_ins_file'))
@endif

{{--
    Include the user configurable nav bar content.
    --}}

@if(View::exists('ecoHelpers.'.$config::get('layout.navbar_header_file')))
    @include('ecoHelpers.'.$config::get('layout.navbar_header_file'))
@endif

{{--
The system banner displays on every page right below the navigation banner
For security reasons; the banner is configuarble to enforce being logged in to view.
@if ( ($config::get('layout.options.banner_auth') && Auth::check()) )
--}}
{{-- If the configuration says not to check if authorized then show banner all the time. --}}
@if (!$config::get('layout.options.banner_auth'))
        @php $show_banner = true; @endphp
@else
        {{-- If the configuration says TO check if authorized then check that. --}}
        @if (Auth::check())
                @php $show_banner = true; @endphp
        @else
                @php $show_banner = false; @endphp
        @endif
@endif
{{-- Based the checks above, decide whether to make the final show banner checks.
        Is it turned on - and if so, should it blink.
        --}}
@if ($show_banner)
    @if ($form['layout']['banner']['state'])
        @if($form['layout']['banner']['blink'])
            <div class="{{$form['layout']['banner']['class']}} blink" id="layout-page-banner">{!! $form['layout']['banner']['content'] !!}</div>
        @else
            <div class="{{$form['layout']['banner']['class']}}" id="layout-page-banner">{!! $form['layout']['banner']['content'] ?? '' !!}</div>
        @endif
    @endif
@endif

{{--
    Set up a container for the main page just below the system banner.
    Set the main container class in the eco-helpers.layout.options section.
    --}}
@if (!empty($form['layout']['full-width']) && $form['layout']['full-width']['state'])
    <main class="{{$config::get('layout.options.page_container_class_full')}}">
@else
    <main class="{{$config::get('layout.options.page_container_class_normal')}}">
@endif
    {{--
        Check to see if any of the content areas in the Title area are on. If not then disable the whole thing.
        --}}
    @if ( $form['layout']['name']['state'] or $form['layout']['description']['state'] or $form['layout']['option-block']['state'] or
    $form['layout']['linkbar']['state'] or $form['layout']['dynamic']['state'] )
        <!--<div class="bg-light mt-2 mb-0 pt-1 pb-1 pr-1 rounded clearfix">-->
        <div class="bg-light p-1 rounded">

            {{--
                An optional block -- usually for an image (like a Contact photo).
                --}}
            @if ($form['layout']['option-block']['state'])
                @if (!empty($form['layout']['option-block']['content']))
                    <div class="container-fluid {{$form['layout']['option-block']['class']}}" id="layout-page-option-block">
                        {!! $form['layout']['option-block']['content'] !!}</div>
                @endif
            @endif

            {{--
                Page title and icon.
                --}}
            @if (!empty($form['layout']['name']['content']) or !empty($form['layout']['icon']['content']))
                <div class="container-fluid" id="layout-page-title">
                    <h1 class="{{$form['layout']['name']['class']}}">
                        @if ($form['layout']['icon']['state'])
                            <i class="{!! $form['layout']['icon']['content'] ?? '' !!}" style="{{$form['layout']['icon']['class']}}"></i>
                        @endif
                        @if ($form['layout']['name']['state'])
                            {{ $form['layout']['name']['content'] ?? '' }}
                        @endif
                    </h1>
                </div>
            @endif

            {{--
                The page description; displayed right below the page name.
                --}}
            @if ($form['layout']['description']['state'])
                <div class="container-fluid" id="layout-page-description">
                    <span class="{{$form['layout']['description']['class']}}">
                        {!!$config::get('layout.options.description_bullet')!!}{{ $form['layout']['description']['content'] }}
                    </span>
                </div>
            @endif

            {{--
                The LinkBar that must be bulit by the individual page controllers when needed.
                --}}

            @if ($form['layout']['linkbar']['state'])
                <div class="container-fluid {{$form['layout']['linkbar']['class']}}" id="layout-page-linkbar">
                    <div class="row">
                        <div class="col-md">
                            <ul class="linkbar">
                            @if (!empty($form['layout']['linkbar']['content']))
                                @foreach ($form['layout']['linkbar']['content'] as $link)
                                    <li><a href="{{$link['href'] ?? '#'}}"
                                           target="{{$link['target'] ?? '_self'}}"
                                           title="{{$link['title'] ?? ''}}">
                                            {{$link['name'] ?? $link['title']}}</a>
                                    </li>
                                    {{-- Place a delimeter character between each link. --}}
                                    @if (!$loop->last)
                                        |
                                    @endif
                                @endforeach
                            @endif
                            </ul>
                        </div>
                        <!--<div class="col-md-2"></div>--> {{-- Forcing the LinkBar to wrap early leaving room for Dropzones. --}}
                    </div>

                </div>
            @endif

            {{--
                The is an Optional Dynamicly created CRUD heading that displays right below the LinkBar area.
                --}}
            @if ($form['layout']['dynamic']['state'])
                <div class="container-fluid {{$form['layout']['dynamic']['class']}}" id="layout-page-dynamic">
                    {!! $form['layout']['dynamic']['content'] !!}</div>
            @endif

        </div>
    @endif

    {{--
        This is the system wide flash message. It can be turned off like all the others
        - But when overriding the standard page layout just remember to put it back in somewhere!
            --}}
    @if ($form['layout']['flash']['state'])
        <div class="container-fluid {{$form['layout']['flash']['class']}}" id="layout-page-flash">

            {{-- This is the standard flash message --}}
            @if (session('message'))
                {!! session('message') !!}
            @endif

            {{-- And this comes from the default Laravel error system. --}}
            @if ($errors->any())

                @foreach ($errors->all() as $error)
                    <li>{!!  $error !!}</li>
                @endforeach

            @else
                {{-- system flash message place holder - to keep the layout intact. --}}
                &nbsp;
            @endif
        </div>
    @endif

    {{--
    This is the page attention message. Is is used to show special conditions such as "Archived" or "In Active".
    --}}
    @if ( ($form['layout']['attention']['state']) && !empty($form['layout']['attention']['content']) )
        <div class="container-fluid" id="layout-page-attention">
            <h4 class="pb-1 text-light text-center rounded {{$form['layout']['attention']['class']}}">{!! $form['layout']['attention']['content'] !!}</h4>
        </div>
    @else
        <div class="container-fluid" id="layout-page-attention"></div>
    @endif

{{--
    Yeild the standard page conetent area for all views to use.
    --}}
@yield('base_body')

{{--
    Include the standard footer (user configureable in views/eco-configure).
    @include('ecoHelpers::core.eh-app-template_footer_shell')
    --}}

@if(View::exists('ecoHelpers.'.$config::get('layout.footer_file')))
    @include('ecoHelpers.'.$config::get('layout.footer_file'))
@else
    <p>NO USER FOOTER FILE PRESENT.</p>
    <p><em>Configure filename in config/eco-helpers.php and place the blade template file in views/ecoHelpers/configurable folder.</em></p>
@endif

{{-- Include Javascript auto-loader mechanism. --}}
@include('ecoHelpers::core.eh-js-loader')

{{-- Global js required by all crud pages - DELETE BUTTON ROUTING: --}}
<script type="text/javascript" src="{{asset('vendor/ecoHelpers/js/eh-delete-me.js')}}"></script>

{{-- Global js required by all crud pages - SAVE BUTTON PROCESSING. --}}
<script type="text/javascript" src="{{asset('vendor/ecoHelpers/js/eh-save-me.js')}}"></script>

{{-- Global js required for User Notifications. --}}
<script type="text/javascript" src="{{ asset('vendor/ecoHelpers/js/eh-notifications.js') }}"></script>

{{-- Originally from auto-loader[1]['unsaved']. Moved here so it's always included. --}}
<script type="text/javascript">
    $("form.form-crud").change(function () {

        // Update the system flash message on any form input change.
        @if (true)
        $('#layout-page-flash').html('You have <strong>unsaved</strong> changes.');
        @endif

    });
</script>

{{--
    Yield to other templates to add additional javascript elements.
    --}}
@yield('base_js')

</main>

{{--
Final override css or js (last to load). --}}

@if(View::exists('ecoHelpers.'.$config::get('layout.override_loader_file')))
    @include('ecoHelpers.'.$config::get('layout.override_loader_file'))
@endif

</body>
</html>


