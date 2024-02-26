<?php

namespace ScottNason\EcoHelpers\Classes;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;

use ScottNason\EcoHelpers\Models\ehPage;



/**
 * Layout class.
 * Handles all standard page/form area display control interaction to the base template.
 *  - banner, name, icon, description, linkbar, heading, flash, attention, option-block, login
 *
 *  - NOTE: that the header and footer areas are now completely independent and controlled by their own template files.
 */

class ehLayout
{

    /**
     * The array that ultimately is returned and is responsible for controlling the view areas in the base template.
     * @var array
     */
    protected static $layout = [];

    // Collapse has some challenges implementing in the base template -- for now, those are not solved for,
    // so everything naturally collapses unless we provide a placeholder.

    // For each $defined_area:
    //    'banner'=>[
    // 'state'=>true,                           // display this area on the base template
    // 'content'=>'whatever to display,         // what to display in it
    // 'collapse'=> true,                       // of no display, then should we maintain (set in config file)
    // 'collapse_chr'=> ' ',                    //
    // 'class'='css=class-name']                // css class -- set in config file



    /**
     * These are the 10 fixed areas (pre-defined) in the base (page) template and should not be changed.
     * 'banner'             // System banner under the navbar -- before the page title block.
     * 'name (title)'       // Name of this page's title.
     * 'icon'               // A place for an image or icon at the left of the page title.
     * 'heading/descp'      // The page's descriptive text underneath the page title.
     * 'linkbar'            // The Linkbar navigation provided by the Linkbar class in conjunction with the Menus table and Menu system.
     * 'dynamic'            // Controller defined dynamic message. Underneath the Linkbar area.
     * 'system flash'       // The main system flash area; under the page header area, below the dynamic heading and above the attention message
     *                      //  - Note; that if you turn the flash area off, you'll need to service the session['message'] in your own template.
     * 'attention'          // Any attention message right below the system flash area (Archived, Deleted, etc)
     * 'option-bock         // Image or icon to the left of the Page Title (generally will leave off the icon; frequently used for images like Contacts)
     *
     */


    /**
     * The areas that are laid out (and controllable) in the base template.
     * HTML IDs are prepended with 'layout-' and the name of the key (i.e.- layout-page-dynamic, layout-page-name, etc.)
     *
     * @var string[]
     */
    protected static $defined_areas = [
        'banner','name','icon','description','linkbar','dynamic','flash','attention','option-block'
    ];

    /**
     * Custom keys that can be used as "methods" to call methods that are not directly related to display areas.
     * Example: the full-width page setting.
     *
     * @var string[]
     */
    protected static $custom_keys = [
        'full-width',
    ];


    /**
     * Reset and populate an initial layout control array the base template using default values or--if using it--the Menu system.
     *
     *  Note, these all come from config.eco-helpers.php
     *   so you must have previously run php artisan vendor:publish on this package.
     *
     */
    public static function initLayout() {

        // Simple check to determine if the calling page controller is extending the eco base controller properly.
        if (debug_backtrace()[1]['function']=='require') {

            // This was called from an @inject inside of a blade template.
            //dd('called from blade');

        } else {

            // Called normally from a Controller
            // So check to make sure that Controller is extending the ehBaseController
            //$tmp = debug_backtrace()[1]['object'];
            $tmp = debug_backtrace()[1]['class'];       // The last class to call initLayout()
            if (!method_exists($tmp, 'doesExtendBaseController')) {
                dd('Layout error 1402: Remember that Controllers must extend ehBaseController to properly interact with the Layout class.');
            }
        }


        // Default value for the $form['layout']['when_adding'] variable
        // Should be set to true in the create() method if the blade template is using it.
        // Allows templates to work as both show() and create() by dropping things or that aren't there yet in create().
        self::$layout['when_adding'] = false;


        // Setup an empty buttons array to keep from throwing an error if controller doesn't set it.
        self::$layout['buttons'] = [];


        // Initialize the per page "rights" array for the template to use.
        // self::initUserRights();  // Moved this functionality to access and took it out of the form variable.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Grab all of the default and options initial values defined in config('eco-helpers.layout')
        $default = ehConfig::get('layout.default');
        // Check to ensure that we've published the config/eco-helpers.php file.
        if (empty($default)) {
            dd('Layout error 1403: Remember to run php artisan vendor:publish and choose -  Provider: ScottNason\EcoHelpers\Providers\EcoHelpersServiceProvider');
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // LAYOUT DEFAULTS: from eco-helpers config file
        foreach(self::$defined_areas as $area) {

            // Loop and build out the initial layout array using the default layout
            // values defined in eco-helpers['layout.default'].
            self::$layout[$area]['state'] = $default[$area]['state'];
            self::$layout[$area]['content'] = $default[$area]['content'];
            self::$layout[$area]['collapse'] = $default[$area]['collapse'];
            self::$layout[$area]['collapse_chr'] = $default[$area]['collapse_chr'];
            self::$layout[$area]['class'] = $default[$area]['class'];

            // Deal with the system banner a little different since it has a Config settings.
            if ($area == 'banner' && !empty(ehConfig::get('system_banner'))) {
                self::$layout[$area]['content'] = ehConfig::get('system_banner');
                self::$layout[$area]['blink'] = ehConfig::get('system_banner_blink');
            } else {
                self::$layout[$area]['content'] = $default[$area]['content'];
                self::$layout[$area]['blink'] = ehConfig::get('layout.options.banner_blink');
            }

        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Pull and set the defaults for any $custom_keys:
        self::$layout['full-width']['state'] = ehConfig::get('layout.options.full_width');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // AUTOLOADER: defaults -- Hard code '0' (the all-pages global css and js) as always on.
        // 0-css-autoload.blade.php and 0-js-autoload.blade.php should load for every page refresh.
        self::$layout['auto_load'][0] = 'static';


        ///////////////////////////////////////////////////////////////////////////////////////////
        // MAIN DROPDOWN MENUS: Create the navbar user dropdown menus
        // ehMenus() will deliver a complete menu hierarchy based on the logged in user's acting role
        // page security along with any individual page security settings (public, auth, full security check).
        $menus = new ehMenus(0,'user');
        self::$layout['menus'] = $menus->getPages();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // PAGE INFORMATION: Pull the page information for this route.
        $p = ehPage::getPageInfo();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // RESOURCEFUL ROUTE?
        // Check to see if this is a resourceful route and modify the page name for variations.
        $resource_name_pre = '';
        $resource_name_post = '';
        $tmp_route = Route::currentRouteName();
        $tmp_route = explode('.',$tmp_route);

        if (count($tmp_route)>1) {
            switch ($tmp_route[1]) {
                case 'show':
                    $resource_name_post = ' Detail';
                    break;
                case 'index':
                    $resource_name_post = ' List';
                    break;
                case 'create':
                    $resource_name_pre = 'New ';
                    break;
            }
        }

        if ($p) {

            self::setName($resource_name_pre.$p->name.$resource_name_post);
            self::setIcon($p->icon);
            self::setDescription($p->description);
            return $p;

        } else {

            self::setName('No Page Entry in Pages');
            self::setDescription(' - no page entry in pages.');

        }

        return null;

    }



    /**
     * Generic method call originally used directly to set any of the area parameters (ehLayout::setName).
     *  Where "Name" refers to one of the display areas defined in self::$defined_areas.
     *
     *  BUT...to make it easier with IDE type-ahead, each area has its own setter below now
     *  and it does it's thing before passing off to this generic method that's responsible for
     *  setting the 'content' value and the 'state'.
     *
     *  Note: that below here, we built out all the needed setters.
     *  Note: that this magic method could be used without the setters, but they are needed for IDE type-ahead help.
     *
     *  USAGE:  setName('some value')   - set the text in the display area.
     *          setName(true/false)     - turn the display area on or off.
     *          setName()               - same as setName(true);
     *
     * @param $method
     * @param $value_array
     */
    public static function __callStatic( $method, $passed_value = null )
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Note on the $passed_value variable type:
        // When it comes in from one of the known methods (setters) established below,
        // $passed_value will be passed as the expected string type only. (Except for setLinkbar which passes an array)
        // But, when this magic method intercepts an undefined method call,
        // $passed_value is an array! So we need to deal with that here.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Setup the internal $value from the $passed_value.
        // If there is no passed value then this will act as a "turn-on" call.
        if ($passed_value === null) {       // If truly nothing was passed.
            $value = true;                  // This will be interpreted as a call to turn on this display area.
        } else {

            // So, $passed_value has something other than null in it.
            if (gettype($passed_value) === 'array' && $method != 'setLinkbar') {

                // If $passed_value was from an unknown method call and got straight here
                //  without going through any of the defined setters in the section below.
                if (count($passed_value) > 0) {
                    $value = $passed_value[0];      // In that case, just get the first element in the array.
                } else {
                    $value = $passed_value;         // In some cases the array could be empty.
                }

            } else {

                // This must've come from one of the defined setters below.
                $value = $passed_value;         // So, just use the passed value as is.

            }

        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // CREATE THE DISPLAY AREA $name based on the method name called from
        // to be used to pull the state variable name.
        // In other words, remove the first 3 characters ("set"), convert any remaining camel case into dashes
        // and then make sure everything is all lower case.

        // Convert camel case to dashes -- beginning with the 4th character of the string.
        // (Is taking care of double words like optionBlock. Note that $pieces[0] is always empty.)

        // Remove the word "set" at the beginning of the method call -- then split on any uppercase characters.
        $pieces = preg_split('/(?=[A-Z])/',substr($method,3,strlen($method)));
        $name = '';     // Resulting defined area name variable.
        $iCnt = 0;      // Piece counter for the preg_slit $pieces.
        foreach ($pieces as $piece) {

            // Force all to lower case.
            $name .= strtolower($piece);

            // Do not add a dash in front of the $piece[0] element or the last element.
            if ($iCnt != 0 && isset($pieces[$iCnt+1])) {
                $name .= '-';                   // Add the dash.
            }
            $iCnt++;

        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Check the final name to see if it matches one of the pre-defined display area names.
        // Note: these "non user configurable" areas are defined at the top: self::$defined_areas.
        $hard_stop = false;
        if (!in_array($name, self::$defined_areas)) {

            // Also check the $custom_keys before declaring the hard stop error.
            if (!in_array($name, self::$custom_keys)) {
                $hard_stop = true;
            }
        }
        if ($hard_stop) {
            dd('Error: Invalid display area: '. $name . '. No method name '.$method.'(). ', self::$defined_areas, self::$custom_keys);
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Then finally, process the request from the original setter call.
        // On/ Off
        // If passing true/false -- just turn on/off the display without changing its value.
        if (gettype($value) == 'boolean') {
            self::$layout[$name]['state'] = $value;
            return;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the display areas value.
        // If $value is not true/false (null was already caught above), then change the contents to the $passed_value.
        if (!empty($value)) {
            //self::$layout[$name]['state'] = true;       // Calling with content assumes we're turning it on. (do we need this?)
            self::$layout[$name]['content'] = $value;
        }


    }

    /**
     * Call with () to turn the banner on.
     * Call with (true) to turn the banner on.
     * Call with (false) to turn it off.
     * Call with ('some value') to change its contents.
     * Call with ('some value', 'class') set the class.
     *
     * @param $value
     * @return null
     */
    public static function setBanner($value = null, $class = null) {

        // Save the class (if present) before passing control off to the catch-all generic method setter.
        if (!empty($class)) {
            self::$layout['banner']['class'] = $class;
        }
        // Call the catch-all generic setter to complete the setter logic for the banner.
        return self::__callStatic('setBanner', $value);
    }
    /**
     * Call with () to turn the name on.
     * Call with (true) to turn the name on.
     * Call with (false) to turn it off.
     * Call with ('some value') to change its contents.
     * Call with ('some value', 'class') set the class.
     *
     * @param $value
     * @return null
     */
    public static function setName($value = null, $class = null) {

        // Save the class (if present) before passing control off to the catch-all generic method setter.
        if (!empty($class)) {
            self::$layout['name']['class'] = $class;
        }

        // Call the catch-all generic setter to complete the setter logic for the page name.
        return self::__callStatic('setName', $value);
    }
    /**
     * Call with () to turn the name icon on.
     * Call with (true) to turn the icon on.
     * Call with (false) to turn it off.
     * Call with ('some value') to change its contents.
     * Call with ('some value', 'inline_style') set to set an inline css style="" tag.
     *  Note; That the inline style on the icon is implemented in the eh-app-template file.
     *        It provides the style = "{{$variable}}"
     *
     * Note: setIcon will strip off any <i> tag and just leave the icon "class".
     *
     * @param $value
     * @return null
     */
    public static function setIcon($value = null, $inline_style = null) {

        // Save the inline style (if present) before passing control off to the catch-all generic method setter.
        if (!empty($inline_style)) {
            self::$layout['icon']['class'] = $inline_style;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Check for and strip off the passed <i> tag; this should be the icon "class" only since
        // (was the whole Font Awesome 6 class was copied into the field?)
        // <i class="fa-solid fa-watch-smart"></i>
        // The eh-app-template is already providing the <i> portion and just expects the class by itself.
        // So, if it's in the $value, then get rid of it.
        //  Note: This functionality is duplicated in the PagesController@dataConsistencyCheck
        //        section and maybe could be combined/simplified.

        $tmp = $value;
        $tmp = str_replace('<i class="', "", $tmp);
        $tmp = str_replace('"></i>', "", $tmp);
        $value = $tmp;

        // Call the catch-all generic setter to complete the setter logic for the page icon.
        return self::__callStatic('setIcon', $value);
    }
    /**
     * Call to () to turn the descriptive heading on.
     * Call with (true) to turn the descriptive heading on.
     * Call with (false) to turn it off.
     * Call with ('some value') to change its contents.
     * Call with ('some value', 'class') set the class.
     *
     * @param $value
     * @return null
     */
    public static function setDescription($value = null, $class = null) {

        // Save the class (if present) before passing control off to the catch-all generic method setter.
        if (!empty($class)) {
            self::$layout['description']['class'] = $class;
        }

        // Call the catch-all generic setter to complete the setter logic for the page description.
        return self::__callStatic('setDescription', $value);
    }

    /**
     * Call with () to turn the Linkbar on.
     * Call with (true) to turn the Linkbar on.
     * Call with (false) to turn it off.
     * Call with ('some value', 'class') set the class.
     * Call with ($linkbar->getLinkbar()) or pass your own array to change its contents.
     *
     *
     * @param $linkbar
     * @return null
     */
    public static function setLinkbar($linkbar = null, $class = null) {

        // Save the class (if present) before passing control off to the catch-all generic method setter.
        if (!empty($class)) {
            self::$layout['linkbar']['class'] = $class;
        }

        // Call the catch-all generic setter to complete the setter logic for the linkbar.
        return self::__callStatic('setLinkbar', $linkbar);
    }

    /**
     * Call with () to turn the dynamic heading on.
     * Call with (true) to turn the dynamic heading on.
     * Call with (false) to turn it off.
     * Call with ('some value') to change its contents.
     * Call with ('some value', 'class') set the class.
     *
     * @param $value
     * @return null
     */
    public static function setDynamic($value = null, $class = null) {

        // Save the class (if present) before passing control off to the catch-all generic method setter.
        if (!empty($class)) {
            self::$layout['dynamic']['class'] = $class;
        }

        // Call the catch-all generic setter to complete the setter logic for the dynamic header.
        return self::__callStatic('setDynamic', $value);
    }

    /**
     * Call with () to turn the system flash message on.
     * Call with (true) to turn the flash message on.
     * Call with (false) to turn it off.
     * Call with ('some value') to change its contents.
     * Call with ('some value', 'class') set the class.
     *
     * @param $value
     * @return null
     */
    public static function setFlash($value = null, $class = null) {

        // Save the class (if present) before passing control off to the catch-all generic method setter.
        if (!empty($class)) {
            self::$layout['flash']['class'] = $class;
        }

        // Save the flash message.
        session(['message' => $value]);

        // Call the catch-all generic setter to complete the setter logic for the flash message.
        return self::__callStatic('setFlash', $value);
    }

    /**
     * Call with () to turn the attention message on.
     * Call with (true) to turn the attention message on.
     * Call with (false) to turn it off.
     * Call with ('some value') to change its contents.
     * Call with ('some value', 'class') set the class.
     *
     * @param $value
     * @return null
     */
    public static function setAttention($value=null, $class = null) {

        // Save the class (if present) before passing control off to the catch-all generic method setter.
        if (!empty($class)) {
            self::$layout['attention']['class'] = $class;
        }

        // Call the catch-all generic setter to complete the rest of the setter logic.
        return self::__callStatic('setAttention', $value);

    }

    /**
     * Call with () to turn the option-block area on.
     * Call with (true) to turn the option block on.
     * Call with (false) to turn it off.
     * Call with ('some value') to change its contents.
     * Call with ('some value', 'class') set the class.
     *
     * @param $value
     * @return null
     */
    public static function setOptionBlock($value = null, $class = null) {

        // Save the class (if present) before passing control off to the catch-all generic method setter.
        if (!empty($class)) {
            self::$layout['option-block']['class'] = $class;
        }

        // Call the catch-all generic setter to complete the rest of the setter logic.
        return self::__callStatic('setOptionBlock', $value);
    }

    /**
     * Call with () or (true) to turn on full width.
     * Call with (false) to turn off full width.
     * Note - these will use the 2 keys defined in the eco-helpers config file:
     * 'page_container_class_normal' => 'xxxxx',
     * 'page_container_class_full' => 'xxxxx',
     *
     * @param $value
     * @return null
     */
    public static function setFullWidth($value=null) {
        // Call the catch-all generic setter to complete the setter logic for the page name.
        return self::__callStatic('setFullWidth', $value);
    }

    /**
     * Setter to set the a single button in the buttons array inside the layout array.
     *
     *  Note: buttons display in the pre-defined base layout button area.
     *
     * @param string $button_name
     * @param string $html_button
     */
    public static function setButton($button_name = null, $html_button = '')
    {

        if (!isset(self::$layout['buttons'])) {
            self::$layout['buttons'] = [];
        }

        self::$layout['buttons'][$button_name] = $html_button;

    }

    /**
     * Does the same thing that setButtons does -- which is the way to manually set you own buttons.
     * But this automatically builds out the Save, New and Delete buttons based on this user's permissions.
     * Note: This uses the html button code in eco-helpers.layout.default_buttons[].
     * Note: Once set, these buttons are returned in the $layout['buttons'] array for use in the template.
     *
     * Usage:
     * ehLayout::setStandardButtons();
     * ehLayout::setStandardButtons('save');
     * ehLayout::setStandardButtons(['new','delete','save']);
     *
     * @param $parameter       // Accepts a single string button name, array (csv), or blank for all.
     * @return void
     */
    public static function setStandardButtons($parameter=null) {

        // Pull the default_buttons array out of the config file.
        $buttons = ehConfig::get('layout.default_buttons');

        $save = false;
        $new = false;
        $delete = false;

        if (gettype($parameter) == 'NULL') {            // If no parameter is passed, check for permissions on all 3 buttons.
            $save = true;
            $new = true;
            $delete = true;
        } elseif (gettype($parameter) == 'string') {    // If a string is passed, see if it matches any button name.
            switch ($parameter) {
                case 'save':
                    $save = true;
                    break;
                case 'new':
                    $new = true;
                    break;
                case 'delete':
                    $delete = true;
                    break;
            }
        } elseif (gettype($parameter) == 'array') {     // if an array is passed, check all elements to see if any match a button name.
            foreach($parameter as $key) {
                switch ($key) {
                    case 'save':
                        $save = true;
                        break;
                    case 'new':
                        $new = true;
                        break;
                    case 'delete':
                        $delete = true;
                        break;
                }
            }
        }




        // SECURITY: Button control.
        // Check and set each button based on this user's permission level for the current route.
        if ($save) {
            if (ehAccess::chkUserResourceAccess(Auth()->user(),Route::currentRouteName(),ACCESS_EDIT)) {
                self::setButton('save', $buttons['save']);
            }
        }

        if ($new) {
            if (ehAccess::chkUserResourceAccess(Auth()->user(),Route::currentRouteName(),ACCESS_ADD)) {
                self::setButton('new', $buttons['new']);
            }
        }

        if ($delete) {
            if (ehAccess::chkUserResourceAccess(Auth()->user(),Route::currentRouteName(),ACCESS_DELETE)) {
                self::setButton('delete', $buttons['delete']);
            }
        }

    }





    /**
     * Set all defined areas either on or off.
     * setAll() with no parameters is same as true (all on).
     *
     * @param $all_on
     * @return void
     */
    public static function setAll($all_on = true) {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Get the list of defined areas from the top of this file.
        $defined_areas = self::$defined_areas;


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Turn off the display area without changing its value.
        if ($all_on === false) {
            foreach($defined_areas as $area) {
                self::$layout[$area]['state'] = false;
            }
            return;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Turn on the display area without changing its value.
        if ($all_on === true) {
            foreach($defined_areas as $area) {
                self::$layout[$area]['state'] = true;
            }
        }

        // If anything other than true/false is passed then no change to current state.

    }



    /**
     * Turns on the user configurable loading of per page js and css needed for specialty functions.
     * Can call either by name or number (as defined in eco-helpers.layout.auto_loaders)
     *
     * The actual code for each of these is contained in the views/ecoHelpers/auto-load/nn-autoload.php
     * file associated with each.
     *
     *  EXAMPLES OF THINGS INCLUDED IN THE AUTO-LOAD FILES:
     *  - textedit
     *  - datepicker
     *  - datetimepicker
     *  - helpsystem
     *  - chosen
     *  - datatables
     *
     * @param null $value
     */
    public static function setAutoload($input=null) {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // $value can be a number or a name as defined in eco-helpers.layout.auto_loaders
        $auto_loader_name = '';
        $auto_loader_number = null;

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Check to see if we passed and array or not.
        // If we did then use it directly in the loop below.
        // Otherwise turn the input into a single element array so it works in the loop.
        if (gettype($input) == 'array') {
            $input_array = $input;
        } else {
            $input_array[] = $input;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Loop the auto_load array and check each item.
        foreach($input_array as $value) {

            // 1. Are we passing a number? If so then use the $key=>value.
            if (gettype($value) == 'integer') {
                // Grab the index number for the list of defined names (but make sure the index exists first).
                if (isset(config('eco-helpers.layout.auto_loaders')[$value])) {
                    $auto_loader_name = config('eco-helpers.layout.auto_loaders')[$value];
                    $auto_loader_number = $value;
                }
            }

            // 2. Are we passing a string? If so then we need to see if this exists as a $value in the array.
            if (gettype($value) == 'string') {

                if (in_array($value, config('eco-helpers.layout.auto_loaders'))) {
                    // If $value is in the defined list of auto_loaders then just use that name directly here.
                    $auto_loader_name = $value;
                    $auto_loader_number = array_search($value, config('eco-helpers.layout.auto_loaders'));
                }
            }

            // 3. If something other than a string or number was passed then punch out.
            // Note: we'll also get here if either an undefined name or index number is passed.
            if ($auto_loader_name === '') {
                return;     // Do nothing and just punch out.
            }

            // 4. Set the auto_loader name into the layout variable.
            // (Note: that if an auto_loader name exists--not empty--then it is "on".)
            // No need for -> self::$layout['auto_load'][$auto_loader_number][$auto_loader_name] = true;


            // Hard code '0' (the all page globals) as always on.
            // self::$layout['auto_load'][0] = 'static';    // Moved this to the init method

            // It's just a $key=>value pair for this auto_loader for the template to check.
            self::$layout['auto_load'][$auto_loader_number] = $auto_loader_name;

        }

    }

    /**
     * Set the flag for the templates when using create() or show() methods.
     * Certain things are not available during new records adds so template can
     * conditionally drop them out based on this value.
     *
     * Normally the create() section of the controller will turn this on.
     *
     * @param $value
     * @return void
     */
    public static function setWhenAdding($value=false) {
        if ($value) {
            self::$layout['when_adding'] = true;
        } else {
            self::$layout['when_adding'] = false;
        }
    }



    /**
     * Save all of this user's permissions for this route into the $form array for the template to use.
     * i.e $form['right']['SEC_EDIT'] (or whichever) should be available to any template to test.
     */
    /* Moved this functionality to access and took it out of the form variable.
    protected static function initUserRights() {

        $rights = [];
        if (Auth()->user() && Route::currentRouteName()) {

            $user_id = Auth()->user()->id;
            //$d = Auth()->user()->ugsID;
            $d = Auth()->user()->getActingRole();
            //$role_id = Auth()->user()->role_id;

            // Get the access token for this user on the current route -- then decode it into a usable array.
            //$combined_token = Access::getAccessToken(Route::currentRouteName(), $d, $user_id, $role_id);
            $combined_token = Access::getAccessToken(Route::currentRouteName(), $d, $user_id);


            //$rights = Access::decodeToken($combined_token, ehRole::findOrFail(Auth()->user()->ugsID)->site_admin);
            $rights = Access::decodeToken($combined_token);

        }
        self::$layout['right'] = $rights;
//        self::addKeyValuePairToSessionKey('layout', 'right', $rights);
    }
*/




    /**
     * Return the $layout array.
     *
     * @return array
     */
    public static function getLayout() {
        return self::$layout;
    }


}
