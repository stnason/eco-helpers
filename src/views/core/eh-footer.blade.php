@inject('settings','ScottNason\EcoHelpers\Classes\ehConfig')
<style>
    /*
    .footer {
        align-items: center;
        padding-bottom: 0;
        margin: 0;
        padding-top: 10px; /* Can't explain why but I need this to approximate a vertical center.
        padding-left: 14px;
        padding-right: 14px;
        color: white;
        background: rgba(0, 0, 0, 0.7) !important;
    }
*/

    /*
        .footer p {
            padding: 0;
            margin: 0;
            vertical-align: middle;
            /*margin-top: -2px;
            font-weight: lighter;
            color: floralwhite;
        }
    */
    .footer p {
        margin-top: 0;
        margin-bottom: 0;
    }
    .footer a {
        color: gray;
    }
    .footer a:hover {
        color: antiquewhite;
    }
</style>
<div class="footer fixed-bottom bg-dark">
    <div class="row">
        <div class="col-md">
            <p class="text-left">{{ config('app.name') }} {{ config('version.APP_VER') }} ({{ config('app.env') }})</p>
            <p class="text-left">{{$settings::get('message_copyright')}}</p>
        </div>

        <div class="col-md-4">

            <p class="text-center">
                <a href="#">home</a> |

                {{--
                <a href="{{route('music')}}">music</a> |
                <a href="{{route('videos')}}">video</a> |
                <a href="{{route('writing')}}">writing</a> |
                <a href="#">the Creative Journey</a> |
                <a href="{{route('about-us')}}">about np</a> |
                <a href="{{route('contact-us')}}">contact us</a> |
                <a href="{{route('news')}}">news</a> |
                <a href="{{route('links')}}">links</a> |
                --}}

                <a href="_depts/0-main/downloads.php">downloads</a>
            </p>

        </div>

        <div class="col-md">
            <p class="text-end">Last Update: {{ config('version.APP_LASTUPDATE') }}</p>
        </div>
    </div>
</div>






