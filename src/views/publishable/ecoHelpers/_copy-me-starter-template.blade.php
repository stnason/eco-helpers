@extends('ecoHelpers::core.eh-app-template')
{{--
    Add any class @injects here.
    --}}


@section ('base_head')
    {{--
        Place any additional html head entries here.
        Commonly used for per page <style> entries.
        --}}

@endsection ('base_head')


@section ('base_body')
    {{--
        Main page body content goes here.
        This is sandwitched in between the main nav header and the footer.
        --}}

@endsection ('base_body')


@section ('base_js')
    {{--
        Any per page javascript goes here.
        In the master app template, this is positioned at the bottom of the page,
        right before the closeing </body></html> tags.
        --}}

@endsection ('base_js')