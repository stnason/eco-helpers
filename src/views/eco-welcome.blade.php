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
        height: 630px;
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

            <div class="col bg-light rounded h-100 m-2 ps-4 pe-4 overflow-scroll">

                <h4 class="text-center pt-2">Sample Controller</h4>
                <p>Take a look at: <strong>App/Http/Controller/ehHomeController.php</strong> to see how to toggle the
                    display areas on and off and override their default contents using the <strong>ehLayout</strong> class.</p>
                <ul>
                    <li>ehLayout::init()</li>
                    <li>ehLayout::setName()</li>
                    <li>ehLayout::setAttention()</li>
                    <li>ehLayout::setDynamic()</li>
                    <li>ehLayout::setOptionBlock()</li>
                    <li>ehLayout::setBanner()</li>
                    <li>ehLayout::getLayout()</li>
                </ul>
                <p>...then pass the layout to the view.</p>

            </div>

            <div class="col bg-light rounded h-100 m-2 ps-4 pe-4 overflow-scroll">

                <h4 class="text-center pt-2">Configurations</h4>
                <p>Some quick settings in the <strong>eco-helpers.php</strong> config file:</p>
                <ul>
                    <li><strong>Access Control</strong>
                    <ul>
                        <li class="small">The <strong>Access Control System</strong> is enabled by default to allow the user login and security control of pages and routes. It can be turned off in the <strong>eco-helpers.php</strong> config file under the <strong>'access'</strong> section.</li>
                    </ul>
                    </li>

                    <li class="pt-2"><strong>Menus & Pages</strong>
                        <ul>
                            <li class="small">The hierarchical <strong>Menu System</strong> is enabled by default to show the dropdown menu in the top navbar. Nested menus are created directly in the <a href="/pages">Menus/Pages utility</a>. Menus can be turned off in the <strong>eco-helpers.php</strong> config file under the <strong>'menus'</strong> section.</li>
                        </ul>
                    </li>

                    <li class="pt-2"><strong>System Settings</strong>
                        <ul>
                            <li class="small">Take a look at the System Settings to see what the System Administrator has access to. <a href="/config">Settings & Configuration</a></li>
                        </ul>
                </ul>

            </div>

            <div class="col bg-light rounded h-100 m-2 ps-4 pe-4 overflow-scroll">

                <h4 class="text-center pt-2">More Information</h4>

                <h5>Sample Logins</h5>
                <ul>
                    <li>ehAdmin@email.com / ehAdmin</li>
                    <li>ehUser@email.com / ehUser</li>
                </ul>


                <ul>
                    <h5>Authenticated</h5>
                    @auth

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



                <ul>
                    <li><a target="+blank" href="https://github.com/stnason/eco-helpers/tree/main/documentation">Documentation</a></li>
                </ul>

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













