<section id="eh-layout-page-flash-attention-wrapper">
    {{--
        ### FLASH AREA ###
        This is the system wide flash message. It can be turned off like all the others
        But when overriding the standard page layout just remember to put it back in somewhere!
        USAGE: ehLayout::setFlash('text', 'css-class');
            // !! Note this is not commonly called this way since it's part of the crud flash system !!
            --}}
    @if ($form['layout']['flash']['state'])
        <div class="{{$form['layout']['flash']['class']}}" id="eh-layout-page-flash">
            {{-- This is the standard flash message --}}
            @if (session('message'))
                {!! session('message') !!}
            @endif
            {{-- And this comes from the default Laravel error system. --}}
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <li>{!!  $error !!}</li>
                @endforeach
            @else
                {{-- system flash message place holder - to keep the layout intact. --}}
                &nbsp;
            @endif
        </div>
    @endif
    {{--
        ### ATTENTION MESSAGE ###
        This is the page attention message.
        It is used to show special conditions such as "Archived" or "In Active".
        USAGE: ehLayout::setFlash('text', 'css-class');
        --}}
    @if ( ($form['layout']['attention']['state']) && !empty($form['layout']['attention']['content']) )
        <p id="eh-layout-page-attention" class="{{$form['layout']['attention']['class']}}">
            {!! $form['layout']['attention']['content'] !!}
        </p>
        {{--
        This causes a line of the defined background color to show when we turn off the area.
        @else
            <p id="eh-layout-page-attention" class="{{$form['layout']['attention']['class']}}">
            </p>
        --}}
    @endif
</section>
