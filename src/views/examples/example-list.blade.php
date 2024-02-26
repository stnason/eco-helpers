@extends('ecoHelpers::core.eh-app-template')


@section ('base_head')
    <style>

    </style>
@endsection ('base_head')


@section ('base_body')

    <div class="container">

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
            @foreach ($form['examples'] as $example)

                {{-- COLORIZE - Set the class of the row for colorizing In Active --}}
                @php $class = 'dt-active'; @endphp
                @if ($example->active !== 1)
                    @php $class = 'text-muted'; @endphp
                @endif

                {{-- COLORIZE - Set the class of the row for colorizing In ROLE --}}
                @if ($example->site_admin == 1)
                    @php $class .= ' bg-danger'; @endphp
                @endif


                {{-- Loop (horizontal) the data rows from the $usefield array --}}
                {{-- Field processing loop --}}
                <tr $class="{{$class}}">

                    @foreach ($form['use_fields'] as $key=>$value)
                        @php $continue = false; @endphp

                        <td class="{{ $class }}">

                            {{-- Group ID/Name link to Group Profile --}}
                            @if ($key == 'name' || $key=='id')
                                <a class="{{ $class }}" href="{{ config('app.url') }}/examples/{{ $example->id }}">{{ $example->$key }}</a>
                                {{-- create a faux php continue statement --}}
                                @php $continue = true; @endphp
                            @endif

                            {{-- Turn 0/1 into No/Yes. --}}
                            @if ($key == 'active' || $key == 'site_admin' || $key == 'restrict_flag')
                                {{ ($example->$key === 1 ? "Yes" : "No") }}
                                {{-- create a faux php continue statement --}}
                                @php $continue = true; @endphp
                            @endif


                            {{-- otherwise display key wihtout a link --}}
                            @if (!$continue)   {{-- create a faux php continue statement --}}
                            {{ $example->$key }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach


            </tbody>
        </table>
    </div>


    {{--
        Set the sort order and sort column.
        This needs to proceed any of the footer js applied in the standard footer
        perpagejs below is too late (it's applied after the datatables initi script runs).
        --}}
    <script type="application/javascript">
        dtsortcolumn = 3;           // 0-based column name to sort by
        dtsortdirection = "asc";    // Either asc or dec.
    </script>
@endsection ('base_body')



@section ('base_js')
    <script type="text/javascript">

        $(document).ready(function () {

            // An alternate way to colorize the datatables cells if the sorting process overwrites if for some reason.
            //  (sorting_1) are being overwritten by Datatables after the fact. So, in some cases, may need to do it here instead of in the template code.
            // $('td.dt-active').css('background', 'lightgreen');   // You can use the template code to a apply a certain css class/
            // $('td.dt-inactive').css('background', 'yellow');     // Then set the color of that class here.

        });

    </script>
@endsection ('base_js')