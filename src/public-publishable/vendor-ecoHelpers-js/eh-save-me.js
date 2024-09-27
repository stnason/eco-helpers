///////////////////////////////////////////////////////////////////////////////////////////
// Intercept the Save button for duplicate prevention.
// This is intended to disable the save button while waiting on back-end server;
//  form is posting so it will eventually redirect and should redraw itself.
var save_button = $("#save");

if (save_button.length) {

    save_button.click(function() {

        save_button.replaceWith('<button class="btn btn-primary" type="button" disabled><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving</button>');
        save_button.prop("disabled",true);

        // If more than one form on a page then Firefox will log out on $("form").submit()
        // Both of these just "hang" on Firefox.
        //$(".eh-form-crud").submit();          // Internal eco-forms are now namespaced with the "eh-".
        //$("[class$='form-crud']").submit();     // Allowing user forms to just use form-crud

        // 09/27/2024
        $("[class$='form-crud'], form[class^='eh-form-crud']").submit();     // Allowing user forms to just use form-crud

        // document.forms[".eh-form-crud"].submit();

    });
}