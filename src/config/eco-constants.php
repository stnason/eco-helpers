<?php

/*
|--------------------------------------------------------------------------
| ACCESS CONTROL PERMISSION LEVELS
|--------------------------------------------------------------------------
|
| These are the constants used byt the Access Control system.
|   These are part of the core package, not published and should not be changed.
|
*/
define('ACCESS_VIEW', 1);                  // route level - chkUserResourceAccess() (in base Controller)
define('ACCESS_EXPORT_RESTRICTED', 2);     // page level - chkUserFeatureAccess() (on each page controller)
define('ACCESS_EXPORT_DISPLAYED', 4);      // page level - chkUserFeatureAccess() (on each page controller)
define('ACCESS_EDIT', 8);                  // route level - chkUserResourceAccess() (in base Controller)
define('ACCESS_ADD', 16);                  // route level - chkUserResourceAccess() (in base Controller)
define('ACCESS_DELETE', 32);               // route level - chkUserResourceAccess() (in base Controller)
define('ACCESS_EXPORT_TABLE', 64);         // page level - chkUserFeatureAccess() (on each page controller)
define('ACCESS_FEATURE_1', 128);           // page level - chkUserFeatureAccess() (on each page controller)
define('ACCESS_FEATURE_2', 256);           // page level - chkUserFeatureAccess() (on each page controller)
define('ACCESS_FEATURE_3', 512);           // page level - chkUserFeatureAccess() (on each page controller)
define('ACCESS_FEATURE_4', 1024);          // page level - chkUserFeatureAccess() (on each page controller)
define('ACCESS_ADMIN', 2048);              // page level - chkUserFeatureAccess() (on each page controller)

// Note: This empty return is needed to keep the config system happy (it's expecting an array).
return [];