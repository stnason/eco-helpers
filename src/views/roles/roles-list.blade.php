@extends('ecoHelpers::core.eh-app-template')
@inject('page', 'ScottNason\EcoHelpers\Models\ehPage')

@section ('base_head')
@endsection ('base_head')

@section ('base_body')

<table id="datatable" class="{{ config('eco-helpers.datatables_class') ?? '' }}">
    <thead>
        <tr>
            @foreach ($form['use_fields'] as $field=>$label)
                <th>{{ $label }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>

    {{-- Loop (vertical) the records in the result set query by the $usefields array. --}}
    @foreach ($form['roles'] as $role)

        {{-- COLORIZE - Set the class of the row for colorizing In Active --}}
        @php $class = 'dt-active'; @endphp
        @if ($role->active !== 1)
            @php $class = 'text-muted'; @endphp
        @endif

        {{-- COLORIZE - Set the class of the row for colorizing In ROLE --}}
        @if ($role->site_admin == 1)
            @php $class .= ' bg-warning'; @endphp
        @endif

        {{-- Loop (horizontal) the data rows from the $usefield array --}}
        {{-- Field processing loop --}}
        <tr $class="{{$class}}">

            @foreach ($form['use_fields'] as $key=>$value)
                @php $continue = false; @endphp

                <td class="{{ $class }}">

                    {{-- Role ID/Name link to Role Profile --}}
                    @if ($key == 'name' || $key=='id')
                        <a class="{{ $class }}" href="{{ config('app.url') }}/roles/{{ $role->id }}">{{ $role->$key }}</a>
                        {{-- create a faux php continue statement --}}
                        @php $continue = true; @endphp
                    @endif

                    {{-- Turn 0/1 into No/Yes. --}}
                    @if ($key == 'active' || $key == 'site_admin' || $key == 'restrict_flag')
                        {{ ($role->$key === 1 ? "Yes" : "No") }}
                        {{-- create a faux php continue statement --}}
                        @php $continue = true; @endphp
                    @endif

                    {{-- Default home page --}}
                    @if ($key == 'default_home_page')
                        @php $hp = $page::getPageInfo($role->default_home_page); @endphp

                        <a class="{{ $class }}" href="{{ config('app.url') }}/pages/{{ ($hp ? $hp->id : '') }}">
                            {{ ($role->default_home_page == 0 ? "Home" : $hp->name) }}</a>

                        {{-- create a faux php continue statement --}}
                        @php $continue = true; @endphp
                    @endif

                    {{-- otherwise display key wihtout a link --}}
                    @if (!$continue)   {{-- create a faux php continue statement --}}
                    {{ $role->$key }}
                    @endif
                </td>
            @endforeach
        </tr>
    @endforeach

    </tbody>
</table>



{{--
    Set the sort order and sort column.
    This needs to proceed any of the footer js applied in the standard footer
    perpagejs below is too late (it's appliced after the datatables script runs).
    --}}
<script type="application/javascript">
    dtsortcolumn = 3;           // Role Name
    dtsortdirection = "asc";
</script>
@endsection ('base_body')

@section ('base_js')
@endsection ('base_js')