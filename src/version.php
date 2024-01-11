<?php
return [
    'APP_VER' => 'v1.0.27',
    'APP_LASTUPDATE' => '01/11/2024'
];

/**
 * mm/dd/yyyy:vx.x: 0.00 :descriptions (REMEMBER - don't use colons in the descriptions -- it's a delimiter.)
 * version/ update history
 * 01/11/2024:v01.00.27: 3.75 :Cleaning up ehExamplesController. Finish out @update. Fix timezone, unauthorized get-next. Work on intented route after login.
 * 01/10/2024:v01.00.26: 2.50 :Clean up Example; fix bio too wide, jQuery datepicker error, no GoTo dropdown list, goto not working, set email focus on login.
 * 01/10/2024:v01.00.26: 1.25 :Clean up user detail edit. Fix rehashing password. Remove Force Password. Change Verified date to long.
 * 01/09/2024:v01.00.26: 1.75 :Do-Over on package and git repository. Deleted both and started a new local git repository. (had somehow deployed the _DO_NOT_DEPLOY folder with the .env file in it!)
 * 01/08/2024:v01.00.24: 3.00 :Comments, TODOs and operational (on-the-fly) punch list items.
 * 01/07/2024:v01.00.24: 2.00 :Change menu array generator to use created/updated_at. Fix eco-helpers missing initial ehLayout. More L11 testing.
 * 01/07/2024:v01.00.24: 0.25 :Changing the git versioning to a 1.x.x to see if that helps to keep current.
 * 01/07/2024:v00.00.23: 1.50 :Laravel 11 tests. Still fighting with git package version and getting the most current!!
 * 01/06/2024:v00.00.23: 1.50 :Move all Breeze Auth controllers into project.
 * 01/06/2024:v00.00.22: 1.75 :Test drive w/Laravel 11 (--dev).
 * 01/05/2024:v00.00.22: 1.00 :TODOs and comments.
 * 01/04/2024:v00.00.22: 1.00 :TODOs and comments.
 * 01/01/2024:v00.00.21: 1.00 :Moving forward finally. Testing and trouble shooting various kinds of access.
 * 12/27/2023:v00.00.21: 2.00 :Finishing out the forgot, rest and request forms.
 * 12/26/2023:v00.00.21: 2.00 :Changing registration form and flushing out new RegisteredUserController.
 * 12/25/2023:v00.00.21: 2.00 :Finally back on track. Testing out the rest of the Login eco system.
 * 12/24/2023:v00.00.21: 2.00 :If it ain't one thing...now I can't get a logout form to work! Struggling with the return type declarations in ehLoginAndAuthenticatedFunctions trait so just getting rid of them. (not sure if there's really an impact or not.)
 * 12/24/2023:v00.00.21: 1.00 :OMG! I was using Auth()->logout() in the navbar for user sign out. Instead of route('logout'). I truly am my own worst enemy!
 * 12/23/2023:v00.00.21: 2.00 :Made little progress yesterday. Taking another run at Breeze and new--yet again--Laravel project. Can login, but can't stay logged in when changing pages (same symptom with Breeze or UI). ??
 * 12/22/2023:v00.00.20: 2.00 :(sigh) Authentication should not be this difficult; Back on UI; still working on email verification.
 * 12/21/2023:v00.00.20: 2.00 :May have to throw in the towel and go back to Laravel/UI. Having a lot of trouble with email verify working with eco. Added 'verfied' middleware to the ehBaseController access check.
 * 12/20/2023:v00.00.20: 2.00 :Still working on getting auth to not login without verifying email. Change user migration to use original created/updated_at fields.
 * 12/19/2023:v00.00.19: 3.00 :Adding back in the idea of protected routes for the auth routes.
 * 12/16/2023:v00.00.19: 3.00 :Still following all the Breeze stuff through and trying to re-tool to match it. (may have a middleware order problem when logging in to verify email)
 * 12/14/2023:v00.00.19: 3.00 :Trying to learn Breeze enough to interact with it.
 * 12/11/2023:v00.00.19: 2.00 :Starting a clean Laravel Breeze project.
 * 12/08/2023:v00.00.18: 2.00 :Working on building out role change (users/role).
 * 12/08/2023:v00.00.18: 1.00 :Update (clean-up) Deployment guide.
 * 12/08/2023:v00.00.18: 4.00 :Dropping the idea of authentication popups (and Livewire) and moving forward with just fullscreen templates.
 * 12/05/2023:v00.00.17: 2.00 :Playing with Livewire to see if this can help solve the route/modal popup problems (so far no luck!)
 * 12/04/2023:v00.00.17: 3.00 :Authentication-middleware is redirecting to route instead of using the popup;
 * 12/03/2023:v00.00.17: 3.00 :Continue testing menus and auth (punch list items); working on behaviour of ehMenus.
 * 12/03/2023:v00.00.16: 2.00 :Start running through some real rough testing on manual routes, then enable menus.
 * 12/02/2023:v00.00.16: 3.00 :Trouble shooting interaction between login errors and eh-user-roles issues. Looks like you need the dataType: json for the Laravel back-end auth to work properly with ajax auth calls.
 * 12/01/2023:v00.00.16: 3.00 :Experimenting with splitting out the authentication into separate files.
 * 11/30/2023:v00.00.16: 2.00 :Trouble shooting login issues (Ajax/ routes/ redirect...)
 * 11/30/2023:v00.00.16: 2.00 :Final deployment cleanup; fix notification error when false; add version to splash page.
 * 11/29/2023:v00.00.15: 4.00 :Start another fresh clean Laravel project to continue testing deployment.
 * 11/24/2023:v00.00.14: 2.00 :Rough out idea on publishable ehHomeController for initial setup ana examples.
 * 11/23/2023:v00.00.13: 4.00 :Finishing up the sample data structure with pages and tokens.
 * 11/22/2023:v00.00.12: 4.00 :Building out ehSampleData class to do heavy lifting for the  eco-helpers:sample-data artisan command.
 * 11/21/2023:v00.00.12: 5.00 :Setup new project to start running through deployment testing. Modified all migrations to get rid of errors then modified ServiceProvider to Register rather than Publish.
 * 11/19/2023:v00.00.10: 3.50 :Experimenting with console command to possibly generate sample data.
 * 11/17/2023:v00.00.10: 3.00 :Continue cleaning up the deployment guide. Start to look at creating an Artisan command for sample data.
 * 11/17/2023:v00.00.10: 4.75 :Change table naming (prepend "eh_") and attempt to add sample/startup data. Add notification section to config file.
 * 10/28/2023:v00.00.09: 1.75 :Struggling to get current version to pull with composer. Apparently the last version is always considered "stable". Use "dev-master@dev" in composer json to force grabbing the most current (possibly breaking) version.
 * 10/27/2023:v00.00.08: 2.75 :Cleaning up documentation on authentication system. Added authentication_modals config to eco-helpers config and app template.
 * 09/14/2023:v00.00.07: 2.00 :Still going in circles with Registration; fixing includes, namespaces, missing routes...
 * 09/11/2023:v00.00.07: 2.00 :Cleaning up issues with initial deployment and database migrations.
 * 09/10/2023:v00.00.07: 2.00 :Cleaning up issues with registration. Adding default values to users table.
 * 08/23/2023:v00.00.06: 2.00 :Working on TODOs.
 * 08/21/2023:v00.00.06: 2.00 :Fix issue with banner and banner_auth setting in main template.
 * 08/20/2023:v00.00.06: 2.00 :Deployment; authentication on home not working. FIXED; missing /home route when in RouteServiceProvider ->  public const HOME = '/home';
 * 08/18/2023:v00.00.05: 2.00 :Deployment; just running through and cleaning up the deployment guide. Have found no way to rename before copy so may just adopt the use of the --force flag if publishing against a clean new Laravel install.
 * 08/17/2023:v00.00.05: 2.00 :Deployment; trying a new approach to registering middleware under register(). Trying to figure out if there's a way to rename files before publishing.
 * 08/15/2023:v00.00.05: 2.00 :Deployment; Adding trait so BaseController and LoginController can share same check function.
 * 08/14/2023:v00.00.05: 1.00 :Deployment; Still cleaning up and documenting deployment;
 * 08/13/2023:v00.00.05: 3.00 :Deployment; Playing with deployment; updating guide and process for new Laravel project.
 * 07/14/2023:v00.00.05: 3.00 :Issue with removing multiple users from User Dialog in Role Detail; refactor to build and use an array of users in removeUserFromRole().
 * 07/13/2023:v00.00.05: 2.75 :Still reworking the remove users from role eco-system. Renamed deletedUserRole to deleteRoleFromUser and added removeUserFromRole.
 * 07/12/2023:v00.00.05: 1.50 :Attempting to finalize role deletion; and working on Remove Users from role Users dialog.
 * 07/10/2023:v00.00.05: 1.50 :Add counter for User checkbox count in the Roles detail. Add Selected (select all/none).
 * 07/10/2023:v00.00.05: 1.00 :Fix issue with BaseController and ehCheckPermissions check for ehConfig::get('access.enabled') != true. Add redirect to logout on any Ajax login .fail.
 * 07/03/2023:v00.00.05: 3.00 :Implement a check in ehBaseController to see if permissions/access is enabled. Fix an infinite loop with the ehConfig initialization.
 * 06/22/2023:v00.00.05: 2.00 :Clean up css on users in role scrollable div. Start playing with checkbox idea to remove users.
 * 06/16/2023:v00.00.05: 3.00 :Build out ehRoles destroy.
 * 06/15/2023:v00.00.05: 3.00 :Build out ehRoles create/store and dataConsistency.
 * 06/13/2023:v00.00.05: 3.00 :Start working on permissions for ehLinkbar along with other behavior and functionality.
 * 06/12/2023:v00.00.05: 1.00 :Modify Layout@setStandardButtons() to accept a string or array to call specific buttons,
 * 06/08/2023:v00.00.05: 1.00 :Refactor ehAccess@chkUserResourceAccess() to use ehAccess@getUserRights() as the basis for the checks.
 * 06/08/2023:v00.00.05: 1.00 :Still testing access.allow_unauthenticated_children under various conditions.
 * 06/07/2023:v00.00.05: 4.00 :Working on adding access.allow_unauthenticated_children (and all the permissions and access ramifications of that)
 * 06/06/2023:v00.00.05: 2.00 :Testing Public vs Permissions check variation on users.
 * 06/05/2023:v00.00.05: 2.00 :Running down TODO's. Went down a wierd rabbit hole with both DB;;select() and model;;where() returning the wrong data! But next day -- all is good (??)
 * 06/02/2023:v00.00.05: 2.00 :Cleaning up the tech manual, ticking off TODO's, and tweaking behavior as I document it.
 * 06/01/2023:v00.00.05: 1.00 :Build out the config and logic for the ehLayout@setStandardButton() call.
 * 06/01/2023:v00.00.05: 1.00 :Rename ehSettings to ehConfig. Add .dropdown-submenu-no-flyout to navbar-multilevel.css for modules or submenus with no children.
 * 05/31/2023:v00.00.05: 2.00 :More work on flushing out settings create and OOTB defaults. Role in ability to get APP_LASTUPDATE and APP_VER specifically for this package.
 * 05/31/2023:v00.00.05: 2.00 :Refactor ehConfig to allow multi-level (dot syntax) key access.
 * 05/31/2023:v00.00.05: 2.00 :Working on datetime format display for all forms (attempting to switch to UTC default system or user defined w/timezone display)
 * 05/30/2023:v00.00.05: 2.00 :Cleaning up Page creation consistency checks and rules. Add when_adding to Layout.
 * 05/29/2023:v00.00.05: 2.00 :Cleaning up ehPagesController; adding deletion rules; fixing New (create) issues;
 * 05/28/2023:v00.00.05: 1.00 :Cleaning up ehMenus addDisplayClass() rules.
 * 05/26/2023:v00.00.05: 5.00 :Plain-ole reactive debugging; just trying to set various security levels and test user.
 * 05/25/2023:v00.00.05: 2.00 :More design changes; remove routes from access_tokens and all calls to it. Start paradigm of "normalizing" to objects for $role, $user, $page.
 * 05/25/2023:v00.00.05: 2.00 :Building out ehAccess - login and access checking permissions checking.
 * 05/24/2023:v00.00.05: 2.00 :Building out ehAccess - login and access checking permissions checking.
 * 05/22/2023:v00.00.05: 4.00 :Starting to organize responsibilities between check_permissions, ehUserFunctions and ehAccess.
 * 05/21/2023:v00.00.05: 3.00 :Working on ehAccess, ehRoles, ehSuerFunctions interactions.
 * 05/19/2023:v00.00.05: 2.00 :Cleaning; general technical documentation updates.
 * 05/18/2023:v00.00.05: 2.00 :Fix login redirect problem with modal. Ended up doing it in the Ajax form submit in the eh_login-modal template.
 * 05/17/2023:v00.00.05: 2.00 :Work on login redirect problem with modal.
 * 05/16/2023:v00.00.05: 2.00 :Work on login redirect problem with modal.
 * 05/16/2023:v00.00.05: 1.00 :Modify ehBaseController to have a $page->security check for auth middleware y/n.
 * 05/15/2023:v00.00.05: 2.00 :Fighting with default_role radio button top margin Safari to Chrome. Still cleaning up "Group" references and function names.
 * 05/14/2023:v00.00.05: 3.00 :ehUsersController consistency rules.
 * 05/13/2023:v00.00.05: 3.00 :Major refactor to eliminate anything 'group'. The overall paradigm is now based on 'roles' only.
 * 05/13/2023:v00.00.05: 3.00 :Still cleaning up deployment. Add Storage disk, 'disks.users' in register().
 * 05/12/2023:v00.00.05: 2.00 :Working on the main ehCheckPermissions logic.
 * 05/11/2023:v00.00.05: 2.00 :Continue work on ehCheckPermissions (finally).
 * 05/11/2023:v00.00.05: 3.00 :Yet another approach to extending the LoginController to add the eco-helper additional funcitons.
 * 05/10/2023:v00.00.05: 1.00 :Still working with deployment and getting authentication to work. Trying to fine tune the relationship between my overlay functions and the built-in Laravel ones.
 * 05/09/2023:v00.00.05: 1.00 :Move the functions from ehUser to ehUserFunctions Trait.
 * 05/09/2023:v00.00.05: 2.00 :Working out kinks with new project deployment. Identified memory exhausted error caused by write permissions on log file.
 * 05/08/2023:v00.00.05: 2.00 :Cleaning up deployment notes and deployment issues with publishing.
 * 05/03/2023:v00.00.05: 2.75 :Fighting issues with logging in. Looks like any kind of an error just logs you in (or not).
 * 05/03/2023:v00.00.05: 0.75 :Fix Login modal not focusing on first input in eh-login-modal template.
 * 05/02/2023:v00.00.05: 2.00 :Start flushing out the ehCheckPermissions middleware.
 * 05/01/2023:v00.00.05: 2.00 :Start roughing out the Menus-Pages front-end (js) rules. Started in the pages-detail template but will move out later.
 * 03/31/2023:v00.00.05: 2.00 :Hopefully getting close to finalizing the pages table and menu security interaction; adding fields for http methods.
 * 03/28/2023:v00.00.05: 2.00 :Progress on getting the auth hook for the additional checks into ehAuthenticatedSessionController.
 * 03/27/2023:v00.00.05: 2.00 :Studying up on gates and policies. This may be a major design turning point!!
 * 03/26/2023:v00.00.05: 2.00 :Starting to explore hooking into the Auth system. (?)
 * 03/25/2023:v00.00.05: 1.00 :Finally solved (and redesigned/refactored ehMenus. Moving on to the ripple effect clean-up (like the mess that happens when changing or removing roles).
 * 03/24/2023:v00.00.05: 5.00 :Still working on ehMenus in relationship to GroupsController grid. Mostly new design is completed now.
 * 03/23/2023:v00.00.05: 3.00 :Still working on ehMenus in relationship to GroupsController grid.
 * 03/22/2023:v00.00.05: 4.00 :Redesigning ehMenus class.
 * 03/21/2023:v00.00.05: 2.00 :Still struggling with sub-menus on GroupsController.
 * 03/20/2023:v00.00.05: 2.00 :Struggling with sub-menus on GroupsController rights grid! @getPageListByModule()
 * 03/19/2023:v00.00.05: 3.00 :Clean up; new ehConfig@get() usages; various errors all over the place. Clean up some of the TODOs.
 * 03/16/2023:v00.00.05: 2.00 :Success on 2nd attempt to move login controller (AuthenticatedSessionController) into package.
 * 03/15/2023:v00.00.05: 3.00 :Wasted side trip fixing "too many redirect". Somehow LoginRequest got added into the index() function on home page!!
 * 03/14/2023:v00.00.05: 3.00 :Cleaning up ehMenus along with ehAccess. Still working out final security model.
 * 03/13/2023:v00.00.05: 3.00 :Continue work on Access primary design. Start implementing pages save rules (consistency check).
 * 03/10/2023:v00.00.05: 3.00 :Continue work on ehExamples (detail).
 * 03/09/2023:v00.00.05: 3.00 :Start work on the ehExamples infrastructure.
 * 03/07/2023:v00.00.05: 3.00 :Continue work on login screens and js.
 * 03/06/2023:v00.00.05: 2.00 :Start flushing out the various authentication modals (login, register, forgot/change password).
 * 03/03/2023:v00.00.05: 6.00 :Notifications. (learned about 'web' middleware for ajax session (auth) management. Cleaned up js for Bootstrap 5 changes.
 * 03/02/2023:v00.00.05: 6.00 :Continue renaming (views). And trouble shooting. Start work on notifications.
 * 03/01/2023:v00.00.05: 4.00 :Misc clean up from the renaming; still fixing stuff. Fine tuning login. Added User Roles in navbar.
 * 03/01/2023:v00.00.05: 2.00 :Continue finding issues with renaming ("eh"). Continue renaming to js and views.
 * 02/28/2023:v00.00.05: 4.00 :Attempting to get a login modal working properly with ajax (including data validation and final redirect).
 * 02/28/2023:v00.00.05: 2.00 :Back to working on Access (now ehAccess).
 * 02/28/2023:v00.00.05: 3.00 :MAJOR renaming effort. Prefis all Controllers, Classes and Models with "eh" for clarity.
 * 02/27/2023:v00.00.05: 4.00 :Renaming re-working, publishable template to include base. Adding ehConfig class.
 * 02/25/2023:v00.00.05: 2.00 :User Profile password re-hash;
 * 02/24/2023:v00.00.05: 8.00 :Punch list items; unsaved, + Group auto submit, begin Access design changes.
 * 02/23/2023:v00.00.05: 4.00 :Fix Composer repository issues after Laravel 10 upgrade. Struggling with dates and $cast[] in Controls.
 * 02/22/2023:v00.00.05: 6.00 :More cleanup and finish up on User Profile.
 * 02/21/2023:v00.00.05: 6.00 :Flushing out the user-detail display for the UsersController@show(). Added routines for managing roles.
 * 02/14/2023:v00.00.05: 4.00 :Add user rights to Layout. Rework User model to extend ehUser.
 * 02/13/2023:v00.00.05: 2.00 :Start work on User Profile.
 * 02/11/2023:v00.00.05: 2.00 :Cleaning up deployment (testing on NasonStudios.com clean L9 install).
 * 02/04/2023:v00.00.05: 3.00 :Working out issues with the GET passed ?module_id and interactions with resource security (page_id vs route_name).
 * 02/03/2023:v00.00.05: 1.75 :Group Detail; module and group goto submit functions.
 * 02/02/2023:v00.00.05: 1.75 :Reworking Menus to deal with the new type='module' way of limiting to just modules.
 * 02/02/2023:v00.00.05: 3.00 :Redesign Access to use a more $page_id centric approach for saving and deleting.
 * 02/01/2023:v00.00.05: 5.00 :More redesign on Groups; moving back to using a "module" type flag.
 * 01/31/2023:v00.00.05: 5.00 :Redesigning the whole 'resource' route security check idea.
 * 01/29/2023:v00.00.05: 2.00 :Group Detail and Access; refactoring.
 * 01/27/2023:v00.00.05: 2.00 :Group Detail; styling and refactoring.
 * 01/26/2023:v00.00.05: 2.00 :Group Detail; styling. (had to add a whole bunch of garbage to Acces that will all have to be refactored big time!
 * 01/25/2023:v00.00.05: 6.00 :Group Detail; styling. Build update_table migration for users. Add a roles table and user sample data.
 * 01/23/2023:v00.00.05: 2.00 :Group Detail.
 * 01/21/2023:v00.00.05: 2.00 :Continue work on Group Detail.
 * 01/20/2023:v00.00.05: 4.00 :Add Groups and Access_Tokens tables w/sample data. Start flushing out Group List view.
 * 01/19/2023:v00.00.05: 4.00 :Refactor Menus System for table only use (remove everything that has to do with dealing with array menus).
 * 01/17/2023:v00.00.05: 4.00 :Rename Controls to Control. Add back TOP LEVEL menu link. Start laying out access framework,
 * 01/16/2023:v00.00.05: 3.00 :Rough out initial Linkbar class (no frills - auto module pull only).
 * 01/15/2023:v00.00.05: 5.00 :Mostly final clean-up on the Pages crud system; add consistencyRule(). Rename all date config constants. Add date_long to Controls.
 * 01/14/2023:v00.00.05: 5.00 :Renaming and cleaning up file names for clarity and consistency. Added array capability to Auto Loader.
 * 01/13/2023:v00.00.05: 6.00 :(started tracking time); working to complete the Menus / Pages CRUD system.
 * ------------------------------------------------------------------------------
 * 12/01/2022:v00.00.02:99.00 :Started seriously working on the 5th iteration of the project. Scope has grown over time to include a complete range of ecoHelper functionality.
 * 12/31/2021:v00.00.00:99.00 :Toying with idea. Testing and package setup.
 * 12/31/2020:v00.00.00:99.00 :Toying with idea. Testing and package setup.
 * 06/01/2019:v00.00.00:99.00 :This idea has been in the works since mid 2019 and worked on through several attempts for many hours. (no idea how many since it hasn't been tracked)
 */