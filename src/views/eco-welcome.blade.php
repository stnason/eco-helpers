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
                <p>These are the main page areas that can be set in the controller.</p>
                <p>Take a look at: <strong>App/Http/Controller/ehHomeController.php</strong> to see how to toggle the
                    display areas on and off and override their default contents using the <strong>ehLayout</strong> class (<em>mouse over to see the page area affected</em>).</p>
                <ul>
                    <li id="ehLayout-init"><strong>ehLayout::init()</strong></li>
                    <li id="ehLayout-setBanner">ehLayout::setBanner()</li>
                    <li id="ehLayout-setOptionBlock">ehLayout::setOptionBlock()</li>
                    <li id="ehLayout-setName">ehLayout::setName()</li>
                    <li id="ehLayout-setDescription">ehLayout::setDescription()</li>
                    <li id="ehLayout-setLinkbar">ehLayout::setLinkbar()</li>
                    <li id="ehLayout-setDynamic">ehLayout::setDynamic()</li>
                    <li id="ehLayout-setFlash">ehLayout::setFlash()</li>
                    <li id="ehLayout-setAttention">ehLayout::setAttention()</li>
                    <li id="ehLayout-getLayout">$form['layout'] = ehLayout::getLayout()</li>
                </ul>
                <p>...then pass <strong>$form['layout']</strong> to the view.</p>

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
                            <li class="small">Take a look at the System Settings to see what the System Administrator has access to. <a href="/settings">Settings & Configuration</a></li>
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
    <script>
    $(document).ready(function () {



        // Highlight the page areas when mousing over the ehLayout functions:
        $("#ehLayout-setBanner").on("mouseover", function() {
            $("#eh-layout-page-banner").css("background-color", "yellow");
            $("#ehLayout-setBanner").css("cursor", "pointer");
        }).on("mouseout", function() {
            $("#eh-layout-page-banner").css("background-color", "");
        });

        $("#ehLayout-setOptionBlock").on("mouseover", function() {
            $("#eh-layout-page-option-block").css("background-color", "yellow");
            $("#ehLayout-setOptionBlock").css("cursor", "pointer");
        }).on("mouseout", function() {
            $("#eh-layout-page-option-block").css("background-color", "");
        });

        $("#ehLayout-setName").on("mouseover", function() {
            $("#eh-layout-page-name").css("background-color", "yellow");
            $("#ehLayout-setName").css("cursor", "pointer");
        }).on("mouseout", function() {
            $("#eh-layout-page-name").css("background-color", "");
        });

        $("#ehLayout-setDescription").on("mouseover", function() {
            $("#eh-layout-page-description").css("background-color", "yellow");
            $("#ehLayout-setDescription").css("cursor", "pointer");
        }).on("mouseout", function() {
            $("#eh-layout-page-description").css("background-color", "");
        });

        $("#ehLayout-setLinkbar").on("mouseover", function() {
            $("#eh-layout-page-linkbar").css("background-color", "yellow");
            $("#ehLayout-setLinkbar").css("cursor", "pointer");
        }).on("mouseout", function() {
            $("#eh-layout-page-linkbar").css("background-color", "");
        });

        $("#ehLayout-setAttention").on("mouseover", function() {
            $("p#eh-layout-page-attention").removeClass('bg-info');
            $("#eh-layout-page-attention").css("color", "black");
            $("p#eh-layout-page-attention").css("background-color", "yellow");      {{-- can't change background here. ?--}}
            $("#ehLayout-setAttention").css("cursor", "pointer");
        }).on("mouseout", function() {
            $("p#eh-layout-page-attention").addClass('bg-info');
            $("#eh-layout-page-attention").css("color", "");
        });

        $("#ehLayout-setDynamic").on("mouseover", function() {
            $("#eh-layout-page-dynamic").css("background-color", "yellow");
            $("#ehLayout-setDynamic").css("cursor", "pointer");
        }).on("mouseout", function() {
            $("#eh-layout-page-dynamic").css("background-color", "");
        });



        $("#ehLayout-setFlash").on("mouseover", function() {
            $("#eh-layout-page-flash").css("background-color", "yellow");
            $("#ehLayout-setFlash").css("cursor", "pointer");
        }).on("mouseout", function() {
            $("#eh-layout-page-flash").css("background-color", "");
        });



    });
    </script>

@endsection ('base_js')













