{{--
 * The autoload file associated with the eco-helpers config file array
 * entry for 'auto_loaders' => [5 => 'name']
 *
 *  Usage (available in the controller as):
 *     ehLayout::setAutoload('name'); or ehLayout::setAutoload(5);
 *
 --}}



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #5
    ///////////////////////////////////////////////////////////////////////////////////////////
    Chosen multi-select. --}}

<!-- Chosen functions-->
<script type="text/javascript" src="{{ config('path.CHOSEN') }}/chosen.jquery.js"></script>
<script type="text/javascript" class="init">
    // Cosen multi-select function setup
    var config = {
        '.chosen-select': {},
        '.chosen-select-deselect': {allow_single_deselect: true},
        '.chosen-select-no-single': {disable_search_threshold: 10},
        '.chosen-select-no-results': {no_results_text: 'Oops, nothing found!'},
        '.chosen-select-width': {width: "95%"}
    }
    for (var selector in config) {
        $(selector).chosen(config[selector]);
    }
</script>


