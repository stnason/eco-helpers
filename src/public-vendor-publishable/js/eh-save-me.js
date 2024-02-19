///////////////////////////////////////////////////////////////////////////////////////////
// Intercept the Save button (duplicate prevention)
// This is intended to disable the save button while waiting on back-end server; form is posting so it will eventually redirect and redraw itself.
var save_button = $("#save");

if (save_button.length) {

    save_button.click(function() {

        save_button.replaceWith('<button class="btn btn-primary" type="button" disabled><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving</button>');
        save_button.prop("disabled",true);

        // If more than one form on a page then Firefox will log out on $("form").submit()
        // Both of these just "hang" on Firefox.
        $(".form-crud").submit();  // All CRUD forms should (?) be using this class.
        // document.forms[".form-crud"].submit();

    });
}