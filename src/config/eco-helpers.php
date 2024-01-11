<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Date Formats
    |--------------------------------------------------------------------------
    | System-wide settings for the date time format to display and for the js datepicker.
    |
    |   h is used for 12 hour time
    |   H is used for 24 hour time
    |   i stands for minutes
    |   s seconds
    |   a will return am or pm (use in uppercase for AM PM)
    |   m is used for months with digits
    |   d is used for days in digit
    |   Y uppercase is used for 4 digit year (lowercase for two digit year)
    |
    */
    'date_format_php_short' => 'm/d/Y',                 // The format PHP should use to create dates for display use (w/o time).
    'date_format_php_long' => 'm/d/Y h:i:s A',          // The format PHP should use to create dates for display use (with time).
    'date_format_js_short' => 'MM/DD/YYYY',             // JS date picker format for the web-form display of dates.
    'date_format_js_long' => 'MM/DD/YYYY h:i:s A',      // JS date picker format for the web-form display of dates.
    'date_format_sql_short' => 'Y-m-d',                 // Format to store in the mysql database when using the data only.
    'date_format_sql_long' => 'Y-m-d H:i:s',            // Format to store in the mysql database when adding the time to the date.
                                                        // Remember that we need the "H" 24 hour format here to save the timestamp properly for AM/PM later.

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
    //TODO: this has to go away. Find where it's used and lock it into always using default.
    // If you have Admin type access and you switch to a lower level, you could be permanently locked out.

    'role_at_login' => 0,


    /*
    |--------------------------------------------------------------------------
    | New Registered User Role
    |--------------------------------------------------------------------------
    | Upon completing the self-registration process, the user will be assigned this role.
    |   (Eco Sample data creates 3 roles; id: 4 is a NO ACCESS ROLE.
    |
    */
    'new_user_role' => 4,


    /*
    |--------------------------------------------------------------------------
    | Datatables class
    |--------------------------------------------------------------------------
    | The html class(es) to use when creating datatables on a page.
    | Note: "small" is the Bootstrap 5 text helper. - But along with the override css that's too small now.
    |
    */
    'datatables_class' => 'display compact cell-border nowrap order-column stripe hover',


    /*
    |--------------------------------------------------------------------------
    | Copy "From" Role
    |--------------------------------------------------------------------------
    | This is used by the Roles module to control which role id is the default "copy-from" group.
    | (for the permissions copy)
    |
    */
    'default_copy_from_role_id' => 6,      // 6 was the legacy "outside company with lowest access" group.


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
        | Main Template User Add-In file:
        |--------------------------------------------------------------------------
        | Additional template of your own to include in the base template (at the top of the <body>).
        |
        */
        'app_add_ins_file' => 'app-add-ins',


        /*
        |--------------------------------------------------------------------------
        | Base-template html head:
        |--------------------------------------------------------------------------
        | (metadata section).
        |
        */
        'html_head_file' => 'html-head',


        /*
        |--------------------------------------------------------------------------
        | NAV Header:
        |--------------------------------------------------------------------------
        | Standard NAV header configuration (User configurable navbar file).
        |
        */
        'navbar_header_file' => 'navbar-header',


        /*
        |--------------------------------------------------------------------------
        | Footer:
        |--------------------------------------------------------------------------
        | Standard footer configuration (User configurable footer file).
        |
        */
        'footer_file' => 'footer-1',


        /*
        |--------------------------------------------------------------------------
        | CSS & JS Auto-Loaders:
        |--------------------------------------------------------------------------
        | Static and auto loaded entries for css and js.
        | These are handled by the master template as "per page" directives which
        | can be called by the controller.
        |
        */
        'css_loader_file' => 'css-loader',
        'js_loader_file' => 'js-loader',

        /*
        |--------------------------------------------------------------------------
        | CSS & JS Auto-Loaders Commands:
        |--------------------------------------------------------------------------
        | CSS & JS auto-loaders defined in the loader files above.
        | These are available in the controller as:
        |   ehLayout::setAutoload('unsaved');
        |   ehLayout::setAutoload('datatables');
        |   ehLayout::setAutoload('datepicker');
        |
        */
        'auto_loaders' => [
            1 => 'unsaved',           // Included by default in the base-template.
            2 => 'datepicker',        // Jquery UI
            3 => 'datetimepicker',    // JQuery UI
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


        /*TODO: This section may need to go away.
            It starts to be confusing as to where the page info is coming from.
            The original idea was that we didn't necessarily need a pages table but that's long since gone by the wayside.
            In order to uses Menus and/or Permissions, eh-pages table is a requirement.
                !!! ehLayout::initLayout(); IS USING THIS !!!
            1/4/2024: Commenting the whole block out to see if we have any negative impact before completely deleting it.
            1/7/2024: Turns out it is used by ehLayout::initLayout();
                      So back in until we can look into this further and make some design decisions.
        */

        /*
        |--------------------------------------------------------------------------
        | Defaults for Layout::initLayout().
        |--------------------------------------------------------------------------
        |
        | Set which content "areas" are default on (displayed) after an initLayout()
        |   WARNING: Do not delete or rename any of these. They match the base template and Layout files.
        |
        |   Set the default (starting value) of the page display areas:
        |   state:      (true/false); on or off
        |   content:    "What should it say if not set by the Controller"
        |   class:      css class used to style this base template element.
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
                    ['href' => 'https://nasonproductions.com', 'name' => 'np.com', 'title' => 'link to np.com', 'target' => '_blank'],
                    ['href' => 'https://nasonproductions.com', 'name' => 'np.com', 'title' => 'link to np.com', 'target' => '_blank'],
                    ['href' => 'https://nasonproductions.com', 'name' => 'np.com', 'title' => 'link to np.com', 'target' => '_blank'],
                ],
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => ''
            ],
            'dynamic' => [
                'state' => true,
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
                'state' => true,
                'content' => 'Eco Helper Attention message.',
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => 'bg-warning'
            ],
            'option-block' => [
                'state' => true,
                'content' => 'Option Block',
                'collapse' => false,
                'collapse_chr' => ' ',
                'class' => ''
            ],


        ],


        /*
        |--------------------------------------------------------------------------
        | Basic page layout options.
        |--------------------------------------------------------------------------
        */

        'options' => [

            'banner_blink' => false,        // User controllable banner blink. (for on-the-fly setting of more important messages)
            'banner_auth' => false,         // Show banner only when authenticated.

            //'round' => false,             // Round what -- all the box areas or what ??

            // The farthest "outside" container??
            'width_full' => true,           // I think this is bs specific so not sure how to generify? (ask for a basic container class?)
            'page_container_class' => 'container pt-2',

            // Used by Controls to colorize any "alert_if" message.
            'alert_if_class' => 'bg-warning bg-opacity-25',

            'description_bullet' => '&#x2014; '    // Pre-pended to the left of the page's descriptive heading text.
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Page and Menus System Section
    |--------------------------------------------------------------------------
    */
    'menus' => [
        'enabled' => true,      // If you want to use your own menus system, or just static hard-coded, then just turn this off.
                                // If this is true, eh-pages is required so you'll need to have run the ecoHelpers migration.
    ],


    /*
    |--------------------------------------------------------------------------
    | Notifications Section
    |--------------------------------------------------------------------------
    */
    'notifications' => [

        //TODO: REVIEW: This may have to be deprecated--or at minimum--have a lot more control built in.
        // THIS IS SPECIFICALLY used when changing roles -- so it would have to auto-enable or warn the
        // user to do that in the case that it was off and you enabled the access system.
        // (Probably best just left on though.)
        'enabled' => true,   // Enable the eco-helpers notifications system.

        // Expansion room here for other notification configuration as needed.
        // auto_delete_when_read (as apposed to just marking as read and letting the user delete it.)
    ],

    /*
    |--------------------------------------------------------------------------
    | Controls Section
    |--------------------------------------------------------------------------
    */
    'controls' => [],


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
        | This is implemented in the ehBaseController's __construct() method.
        | and in the ehCheckPermissions middleware.
        |
        */
        'enabled' => true,

        /*
        |--------------------------------------------------------------------------
        | Allow Unauthenticated Child Menus
        |--------------------------------------------------------------------------
        | When a child page is set to public and the parent module is
        | set to authenticate or check permissions;
        | true = allow direct uri access to that route.
        | false = use the parent module's setting.
        |
        */
        'allow_unauthenticated_children' => true,

    ],


];
