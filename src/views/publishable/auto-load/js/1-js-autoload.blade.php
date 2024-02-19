{{--
 * The auto-load file associated with the eco-helpers config file array
 * entry for 'auto_loaders' => [1 => 'name']
 *
 *  Usage (available in the controller as):
 *     ehLayout::setAutoload('name'); or ehLayout::setAutoload(1);
 *
 --}}


{{--TODO: Is there a way to move this into a "base" template somewhere so it
    can't be accidently removed. --}}
{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #1
    ///////////////////////////////////////////////////////////////////////////////////////////
    Catch any form change and display an Unsaved changes message. --}}

<script type="text/javascript">
    $("form.form-crud").change(function () {

        // Update the system flash message on any form change.

        {{--TODO: this is using the permissions system to check for ADD or EDIT rights.
            Use new getUserRights() ?
        --}}
        {{-- @if($form['right']['ADD'] || $form['right']['EDIT']) --}}
        @if (true)
        $('#layout-page-flash').html('You have <strong>unsaved</strong> changes.');
        @endif

    });
</script>
