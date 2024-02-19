{{--
 * The auto-load file associated with the eco-helpers config file array
 * entry for 'auto_loaders' => [3 => 'name']
 *
 *  Usage (available in the controller as):
 *     ehLayout::setAutoload('name'); or ehLayout::setAutoload(3);
 *
--}}



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #3
    ///////////////////////////////////////////////////////////////////////////////////////////
    Date/Time picker. --}}

<!-- JQuery Date Time Picker -->
<script type="text/javascript" src="{{ config('path.DTPICKER') }}/jquery.datetimepicker.full.js"></script>
{{-- Install the date time picker handlers
Note: these are created in each action_form_vars file by a call to createDatePickerFields($this->t);
--}}
{{--
<script type="text/javascript">
    $(function() {
        {{ form.datepickerjs | raw }}
    });
</script>
--}}
