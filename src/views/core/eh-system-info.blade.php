{{-- Standard form information header; for end-user form content headings.
     Note; calling template must set the $model variable to the current working model for that page.
--}}

{{-- w/o rollup/rolldown.
<p class="form-header-information">system information:</p>
<div class="row">
--}}

{{-- With rollup/rolldown. --}}
<div><p class="form-header-security" data-bs-toggle="collapse" data-bs-target=".multi-system-information">
        system information:</p></div>
<div class="row collapse multi-system-information">

    {{-- Left column of form data. --}}
    <div class="col-md">
        <div class="form-group d-inline-flex flex-wrap">
            {!! $control::label(['field_name'=>'updated_by', 'display_name'=>$model, 'errors'=>$errors]) !!}
            {!! $control::input(['field_name'=>'updated_by', 'model'=>$model, 'disabled'=>true, 'errors'=>$errors]) !!}

            {!! $control::label(['field_name'=>'created_by', 'display_name'=>$model, 'errors'=>$errors]) !!}
            {!! $control::input(['field_name'=>'created_by', 'model'=>$model, 'disabled'=>true, 'errors'=>$errors]) !!}
        </div>
    </div>

    {{-- Right column of form data. --}}
    <div class="col-md">
        <div class="form-group d-inline-flex flex-wrap">
            {!! $control::label(['field_name'=>'updated_at', 'display_name'=>$model, 'errors'=>$errors]) !!}
            {!! $control::input(['field_name'=>'updated_at', 'model'=>$model, 'disabled'=>true, 'date_long'=>true, 'errors'=>$errors]) !!}

            {!! $control::label(['field_name'=>'created_at', 'display_name'=>$model, 'errors'=>$errors]) !!}
            {!! $control::input(['field_name'=>'created_at', 'model'=>$model, 'disabled'=>true, 'date_long'=>true, 'errors'=>$errors]) !!}
        </div>
    </div>
</div>