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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{--
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous"></script>
--}}



