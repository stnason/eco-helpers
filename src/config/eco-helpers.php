<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Date Formats
    |--------------------------------------------------------------------------
    | System-wide settings for the date time format to display and for the js datepicker.
    |
    |   h is used for 12-hour time
    |   H is used for 24-hour time
    |   i stands for minutes
    |   s seconds
    |   a will return am or pm (use in uppercase for AM PM)
    |   m is used for months with digits
    |   d is used for days in digit
    |   Y uppercase is used for 4 digit year (lowercase for two digit year)
    |
    */
    'date_php_short' => 'm/d/Y',                // The format PHP should use to create dates for display use (w/o time; Note: see dt_sort below).
    'date_php_long' => 'm/d/Y h:i:s A',         // The format PHP should use to create dates for display with time.
    'date_js_short' => 'MM/DD/YYYY',            // JS date picker format for the web-form display of dates.
    'date_js_long' => 'MM/DD/YYYY h:i:s A',     // JS date picker format for the web-form display of dates.
    'date_dt_sort' => 'Y-m-d',                  // Format used in datatables to allow proper sorting when clicking on column.
    'date_sql_short' => 'Y-m-d',                // Format to store in the mysql database when using the data only.
    'date_sql_long' => 'Y-m-d H:i:s',           // Format to store in the mysql database when adding the time to the date.
                                                // Remember that we need the "H" 24-hour format here to save the timestamp properly for AM/PM later.

    /*
    |--------------------------------------------------------------------------
    | Created_by/ Updated_by name stamp
    |--------------------------------------------------------------------------
    | How will the updated_by and create_by fields be stamped.
    |  It may not be readily apparent, but there are use-cases for using something
    |   other than just the user id. Like just the name or the name and the (id).
    |    The user may have gotten married and changed their name or
    |     the user may have been purged or deleted.
    |
    |   '$'     - indicates this is a field from the User model.
    |   'w/o $' - indicates this is just text to include as is.
    |
    |   (processed from top to bottom and just concatenated together in the ehHasUserstamps trait.)
    */
    'user_update_stamp' => [
        //'$first_name',    // You can use any field from the User model.
        //'$last_name',     // "
        //'$email',         // "
        '$name',            // The user's registered (User()->name) login name ( as determined by User::uniqueUserName() ).
        ' (',               // Add any text you want: Just wrapping the User()->id in parentheses.
        '$id',              // The User()->id field
        ')'                 // Add any text you want: Closing parenthesis around the $id number.
    ],

    /*
    |--------------------------------------------------------------------------
    | User Photos Disk
    |--------------------------------------------------------------------------
    | The Laravel storage disk where the contact photos are stored.
    |
    */
    'users_photo_disk' => 'users',


    /*
    |--------------------------------------------------------------------------
    | User's Role at Login
    |--------------------------------------------------------------------------
    | When logging in use:
    |   0='default' ; the default role set in the user profile.
    |   1='last'    ; the last role used.
    |   2='user'    ; NOT IMPLEMENTED. Possible future expansion.
    |
    */
    'role_at_login' => 0,


    /*
    |--------------------------------------------------------------------------
    | New Registered User Role
    |--------------------------------------------------------------------------
    | Upon completing the self-registration process, the user will be assigned this role.
    |  This will also be used in the ehUsersController@dataConsistencyCheck()
    |   when the login is active and the default role is blank.
    |    (Eco Sample data creates 3 roles; id: 4 is a NO ACCESS ROLE.)
    |
    */
    'new_user_role' => 4,


    /*
    |--------------------------------------------------------------------------
    | Datatables defaults class
    |--------------------------------------------------------------------------
    | The html class(es) to use when creating datatables on a page.
    |  Note: "small" is the Bootstrap 5 text helper.
    |  - But may be to small if using an override css that's sets small too.
    |
    | When not specified as an optional $parameter in setAutoload($name, $parameter)
    |  then use the default init file.
    */
    'datatables_class' => 'display small compact cell-border nowrap order-column stripe hover',
    'datatables_default_init' => 'eh-dt-standard-init',

    /*
    |--------------------------------------------------------------------------
    | Copy "From" Role
    |--------------------------------------------------------------------------
    | This is used by the Roles module to control which role id is the default "copy-from" group.
    |  (for the permissions copy)
    |
    */
    'default_copy_from_role_id' => 6,      // 6 was the legacy "outside company w/lowest access" role.


    /*
    |--------------------------------------------------------------------------
    | Captcha settings
    |--------------------------------------------------------------------------
    | This is used to configure the parameters for the custom captcha class.
    |
    */
    'captcha' => [
        'captcha-some-parameter1' => 1,
        'captcha-some-parameter2' => 2,
        'captcha-some-parameter3' => 3,
    ],




    /*
    |--------------------------------------------------------------------------
    | Layout Section:
    |--------------------------------------------------------------------------
    */
    'layout' => [

        /*
        |--------------------------------------------------------------------------
        | User customizable templates:
        |--------------------------------------------------------------------------
        | Note: these templates must be in the resources/views/ecoHelpers folder.
        |
        */


        /*
        |--------------------------------------------------------------------------
        | Main Template User Add-In files:
        |--------------------------------------------------------------------------
        | Additional templates of your own to include in the base template
        |  (at either the top or bottom of the <body></body>).
        |
        */
        'app_add_ins_file_top' => 'eh-app-add-ins_top',         // Top of the document; right after <body> but before <main>
        'app_add_ins_file_bottom' => 'eh-app-add-ins_bottom',   // Bottom; right after </main> but before </body>

        /*
        |--------------------------------------------------------------------------
        | Base-template html head:
        |--------------------------------------------------------------------------
        | (metadata section).
        |
        */
        'html_head_file' => 'eh-html-head',


        /*
        |--------------------------------------------------------------------------
        | NAV Header:
        |--------------------------------------------------------------------------
        | Standard NAV header configuration (User configurable navbar file).
        |
        */
        'navbar_header_file' => 'eh-navbar-header',


        /*
        |--------------------------------------------------------------------------
        | Footer:
        |--------------------------------------------------------------------------
        | Standard footer configuration (User configurable footer file).
        |
        */
        'footer_file' => 'eh-footer',

        /*
        |--------------------------------------------------------------------------
        | CSS & JS Auto-Loaders Commands:
        |--------------------------------------------------------------------------
        | These are the available methods in the Controller (usage):
        |   ehLayout::setAutoload('datatables');    or ehLayout::setAutoload(6);
        |   ehLayout::setAutoload('datepicker');    or ehLayout::setAutoload(2);
        |
        |   NOTE: 'unsaved' is now hard-coded into the base template but included
        |          here for completeness.
        */
        'auto_loaders' => [
            0 => 'global',            // The initial--global--js and css for all pages.
            1 => 'unsaved',           // CRUD 'unsaved changes'; Included by default in the base-template.
            2 => 'datepicker',        // From jQuery UI
            3 => 'datetimepicker',    // From jQuery UI
            4 => 'help-system',       // Integrated pop-over help system by field.
            5 => 'chosen',            // Multi-select plugin
            6 => 'datatables',        // Datatables plugin
            7 => 'textedit',          // Tiny MCE text edit plugin
            8 => 'video',             // Video popup and playback functions.
        ],


        /*
        |--------------------------------------------------------------------------
        | CRUD Buttons:
        |--------------------------------------------------------------------------
        | The html button data used when pulling the standard buttons from ehLayout.
        |
        */
        'default_buttons' => [
            'save' => '<input class="btn btn-primary" type="submit" id="save" name="save" value="Save">',
            'new' => '<input class="btn btn-primary" type="submit" id="new" name="new" value="New">',
            'delete' => '<input class="btn btn-primary" type="submit" id="delete" name="delete" value="Delete">',
        ],


        /*
        |--------------------------------------------------------------------------
        | Defaults for Layout::initLayout().
        |--------------------------------------------------------------------------
        | Set default values for all display areas after an ehLayout::initLayout().
        |   WARNING: Do not delete or rename any of these.
        |            They match the base template and Layout files.
        |
        |   Set the default (starting value) of the page display areas:
        |   state:      (true/false); on or off
        |   content:    "What should it say if not set by the Controller"
        |   class:      css class used to style this base template element.
        |               Remember: These all have values in the eco-override.css file
        |                         and would rarely be set individually (ehLayout::setName('content','class'))
        |
        */

        'default' => [
            'banner' => [
                'state' => true,
                'content' => 'Eco Helper Banner - ' . date("l F jS, o"),     // Text based date format
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => 'container-fluid',
            ],
            'name' => [
                'state' => true,
                'content' => 'Eco Helper Page Name',
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => ''
            ],
            'icon' => [
                'state' => true,
                'content' => 'fa-solid fa-leaf',      // Note: this is the Font Awesome class only. The base template will add the <i> element.
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => ''
            ],
            'description' => [
                'state' => true,
                'content' => 'Eco Helper page descriptive heading',
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => ''
            ],
            'linkbar' => [
                'state' => true,
                'content' => [
                    // Default entries are loaded into ehLayout on init and only used if
                    // ehLayout@setLinkbar() is called w/ no parameters.
                    //  - normally you'd see: ehLayout::setLinkbar($linkbar->getLinkBar());
                    // Note That these default entries will not be security checked before displaying them.
                    //      Everyone will see them.
                    //      When using these defaults, the Menus/Pages entries will still determine
                    //      the level of security control (so user may get an error message or thrown back to home).
                    ['href' => '#', 'name' => 'Sample1', 'title' => 'Sample 1', 'target' => '_self'],
                    ['href' => '#', 'name' => 'Sample2', 'title' => 'Sample 2', 'target' => '_self'],
                    ['href' => '#', 'name' => 'Sample3', 'title' => 'Sample 3', 'target' => '_self'],
                    ['href' => '#', 'name' => 'Sample4', 'title' => 'Sample 4', 'target' => '_self'],
                ],
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => ''
            ],
            'dynamic' => [
                'state' => false,
                'content' => 'dynamic helper descriptive heading area',
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => ''
            ],
            'flash' => [
                'state' => true,
                'content' => 'Flash message area',
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => ''
            ],
            'attention' => [
                'state' => false,
                'content' => 'Eco Helpers <strong>Attention</strong> message',
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => 'bg-warning'
            ],
            'option-block' => [
                'state' => false,
                'content' => 'Option Block',
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => ''
            ],


        ],


        /*
        |--------------------------------------------------------------------------
        | Basic page layout options.
        | Note: When specifying display classes use either classes from your own
        |       custom css or from a CSS Framework (like Bootstrap or whatever).
        |--------------------------------------------------------------------------
        */

        'options' => [

            // System banner blink behavior.
            'banner_blink' => false,        // User controllable banner blink. (for on-the-fly setting of more important messages)
            'banner_auth' => true,          // Show banner only when authenticated.

            // Linkbar delimiters (between linkbar items)
            'linkbar_delimiter'=>' | ',

            // The <main> -- "outside" page container.
            'full_width' => true,

            // Specific to class to use for either full_width=true or full_width=false (normal).
            'page_main_class_full' => 'container-fluid ps-0 pe-0',
            'page_main_class_normal' => 'container pt-2',


            // for ehControls
            'alert_if_class' => 'bg-warning bg-opacity-25', // Used to colorize any "alert_if" message.
            'text_warning'=>'text-danger',                  // The css class for the error label text.
            'box_warning'=>'border-danger',                 // The css class for the error input box.


            // Pre-pended to the left of the page's descriptive heading text (if desired).
            'description_bullet' => '&#x2014; '     // An html wide dash.
            //'description_bullet' => '&bull; '     // An html small bullet.
            //'description_bullet' => '&#x2609; '   // An html entity.
                                                    // Using Fontawesome 6 icons.
            //'description_bullet' => '<i class="fa-solid fa-arrow-right-long"></i> '
            //'description_bullet' => '<i class="fa-solid fa-circle-info"></i> '
            //'description_bullet' => '<i class="fa-solid fa-minus"></i> '

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Page and Menus System Section
    |--------------------------------------------------------------------------
    */
    'menus' => [
        'enabled' => true,      // If you want to use your own menus system, or just static hard-coded, then just turn this off.
                                // If this is true, eh-pages is required, so you'll need to have run the ecoHelpers migration.
    ],


    /*
    |--------------------------------------------------------------------------
    | Notifications Section
    |--------------------------------------------------------------------------
    */
    'notifications' => [

        // 'enabled' => true,   // Must be on for the change role functionality to work and display properly.

        // Expansion room here for other notification configuration as needed.
        // auto_delete_when_read (as apposed to just marking as read and letting the user delete it.)

        // Role changes automatically provide an indication in a popup.
        // Should we additionally send a message to the flash area?
        'flash_role_change'=>false,
    ],


    /*
    |--------------------------------------------------------------------------
    | Controls Section
    |--------------------------------------------------------------------------
    */
    'controls' => [
        'options' => [
            'def_rows'=>3,                                  // Default rows for a text area input if nothing specified.
            'def_add_blank'=>false,                         // Default value for add_blank when not included from the input.
        ]

    ],


    /*
    |--------------------------------------------------------------------------
    | Access Section
    |--------------------------------------------------------------------------
    */
    'access' => [

        /*
        |--------------------------------------------------------------------------
        | Access (permissions) System
        |--------------------------------------------------------------------------
        | Turn on (or off) the Access System (page control <-> Role permissions by route)
        |  This is implemented in the ehBaseController's __construct() method.
        |   and in the ehCheckPermissions middleware.
        |
        */
        'enabled' => true,

        /*
        |--------------------------------------------------------------------------
        | Allow Unauthenticated Child Menus
        |--------------------------------------------------------------------------
        | When a child page is set to public and the parent module is
        |  set to authenticate or check permissions;
        |   true = allow direct uri access to that route.
        |   false = restrict children to the same security setting as the parent module.
        |
        */
        'allow_unauthenticated_children' => true,

        /*
        |--------------------------------------------------------------------------
        | Home page after login/ logout
        |--------------------------------------------------------------------------
        | The URL (not a route!) to go to after successfully logging in or logging out.
        |
        |  Note: Set either of these to blank ( 'login_home_page' => '' ) to use the hard-coded defaults:
        |   Login default uses whatever is defined in the RouteServiceProvider::HOME
        |   Logout default uses the root of the website: '/'
        */
        'login_home_page' => 'dashboard',
        'logout_home_page' => 'dashboard',

    ],


];
