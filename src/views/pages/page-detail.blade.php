@inject('valid', 'App\Classes\ValidList')
@inject('control', 'ScottNason\EcoHelpers\Classes\ehControl')
@extends('ecoHelpers::core.eh-app-template')

@section ('base_head')
    {{-- Get the Menu & Page List styling.  --}}
    <link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/eh-page-list.css')}}">
    <style>

        /* Override the left padding from pages-list.css - since we are only showing modules. */
        ul.tree-view {
            font-size: .9em;        /* A little smaller than the Menu List page. */
            padding-left: 1.4em;    /* There is nothing on top of this so we can move back over that indent. */
        }


        /* The Fontawesome icon positioning next to where you enter it. */
        #page-icon {
            margin-left: -20px;
            margin-right: 8px;
            font-size: large;
        }


        /* This is used to move the text to the right of checkboxes and radio buttons over to match that of inputs. */
        .shift-right {

            /*margin-left: 42px;        ummm...this is only needed at certain (smaller) width breaks. (?) */
        }



    </style>
@endsection ('base_head')

@php
    // When needed, the Controller is responsible for setting this flag using ehLayout::setWhenAdding($value).
    // It will normally default to false, so it only needs to be set when adding a record through create().
    // Note: Although it can be used directly, we're giving it a local template variable name here for clarity
    // and to help document is function and use.
    $form['layout']['when_adding'] = $form['layout']['when_adding'];
@endphp


@section ('base_body')

    <div class="container">


        <form class="form-crud" method="post" action="{{ $form['layout']['form_action'] }}">
            @csrf
            @method($form['layout']['form_method'] ?? 'PATCH')

            {{-- ######################################################################## --}}
            {{-- Build out the BUTTON area and enumerate over any possible buttons ###### --}}
            {!! $control::buttonAreaHTML($form['layout']['buttons']) !!}
            {{-- ######################################################################## --}}


            <div class="row">
                <div class="col-md-8">



                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'id', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                <span class="no-control">{{$page->id}}</span>
                                {{--
                                {!! $control::input(['field_name'=>'id', 'model'=>$page, 'errors'=>$errors,  'additional_class'=>'input-narrow', 'disabled'=>'true' ]) !!}
                                --}}
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">

                            </div>
                        </div>
                    </div>


                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'active', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::radio(['field_name'=>'active', 'model'=>$page, 'alert_if'=>'0', 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No']]) !!}
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">

                            @if(!$form['layout']['when_adding'])      {{-- This is not available yet when adding a new record. --}}
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'goto', 'display_name'=>'Go To', 'errors'=>$errors]) !!}
                                {!! $control::select([
                                'field_name'=>'goto',
                                'model'=>$page,
                                'selections'=>$valid::getList('module_list_all'),
                                'preselect'=>$form['module_id'],    // This is set in the PagesController show method which is already checking for the current module.
                                'auto_submit'=> true,
                                'add_blank'=>false,
                                'errors'=>$errors]) !!}
                            </div>
                            @endif

                        </div>
                    </div>



                    <div><p class="form-header-information">page type and association:</p></div>


                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'security', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::select([
                               'field_name'=>'security',
                               'model'=>$page,
                               'selections'=>$valid::getList('page_security'),
                               'add_blank'=>false,
                               'auto_submit'=>false,            // Since the value here is not a menu id we'd have to use a custom submit'
                               'errors'=>$errors]) !!}
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">

                                {{--
                                0=>'No Access',
                                1=>'Public access',
                                2=>'Authenticated only',
                                3=>'Full permissions check',
                                --}}

                                @if ($page->security === 0)
                                    <p class="text-danger">This page is currently configured with <strong>NO ACCESS</strong>.</p>
                                @elseif ($page->security === 1)
                                    <p class="text-danger"><strong>WARNING:</strong> This page is <strong>Public</strong> and
                                        <strong>WILL NOT</strong> be checked by the <strong>Role Security Access System</strong>.</p>
                                @elseif ($page->security === 2)
                                    <p class="text-info">You must be <strong>logged in</strong> to access this page.</p>
                                @elseif ($page->security === 3)
                                    <p class="text-info">This page <strong>will require a login</strong> and then be checked by the
                                        <strong>Role Security Access System</strong>.</p>
                                @endif

                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'type', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::select([
                               'field_name'=>'type',
                               'model'=>$page,
                               'selections'=>$valid::getList('page_type'),
                               'add_blank'=>false,
                               'auto_submit'=>false,            // Since the value here is not a menu id we'd have to use a custom submit'
                               'errors'=>$errors]) !!}

                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">


                                <p class="small text-danger">

                                    @if ($page->type == 'module')
                                        This page is a <strong>Module</strong> and
                                        will be used as a top-level (root) Menu category.
                                    @endif
                                    @if ($page->type == 'page')
                                        This is a <strong>displayable</strong> page

                                        @if($page->menu_item == '1')
                                            and <strong>will have</strong> a <strong>Menu Item</strong>.
                                        @else
                                            but will <strong>not have</strong> a <strong>Menu Item</strong>.
                                        @endif
                                    @endif
                                    @if ($page->type == 'method')
                                       This page is <strong>not displayable</strong> but is
                                            just a back-end <strong>script</strong> or <strong>method</strong> call.
                                    @endif
                                    @if ($page->type == 'resource')
                                        This page belongs to a <strong>resourceful stack</strong> and
                                        its associated permissions.
                                    @endif

                                </p>

                            </div>
                        </div>
                    </div>
                    


                    <div><p class="form-header-information">page name and menu control:</p></div>


                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'name', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::input(['field_name'=>'name', 'model'=>$page, 'errors'=>$errors]) !!}
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'icon', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::input(['field_name'=>'icon', 'model'=>$page, 'errors'=>$errors]) !!}
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <p class="form-em"><i id="page-icon" class="{{$page->icon}}"></i>A <strong><a href="https://fontawesome.com/icons?d=gallery" target="_blank">Fontawesome</a></strong>-class name only-(appears left of page name).</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'alt_text', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::input(['field_name'=>'alt_text', 'model'=>$page, 'additional_class'=>'input-wide', 'errors'=>$errors]) !!}
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'description', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::input(['field_name'=>'description', 'model'=>$page, 'additional_class'=>'input-wide', 'errors'=>$errors]) !!}
                            </div>
                        </div>
                        {{-- Right column of form. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <!--
                                <p class="form-em">Brief <strong>page explanation</strong> to display right below the page name.
                                </p>
                                -->
                            </div>
                        </div>
                    </div>


                    <div><p class="form-header-information">controls and routing:</p></div>

                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                @php
                                // Get the whole menu list but add a "0-TOP LEVEL" selection at the top.

                                //$menu_list = $valid::getList('menus_list');
                                $menu_list = $valid::getList('modules_submenus_list');
                                $menu_list = [0=>'TOP LEVEL'] + $menu_list;     // Note: doing it this instead of array_merge() or unshift() keeps from renumbering the keys!!

                                @endphp
                                {!! $control::label(['field_name'=>'parent_id', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::select([
                                'field_name'=>'parent_id',
                                'model'=>$page,
                                'selections'=>$menu_list,
                                'add_blank'=>false,
                                'auto_submit'=>false,
                                'errors'=>$errors]) !!}
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <p class="form-em">Which <strong>module</strong> or <strong>submenu</strong> does this <strong>page belong to</strong>?
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'menu_item', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::radio(['field_name'=>'menu_item', 'model'=>$page, 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No']]) !!}
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <p class="shift-right form-em"><strong>Include</strong> this page in the <strong>Menu System </strong>(y/n)?
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                @php
                                    if ($page->menu_item != '1') {
                                        $disabled = true;
                                    } else {
                                        $disabled = false;
                                    }
                                @endphp
                                {!! $control::label(['field_name'=>'order', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::input(['field_name'=>'order', 'model'=>$page, 'errors'=>$errors]) !!}
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <p class="form-em">Assign the <strong>position</strong> in the
                                    <strong>Menu</strong> tree.</p>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                @php
                                    if ($page->menu_item != '1') {
                                        $disabled = true;
                                    } else {
                                        $disabled = false;
                                    }
                                @endphp
                                {!! $control::label(['field_name'=>'route', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::input(['field_name'=>'route', 'model'=>$page, 'errors'=>$errors]) !!}
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <p class="form-em">The Laravel <strong>route</strong> name.</p>
                            </div>
                        </div>
                    </div>

                {{-- MOST PROBABLY DEPRECATING THESE FIELDS -- don't think they ever panned out as any help for a viable security check.
                    <div class="row">
                        {{-- Left column of form data.
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                @php
                                    if ($page->menu_item != '1') {
                                        $disabled = true;
                                    } else {
                                        $disabled = false;
                                    }
                                @endphp
                                {!! $control::label(['field_name'=>'route_type', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::select([
                               'field_name'=>'route_type',
                               'model'=>$page,
                               'selections'=>$valid::getList('route_type'),
                               'add_blank'=>true,
                               'auto_submit'=>false,
                               'errors'=>$errors]) !!}
                            </div>
                        </div>
                        {{-- Right column of form data.
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <p class="form-em">The HTML <strong>method</strong> name for this route.</p>
                            </div>
                        </div>
                    </div>
--}}




                    {{--
                    <div class="row">

                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'http_get_head', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::checkbox(['field_name'=>'http_get_head', 'model'=>$page, 'errors'=>$errors ]) !!}
                            </div>
                        </div>

                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <p class="shift-right form-em">The http <strong>method</strong> type for this route.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'http_put_patch', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::checkbox(['field_name'=>'http_put_patch', 'model'=>$page, 'errors'=>$errors ]) !!}
                            </div>
                        </div>

                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <p class="shift-right form-em">The http <strong>method</strong> type for this route.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="row">

                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'http_post', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::checkbox(['field_name'=>'http_post', 'model'=>$page, 'errors'=>$errors ]) !!}
                            </div>
                        </div>

                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <p class="shift-right form-em">The http <strong>method</strong> type for this route.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'http_delete', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::checkbox(['field_name'=>'http_delete', 'model'=>$page, 'errors'=>$errors ]) !!}
                            </div>
                        </div>

                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <p class="shift-right form-em">The http <strong>method</strong> type for this route.
                                </p>
                            </div>
                        </div>
                    </div>
                --}}






                    <div><p class="form-header-information">comments and special permissions:</p></div>

                    <div class="row">
                        <div class="col-sm">
                            {{-- Leaving off the -flex at the end of d-inline causes the label to be above
                            the <textarea> but it seems to be the only way I can get the width to be 100%
                            and responsive.
                            --}}

                            <div class="form-group d-inline-flex">
                                <label>&nbsp;</label>
                                <p class="form-em">Are there any <strong>special permissions</strong> hard coded in this page?
                                    (displayed in <strong>Group permissions</strong>)</p>
                            </div>

                            <div class="form-group d-inline">
                                {!! $control::label(['field_name'=>'feature_1', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::textarea(['field_name'=>'feature_1', 'model'=>$page, 'rows'=>'2', 'errors'=>$errors]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm">
                            {{-- Leaving off the -flex at the end of d-inline causes the label to be above
                            the <textarea> but it seems to be the only way I can get the width to be 100%
                            and responsive.
                            --}}
                            <div class="form-group d-inline">
                                {!! $control::label(['field_name'=>'feature_2', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::textarea(['field_name'=>'feature_2', 'model'=>$page, 'rows'=>'2', 'errors'=>$errors]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm">
                            {{-- Leaving off the -flex at the end of d-inline causes the label to be above
                            the <textarea> but it seems to be the only way I can get the width to be 100%
                            and responsive.
                            --}}
                            <div class="form-group d-inline">
                                {!! $control::label(['field_name'=>'feature_3', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::textarea(['field_name'=>'feature_3', 'model'=>$page, 'rows'=>'2', 'errors'=>$errors]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm">
                            {{-- Leaving off the -flex at the end of d-inline causes the label to be above
                            the <textarea> but it seems to be the only way I can get the width to be 100%
                            and responsive.
                            --}}
                            <div class="form-group d-inline">
                                {!! $control::label(['field_name'=>'feature_4', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::textarea(['field_name'=>'feature_4', 'model'=>$page, 'rows'=>'2', 'errors'=>$errors]) !!}
                            </div>
                        </div>
                    </div>


                    <div class="spacer-line"></div>

                    <div class="row">
                        <div class="col-sm">
                            {{-- Leaving off the -flex at the end of d-inline causes the label to be above
                            the <textarea> but it seems to be the only way I can get the width to be 100%
                            and responsive.
                            --}}

                            <div class="form-group d-inline-flex">
                                <label>&nbsp;</label>
                                <p class="form-em"><strong>General comments</strong> to help clarify when assigning
                                    <strong>permissions</strong>.
                                    (displayed in <strong>Group permissions</strong>)</p>
                            </div>

                            <div class="form-group d-inline">
                                {!! $control::label(['field_name'=>'comment', 'display_name'=>$page, 'errors'=>$errors]) !!}
                                {!! $control::textarea(['field_name'=>'comment', 'model'=>$page, 'rows'=>'2', 'errors'=>$errors]) !!}
                            </div>
                        </div>

                    </div>


                </div>


                {{-- Visual Menu Tree sidebar.
                     Note; that the $form['parent'] always returns just one record so we need to specify key [0] here.
                        --}}
                <div class="col-md-4">

                    @if(!$form['layout']['when_adding'])
                    <p class="ms-3"><strong>{{$page->name}} {!! $form['tree_layout_explanation'] !!}</strong></p>

                    {{-- Build the recursive menu structure with the appropriate css formatting for this module. --}}
                    <ul id="page-tree" class="tree-view">

                            @foreach($form['whole_module'] as $page_item)
                                @include('ecoHelpers::core.eh-child-pages')
                            @endforeach

                    </ul>
                    @endif

                </div>

            </div>

            {{-- Standard form information header; for endu-user form content headings. --}}
            @php $model = $page @endphp
            @include('ecoHelpers::core.eh-system-info')


        </form>

    </div>

@endsection ('base_body')



@section ('base_js')
    <script src="{{asset('/vendor/ecoHelpers/js/eh-goto-submit.js')}}"></script>
    <script type="text/javascript">
        // Goto submit for the Module list dropdown
        goto_url = "{{config('app.url')}}/pages";

        // Replace the generic "delete this record" message with a more specific one.
        delete_me_message = "Are you sure you want to permanently delete the page: \n{{$page->id.'-'.$page->name}}?";






        ///////////////////////////////////////////////////////////////////////////////////////////
        // Menus-Pages front-end validation rules

        // Setup the handles.
        /* DEPRECATED
        var v_type = $("#type");
        var v_http_get_head = $("#http_get_head");
        var v_put_patch = $("#http_put_patch");
        var v_post = $("#http_post");
        var v_delete = $("#http_delete");
        */

        /**
         * Check the state of all of the HTTP Method checkboxes to see if they are all on or not.
         * @returns {boolean}
         */
        /* DEPRECATED
        function all_http_methods_checked() {
            if (
                v_http_get_head.is(':checked') &&
                v_put_patch.is(':checked') &&
                v_post.is(':checked') &&
                v_delete.is(':checked')
            ) {
                return true;
            } else {
                return false;
            }
        }
         */


        ///////////////////////////////////////////////////////////////////////////////////////////
        // AUTO-SET Front-end rules
        // If the Page "type" is a resource.
        /* DEPRECATED
        v_type.change(function() {
           if (v_type.val() == "resource") {
               // Check all of the HTTP Method Types
               v_http_get_head.prop("checked", true);
               v_put_patch.prop("checked", true);
               v_post.prop("checked", true);
               v_delete.prop("checked", true);
           }

            if ( v_type.val() != "resource" && all_http_methods_checked() ) {
                // Uncheck all of the HTTP Method Types
                v_http_get_head.prop("checked", false);
                v_put_patch.prop("checked", false);
                v_post.prop("checked", false);
                v_delete.prop("checked", false);
            }

        });
        */




    </script>
@endsection ('base_js')