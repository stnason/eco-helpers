{{-- ecoHelpers Auto-Loader System --}}

{{-- ###################################################################################################################### --}}
{{-- AUTO-LOADER JS FILES.
        Note: the auto-loader mechanism (ehLayout::setAutoload()) always includes the '0' array key by default. --}}
{{-- ###################################################################################################################### --}}

{{-- Loop through the auto_load array and include any called for. --}}
@foreach($form['layout']['auto_load'] as $key=>$auto_load)
    @if (isset($auto_load))
        @include('ecoHelpers.auto-load.js.'.$key.'-js-autoload')
    @endif
@endforeach