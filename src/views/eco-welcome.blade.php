@extends('ecoHelpers::core.eh-app-template')
@inject('eh_roles', 'ScottNason\EcoHelpers\Models\ehRole')
{{--
    Add any class includes here.
    --}}


@section ('base_head')
    {{--
        Place any additoinal html head entries here.
        Most commonly used for per page <style> entries.
        --}}
<style>
    /* Fixed height for the eco splash page cards. */
    .fixed-height {
        height: 430px;
    }
</style>
@endsection ('base_head')


@section ('base_body')
    {{--
        Main page body content in here.
        This is positioned under the main nav header and over the footer.
        --}}


    <div class="container text-left">
        <div class="row align-items-start fixed-height">

            <div class="col bg-light rounded h-100 m-2 ps-4 pe-4">

                <h5 class="text-center pt-2">Sample Controller</h5>
                <p>Take a look at: <strong>App/Http/Controller/ehHomeController.php</strong> to see how to toggle the
                    display
                    areas on and off using the <strong>ehLayout</strong> class.</p>
                <ul>
                    <li>ehLayout::init()</li>
                    <li>ehLayout::setAttention()</li>
                    <li>ehLayout::setDynamic()</li>
                    <li>ehLayout::setOptionBlock()</li>
                    <li>ehLayout::setBanner()</li>
                </ul>

                <ul>
                @auth

                    <h5>Authentication Information:</h5>
                    <li>User Name: {{Auth()->user()->name}}</li>
                    <li>Default role: {{Auth()->user()->default_role}}-
                        {{$eh_roles::find(Auth()->user()->default_role)->name}}</li>
                    <li>Acting role: {{Auth()->user()->acting_role}}-
                        {{$eh_roles::find(Auth()->user()->acting_role)->name}}</li>
                    <li>Timezone: {{date_default_timezone_get()}}</li>

                @else
                    <li>User is not logged in.</li>
                @endauth
                </ul>

            </div>
            <div class="col bg-light rounded h-100 m-2 ps-4 pe-4">

                <h5 class="text-center pt-2">Configurations</h5>
                <p>Some quick settings in the <strong>eco-helpers.php</strong> config file:</p>
                <ul>
                    <li>Access Control
                    <ul>
                        <li class="small">Enable the access system to allow the user login and security control of pages and routes.</li>
                    </ul>
                    </li>

                    <li>Menus & Pages
                        <ul>
                            <li class="small">Enable the menu system to show the dropdown menu in the top navbar. Nested menus are created directly from the entries in the <a href="/pages">pages table</a>.</li>
                        </ul>
                    </li>

                    <li><a href="/config">Settings & Configuration</a></li>
                </ul>

            </div>
            <div class="col bg-light rounded h-100 m-2 ps-4 pe-4">

                <h5 class="text-center pt-2">More Information</h5>
                <p>Sample Logins:</p>
                <ul>
                    <li>ehAdmin@email.com / ehAdmin</li>
                    <li>ehUser@email.com / ehUser</li>
                </ul>


                <p><a target="+blank" href="https://github.com/stnason/eco-helpers/tree/main/documentation">Documentation</a></p>

            </div>

        </div>

    </div>

@endsection ('base_body')


@section ('base_js')
    {{--
        Any per page javascript goes here..
        This is positioned at the bottom of the page, right before the closeing </body></html> tags.
        --}}

@endsection ('base_js')













