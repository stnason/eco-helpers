<?php

namespace ScottNason\EcoHelpers;
use App\Traits\ConvertDatesToSavable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class Controls (form control helpers)
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
    * 'radio'             => radio button array ($btn_value=>$display)
    * 'disabled'          => html disabled attribute (true or false)
    * 'bold_before'       => beginning bold tag (label)
    * 'bold_after'        => ending bold tag (label)
    * 'value'             => the input value attribute
    * 'selections'        => $key=>$value pair array for select inputs
    * 'preselect'         => $key value of the preselected select value (if a default is desired on initial entry)
    * 'alert_if'          => add a def_alert_class to the additional_class if the controls value matches this.
 *  * 'alert_class'       => the class used for the alert_if function
 *
 * @package App\Classes
 */
Class Controls
{
    protected static $text_warning = 'text-danger';      // Bootstrap or custom css class for the error label text.
    protected static $box_warning = 'border-danger';     // Bootstrap or custom css class for the error input box.
    protected static $def_rows = 3;                      // Default rows for a text area input if nothing specified.
    public static    $def_alert_class = 'alert alert-dark'; // When requesting an alert -- use this class.
                                                         // Moved this to app.php so just putting something in here to watch for places to fix it.
    //protected static $cp = [];                         // Model Custom Properties (disabled, required, labels)

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
        $target = '_self';
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
            if ($p['value'] == $btn_value) {
                $checked = 'checked';
                $strongF = '<strong>';      // make the current selection label bold
                $strongB = '</strong>';
            } else {
                $checked = '';
                $strongF = '';
                $strongB = '';
            }

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

    // Check to see if checked.
    $field_name = $p['field_name'];
    // Did we provide a valid model or is this an add hoc control?
    if ($p['model'] == null) {
        if (request()->input($field_name) == $p['value']) {     // if not model specified then check the input request'
            $checked = 'checked';
        } else {
            $checked = '';
        }
    } else {
        if ($p['model']->$field_name == $p['value']) {
            $checked = 'checked';
        } else {
            $checked = '';
        }
    }



    $input = '<input
        class="form-control '.$p['error_class_box'].' '.$p['additional_class'].'"
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
     * Use the $form['buttons'] array to build out the complete html button area.
     * Note: this is used in all forms with buttons:
     <div class="container">
        <form class="form-crud" method="post" action="{{ $form['form_action'] }}">
        @csrf
        @method('PATCH')

        {{-- ######################################################################## --}}
        {{-- Build out the BUTTON area and enumerate over any possible buttons ###### --}}
        {!! $control::buttonArea($form['buttons']) !!}
        {{-- ######################################################################## --}}

        <div class="row">
        {{-- Left column of form data. --}}
     *
     * @param $buttonArray
     * @return string
     *
     */
    public static function buttonArea($buttonArray)
    {

        // Remove any empty elements
        $buttonArray = array_filter( $buttonArray, 'strlen' );

        $buttonArea = '';

        if (!empty($buttonArray)) {         // This is just to remove the 2 <hr> elements when there are no buttons to display.

            $buttonArea = '
          <hr>
            <div class="form-group d-inline" id="system-page-buttons">
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
                    $buttonArea .= $button;
                }
            }
            $buttonArea .= '
                            </div>
                        </div>
                    <!--</div>-->
                </div>
                <hr>
            ';

        }

        if (Layout::getFormArray('buttons_show')) {
            return $buttonArea;
        } else {
            return '';
        }

    }

    /**
     * Check for deal-breaker fields and store the rest into the internal array for all other methods to use.
     * @param $parameters
     * @return mixed
     */
    protected static function processParameters($parameters) {

        // Check that $parameters is an array and and contains a populated field_name parameter
        if (!is_array($parameters) || empty($parameters['field_name'])) {
            return false;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // PROCESS ALL THE PARAMETERS


        ###########################################
        // global alert_if color setting
        ###########################################
        self::$def_alert_class = config('app.alert_if_class');


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
        // If this field name has a date property defined in the model
        $is_date = false;

        //if ($model && in_array($parameters['field_name'],$cp['dates']) && !empty($value)) {   // Moved the empty check into the if block.
                                                                                                // We still want a date picker on an empty field.
        if ($model && in_array($parameters['field_name'],$cp['dates']) ) {

/*
###########################################
            // TESTING ONLY ---------------------------
            if($parameters['field_name'] == 'wim_config_cert_date') {
                dd('h', $value , $value->year < 1);
            }
###########################################
*/

            // Not completely sure where this comes from but some of the date fields are storing this in mysql when they are blank.
            // That value plays havoc with the carbon format below (returns something really queer like 11/30/-0001)
            // if (!empty($value) && $value == '0000-00-00') {   // Umm..this is already converted to a Carbon data here.
            // This only (specifically) targets the Carbon issue where it turns 0000-00-00 into 11/30/-0001
            if (!empty($value) && $value->year < 1) {
                $value = '';
            }

            // Note that if defined in the model, this $value is already a Carbon instance.
            // So if the field is not blank, then format to the system date format.
            if (!empty($value)) {
                $value = $value->format(config('app.date_format_short'));
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
                $disabled = 'readonly';
            } else {
                $disabled = '';
            }
        } else {
            // If the passed parameter is empty - then check the model
            if($model) {
                // If this field name has a disable defined in the model
                $tmp = false;
                if (isset($cp['disabled'])) {
                    $tmp = $model->getCustomProperty('disabled',$parameters['field_name']);
                }

                if ($tmp) {
                    // Use the disabled name stored in the model
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
                // If this field name has a required defined in the model
                $tmp = false;
                if (isset($cp['required'])) {
                    $tmp = $model->getCustomProperty('required',$parameters['field_name']);
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
        // When using true, the goto_submit.js class will be used; simply redirecting to the url specified with the id
        // If text is passed then we assume that is a custom handler and we use that.
        ###########################################
        //if (!empty($parameters['auto_submit']) && $parameters['auto_submit']) {
        if (!empty($parameters['auto_submit'])) {
            // If the parameter is boolean and it's true then use the built in 'goto'
            if(is_bool($parameters['auto_submit']) && $parameters['auto_submit']) {
                $auto_submit = 'onchange="goto_submit()"';
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
        // alert (if match value)
        // Note: the "alert" parameter is not passed back -- it only modifies the "additional_class"
        ###########################################
        if (isset($parameters['alert_class'])) {                    // See if there's been an alert class passed in.
            self::$def_alert_class = $parameters['alert_class'];    // And if so, replace the default with it.
        }
        if (isset($parameters['alert_if'])) {                       // Then, if the alert_if flag is set,
            if ($parameters['alert_if'] == $value)                  // See if the current field value = that setting.
            $additional_class .= ' '.self::$def_alert_class;        // Empty pad the front in case additional_class has something in it.
        }


        ###########################################
        // add_blank
        // add an empty '' selection at the top of the select
        ###########################################
        if (isset($parameters['add_blank'])) {
            $add_blank = $parameters['add_blank'];
        } else {
            $add_blank = true;  // Let's default to adding one since that is the most common use case.
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
        $processed_parameters = [
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
            'radio'=> $radio,
            'disabled'=> $disabled,
            'bold_before'=> $pre,
            'bold_after'=> $post,
            'value' => $value,
            'selections'=> $selections,
            'preselect'=>$preselect,
            'add_blank'=>$add_blank,
            'is_date'=>$is_date

        ];

        return $processed_parameters;
    }


    /**
     * Internal function to grab the model custom attributes if they are present.
     * @param $model
     * @return array|bool
     */
    protected static function getModelProperties($model) {

        $tmp = [];
        if (is_object($model)) {
            $tmp['labels'] = $model->getCustomProperty('labels');
            $tmp['disabled'] = $model->getCustomProperty('disabled');
            $tmp['required'] = $model->getCustomProperty('required');
            $tmp['dates'] = $model->getCustomProperty('dates');
            return $tmp;
        } else {
            return false;
        }

    }



}
