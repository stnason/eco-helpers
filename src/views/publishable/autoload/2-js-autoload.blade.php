{{--
 * The autoload file associated with the eco-helpers config file array
 * entry for 'auto_loaders' => [2 => 'name']
 *
 *  Usage (available in the controller as):
 *     ehLayout::setAutoload('name'); or ehLayout::setAutoload(2);
 *
 --}}



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #2
    ///////////////////////////////////////////////////////////////////////////////////////////
    Date (only) picker. --}}

<!-- JQuery Date Picker -->
<script   src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"
          integrity="sha256-xLD7nhI62fcsEZK2/v8LsBcb4lG7dgULkuXoXB/j91c="
          crossorigin="anonymous"></script>

{{-- Install the date picker handlers
     Remember that any element that needs a data picker will need the
      'additional_class'=>'datepicker' added.
--}}
<script type="text/javascript">
    $(".datepicker").datepicker();
</script>


