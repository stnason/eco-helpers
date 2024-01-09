/**
 * UserNotification
 * functions and Ajax routines.
 *
 */

    //TODO: Explore the idea of stacking multiple notifications in the message_modal - each with it's own 'x' button if user clearable.
    // Need to implement the route viewable flag (if auto popup; should it do it on all or just one page?)
    // Need to implement "is user clearable".

//var message_modal = $("#message-modal");  // I think this is new for Bootstrap 5.x. This no longer works directly but must be instantiated like this:
// Activate the modal so js can talk to it.
const message_modal = new bootstrap.Modal('#message-modal', {
        keyboard: false
    })

var message_modal_title = $("#message-modal-title");
var message_modal_content = $("#message-modal-content");
var message_modal_delete = $("#message-modal-delete");
var user_notification = $("#user-notification");
var title_bar_badge = $("#title-bar-badge");

var async = false;      // false waits but is deprecated; true moves on but doesn't work for my application.

$.ajaxSetup({
    headers: {
        /* REMEMBER: This must be set in the calling page additional head area
                 <meta name="csrf-token" content="{{ csrf_token() }}">	*/
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});


/**
 * Get the next notification in line for this user.
 *
 * @returns {string}
 */
function getNext() {

    var notification = "";
    /* TODO: We were getting a 401 Unauthorized message. Is that fixed for all users--before and after login?? */
    $.ajax({
        type: "POST",
        url: notification_url + "/get-next",
        async: async,                       // false waits but is deprecated; true moves on but doesn't work for my application.
        // async: true,                     // Experimenting with trying to get rid of the Firefox Synchronous XMLHttpRequest message
        cache: false,
        dataType: "json",
        success: function(data){
            notification = data;
        }
    });

    return notification;
}

function getTotal() {
    var notification = "";
    $.ajax({
        type: "POST",
        url: notification_url + "/get-total",
        async: async,                       // false waits but is deprecated; true moves on but doesn't work for my application.
        //async: true,
        cache: false,
        dataType: "json",
        success: function(data){
            notification = data;
        }
    });
    return notification;
}

function deleteNext() {

    var notification = '';

    $.ajax({
        type: "POST",
        url: notification_url + "/delete-next",
        async: async,                       // false waits but is deprecated; true moves on but doesn't work for my application.
        //async: true,
        cache: false,
        dataType: "json",
        success: function(data){
            notification = data;
            var tmp = getTotal();
            title_bar_badge.text(tmp);      // Refresh the title bar badge number. (how many notifications)
            if (tmp === 0 || tmp === "0") {
                user_notification.hide();   // If the notifications are down to zero then hide the Notification link in the title bar.
            }
        }
    });



    /*
    $.ajax({
        type: "POST",
        url: notification_url + "/delete-next",
        async: async,                       // false waits but is deprecated; true moves on but doesn't work for my application.
        //async: true,
        cache: false,
        dataType: "json",
    }).fail(function (data) {
        notification = data;
    }).done(function (data) {
        notification = data;

        var tmp = getTotal();
        title_bar_badge.text(tmp);      // Refresh the title bar badge number. (how many notifications)
        if (tmp === 0 || tmp === "0") {
            user_notification.hide();   // If the notifications are down to zero then hide the Notification link in the title bar.
        }
    });
     */

    return notification;
}


////////////////////////////////////////////////////////////////////////////////////////////
// Add a Notification click handler.
user_notification.on("click", function(e) {

    // Get the next notification in line and display it.
    var notification = getNext();

    message_modal_title.html(notification.title);
    message_modal_content.html(notification.content);
    message_modal.show();
    message_modal_delete.show();

});


////////////////////////////////////////////////////////////////////////////////////////////
// Add a Delete button click handler.
message_modal_delete.on("click", function(e) {
    deleteNext();
});


////////////////////////////////////////////////////////////////////////////////////////////
/* Execute on each page refresh. */
var next_notification = getNext();

////////////////////////////////////////////////////////////////////////////////////////////
// Display any pending--next in line--auto popup messages.
/* !!! WARNING !!!:
       Need to ensure various browsers see either a number or a string as 1.
       */

// Is there a next notification?
if (next_notification !== null) {
    if (next_notification.auto_popup === 1 || next_notification.auto_popup === "1") {

        message_modal_title.html(next_notification.title);
        message_modal_content.html(next_notification.content);

        if (next_notification.auto_clear === 1 || next_notification.auto_clear === "1") {

            message_modal_delete.hide();
            deleteNext();
        }

        //message_modal.modal("show");
        message_modal.show();
    }
}



/*
    ////////////////////////////////////////////////////////////////////////////////////////////
    // When the modal is shown do this.
    message_modal.on('shown.bs.modal', function (e) {

    });

    // When the modal is hidden do this.
    message_modal.on('hide.bs.modal', function (e) {

    });


    ////////////////////////////////////////////////////////////////////////////////////////////
    // Generic ajax outline
    //
    //  {{-- Laravel CSRF mechanism. This is needed for the ajax call to match the right token. --}}
    // $.ajaxSetup({
    //     headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    // });
    //
    // $.ajax({
    //
    // }).fail(function (data) {
    //
    // }).done(function (data) {
    //
    // });

*/


