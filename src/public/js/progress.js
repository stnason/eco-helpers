/*///////////////////////////////////////////////////////////////////////////////////////////

Standard Progress system (Eco 3.0L)

Interacts with these handles:
    var P_title = $('#standard-progress-title');
    var P_percent = $('#standard-progress-percent');
    var P_message = $('#standard-progress-message');
    var P_control = $('#standard-progress-control-class');
*/


/* Default polling time is set here -- Note: That it can be overridden from the Controller. */
if(typeof polling_milliseconds === 'undefined') {
    var polling_milliseconds = 750;
}


///////////////////////////////////////////////////////////////////////////////////////////
/**
 * When you open the progress modal, kick off the polling loop.
 */
P_modal.on("show.bs.modal", function () {

    // Kick off the loop that reads the progress file and update the modal data (from progress.js).
    progressLoop(progress_file_url, polling_milliseconds);

});

/**
 * After closing the standard progress modal,
 * then refresh to get the new file list and flash message.
 */
P_modal.on("hide.bs.modal", function () {

    window.location.reload();

});


///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Polling loop timer to control how long the polling interval should be.
 * var polling_milliseconds is defaulted to 200 milliseconds but can be set
 * in the calling Controller too.
 *
 * @param progress_file_url
 * @param polling_milliseconds
 */
var polling_timer;
function progressLoop(progress_file_url, polling_milliseconds) {

    polling_timer = setInterval(function(){updateProgress(progress_file_url);}, polling_milliseconds);

    /*
    // Not sure what advantage this has over the simple version above. (?)
    var deferred = $.Deferred();
    // Refresh the progress modal status every polling_milliseconds milliseconds.
    polling_timer = window.setInterval(function () {
            updateProgress(progress_file_url);
            deferred.resolve();
        }, polling_milliseconds
    );
    return deferred.promise();
     */

}



///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Called by the progressLoop at its defined polling_milliseconds;
 *  - Makes an ajax call to the server progress url and reads the progress_file_name's contents
 *  - Decodes the output (see ProgressController for json message structure) and
 *  - Updates the handles contained in the standard progress modal;  @include('standard_progress_modal');
 *
 * @param progress_file_url
 *
 */
function updateProgress(progress_file_url) {

    // Read the contents of the server progress file.
    $.ajax({
        url: progress_file_url,
        success: function (data){

            // Get the data read from the progress file.
            var d = JSON.parse(data);

            //console.log(JSON.stringify(d));

            //alert(JSON.stringify(d));
            //console.log('stop_polling: '+d.stop_polling+' - percent: '+d.percent);

            // Update the progress modal with the information retrieved from the progress file.
            P_title.text(d.title);
            P_percent.html(d.percent+'%');
            P_message.html(d.message);
            P_control.removeClass().addClass('c100 p'+d.percent+' big orange');


            // Check to see if we should stop the loop timer.
            if (d.stop_polling || d.percent >= 100) {


                // Remove the polling loop timer.
                //clearTimeout(polling_timer);
                clearInterval(polling_timer);

            }




        }
    });
}



