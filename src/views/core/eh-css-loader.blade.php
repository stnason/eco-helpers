{{-- ecoHelpers Auto-Loader System --}}

{{-- ###################################################################################################################### --}}
{{-- AUTO-LOADER CSS FILES.
        Note: the auto-loader mechanism (ehLayout::setAutoload()) always includes the '0' array key by default. --}}
{{-- ###################################################################################################################### --}}

{{-- Loop through the auto_load array and include any called for. --}}
@foreach($form['layout']['auto_load'] as $key=>$auto_load)
    {{-- Remember: in ehLayout::setAutoload(),
           $auto_load will be set to 'global' for the [0] index key.
           $auto_load is normally set to the name of the auto-load requested.
           $auto_load can be set to an additional $parameter name if passed.
           --}}
   @if (isset($auto_load))
       @include('ecoHelpers.auto-load.'.$key.'-css-autoload')
   @endif
@endforeach