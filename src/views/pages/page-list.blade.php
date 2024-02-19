@extends('ecoHelpers::core.eh-app-template')
@section ('base_head')
    {{-- specific css to get the Menu & Page List to Display  --}}
    <link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/eh-page-list.css')}}">

    {{-- Disable all of the auto-generated links in the legend display.
         (because the id's are not real and will throw an exception when they don't line up with real page id's)
    --}}
    <style>
    ul#legend {
    pointer-events: none;
    cursor: default;
    }
    </style>

@endsection ('base_head')

@section ('base_body')

    <div class="container pt-4 pl-5 pr-5">

        <div class="row">

            {{-- Left hand column (main content) --}}
            <div class="col-md gx-4">
                <h4 class="ms-3">Pages Tree {!! $form['tree_layout_explanation'] !!}</h4>
                {{-- Build the recursive menu structure with the appropriate css formatting --}}
                <ul id="page-tree" class="tree-view">

                    @if (!config('eco-helpers.menus.enabled'))
                        <li>THE MENUS SYSTEM IS NOT ENABLED</li>
                        <li>(you can control this in the eco-helpers config file)</li>
                    @endif

                    @foreach($form['layout']['pages_all'] as $page_item)
                        @include('ecoHelpers::core.eh-child-pages')
                    @endforeach

                </ul>
            </div>

            <div class="col-md gx-4">
                <h4 class="ms-3">Legend {!! $form['tree_layout_explanation'] !!}</h4>
                <ul id="legend" class="tree-view">

                    @foreach($form['layout']['pages_legend'] as $page_item)
                        @include('ecoHelpers::core.eh-child-pages')
                    @endforeach

                </ul>
            </div>

        </div>

    </div>

@endsection ('base_body')

@section ('base_js')
@endsection ('base_js')