@extends('ecoHelpers::core.eh-app-master-template')
{{--
    Add any class @injects here.
    --}}


@section ('additional-head')
    {{--
        Place any additional html head entries here.
        Commonly used for per page <style> entries.
        --}}

@endsection ('additional-head')


@section ('main-content')
    {{--
        Main page body content goes here.
        This is sandwitched in between the main nav header and the footer.
        --}}

@endsection ('main-content')


@section ('per-page-js')
    {{--
        Any per page javascript goes here.
        In the master app template, this is positioned at the bottom of the page,
        right before the closeing </body></html> tags.
        --}}

@endsection ('per-page-js')