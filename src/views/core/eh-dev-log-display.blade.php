{{-- Development login display. --}}
@extends('ecoHelpers::core.eh-app-template')
@inject('control', 'ScottNason\EcoHelpers\Classes\ehControl')

@section ('base_head')

@endsection ('base_head')


@section ('base_body')


    <form class="eh-form-crud pb-0" method="{{$form['layout']['form_method']}}" action="{{ $form['layout']['form_action'] }}">
        @csrf
        @method($form['layout']['form_method'] ?? 'PATCH')

        <div class="d-inline-flex form-group">

            {{-- narcan_status:   1=Application;  2=eco Framework --}}
            {!! $control::radio([
                'field_name'=>'filter_which_log',
                'model'=>null,
                'auto_submit'=>"onclick=this.form.submit()",
                'errors'=>$errors,
                'radio'=>[1=>'Application', 2=>'ecoFramework']
                ]) !!}
        </div>

    </form>


    <div class="">

        <p>Total Time: {{ $form['total_time_spent']}} hours</p>


        @foreach($form['dev_log_as_array'] as $line)

            {!! $line !!}

            @if( str_contains($line,"version/ update history"))
                <p class="spacer-line"></p>
            @endif

        @endforeach

    </div>

@endsection ('base_body')

@section ('base_js')
    <script type="text/javascript">

        $(document).ready(function () {

        });

    </script>
@endsection ('base_js')
