<?php

namespace ScottNason\EcoHelpers\Classes;

//use App\Traits\ehConvertDatesToSavable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * An html forms helper for building out various input fields and managing persistent data and error highlighting.
 *
 * Form/ input helpers
 *
 * passed parameters:
    * 'field_name'        => the actual database field name
    * 'display_name'      => either the static label text to display or a model instance to check against to see if it has label definitions
    * 'model'             => the associated model name
    * 'additional_class'  => can be used to add things like date/time picker, etc.
    * 'errors'            => the error object
    * 'error_class_text'  => the css class for a text error display
    * 'error_class_box'   => $the css class for a div error display
    * 'required'          => html required attribute (true or false)
    * 'placeholder'       => the placeholder text for this input
    * 'auto_submit'       => add and auto submit handler
    * 'link'              => add a <a> link (for labels)
    * 'target'            => the target attribute of the link <a> tag.
    * 'radio'             => radio button array ($btn_value=>$display)
    * 'disabled'          => html disabled attribute (true or false)
    * 'bold_before'       => beginning bold tag (label)
    * 'bold_after'        => ending bold tag (label)
    * 'value'             => the input value attribute
    * 'selections'        => $key=>$value pair array for select inputs
    * 'preselect'         => $key value of the preselected select value (if a default is desired on initial entry)
    * 'alert_if'          => add a def_alert_class to the additional_class if the controls value matches this.
    * 'alert_class'       => the class used for the alert_if function
    * 'add_blank'         => when building a <select> drop-down should there be a blank entry on top?
    * 'date_long'         => set true to force the system defined date_format_long (date + time).
 *
 * @package App\Classes
 */
Class ehControl
{
    // Moved these to the layout.options section of eco-helpers
    // These will all be checked and assigned at the top of processParameters().
    protected static $text_warning = '';        // Bootstrap or custom css class for the error label text.
    protected static $box_warning = '';         // Bootstrap or custom css class for the error input box.
    public static    $alert_if_class = '';      // When requesting an alert -- use this class.

    // Moved these to the controls.options section of eco-helpers.
    protected static $def_rows = null;          // Default rows for a text area input if nothing specified.
    protected static $def_add_blank = false;    // When add_blank is missing form the input, use this value.



    //protected static $cp = [];                // Model Custom Properties (disabled, required, labels)
                                                // These should be using public properties now.


    /**
     * Label creation helper
     *  - Will automatically bold the label on a validation error.
     *
     * @param array $parameters         - the parameters for this function
     * @return bool|string              - the completed html label
     */
    public static function label($parameters) {

        // Grab the parameters and check for minimum requirements and process them all into an internal $p array object.
        $p = self::processParameters($parameters);
        if ($p === false) {
            return false;
        }

        // Are we requesting a link for this label?
        $pre_link = '';
        $post_link = '';

        // Grab the pre-processed target attribute value from the parameters object.
        $target = $p['target'];


        // If a link is passed then build that out for the label.
        if (!empty($p['link'])) {
            $pre_link = '<a href="'.$p['link'].'" target="'.$target.'">';
            $post_link = '</a>';
        }

        // Build out the label with all of the parameters
        $label = '<label
        for="'.$p['field_name'].'
        " class="'.$p['error_class_text'].' '.$p['additional_class'].'">'.$pre_link.
            $p['bold_before'].$p['display_name'].$p['bold_after'].$post_link.'</label>'.config('app.nl');

        return $label;
    }


    /**
     * Text input creation helper.
     *
     * @param array $parameters         - the parameters for this function
     * @return bool|string              - the completed html input
     */
    public static function input($parameters) {

        // Grab the parameters and check for minimum requirements and process them all into an internal $p array object.
        $p = self::processParameters($parameters);

        if ($p === false) {
            return false;
        }

        // If this input is not readonly - and it is listed in the dates[] array for the model, then add the datepicker class.
        $date_class = "";
        if ($p['is_date'] && $p['disabled'] != 'readonly') {
            $date_class = "datepicker";     // This is the class that the jquery selector is looking for.
        }


        /*
        if($p['field_name'] == 'benchmark_volume') {
            dd($p['field_name'], $p['number_format']);
        }
        */

/* Umm.. this is being done somewhere else. (down around 640)
        if (!empty(ehConfig::get('date_format_php_short')) && $p['is_date']) {
            $t = $p['field_name'];
            //dd($p['field_name'],request()->$t);
            $time_format = ehConfig::get('date_format_php_short');
            //$p['value'] = $p['field_name']->format($time_format);
        }
*/

        $input = '<input
        class="form-control '.$p['error_class_box'].' '.$p['additional_class'].' '.$date_class.'"
        type="'.$p['type'].'"
        id="'.$p['field_name'].'"
        name="'.$p['field_name'].'"
        placeholder="'.$p['placeholder'].'"
        value="'.$p['value'].'" '.$p['disabled'].' '.$p['required'].'>'.config('app.nl');

        return $input;
    }

    /**
     * Textarea input creation helper.
     *
     * @param $parameters
     * @return bool|string
     */
    public static function textarea($parameters) {

        // Grab the parameters and check for minimum requirements and process them all into an internal $p array object.
        $p = self::processParameters($parameters);
        if ($p === false) {
            return false;
        }

        $input = '<textarea
        class="form-control form-textarea '.$p['error_class_box'].' '.$p['additional_class'].'"
        type="textarea"
        rows="'.$p['rows'].'"
        name="'.$p['field_name'].'"
        id="'.$p['field_name'].'"
        '.$p['disabled'].' '.$p['required'].'>'.$p['value'].'</textarea>';

        return $input;
    }

    /**
     * Radio button creation helper.
     *
     * @param $parameters
     * @return bool|string
     */
    public static function radio($parameters) {

        // Grab the parameters and check for minimum requirements and process them all into an internal $p array object.
        $p = self::processParameters($parameters);
        if ($p === false) {
            return false;
        }

        // Do we have a radio array defined?
        if (empty($p['radio'])) {
            return false;
        }

        // Note: this is getting kind of messy; see the $auto_submit processing near the bottom; it builds out a couple different calls already.
        $auto_submit = '';
        if ($p['auto_submit'] != '') {                     // For now just checking to see if something at all is set.
            $auto_submit = ' onclick="this.form.submit()" ';
        }


        $radio = '
        <div class="form-control form-radio '.$p['error_class_box'].' '.$p['additional_class'].'">';
        foreach($p['radio'] as $btn_value=>$display) {

            // Check to see which value is already checked.
            // Making sure to account for teh case where the stored value may be undetermined (null)
            //  rather than No or "0" -- that should show nothing checked.
            if ($p['value'] === $btn_value && $p['value'] !== null) {
                $checked = 'checked';
                $strongF = '<strong>';      // make the current selection label bold
                $strongB = '</strong>';
            } else {
                $checked = '';
                $strongF = '';
                $strongB = '';
            }

            // There is no 'readonly' attribute for a radio button.
            // But disabled elements are not submitted so, not completely sure of the consequences here.
            if ($p['disabled']=='readonly') {$p['disabled']='disabled';}


            // Create each radio button selection.
            // Note: There's a fair bit of css manipulation going on her to make the text selections clickable (see the override css).
            $radio .= '
            <div class="form-check form-check-inline">
                <label class="radio-empty"><input class="form-check-input"
                type="radio" value="'.$btn_value.'"
                name="'.$p['field_name'].'" '.$checked.' '.$p['disabled'].' '.$p['required'].$auto_submit.'>
                <span class="radio-text">'.$strongF.$display.$strongB.'</span>
            </label></div>';
        }
        $radio .='
        </div>';

        return $radio;
    }


    /**
     * Select input creation helper.
     *
     * @param $parameters
     * @return bool
     */
    public static function select($parameters) {

        // Grab the parameters and check for minimum requirements and process them all into an internal $p array object.
        $p = self::processParameters($parameters);
        if ($p === false) {
            return false;
        }

        // Do we have a select array defined?
        if (empty($p['selections'])) {
            return false;
        }

        // Note: the select tag has no "readonly" attribute - only "disabled"
        // So change it here.
        if (!empty($p['disabled'])) {
            $p['disabled'] = 'disabled';
        }


        // Not sure but I think the "rome-select" was affecting the outline error on validation. (?) It works okay without it.
        //class="form-control form-select'.$p['error_class_box'].' '.$p['additional_class'].'"


        $select = '<select       
        class="form-control '.$p['error_class_box'].' '.$p['additional_class'].'"     
        id="'.$p['field_name'].'"
        name="'.$p['field_name'].'"
         '.$p['disabled'].' '.$p['required'].' '.$p['auto_submit'].'>'.config('app.nl');

        // Add an empty selection at the top
        if ($p['add_blank']) {
            $select .= '<option value=""></option>' .config('app.nl');
        }

        if (!empty($p['selections'])) {   // error checking in case a blank list is passed (foreach bombs)
            foreach ($p['selections'] as $key => $value) {
                $sel = "";

                // Was this previously selected? (then make it sticky)
                if (!empty($p['value'])) {

                    if ($p['value'] == $key) {
                        $sel = "selected";
                    }

                } else {

                    // Preselect should only be checked to provide a default value when there currently isn't a value.
                    // If there is a value then we select it above.
                    // Note: that preselect uses the key rather than the value of the select

                    if ($p['preselect'] == $key) {
                        $sel = "selected";
                    }

                }

                $select .= '<option value="' . $key . '" ' . $sel . '>' . $value . '</option>' .config('app.nl');
            }
        }
        $select .= '</select>'.config('app.nl');

        return $select;
    }


    /**
     * selectmultiplesite - make a form drop-down (select) menu from a Site multi-select
     *
     * NOTE: unlike other Form functions here; this one can be called to actually save to the LU table ($write=true)
     *
     * @param $field_name       - field name
     * @param $lookup_table_name- lookup table model
     * @param $cUID             - this person that the multi select is for
     * @param $selection_list   - the list of choices ($key=>$value pairs)
     * @param bool $write       - if true then we delete all the current entries and replace them here; Use false when displaying the form.
     * @param string $luField   - the "item" to match the person (cUID) to in the lu table
     * @return string           - the full html select control
     */
    public static function selectmultiplesite($field_name, $lookup_table_name, $cUID, $selection_list, $write=false, $luField="sID") {

        // Note: Adding the chosen-container class to make a more specific selector when overriding default styles.
        $output = '<span class="chosen-container"><select class="form-control chosen-select" name="'.$field_name.'[]" multiple>'.config('app.nl');

        // $write is passed when calling this method from an update() function; No $write when just displaying the form.
        if ($write) {

            // So if $write, then delete all the current entries for this person before replacing them with the new values.
            $q = "DELETE FROM {$lookup_table_name} WHERE cUID={$cUID};";
            DB::delete($q);


            // Loop and save from the $field_name request() array (if there's a value).
            if (!empty(request()->$field_name)) {

                foreach (request()->$field_name as $key => $value) {

                    $q = "INSERT INTO {$lookup_table_name} (cUID, {$luField}) VALUES ({$cUID},{$value})";
                    DB::insert($q);
                }
            }

        } else {

            // If not writing - then just pull current entries and make the form control.
            $q = "SELECT {$luField} FROM {$lookup_table_name} WHERE cUID={$cUID} ORDER BY {$luField}";
            $result = DB::select($q);

            foreach ($selection_list as $key => $value) {
                $sel = "";

                foreach ($result as $lu_key) {

                    // Convert stdClass to array. ($lu_key ends up being a Laravel stdClass but we need it to be an array for the check below.)
                    $lu_key = (array)$lu_key;

                    // Is the current $key (a choice in the master selection list) contained in the $lu array (the current user's entries)
                    if (in_array($key,$lu_key)) {
                        $sel = "selected";
                    }
                }
                $output .= '<option value="' . $key . '" ' . $sel . '>' . $value . '</option>' . config('app.nl');
            }
            $output .= '</select></span>' . config('app.nl');

            return $output;
        }
    }

public static function checkbox($parameters) {

    // Grab the parameters and check for minimum requirements and process them all into an internal $p array object.
    $p = self::processParameters($parameters);
    if ($p === false) {
        return false;
    }

    /*
     * NOTE: For now, assuming that we just use "1" for the value in any checkbox.
     *       Since the only time they even post -- is if checked; that's really all we're looking for.
     *       If we really need a different value for some reason then that should be the
     *        responsibility of dataConsistencyChecks for that controller.
     */
    $p['value'] = 1;


    // Check to see if checked.
    $field_name = $p['field_name'];
    // Did we provide a valid model or is this an add hoc control?
    if ($p['model'] == null) {
        if (request()->input($field_name) != null && request()->input($field_name) == $p['value']) {     // if no model specified then check the input request'
            $checked = 'checked';
        } else {
            $checked = '';
        }
    } else {

        if ($p['model']->$field_name != null && $p['model']->$field_name == $p['value']) {
            $checked = 'checked';
        } else {
            $checked = '';
        }
    }



    $input = '<input
        class="form-check-input '.$p['error_class_box'].' '.$p['additional_class'].'"
        type="checkbox"
        id="'.$p['field_name'].'"
        name="'.$p['field_name'].'"
        value="'.$p['value'].'" '.$p['disabled'].' '.$p['required'].' '.$checked.'>'.config('app.nl');

    return $input;

    /* from the legacy code

    // Set the value of the checkbox to the field name
    $value = $fname;


    // Check to see if checked.
    if (isset($_POST[$fname])) {                        // just some error checking if nulled out
        $checked = 'checked';
    } else {
        //$value = '';
        $checked = '';
    }


    // If $tblObj is a passed table object then check to see if it has any 'disabled' fields defined
    $thisFieldDisabled = '';
    if (is_object($tblObj)) {
        // name is an SQL field name
        // if it's not disabled by the calling program then check the table class for the disabled defaults
        $thisFieldDisabled = self::set_disabled($fname, $tblObj);
    }

    // Then check to see if we're overriding
    if (!$allowEdit) {
        $thisFieldDisabled = ' readonly';
    }

    // Add a javascript on submit event handler
    if ($onsubmit) {
        $onsubmit = 'onchange="this.form.submit();"';
    } else {
        $onsubmit = '';
    }

    $output .= '	 <input  '.$checked.' class="'.self::set_required_class($fname,$tblObj).'" value="'.$value.'" type="checkbox" name="'.$fname.'" id="'.$fname.'" '.$thisFieldDisabled.' '.$onsubmit.' >'.NL;
    $output .= '</div>'.NL;
    $output .= NL;

    */




}

/* Using Layout::setButtons() instead
public static function button($parameters) {

    // Grab the parameters and check for minimum requirements and process them all into an internal $p array object.
    $p = self::processParameters($parameters);
    if ($p === false) {
        return false;
    }

    //ISSUES: this is not working - not passing value
    //$button = '<input class="btn btn-primary" type="submit" name="save" value="Save">';
    //'<input class="btn btn-primary" type="submit" id="save" name="save" value="Save">';

    $button = '<input
        class="form-control '.$p['error_class_box'].' '.$p['additional_class'].'"
        type="'.$p['type'].'"
        id="'.$p['field_name'].'"
        name="'.$p['field_name'].'"
        value="'.$p['value'].'">'.config('app.nl');

    return $button;
}
*/

    /**
     * Use the $form['layout']['buttons'] array to build out the complete html button area.
     *
     * Note: this is used in all forms with buttons like this:
     <div class="container">
        <form class="form-crud" method="post" action="{{ $form['layout']['form_action'] }}">
        @csrf
        @method('PATCH')

        {{-- ######################################################################## --}}
        {{-- Build out the BUTTON area and enumerate over any possible buttons ###### --}}
        {!! $control::buttonAreaHTML($form['buttons']) !!}
        {{-- ######################################################################## --}}

        <div class="row">
        {{-- Left column of form data. --}}
     *
     * @param $buttonArray
     * @return string
     *
     */
    public static function buttonAreaHTML($buttonArray)
    {

        // Remove any empty elements
        $buttonArray = array_filter( $buttonArray, 'strlen' );

        $buttonAreaHTML = '';

        if (!empty($buttonArray)) {         // This is just to remove the 2 <hr> elements when there are no buttons to display.

            $buttonAreaHTML = '
              
            <div id="system-page-buttons"class="row">
                <div class="col-md">
                    <hr>      <!-- Top line over the buttons. -->
                        <div class="form-group d-inline">
                            <!--<div class="button-group">-->
                            <!-- Using the <row> and col to space the buttons scales down to mobile size better than using a fixed label width. -->
                            <div class="row">
                                <!-- Spacer to get buttons to line up with the first column of data entry fields.
                                <label>&nbsp;</label>
                                -->
                                <div class="col-sm-2"></div>
                                <!-- Display all of the buttons for this user. -->
                                <div class="col-sm-10 button-group">';

            if (!empty($buttonArray)) {
                foreach ($buttonArray as $button) {
                    $buttonAreaHTML .= $button;
                }
            }
            $buttonAreaHTML .= '
                                </div> <!-- Button layout colunn 10 on the right. </div> -->
                            </div> <!-- The internal button row to hold the 2 column layout. </div> -->                            
                        </div>  <!-- The buttons form-group </div> -->
                    <hr>    <!-- Bottom line under the buttons. -->
                </div> <!-- Column inside of the outer row </div> -->
            </div>  <!-- #system-page-buttons outer row </div> -->
            ';

        }


// Note for now there is no setButtonArea functionality -- the Controller can just leave out the buttons and they won't show.
//        if (Layout::getLayout('buttons_show')) {
            return $buttonAreaHTML;
//        } else {
            return '';
//        }

    }

    /**
     * Check for deal-breaker fields and store the rest into the internal array for all other methods to use.
     * @param $parameters
     * @return mixed
     */
    protected static function processParameters($parameters) {

        // Check that $parameters is an array and that it contains a populated field_name parameter
        if (!is_array($parameters) || empty($parameters['field_name'])) {
            return false;
        }


        ////////////////////////////////////////////////////////////////////////////////////////////
        // PROCESS ALL THE PARAMETERS


        // Retrieve the options as defined in the config file.
        // Note: These are initiated as empty at the top so something should be in eco-helpers.
        ###########################################
        // global alert_if css display class
        ###########################################
        if (!empty(ehConfig::get('layout.options.alert_if_class'))) {
            self::$alert_if_class = ehConfig::get('layout.options.alert_if_class');
        }

        ###########################################
        // global text_warning css display class
        ###########################################
        if (!empty(ehConfig::get('layout.options.text_warning'))) {
            self::$text_warning = ehConfig::get('layout.options.text_warning');
        }

        ###########################################
        // global $box_warning css display class
        ###########################################
        if (!empty(ehConfig::get('layout.options.box_warning'))) {
            self::$box_warning = ehConfig::get('layout.options.box_warning');
        }

        ###########################################
        // global def_rows - default number of rows for textarea (when not specified).
        ###########################################
        if (!empty(ehConfig::get('controls.options.def_rows'))) {
            self::$def_rows = ehConfig::get('controls.options.def_rows');
        }

        ###########################################
        // global def_add_blank - default number of rows for textarea (when not specified).
        ###########################################
        if (!empty(ehConfig::get('controls.options.def_add_blank'))) {
            self::$def_add_blank = ehConfig::get('controls.options.def_add_blank');
        }




        ###########################################
        // display_name
        // If display_name is passing an object then use it for the $model too (only if it's not set)
        ###########################################
        if (isset($parameters['display_name']) && is_object($parameters['display_name'])) {
            if (!isset($parameters['model'])) {
                $parameters['model'] = $parameters['display_name'];
            }
        }

        ###########################################
        // model (value, field name)
        ###########################################
        $field = $parameters['field_name'];
        $value = '';
        if (isset($parameters['model'])) {
            $model = $parameters['model'];
            $value = $model->$field;    // The original data in the field before updating.
        } else {
            $model = false;
            // if no $model then this can only be old data, right? --- This makes no sense -- not sure what I was thinkin g
            // $value = request()->old($field);

            // Check for a current value then use old if it's not there.
            if (!empty(request()->$field)) {
                $value = request()->$field;
            } else {
                $value = request()->old($field);
            }

        }


        // But if this is a checkbox -- check boxes require a hard coded value to work.
        if (isset($parameters['type']) && $parameters['type']=='checkbox') {
            if (isset($parameters['value'])) {
                $value = $parameters['value'];
            }
        }

/*
        if($parameters['field_name'] == 'sID') {
            dd($model, $value);
        }
*/

        ###########################################
        // custom parameters (from the model)
        // Note; if display_name is passing an object then use it
        ###########################################
        if (isset($parameters['display_name']) && is_object($parameters['display_name'])) {
            $cp = self::getModelProperties($parameters['display_name']);
        } else {
            $cp = self::getModelProperties($model);
        }

        ###########################################
        // dates    - (as defined in the $model's dates[] array)
        ###########################################
        // Prepare for the date checks below.
        $is_date = false;       // Flag for "is this a date field y/n"


        ###########################################
        // Date + Time format?
        if (!isset($parameters['date_long'])) {
             $parameters['date_long'] = false ;
        }

        //if ($model && in_array($parameters['field_name'],$cp['dates']) && !empty($value)) {   // Moved the empty check into the if block.


        ###########################################
        // Get the model's date list.
        if (empty($cp['dates'])) {
            $cp['dates'] = [];
        }

        /*
###########################################
        // TESTING ONLY ---------------------------
if ($parameters['field_name'] == 'created_at') {
    dd($cp['dates'], in_array($parameters['field_name'],$cp['dates']));
}
###########################################
*/



        // We still want a date picker on an empty field.
        //if ($model && in_array($parameters['field_name'],$cp['dates']) ) {    // This was how to check when using our own $dates array on the model
        if ($model && in_array($parameters['field_name'],$cp['dates']) ) {      // Had to switch when

            // Not completely sure where this comes from but some of the date fields are storing this in mysql when they are blank.
            // That value plays havoc with the carbon format below (returns something really queer like 11/30/-0001)
            // if (!empty($value) && $value == '0000-00-00') {   // Umm..this is already converted to a Carbon data here.
            // This only (specifically) targets the Carbon issue where it turns 0000-00-00 into 11/30/-0001
            /* TESTING - What is $value here?
            if (!empty($value) ) {
                dd($parameters['field_name'],$value, is_string($value));
            }
            */


            // Note: this is expecting a Carbon instance which is controlled
            //  by ensuring that the Model has a $casts array defined.
            if (!empty($value) && $value->year < 1) {
                $value = '';
            }

            // Note that if defined in the model, this $value is already a Carbon instance.
            // So if the field is not blank, then format to the system date format.
            // "_short" for date only; "_long" for date + time (as defined in the eco-helpers config file).
            if (!empty($value)) {

                ///////////////////////////////////////////////////////////////////////////////////////////
                // Deal with setting the appropriate timezone to display this date (timestamp) data.
                $tz = '';
                if (!empty(Auth()->user()->timezone)) {
                    // First see if the user has a timezone set in their profile.
                    $tz = Auth()->user()->timezone;
                } elseif (!empty(ehConfig::get('default_time_zone'))) {
                    // If not, then check to see if a system default timezone is set.
                    $tz = ehConfig::get('default_time_zone');
                } elseif (!empty(config('app.timezone'))) {
                    // If not, then check to see if the system has a default timezone.
                    $tz = config('app.timezone');
                }

                // Set the timezone from above.
                // Note: This was inside of the date_long processing below but moved it out here because it can
                //       effect the short date too if it's close enough to midnight.
                if (!empty($tz)) {
                    $value = $value->tz($tz);
                }

                ///////////////////////////////////////////////////////////////////////////////////////////
                // date_long is a parameter passed with the input data to select a date with time or just a date by itself.
                if ($parameters['date_long']) {
                    $value = $value->format(ehConfig::get('date_format_php_long'));
                } else {
                    $value = $value->format(ehConfig::get('date_format_php_short'));
                }
            }

            $is_date = true;    // Set a flag that we can use in the input() builder to assign the datapicker class.
        }



        ###########################################
        // display_name
        // As an object or string.
        // If the display name parameter is empty then check to see if we have $model specified
        ###########################################
        if (empty($parameters['display_name']) || is_object($parameters['display_name'])) {

            // If this field name has a label defined in the model
            $tmp = (isset($cp['labels'][$parameters['field_name']]) ? $cp['labels'][$parameters['field_name']] : false );

            if ($tmp) {
                // Use the display name stored in the model
                $display_name = $tmp;
            } else {
                // Otherwise, just use the field name.
                $display_name = $parameters['field_name'];
            }

        } else {
            if (is_string($parameters['display_name'])) {

                // If the passed display name is a string then use it.
                $display_name = $parameters['display_name'];

            } else {

                // Otherwise, just use the field name.
                $display_name = $parameters['field_name'];

            }
        }

        ###########################################
        // The newly entered data for this field -- used if there's a form validation error.
        ###########################################
        $input_value = request()->old($parameters['field_name']);

/*
if ($parameters['field_name'] == 'wsSiteAssignedTo') {
    dd(request()->input(), $input_value, $value, $parameters['errors']->hasAny($parameters['field_name']));
}
*/

        ###########################################
        // Did we have an errors object?
        ###########################################
        $pre = '';
        $post = '';
        $error_class_text = '';
        $error_class_box = '';
        //if ($parameters['errors']->hasAny($parameters['field_name'])) {   // Need to do this if we ANY error

        // But $errors is optional to check it first
        if (!empty($parameters['errors'])) {

            if (!$parameters['errors']->isEmpty()) {
                // We want to keep all old data on ANY error - not just one for this field.
                $value = $input_value;        // If there's a validation error then keep the entered value in the field.

                // Display parameters for the error fields styling.
                if ($parameters['errors']->has($parameters['field_name'])) {
                    $error_class_text = self::$text_warning;
                    $error_class_box = self::$box_warning;
                    $pre = '<strong>';
                    $post = '</strong>';
                }
            }

        } else {
            $parameters['errors'] = [];
        }



        ###########################################
        // type
        ###########################################
        if (empty($parameters['type'])) {
            $type = 'text';
        } else {
            $type = $parameters['type'];
        }

        ###########################################
        // additional_class
        ###########################################
        if (empty($parameters['additional_class'])) {
            $additional_class = '';
        } else {
            $additional_class = $parameters['additional_class'];
        }

        ###########################################
        // placeholder
        ###########################################
        if (empty($parameters['placeholder'])) {
            $parameters['placeholder'] = '';
        }

        ###########################################
        // disabled
        // Check to see if we're passing something and use it -- then check to see if our model specifies it.
        ###########################################
        if (!empty($parameters['disabled'])) {
            if ($parameters['disabled']) {
                // !! NOTE !! disabled elements will not be submitted. (this may cause unintended consequences).
                // There is no 'readonly' for a radio button.
                $disabled = 'readonly';
            } else {
                $disabled = '';
            }
        } else {
            // If the passed parameter is empty - then check the model
            if($model) {
                // If this field name has this disable field entry defined in the model
                $tmp = false;
                if (isset($cp['disabled'])) {

                    $tmp = in_array($parameters['field_name'],$cp['disabled']);
                    //$tmp = $model->getCustomProperty('disabled',$parameters['field_name']);
                }

                if ($tmp) {
                    // Set the readonly attribute
                    $disabled = 'readonly';
                } else {
                    // Otherwise, just use blank.
                    $disabled = '';
                }
            } else {
                $disabled = '';
            }
        }

        ###########################################
        // required
        ###########################################
        if (!empty($parameters['required'])) {
            if ($parameters['required']) {
                $required = 'required';
            } else {
                $required = '';
            }
        } else {
            // If the passed parameter is empty - then check the model

            if($model) {
                // If this field name has a required field entry defined in the model
                $tmp = false;
                if (isset($cp['required'])) {
                    $tmp = in_array($parameters['field_name'],$cp['required']);
                    //$tmp = $model->getCustomProperty('required',$parameters['field_name']);
                }

                if ($tmp) {
                    // Use the required value from the model
                    $required = 'required';
                } else {
                    // Otherwise, just use blank.
                    $required = '';
                }
            } else {
                $required = '';
            }
        }

        ###########################################
        // rows (textarea)
        ###########################################
        if (!empty($parameters['rows'])) {
            if ($parameters['rows']>0) {
                $rows = $parameters['rows'];
            } else {
                $rows = self::$def_rows;
            }
        } else {
            $rows = self::$def_rows;
        }

        ###########################################
        // auto-submit
        // When using true, the eh-goto-submit.js class will be used; simply redirecting to the url specified with the id
        // If text is passed then we assume that is a custom handler and we use that.
        ###########################################
        //if (!empty($parameters['auto_submit']) && $parameters['auto_submit']) {
        if (!empty($parameters['auto_submit'])) {
            // If the parameter is boolean and it's true then use the built in 'goto'
            if(is_bool($parameters['auto_submit']) && $parameters['auto_submit']) {
                $auto_submit = 'onchange="ehGotoSubmit()"';
                // ??
                // generic submit
                // $output .= 'onchange="this.form.submit()"';  // from the old Form class ??
            } else {
                if (is_string($parameters['auto_submit'])) {
                    $auto_submit = 'onchange="'.$parameters['auto_submit'].'"';
                }
            }

        } else {
            $auto_submit = '';
        }

        ###########################################
        // radio (radio buttons array)
        ###########################################
        if (isset($parameters['radio']) && is_array($parameters['radio'])) {
            // If so just move it to the output without any processing.
            $radio = $parameters['radio'];
        } else {
            $radio = '';
        }

        ###########################################
        // selections (select array)
        ###########################################
        if (isset($parameters['selections']) && is_array($parameters['selections'])) {
            // If so just move it to the output without any processing.
            $selections = $parameters['selections'];
        } else {
            $selections = '';
        }

        ###########################################
        // preselect (select)
        // Note: that preselect uses the key rather than the value of the select
        ###########################################
        if (!empty($parameters['preselect'])) {
            // If so just move it to the output without any processing.
            $preselect = $parameters['preselect'];
        } else {
            $preselect = null;
        }


        ###########################################
        // link
        ###########################################
        if (!empty($parameters['link'])) {
            // If so just move it to the output without any processing.
            $link = $parameters['link'];
        } else {
            $link = '';
        }

        ###########################################
        // target
        ###########################################
        if (!empty($parameters['target'])) {
            // If so just move it to the output without any processing.
            $target = $parameters['target'];
        } else {
            $target = '_self';
        }

        ###########################################
        // alert (if match value)
        // Note: the "alert" parameter is not passed back -- it only modifies the "additional_class"
        ###########################################
        if (isset($parameters['alert_class'])) {                    // See if there's been an alert class passed in.
            self::$alert_if_class = $parameters['alert_class'];    // And if so, replace the default with it.
        }
        if (isset($parameters['alert_if'])) {                       // Then, if the alert_if flag is set,
            if ($parameters['alert_if'] == $value)                  // See if the current field value = that setting.
            $additional_class .= ' '.self::$alert_if_class;        // Empty pad the front in case additional_class has something in it.
        }


        ###########################################
        // add_blank
        // add an empty '' selection at the top of the select
        ###########################################
        if (isset($parameters['add_blank'])) {
            $add_blank = $parameters['add_blank'];
        } else {
            $add_blank = self::$def_add_blank;      // Default value when add_blank is missing (defined at top).
        }

        ###########################################
        // number_format
        // Note: for now, we're just passing the number of decimal places to trigger this as a number format field.
        ###########################################
        //if (isset($parameters['number_format'])) {

            // Getting a non well formed numeric value error on saving with a comma.
            //$value = str_replace(',','',$value);            // First you have to remove the commas or number_format will gag.
            //$value = number_format($value, $parameters['number_format']);

        //}
        if (isset($parameters['number_format'])) {
            //$number_format = $parameters['number_format'];
            $value = number_format($value,$parameters['number_format']);
        }


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Build out the whole "internal" object for all of the methods to use.
        return [
            'field_name'=> $parameters['field_name'],
            'display_name'=> $display_name,
            'model'=> $model,
            'type'=> $type,
            'additional_class'=> $additional_class,
            'errors'=> $parameters['errors'],
            'error_class_text' => $error_class_text,
            'error_class_box' => $error_class_box,
            'required'=> $required,
            'placeholder'=> $parameters['placeholder'],
            'rows'=>$rows,
            'auto_submit'=> $auto_submit,
            'link'=> $link,
            'target'=> $target,
            'radio'=> $radio,
            'disabled'=> $disabled,
            'bold_before'=> $pre,
            'bold_after'=> $post,
            'value' => $value,
            'selections'=> $selections,
            'preselect'=>$preselect,
            'add_blank'=>$add_blank,
            'date_long'=>$parameters['date_long'],
            'is_date'=>$is_date
        ];

    }


    /**
     * Internal function to grab the model's custom eco-helpers attributes if they are present.
     *
     * @param $model
     * @return array|bool
     */
    protected static function getModelProperties($model) {

        $dates = [];

        if (is_object($model)) {

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Get the custom date fields for this model.
            // The model has a $casts array set then loop it and pick out the date and datetime entries.
            if (isset($model->casts)) {
                foreach($model->casts as $key=>$value) {
                    if ($value == 'date' || $value == 'datetime' || $value == 'timestamp') {
                        $dates[] = $key;
                    }
                }
            }

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Use magic method to pull custom attributes as long as they are set to public in the model.
            return [
                'labels' => $model->labels,
                'disabled' => $model->disabled,
                'required' => $model->required,
                'dates' => $dates
            ];

        } else {
            return false;
        }

    }


}
