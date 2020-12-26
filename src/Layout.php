<?php

namespace ScottNason\EcoHelpers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;

/**
    * Handles all standard page/form area display control interaction
    *  - banner, heading, names etc.
    */

class Layout
{

    /**
     * Control access to the 'form' session array through here.
     * Call with no parameters to retrieve the whole array.
     * Or pass just the name of the field data you want.
     *
     * 'system_banner'        // Should this page show a banner at all
     * 'blinking_banner'      // Flashing development banner (set in _config)
     * 'system_message'       // Actual system message for top line under nav - header
     * 'heading_style',       // Controller can override but base template used to build the page heading
     * 'name'=>               // Name of this page's Title (can be set in page controller)
     * 'heading'              // page's Heading text under main title (can be set in page controller)
     * 'number'               // menu item number of this page
     * 'menu_text'            // the menu text associated with this menu item (if it is a menu page)
     * 'html_menus'           // the complete html menus structure for this user's rights
     * 'right'                // the user's permissions stack for this route. (user + group + role)
     *
     * @param null $form_field - name of the data parameter you want to get (blank is whole array)
     * @return bool             - return the single data, the whole array or false if the requested data is not there.
     */
    public static function getFormArray($form_field = null)
    {
        if ($form_field == null) {
            // return the whole page array from the session
            return session('form');
        }

        // Return the called for form_field if passed above.
        if ((session('form.'.$form_field)) != null) {
            return session('form.'.$form_field);
        } else {
            return false;
        }
    }


    /**
     * Save all of this user's permissions to the session for any needed use by the template.
     * $form['right'] should be available to any template to use.
     */
    public static function initUserRights() {

        /* Too tightly coupled to the permissions system - probably need to leave all of this out of here.
        $rights = [];
        if (Auth()->user() && Route::currentRouteName()) {

            $user_id = Auth()->user()->usID;
            $group_id = Auth()->user()->ugsID;
            $role_id = Auth()->user()->role_id;

            // Get the access token for this user on the current route -- then decode it into a usable array.
            $combined_token = Access::getAccessToken(Route::currentRouteName(), $group_id, $user_id, $role_id);
            $rights = Access::decodeToken($combined_token, Group::findOrFail(Auth()->user()->ugsID)->site_admin);

        }
        self::addKeyValuePairToSessionKey('form', 'right', $rights);
        */
    }

    /**
     * Initialize the $form array by setting up blank values for all.
     * Additionally, maybe it could accept a $form array with any number of values (?)
     * @param null $form_array
     */
    public static function initFormArray($set_test_data = false)
    {

        $blank = '';

        $form_array = session('form');          // See if there's anything in session('form') key first.
        if (!$form_array) {
            $form_array = [];
        }       // If not then initialize it to an array.

        if ($set_test_data) {

            if (!isset($form_array['option_block'])) {
                $form_array['option_block'] = '<img src="/media_content/images/dummy.jpg" class="float-left rounded-lg mt-2 pr-3">';
            }
            if (!isset($form_array['name'])) {
                $form_array['name'] = 'Test Page Name';
            }
            if (!isset($form_array['heading'])) {
                $form_array['heading'] = '- and example of a test page heading';
            }
            if (!isset($form_array['linkbar'])) {
                $form_array['linkbar'] = [
                    'a' => '<a href="#">a link</a>',
                    'b' => '<a href="#">a link</a>',
                    'c' => '<a href="#">a link</a>'
                ];
            }
            if (!isset($form_array['dynamic_heading'])) {
                $form_array['dynamic_heading'] = 'The <strong>Dynamic</strong> Page Heading';
            }
            if (!isset($form_array['attention_message'])) {
                $form_array['attention_message'] = 'an Attention Message';
            }
            if (!isset($form_array['buttons'])) {
                $form_array['buttons'] = [
                    'save' => '<input class="btn btn-primary" type="submit" name="save" value="Save">',
                    'primary' => '<button type="button" class="btn btn-primary">Primary</button>',
                    'secondary' => '<button type="button" class="btn btn-secondary">Secondary</button>',
                    'success' => '<button type="button" class="btn btn-success">Success</button>',
                    'danger' => '<button type="button" class="btn btn-danger">Danger</button>',
                    'warning' => '<button type="button" class="btn btn-warning">Warning</button>',
                    'info' => '<button type="button" class="btn btn-info">Info</button>',
                    'light' => '<button type="button" class="btn btn-light">Light</button>',
                    'dark' => '<button type="button" class="btn btn-dark">Dark</button>',
                    'link' => '<button type="button" class="btn btn-link">Link</button>'

                ];
            }


            //$form_array['system_banner']        // NOTE: DON'T SET THIS HERE.
            //                                             This should only be allowed to come from the site settings.

            if (!isset($form_array['number'])) {
                $form_array['number'] = '5';
            }

            if (!isset($form_array['quicklinks'])) {
                $form_array['quicklinks'] = ['a' => '<a href="#">a link</a>'];
            }

            // Create a menu here for testing or include some canned "sample" content (sample-menus.php)
            if (!isset($form_array['html_menus'])) {
                $form_array['html_menus'] = file_get_contents(base_path() . '/resources/views/layouts/admin-menus.php');
            }

            //$form_array['flash_message'] = 'System Flash Message area.';
            if (session('message')) {
                session()->flash('message', 'System Flash Message area.');
            }

            // Finally store this into the Laravel session.
            session(['form' => $form_array]);

        } else {

            // Initialize anything that's not set to the predefined blank variable (set above).
            self::setOptionBlock($blank);
            self::setName($blank);
            self::setNameIcon($blank);
            self::setHeading($blank);
            self::setLinkbar($blank);
            self::setDynamicHeading($blank);
            self::setAttentionMessage($blank);
            self::setButton($blank);
            self::setNumber($blank);
            self::setQuicklinks($blank);
            self::setMenuText($blank);
            self::setAutoload($blank);
            self::setJumbotron($blank);

            // self::setHTMLmenus(Menu::buildHTMLFlyoutMenus());

            // TODO: May just move the Menu funtions out to a Menus class but keep the menu control functions here.
            //  since they are part of the base display system in standard nav header.
            // BUT EVEN BETTER YET, would be to re-do the menuing system so we didn't have to provide the full html; just the array.
            //

            // self::setHTMLmenus($blank);

            self::initUserRights();             // save the user permissions in the session $form variable for the template to use.
            //self::setFlashMessage($blank);    // this does something that keeps the flash from working as expected on a form save.
        }
    }

    /**
     * Resets all message areas to their default starting state, then
     * gets the page information for this route and populates the name and heading.
     *
     * @return array|bool
     */
    public static function resetForm() {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Reset the page level display areas to their default starting state.
        // Initialize all of the session('form') values to their default.
        self::showAllMessageAreas();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the default css classes set for standard datatables.
        self::addKeyValuePairToSessionKey('form','datatables_class',
            "display cell-border nowrap order-column compact stripe hover");

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Pull (and set) the page information associated with this route.

        // Too tightly coupled to the Menu system - should pass this in.
        // $p = Menu::getPageInfoByRouteName(Route::currentRouteName());

        $p = null;


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Check to see if this is a resourceful route and modify the page name as appropriate below.
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
            self::setNameIcon($p->name_icon);
            self::setHeading($p->heading);
            return $p;
        } else {
            self::setName('No Page Entry in Pages');
            self::setHeading(' - no page entry in pages.');
        }
    }



    /**
     * Turns all page message areas either ON or OFF.
     *  !EXCEPT! Always resets banner to ON since that is the default system layout
     *
     * Banner           - ALWAYS ON by default.
     * Option Block     - ALWAYS OFF by default.
     * Page Name        - set to value of $show
     * Page Heading     - set to value of $show
     * LinkBar          - set to value of $show
     * Crud Heading     - set to value of $show
     * System Flash     - set to value of $show
     * Attention Msg    - set to value of $show
     * Buttons area     - set to value of $show
     *
     * @param bool $show
     */
    public static function showAllMessageAreas($show = true, $reset = true)
    {

        if ($reset) {                                   // Reset everything to a default starting value.
            self::initFormArray();
        }

        self::showFlashArea(true);              // Default state for System Flash is on. (would only turn off if you're moving it and servicing it elsewhere)
        self::showSystemBannerArea(true);       // On is always the default for the System Banner
        self::showOptionBlockArea(false);       // Off is always the default for the Option Block

        self::showNameArea($show);
        self::showNameIconArea($show);
        self::showHeadingArea($show);
        self::showLinkbarArea($show);
        self::showDynamicHeadingArea($show);
        self::showAttentionMessageArea($show);
        self::showButtonArea($show);

    }


    /**
     * Should this page show a banner at all.
     * Note: any given page controller can turn it off as needed.
     *
     * @param $value - true or false;
     *
     * Note: We're not going to allow turning off the System message or the CRUD message
     *       These are both important system messages that you should not be able to turn off.
     */
    public static function showSystemBannerArea($show = true)
    {
        self::addKeyValuePairToSessionKey('form','system_banner_show',$show);
    }

    public static function showNameArea($show = true)
    {
        self::addKeyValuePairToSessionKey('form','name_show',$show);
    }

    public static function showNameIconArea($show = true)
    {
        self::addKeyValuePairToSessionKey('form','name_icon_show',$show);
    }

    public static function showHeadingArea($show = true)
    {
        self::addKeyValuePairToSessionKey('form','heading_show',$show);
    }

    public static function showLinkbarArea($show = true)
    {
        self::addKeyValuePairToSessionKey('form','linkbar_show',$show);
    }

    public static function showDynamicHeadingArea($show = true)
    {
        self::addKeyValuePairToSessionKey('form','dynamic_heading_show',$show);
    }

    public static function showFlashArea($show = true)
    {
        self::addKeyValuePairToSessionKey('form','flash_message_show',$show);
    }

    public static function showAttentionMessageArea($show = true)
    {
        self::addKeyValuePairToSessionKey('form','attention_message_show',$show);
    }

    public static function showButtonArea($show = true)
    {
        self::addKeyValuePairToSessionKey('form','buttons_show',$show);
    }

    public static function showOptionBlockArea($show = true)
    {
        self::addKeyValuePairToSessionKey('form','option_block_show',$show);
    }


    /**
     * An Optional left float div for additional page heading content - usually like a image file or other block content.
     *
     * @param string $option_block
     */
    public static function setOptionBlock($option_block = '')
    {
        self::addKeyValuePairToSessionKey('form','option_block',$option_block);
    }

    /**
     * The page's Title (
     *  - Can be set (overridden) in page controller; but will default to the the page name set in the Pages table.
     *  - Name for Title attribute and h1 page name
     *
     * @param $value
     */
    public static function setName($form_name = '')
    {
        self::addKeyValuePairToSessionKey('form','name',$form_name);
    }

    /**
     * An icon to place to the left of the page name.
     * Will accept the class name(s) with or with out the <i> tag
     * (a Fontawsome span)
     *
     * @param string $icon_name
     */
    public static function setNameIcon($icon_name = '')
    {
        if (!empty($icon_name)) {

            // <i class="far fa-address-card"></i>

            // Allow this to accept the cut-n-paste direct from the Fontawesome website.
            // Note; the one stored in the Menus table as already been cleanec
            // Clean up the Fontawesome -- should be just the class with no html tags.
            if (strpos($icon_name,'"></i>') > 0) {
                // Already formatted with the <i> tag so just keep it as is.
            } else {
                // Class name only so add the <i> tag.
                $icon_name = '<i class="'.$icon_name.'"></i>';
            }

        }
        self::addKeyValuePairToSessionKey('form','name_icon',$icon_name);
    }



    /**
     * The page's Heading text; right under main Title/Page Name
     *  - Can be set (overridden) in page controller; but will default to the the page heading set in the Pages table.
     *
     * @param $value
     */
    public static function setHeading($heading = '')
    {
        self::addKeyValuePairToSessionKey('form','heading',$heading);
    }

    public static function setLinkbar($whole_linkbar = '')
    {
        self::addKeyValuePairToSessionKey('form','linkbar',$whole_linkbar);
    }

    public static function setDynamicHeading($dynamic_heading = '')
    {
        self::addKeyValuePairToSessionKey('form','dynamic_heading',$dynamic_heading);
    }

    /**
     * Sets a system message into the page array for use by the template and/or flash() system
     *  This is a DISPLAY function ONLY and does not "service" the flash message. Just loads up
     *  the screen display area for use.
     *
     * @param $flash_message
     */

    /* moved above to service flash
    public static function set_flash_display($flash_message='') {
        $_SESSION['form']['flash_message'] = $flash_message;
    }
    */


    /**
     * Setter to set the Page Attention Message area message
     * bg-secondary
     * bg-success
     * bg-danger
     * bg-warning
     * bg-info
     * bg-light
     * bg-dark
     * bg-white
     * @param string $attention_message
     * @param string $attention_message_class
     */
    public static function setAttentionMessage($attention_message = '', $attention_message_class = 'bg-warning')
    {
        self::addKeyValuePairToSessionKey('form','attention_message_class',$attention_message_class);
        self::addKeyValuePairToSessionKey('form','attention_message',$attention_message);
    }

    /**
     * Setter to set the buttons array inside the page array session key.
     *  Pass nothing to clear previous buttons -- or pass values to add to the button array.
     *  Note: buttons display in the pre-defined base layout button area.
     * If no html is specified then default values are used
     *
     * @param string $button_name
     * @param string $html_button
     */
    //public static function setButton($button_name = null, $html_button = null, $form_action = null)
    public static function setButton($button_name = null, $html_button='')
    {

        $tmp_html = $html_button;

        /*
        $tmp_html = '';
        switch ($button_name) {
            case 'save':
                $tmp_html = '<input class="btn btn-primary" type="submit" id="save" name="save" value="Save">';
                break;
            case 'delete':
                $tmp_html = '<input class="btn btn-primary" type="submit" id="delete" name="delete" value="Delete">';
                break;
            case 'new':
                $tmp_html = '<input class="btn btn-primary" type="submit" id="new" name="new" value="New">';
                break;
        }
        */


        // Does this key exist on the session array -- or is the passed button name empty ?
        if (Session::get('form.buttons') == null || empty($button_name)) {
            $tmp_array = [];
        } else {
            $tmp_array = Session::get('form.buttons');
        }

        $tmp_array[$button_name] = $tmp_html;
        Session::put('form.buttons',$tmp_array);

    }






    /**
     * The Menu item number of this page
     * - id (menu id #)
     *
     * @param $value
     */
    public static function setNumber($form_number = '')
    {
        self::addKeyValuePairToSessionKey('form','number',$form_number);
    }

    /**
     * Stores all of the settings for the banner (Jumbotron) array.
     *
     * @param array $jumbotron
     */
    public static function setJumbotron($jumbotron=[])
    {
        self::addKeyValuePairToSessionKey('form', 'jumbotron', $jumbotron);
    }

    /**
     * Set the QuickLinks array
     * @param array $quicklinks
     */
    public static function setQuicklinks($quicklinks=[])
    {
        self::addKeyValuePairToSessionKey('form', 'quicklinks', $quicklinks);
    }

    public static function setFlashMessage($flash_message=[])
    {

        session()->flash('message', $flash_message);
        //session(['form' => ['flash_message' => $flash_message]]);
        //$_SESSION['form']['flash_message'] = $flash_message;
    }


    /**
     * Auto-loader states for js and css include (like text edit, video, date/time pickers, etc)
     *  Note: there is no check for a valid value.
     *
     *  Currently in use in the standard header/footer (10/12/2019);
     *  - textedit
     *  - datepicker
     *  - datetimepicker
     *  - helpsystem
     *  - chosen
     *  - datatables
     *
     * @param null $value
     */
    public static function setAutoload($value=null) {

        if ($value==null) {
            // Using an empty call to reset the key to nothing. (turn all auto-loaders off)
            self::addKeyValuePairToSessionKey('form', 'autoload', []);
        } else {
            // We're adding this to the autoload key so we don't wipe out anything that's already in there.
            self::addKeyValuePairToSessionKey('form.autoload', $value, true);
        }

    }

    /**
     * The Menu text associated with this menu item (if it is a menu page)
     * - used by the per module splash page navigation
     *
     * @param $value
     */
    public static function setMenuText($menu_text = '')
    {
        self::addKeyValuePairToSessionKey('form', 'menu_text', $menu_text);
    }

    /**
     * There is no set_banner() method!
     * This is simply left here for clarity and a reminder:
     *
     * The banner is set in Site Configuration and applied to the base page template automatically.
     * So it would be confusing to allow it to be changed programmatically as well.
     *
     * If you need to change the banner message, login as an Admin and do it in Site Configuration.
     * If you have a custom page and don't want the banner; you can turn it off using showSystemBannerArea().
     */
    /*
    public static function set_banner($value) {
        $_SESSION['sysdefaults']['SYSTEM_BANNER'] = $value;
    }
    */


    /**
     * Holds the complete html menu structure for this user (based on their rights)
     *
     * @param $complete_html_menus
     * 
     */
    
    /* THIS SHOULD NOT BE IN HERE
    public static function setHTMLmenus($complete_html_menus = '')
    {
        self::addKeyValuePairToSessionKey('form', 'html_menus', $complete_html_menus);
    }
    */


    /**
     * Add a key->value pair to a current session array
     * (note: seems like there should be some combination of the session() helper function to do this
     *  but I have been unable to get anything to work as expected -- simply adding key-> value pair to a current array.
     *
     * @param $current_array_name
     * @param $key
     * @param $value
     * @return bool
     */
    protected static function addKeyValuePairToSessionKey ($current_key_name, $key, $value) {

        /* Stuff  that DID work
        $form = Session::get('form');
        $form['system_banner_show'] = $show;
        Session::put('form',$form);
        */

        // If the key is missing then initialize it
        if (session::get($current_key_name) === null) {
            //return false;
            session::put($current_key_name,[]);
        }

        // Get the session $current_key_name before changing it.
        $tmp_array = session::get($current_key_name);

        // Change it - or update it.
        // Change it - or update it.
        $tmp_array[$key] = $value;

        // Then re-save it back to the session.
        session::put($current_key_name,$tmp_array);

        return true;    // Assume everything went okay.

    }


}
