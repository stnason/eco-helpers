@inject('config', 'ScottNason\EcoHelpers\Classes\ehConfig')
{{--
///////////////////////////////////////////////////////////////////////////////////////
WARNING!
This is the base template for all echo-helper core pages'
This file should not be modified.

- Includes 9 standard page display "areas":
    - Banner
    - Option-Block
    - Icon
    - Title
    - Description
    - Linkbar
    - Dynamic Heading
    - System Flash
    - Attention Message

    - These are all configurable at runtime through YourPageController.php using the ehLayout class.

    eh-app-master-template.blade.php provivdes extendible content "blocks" - which is where you are
    going to create your page content:

    - additional-head   - Anything else you need in the <head> section.
                          Often used for per page <style> assignments.
    - main-content      - The entire main centent section of the page.
    - per-page-js       - Any javascript needed for a single page.

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

<div id="eh-main-content" class="{{$main_class}}">
    {{--
        ### THE MAIN ECO HELPERS HEADING BLOCK ###
        Check to see if any of the content areas in the Title area are on. If not then disable the whole thing.
        --}}
    @if ( $form['layout']['title']['state'] or $form['layout']['description']['state'] or $form['layout']['option-block']['state'] or
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
                    USAGE: ehLayout::setTitle('text', 'css-class');
                    --}}
                @if (!empty($form['layout']['title']['content']) or !empty($form['layout']['icon']['content']))
                    <div id="eh-layout-page-name" class="{{$form['layout']['title']['class']}}">

                        @if ($form['layout']['icon']['state'])
                            <i class="{!! $form['layout']['icon']['content'] ?? '' !!}"
                               style="{{$form['layout']['icon']['class']}}"></i>
                        @endif
                        @if ($form['layout']['title']['state'])
                            {{ $form['layout']['title']['content'] ?? '' }}
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
                                        {{$link['title'] ?? $link['title']}}</a>
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

    {{-- 1/29/2026 - so if this template is now just a "partial", then no need for any @yields.
        ### BASE CONTENT BODY ###
        Yeild the standard page content area for all views to use.
    @yield('main-content')
     --}}
    {{--
        ### JS AUTO-LOADER ###
        Include the Javascript $settinger mechanism.
        --}}
    @include('ecoHelpers::core.eh-js-loader')
    {{--
        ### GLOBAL JS ###
        Global js required by all crud pages.
    --}}
    {{-- DELETE Warning. --}}

    {{-- TODO: not sure about these here. vs calling them from the package files -- do they need to be published????? --}}
    <script type=" text/javascript" src="{{asset('vendor/ecoHelpers/js/eh-delete-me.js')}}"></script>
    {{-- Default "auto_submit=>true" behavior when using ehControl::select() . --}}
    <script type="text/javascript" src="{{asset('vendor/ecoHelpers/js/eh-goto-submit.js')}}"></script>
    {{-- Replace the save button with a spinner after submitting. --}}
    <script type="text/javascript" src="{{asset('vendor/ecoHelpers/js/eh-save-me.js')}}"></script>
    {{-- User Notifications. --}}
    <script type="text/javascript" src="{{ asset('vendor/ecoHelpers/js/eh-notifications.js') }}"></script>
    {{-- Unsaved warning message. Auto loader [1] or ['unsaved'] --}}
    <script type="text/javascript">
        @if (isset($form['layout']['auto_load'][1]) && $form['layout']['auto_load'][1] == 'disabled' )
        {{-- From the controller you can disable the 'unsaved' meesage using: ehLayout::setAutoload('unsaved','disabled');--}}
        @else
        $("form[class^='eh-form-crud']").change(function (e) {
            // Update the system flash message on any form input change.
            // EXCEPT: do not do it on any "goto" button change since we're just changing pages.
            if (e.target.id != "goto") {
                $('#eh-layout-page-flash').html('You have <strong>unsaved</strong> changes.');
            }
        });
        @endif
    </script>
</div>