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
            <p class="text-left">{{ config('app.name') }} ({{ config('app.env') }})</p>
            <p class="text-left">last update: {{config('version.APP_LASTUPDATE')}}</p>
        </div>

        <div class="col-md">
            <p class="text-center">
                <a href="#">link</a> |
                <a href="#">link</a> |
                <a href="#">link</a> |
                <a href="#">link</a>
            </p>
        </div>

        <div class="col-md">
            <p class="text-end">eco framework:
                {{ $settings::get('APP_VER') }} -
                {{ $settings::get('APP_LASTUPDATE') }}
            </p>
            <p class="text-end">{{$settings::get('message_copyright')}}</p>
        </div>

    </div>
</footer>
