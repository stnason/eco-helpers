{{--
 * The autoload file associated with the eco-helpers config file array
 * entry for 'auto_loaders' => [0 => 'name']
 *
 * Note: Auto-loader 0 is reserved for the global (app pages) css and js.
 *
 --}}


{{-- ###################################################################################################################### --}}
{{-- SECTION 1 - STATICALLY CALLED JAVASCRIPT FILES. --}}
{{-- ###################################################################################################################### --}}

{{-- Latest version of jquery & bootstrap js --}}
{{-- Local copies:
<script type="text/javascript" src="{{ config('path.JQ') }}/jquery.js"></script>
<script type="text/javascript" src="{{ config('path.BS') }}/js/bootstrap.js"></script>
--}}
{{-- CDN copies: --}}

{{-- jQuery --}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


{{-- Bootstrap 5.3; MOVED TO 0-css-autoload; This has to be in the css (top) loader in order for page elements like the navbar to have access.
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
--}}

{{-- Check for dark or light system theme (mine) and install a listener to change on the fly.
<script src="{{asset('js/check-system-theme.js')}}"></script> --}}

{{-- Bootstrap theme toggler code.
<!-- theme toggler included from the 0-js-autoload.blade.php file. -->
<script type="text/javascript" src="{{asset("js/bs-theme-toggler.js")}}"></script> --}}

{{-- Ensure the logo is updated (code in bs-them-toggler) to the appropraite one for the selected theme.
<script type="text/javascript" src="{{asset('js/update-logo.js')}}"></script> --}}

{{--
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous"></script>
--}}



