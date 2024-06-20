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

    Remember that content for the id="system-page-buttons" is built out
    in the Controls@buttonAreaHTML() call.
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
    {{--
        Include the CSS and auto-loader mechanism. This pulls in the level-0 (global) css links
        and then any page specific css called for by the ehLayout::setAutoload(nn) in each controller.
        --}}
    @include('ecoHelpers::core.eh-css-loader')
    {{--
        The <title> (or 'name') of the page. This will pull automatically from the eh_pages table
        when it is set and can be overriden or manually set by ehLayout::setName('name').
        --}}
    @if (!empty($form['layout']['name']['content']))
        <title>{{ $form['layout']['name']['content'] }}</title>
    @else
        <title>{{ config('app.name', 'eco-helpers') }}</title>
    @endif
    {{-- The Blade yield block used by other templates to add additional <head> items as needed.
         Commonly used to insert per page <style> elements.
         --}}
    @yield('base_head')
</head>
<body>
{{--
    Initial check to ensure this template was passed the $form['layout'] array.
    That is generated by getting the ehLayout contents and we can't continue without it.
    --}}
@if(!isset($form['layout']))
    {{ dd("Missing layout array: You must run Layout::initLayout() and pass to the the template as $form[layout]") }}
@endif
{{--
    The per user notification url.
    --}}
<script type="application/javascript">
    var notification_url = '{{config('app.url')}}' + '/notifications';
</script>
{{--
    The per user notificatoin message modal.
    --}}
@include('ecoHelpers::core.eh-message-modal')
{{--
    Here's where you include any additional html at the top of the document.
    You may have the need to add other elements to the base template.
    (dropzones, modals, other notifcations, whatever.)
    --}}
@if(View::exists('ecoHelpers.'.$config::get('layout.app_add_ins_file_top')))
    @include('ecoHelpers.'.$config::get('layout.app_add_ins_file_top'))
@endif
{{--
    ### NAVIGATION BAR AREA ###
    This is the user configurable navbar area of the page
    --}}
@if(View::exists('ecoHelpers.'.$config::get('layout.navbar_header_file')))
    @include('ecoHelpers.'.$config::get('layout.navbar_header_file'))
@endif
{{--
    ### SYSTEM BANNER AREA ###
    The system banner displays on every page right below the navigation banner
    For security reasons; the banner is configuarble to enforce being logged in to view.

    USAGE: ehLayout::setBanner('text', 'css-class');
        // !! Note this is not commonly called this way since it's globally set in settings !!

    NOTE: We're just setting a $show_banner variable here and then using it below
          to determine the actual visibility of the banner.
    --}}
{{--
    If the configuration says not to check if authorized then
    show banner all the time.
    --}}
@if (!$config::get('layout.options.banner_auth'))
    @php $show_banner = true; @endphp
@else
    {{--
        If the configuration says TO check if authorized then check that first
        and display accordingly.
        --}}
    @if (Auth::check())
        @php $show_banner = true; @endphp
    @else
        @php $show_banner = false; @endphp
    @endif
@endif
{{-- Based on the checks above, decide whether to execute the rest of the show banner checks.
     Is it turned on - and if so, should it blink.
     --}}
@if ($show_banner)
    @if ($form['layout']['banner']['state'])
        @if($form['layout']['banner']['blink'])
            <div id="eh-layout-page-banner" class="{{$form['layout']['banner']['class']}} blink">
                {!! $form['layout']['banner']['content'] !!}
            </div>
        @else
            <div id="eh-layout-page-banner" class="{{$form['layout']['banner']['class']}}">
                {!! $form['layout']['banner']['content'] ?? '' !!}
            </div>
        @endif
    @endif
@endif
{{--
    ### MAIN CONTENT SECTION ###
    Set up a container for the <main> page content just below the system banner.
    Set the main container class in the eco-helpers.layout.options section.
    This can be either "full width" class
    USAGE: There are no direct ehLayout classes to interact with this. Use the individual are calls.
    --}}
@if (!empty($form['layout']['full-width']) && $form['layout']['full-width']['state'])
    @php($main_class = $config::get('layout.options.page_main_class_full'))
@else
    @php($main_class = $config::get('layout.options.page_main_class_normal'))
@endif
<main class="{{$main_class}}">
    {{--
        ### THE MAIN ECO HELPERS HEADING BLOCK ###
        Check to see if any of the content areas in the Title area are on. If not then disable the whole thing.
        --}}
    @if ( $form['layout']['name']['state'] or $form['layout']['description']['state'] or $form['layout']['option-block']['state'] or
        $form['layout']['linkbar']['state'] or $form['layout']['dynamic']['state'] )
        {{--
            This is the main eco-helpers wrapper around the page heading area.
            It includes the option-block, title/name, description, linkbar and dynamic heading.
        --}}
        <section id="eh-layout-page-heading-outer-wrapper">
            {{--
                ### OPTION BLOCK ###
                Commonly used for an image left of the title (like a Contact or Asset photo.
                USAGE: ehLayout::setOptionBlock('text', 'css-class');
                --}}
            @if ($form['layout']['option-block']['state'])
                @if (!empty($form['layout']['option-block']['content']))
                    <div id="eh-layout-page-option-block" class="{{$form['layout']['option-block']['class']}}">
                        {!! $form['layout']['option-block']['content'] !!}
                    </div>
                @endif
            @endif
            <div id="eh-layout-page-heading-inner-wrapper">
                {{--
                    ### PAGE NAME AREA ###
                    Page name/title (<title>) along with an accosiated icon if specified.
                    USAGE: ehLayout::setName('text', 'css-class');
                    --}}
                @if (!empty($form['layout']['name']['content']) or !empty($form['layout']['icon']['content']))
                    <div id="eh-layout-page-name" class="{{$form['layout']['name']['class']}}">

                        @if ($form['layout']['icon']['state'])
                            <i class="{!! $form['layout']['icon']['content'] ?? '' !!}"
                               style="{{$form['layout']['icon']['class']}}"></i>
                        @endif
                        @if ($form['layout']['name']['state'])
                            {{ $form['layout']['name']['content'] ?? '' }}
                        @endif

                    </div>
                @endif
                {{--
                    ### DESCRIPTION ###
                    The page description; displayed right below the page name.
                    USAGE: ehLayout::setDescription('text', 'css-class');
                    --}}
                @if ($form['layout']['description']['state'])
                    <div id="eh-layout-page-description" class="{{$form['layout']['description']['class']}}">
                        {!!$config::get('layout.options.description_bullet')!!}{{ $form['layout']['description']['content'] }}
                    </div>
                @endif
                {{--
                    ### LINKBAR AREA ###
                    The LinkBar that must be bulit by the individual page controllers when needed.
                    USAGE: ehLayout::setLinkbar($linkbar-array, 'css-class');
                    --}}
                @if ($form['layout']['linkbar']['state'])
                    <ul id="eh-layout-page-linkbar" class="{{$form['layout']['linkbar']['class']}}">

                        @if (!empty($form['layout']['linkbar']['content']))
                            @foreach ($form['layout']['linkbar']['content'] as $link)
                                <li><a href="{{$link['href'] ?? '#'}}"
                                       target="{{$link['target'] ?? '_self'}}"
                                       title="{{$link['title'] ?? ''}}">
                                        {{$link['name'] ?? $link['title']}}</a>
                                </li>
                                {{-- Place a delimeter character between each link. --}}
                                @if (!$loop->last)
                                    {!! $config::get('layout.options.linkbar_delimiter') !!}
                                @endif
                            @endforeach
                        @endif

                    </ul>
                @endif
                {{--
                    ### DYNAMIC HEADING ###
                    The is an optional Dynamicly created CRUD heading that displays right below the LinkBar area.
                    USAGE: ehLayout::setLinkbar('text', 'css-class');
                    --}}
                @if ($form['layout']['dynamic']['state'])
                    <div id="eh-layout-page-dynamic" class="{{$form['layout']['dynamic']['class']}}">
                        {!! $form['layout']['dynamic']['content'] !!}
                    </div>
                @endif
            </div>
        </section>
    @endif

    <section id="eh-layout-page-flash-attention-wrapper">
        {{--
            ### FLASH AREA ###
            This is the system wide flash message. It can be turned off like all the others
            But when overriding the standard page layout just remember to put it back in somewhere!
            USAGE: ehLayout::setFlash('text', 'css-class');
                // !! Note this is not commonly called this way since it's part of the crud flash system !!
                --}}
        @if ($form['layout']['flash']['state'])
            <div class="{{$form['layout']['flash']['class']}}" id="eh-layout-page-flash">
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
            ### ATTENTION MESSAGE ###
            This is the page attention message.
            It is used to show special conditions such as "Archived" or "In Active".
            USAGE: ehLayout::setFlash('text', 'css-class');
            --}}
        @if ( ($form['layout']['attention']['state']) && !empty($form['layout']['attention']['content']) )
            <p id="eh-layout-page-attention" class="{{$form['layout']['attention']['class']}}">
                {!! $form['layout']['attention']['content'] !!}
            </p>
            {{--
            This causes a line of the defined background color to show when we turn off the area.
            @else
                <p id="eh-layout-page-attention" class="{{$form['layout']['attention']['class']}}">
                </p>
            --}}
        @endif

    </section>
    {{--
        ### BASE CONTENT BODY ###
        Yeild the standard page content area for all views to use.
        --}}
    @yield('base_body')
    {{--
        ### JS AUTO-LOADER ###
        Include the Javascript auto-loader mechanism.
        --}}
    @include('ecoHelpers::core.eh-js-loader')
    {{--
        ### GLOBAL JS ###
        Global js required by all crud pages.
    --}}
    {{-- DELETE Warning. --}}
    <script type=" text/javascript" src="{{asset('vendor/ecoHelpers/js/eh-delete-me.js')}}"></script>
    {{-- Default "auto_submit=>true" behavior when using ehControl::select() . --}}
    <script type="text/javascript" src="{{asset('vendor/ecoHelpers/js/eh-goto-submit.js')}}"></script>
    {{-- Replace the save button with a spinner after submitting. --}}
    <script type="text/javascript" src="{{asset('vendor/ecoHelpers/js/eh-save-me.js')}}"></script>
    {{-- User Notifications. --}}
    <script type="text/javascript"
            src="{{ asset('vendor/ecoHelpers/js/eh-notifications.js') }}"></script>
    {{-- Unsaved warning message. --}}
    <script type="text/javascript">
        $("form.eh-form-crud").change(function (e) {
            // Update the system flash message on any form input change.
            // EXCEPT: do not do it on any "goto" button change since we're just changing pages.
            if (e.target.id != "goto") {
                $('#eh-layout-page-flash').html('You have <strong>unsaved</strong> changes.');
            }
        });
    </script>
    {{--
        Yield to other templates so they can add additional javascript elements as needed.
        --}}
    @yield('base_js')
</main>

{{--
        ### FOOTER AREA ###
        Include the file specified in the eco-helpers config file.
        --}}
@if(View::exists('ecoHelpers.'.$config::get('layout.footer_file')))
    @include('ecoHelpers.'.$config::get('layout.footer_file'))
@else
    <p>NO USER FOOTER FILE PRESENT.</p>
    <p><em>Configure filename in config/eco-helpers.php and place the blade template file in
            views/ecoHelpers/configurable folder.</em></p>
@endif


{{--
    Final override file to load (outside of <main>).
    --}}
@if(View::exists('ecoHelpers.'.$config::get('layout.app_add_ins_file_bottom')))
    @include('ecoHelpers.'.$config::get('layout.app_add_ins_file_bottom'))
@endif
</body>
</html>