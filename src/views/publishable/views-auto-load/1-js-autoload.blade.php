{{--
 * The auto-load file associated with the eco-helpers config file array
 * entry for 'auto_loaders' => [1 => 'name']
 *
 *  Usage (available in the controller as):
 *     ehLayout::setAutoload('name'); or ehLayout::setAutoload(1);
 *
 --}}

{{--MOVED THIS to the bottom of the "base" template so it can't accidently be left out. --}}
{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #1
    ///////////////////////////////////////////////////////////////////////////////////////////
    Catch any form change and display an Unsaved changes message. --}}
{{--
<script type="text/javascript">
    $("form.form-crud").change(function () {

        // Update the system flash message on any form input change.
        @if (true)
        $('#layout-page-flash').html('You have <strong>unsaved</strong> changes.');
        @endif

    });
</script>
--}}