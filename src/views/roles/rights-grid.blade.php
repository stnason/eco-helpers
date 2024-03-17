{{-- The actual rights display for each page listed. --}}
<table class="rights-grid">
    <thead>
    <tr>

        {{--
        <th class="grid-col-0">X</th>
        <th class="grid-col-1 rightscheckbox warning">a</th>
        <th class="grid-col-1">No</th>
        --}}
        <th class="grid-col-2">Name</th>
        <th class="grid-col-3">Vw</th>
        <th class="grid-col-4">XRes</th>
        <th class="grid-col-5">XDis</th>
        <th class="grid-col-6">Edt</th>
        <th class="grid-col-7">Add</th>
        <th class="grid-col-8">Del</th>
        <th class="grid-col-9">XTbl</th>
        {{--
        <th class="grid-col-10">F1</th>
        <th class="grid-col-10">F2</th>
        <th class="grid-col-10">F3</th>
        <th class="grid-col-10">F4</th>
        --}}

        <th class="grid-col-13">F1 / F3</th>
        <th class="grid-col-13">F2 / F4</th>
        <th class="grid-col-14">Comment</th>
    </tr>
    </thead>


@foreach($form['page_list'] as $key=>$this_page)

    <tr>
        {{--
        <td class="grid-col-0">{!! $this_page['delete_override'] ?? '' !!}</td>
        <td><button class="btn btn-primary btn-sm">all</button></td>
        --}}
        <td class="grid-col-2">
            <div class="{{$this_page['display_class']}}">
                <a target="_blank" href="{{ config('app.url') }}/pages/{{ $key }}">{{$key}}-{{ $this_page['name'] }}</a>

                {{-- display the security access level for this page. --}}
                @if($this_page['security']==0) (none) @endif
                @if($this_page['security']==1) (public) @endif
                @if($this_page['security']==2) (auth) @endif
                @if($this_page['security']==3) (full) @endif

            </div></td>
        <td class="grid-col-3">{!! $this_page['page_bit_view'] !!}</td>
        <td class="grid-col-4">{!! $this_page['page_bit_export_restricted'] !!}</td>
        <td class="grid-col-5">{!! $this_page['page_bit_export_displayed'] !!}</td>
        <td class="grid-col-6">{!! $this_page['page_bit_edit'] !!}</td>
        <td class="grid-col-7">{!! $this_page['page_bit_add'] !!}</td>
        <td class="grid-col-8">{!! $this_page['page_bit_delete'] !!}</td>
        <td class="grid-col-9">{!! $this_page['page_bit_export_table'] !!}</td>

        {{--
        <td class="grid-col-10">{!! $this_page['page_bit_feature_1'] !!}</td>
        <td class="grid-col-10">{!! $this_page['page_bit_feature_2'] !!}</td>
        <td class="grid-col-10">{!! $this_page['page_bit_feature_3'] !!}</td>
        <td class="grid-col-10">{!! $this_page['page_bit_feature_4'] !!}</td>


        {{-- Build a conditional label (F1-F4) in front of each non-empty feature comments.
        @php
            $f1 = ''; $f2 = ''; $f3 = ''; $f4 = '';
            if (!empty($this_page['feature_1'] )) {$f1 = 'F1-'.$this_page['feature_1']; }
            if (!empty($this_page['feature_2'] )) {$f2 = 'F2-'.$this_page['feature_2']; }
            if (!empty($this_page['feature_3'] )) {$f3 = 'F3-'.$this_page['feature_3']; }
            if (!empty($this_page['feature_4'] )) {$f4 = 'F4-'.$this_page['feature_4']; }
        @endphp
        <td class="grid-col-13">{{$f1}}</br>{{$f3}}</td>
        <td class="grid-col-13">{{$f2}}</br>{{$f4}}</td>
        --}}


        <td class="grid-col-13">
            <table class="feature-grid">
                <tr class="feature-grid-top-row">
                    <td class="feature-grid-label">F1</td>
                    <td class="feature-grid-checkbox">{!! $this_page['page_bit_feature_1'] !!}</td>
                    <td class="feature-grid-text">{{$this_page['feature_1']}}</td></tr>
                <tr><td class="feature-grid-label">F3</td>
                    <td class="feature-grid-checkbox">{!! $this_page['page_bit_feature_3'] !!}</td>
                    <td class="feature-grid-text">{{$this_page['feature_3']}}</td></tr>
            </table>
        </td>
        <td class="grid-col-13">
            <table class="feature-grid">
                <tr class="feature-grid-top-row">
                    <td class="feature-grid-label">F2</td>
                    <td class="feature-grid-checkbox">{!! $this_page['page_bit_feature_2'] !!}</td>
                    <td class="feature-grid-text">{{$this_page['feature_2']}}</td></tr>
                <tr><td class="feature-grid-label">F4</td>
                    <td class="feature-grid-checkbox">{!! $this_page['page_bit_feature_4'] !!}</td>
                    <td class="feature-grid-text">{{$this_page['feature_4']}}</td></tr>
            </table>
        </td>


        <td class="grid-col-14">{!! $this_page['comment'] !!}</td>

    </tr>
@endforeach
</table>