{{-- ecoHelpers Auto-Loader System --}}

{{-- ###################################################################################################################### --}}
{{-- AUTO-LOADER JS FILES.
        Note: the autoloader mechanism (ehLayout::setAutoload()) always includes the '0' array key by default. --}}
{{-- ###################################################################################################################### --}}

{{-- Loop through the auto_load array and include any called for. --}}
@foreach($form['layout']['auto_load'] as $key=>$auto_load)
    @if (isset($auto_load))
        {{-- Remember: in ehLayout::setAutoload(),
            $auto_load will be set to 'global' for the [0] index key.
            $auto_load is normally set to the name of the autoload requested.
            $auto_load can be set to an additional $parameter name if passed.

            So, let's check and see if that additional parameter is some kind of
            additional init file to include (this is mostly for datatables client vs server-side)
        --}}

        @include('ecoHelpers.autoload.'.$key.'-js-autoload')

    @endif
@endforeach