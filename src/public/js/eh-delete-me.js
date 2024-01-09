
/**
 * Standard "Delete" warning box
 *  - with form method change to match Laravel resourceful routing expectations.
 *
 *  Remember: This assumes the button's id is "delete".
 *  Remember: You can set the custom message in the calling template if you need to override this one;
 *      (var delete_me_message = "something here").
 *
 * @type {boolean}
 */

$('#delete').click(function () {

    var delete_me = false;

    if (typeof delete_me_message === 'undefined') {
        delete_me_message = 'Are you sure you want to permanently delete this record?';
    }

    delete_me = confirm(delete_me_message);
    if (delete_me) {
        return changeFormMethod("DELETE");
    }
    return false;
});

/**
 * Change the form method using JQuery
 * Remember: That back-side security checks are checked for each resource route action.
 *          (user will still need explicit rights assigned for this action)
 */
function changeFormMethod(new_method) {
    /*  Change the form in <input type="hidden" name="_method" value="PATCH"> */
    $('input[name="_method"]').val(new_method);
    return true;    /* just returning true here since it is the "Delete Me" confirmation. */
}
