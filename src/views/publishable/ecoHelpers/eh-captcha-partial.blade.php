{{-- ehCaptcha partial for form control, --}}
@inject('ehConfig','ScottNason\EcoHelpers\Classes\ehConfig')
{{--
<script>
    // Check for -- and load jQuery as necessary.
    // SAVE YOURSELF THE TROUBLE !! the timing on this is flaky at best.
    // Note: is this seems to give intermittent variable $ not found errors, then jQuery will have to be included
    // somewhere else; like the head of the calling page.
    /*
    if (typeof jQuery == 'undefined') {
        console.log('no jquery so load');
        // jQuery IS NOT loaded,
        // so, load it using plain JavaScript
        (function(){
            var newscript = document.createElement('script');
            newscript.type = 'text/javascript';
            //            newscript.async = true;                     wait (delete async or use defer) for this to load or the eh-captha script will fail.
            newscript.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
            newscript.crossOrigin = "anonymous";
            newscript.integrity = "sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=";
            (document.getElementsByTagName('head')[0]||document.getElementsByTagName('body')[0]).appendChild(newscript);
        })();
             */
}
</script>
--}}

{{-- Pass the form validation state to the eh-captcha.js script.
        eh-captcha.js is expecting this variable to know whether or not to pull a new image.
        we have to manage its state here.
       --}}
<script>
    var validation_error = false;
    @if ($errors->any())
            {{-- On any validation error. --}}
        validation_error = true;
    @else
        validation_error = false;
    @endif
</script>

<div id = "eh-captcha-partial">
    <div class="d-inline-flex">
        <p class="small">
            {!! $ehConfig::get('captcha.display') !!}
        </p>
    </div>
    <div class="d-inline-flex text-center">
        <img title="click after entering" alt="captcha image" id="captcha-image" src="" class="rounded me-1">
        <button id="refresh-button" type="button" class="btn btn-outline fa-solid fa-arrows-rotate fa-2xl"></button>
        <input class = "form-control" name="eh_captcha_input" id="eh_captcha_input" placeholder = " {{$ehConfig::get('captcha.placeholder')}}" value="{{old("eh_captcha_input")}}">
    </div>
</div>

{{-- This has to be called after the button creation so it has access to its id.
     NOTE: If you are placing the partial ABOVE the submit button then you'll have to
     include this at the bottom of the calling form.
--}}
<script src="/vendor/ecoHelpers/js/eh-captcha.js"></script>

