{{--
    Sample Eco-Helpers footer file.
    You can either replace this code or point the configuration setting in eco-helpers.php
    to your footer template file.
     'footer_file' => 'your-footer-template',   !! Note: must be placed in the views/ecoHelpers folder !!
    --}}
@inject('settings','ScottNason\EcoHelpers\Classes\ehConfig')
<style>
    footer {
        color: gray;
        font-size: smaller;
        margin-block: 0;
        margin-inline: 0;
        padding-block: 10px;
        padding-inline: 14px;
    }

    footer p {
        margin-block: 0.3rem;
        margin-inline: 0;
    }

    footer a {
        color: gray;
    }

    footer a:hover {
        color: antiquewhite;
    }
</style>
<footer class="footer fixed-bottom bg-dark">
    <div class="row">

        <div class="col-md">
            <p class="text-left">{{ config('app.name') }} {{ $settings::get('APP_VER') }} ({{ config('app.env') }})</p>
            <p class="text-left">{{$settings::get('message_copyright')}}</p>
        </div>

        <div class="col-md">
            <p class="text-center">
                <a href="#">home</a> |
                <a href="#">music</a> |
                <a href="#">video</a> |
                <a href="#">about np</a> |
                <a href="#">contact us</a> |
                <a href="#">news</a> |
                <a href="#">links</a>
            </p>
        </div>

        <div class="col-md">
            <p class="text-end">Last Update: {{ $settings::get('APP_LASTUPDATE') }}</p>
        </div>

    </div>
</footer>