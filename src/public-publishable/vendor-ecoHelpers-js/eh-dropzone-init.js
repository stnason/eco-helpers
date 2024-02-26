// Create a handle for the system flash message.
var flash_msg = $('#system-page-flash-message');

// Seems like auto-discovery keeps you from specifying any options below.
Dropzone.autoDiscover = false;

/**
 * Use the upload() controller to pull a list of files in the intended directory first.
 *  Note: this will happen after the upload from the Dropzone -- so we call it initially to populate the list first.
 *
    File Object (file) in Dropzone:
        file.
            upload.
                uuid
                progress
                total
                bytesSent
                filename
            status
            previewElement
            previewTemplate
            accepted
            processing
            xhf
 *
 * @param storage_disk
 * @returns {[]}
 */
function pullFileList(storage_disk) {

    var output = [];

    // CSRF token setup for the ajax call
    $.ajaxSetup({
        headers: {
            /* REMEMBER: This must be set in the calling page additional head area
                     <meta name="csrf-token" content="{{ csrf_token() }}">	*/
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Ajax work-file to pull the file_list object before executing the actual file upload.
    $.ajax({

        type: "POST",
        async: false,           // Need to wait for a response before returning it.
        url: upload_url,
        data: {'file_list_only': true, 'storage_disk': storage_disk} // Do need the storage_path passed back here.

    }).done(function (response) {

        response = JSON.parse(response);
        output = response.file_list;

    });

    return output;
}

/**
 * Display the list of files from the pullFileList() function above.
 *
 * @param file_list
 * @param display_div_id
 */
function displayFileList(file_list, display_div_id) {
    var file_list_div = $(display_div_id);
    var file_list_output = '<ul>';
    var checkbox = '';
    $.each(file_list, function (key, value) {

        checkbox = '<input type="checkbox" id="frm_file_' + key + '" name="frm_file[]" value="' + value + '">';
        //file_list_output = file_list_output + '<li class="file-list">' + checkbox + ' - ' + key + ": " + value + '</li>';
        file_list_output = file_list_output + '<li class="file-list">' + checkbox + ' - ' + value + '</li>';

    });
    if (file_list_output == '<ul>') {file_list_output = file_list_output + '<li class="file-list">No files found in upload folder.</li>';}
    file_list_output = file_list_output + '</ul>';
    file_list_div.html(file_list_output);
}


/**
 * Pull a list of files in the intended directory and then display them in our div.
 * Note: the Controller should be setting the $form['storage_disk'] variable as appropriate.
 */
displayFileList(
    pullFileList(storage_disk),
    display_div_id
);

/**
 * Check the comma separated valid extension list for periods and add them as needed
 * DZ needs the periods, the backside processing does not so we can use the same list from the Controller setup.
 *
 * @param valid_extensions
 * @returns {string}
 */
function normalizeValidExtensionArray(valid_extensions) {
    var new_valid_extnesions = '';
    var tmp_array = valid_extensions.split(',');

    // Loop the extensions and see if it has a period in front of it (DZ needs it; backside processing does not)
    $.each(tmp_array, function (key, value) {

        if (value[0] != '.') {
            // Note: trim() function added to deal with spaces in incoming data: (csv, xlsx)
            new_valid_extnesions = new_valid_extnesions + '.'+value.trim()+','
        } else {
            new_valid_extnesions = new_valid_extnesions + value.trim()+','
        }

    });

    // Remove the trailing comma.
    var len = new_valid_extnesions.length;
    if (new_valid_extnesions.substr(len - 1,1) == ",") {
        new_valid_extnesions = new_valid_extnesions.substring(0,len-1);
    }
    return new_valid_extnesions;
}




/**
 * Initialize the Dropzone object.
 */
function ehDropzoneInit() {


    // Normalize the valid file extensions array so that we can use the same one for front and backside processing (i.e. no periods)
    var new_valid_extensions = normalizeValidExtensionArray(valid_extensions);


    // Dropzone.options.myDropzone = {
    // Seems like if auto-discovery is turned off -- you have to instantiate your own object. (?)
    var myDropzone = new Dropzone("#myDropzone", {

        paramName: "file",  // The name that will be used to transfer the file
        maxFilesize: 64,    // MB
        timeout: 360000,    // Default ms timeout is 30,000 (30 seconds)
        acceptedFiles: new_valid_extensions,
        createImageThumbnails: false,
        url: upload_url,

        accept: function (file, done) {

            done();

            // Entering something in done('xxx') sends this to the error: section below. (?)
            //if (file.name != "justinbieber.jpg") {
            //    done("Naha, you don't.");
            //}
            //else { done(); }

        },

        success: function (file, response) {

            // Turn the response object into a usable json object.
            var r = JSON.parse(response);

            // The returned "response" is broken into
            //  ['message']     - for the operation's html status message.
            //  ['data']        - for the specific fields that were changed during the upload.
            //  ['file_list']   - a list of files in the intended directory.

            if (file.accepted) {
                // This updates and additional (larger) progress bar if in the template file.
                $('#progress-bar').attr('aria-valuenow', 100).css('width', 100 + '%').text(100 + '%');
                //$('#ajax-js-message').html('(JS) File <strong>' + file.upload.filename + '</strong> uploaded successfully.');
            } else {
                //$('#ajax-js-message').html('');
                // Replace the system flash area with this error message from this script file.
                flash_msg.html('Client error: There was a problem attempting the upload.');
            }

            // The response back from the server.
            if (response) {

                //$('#ajax-server-message').html(r.message);
                // Replace the system flash area with the message returned from the server.
                flash_msg.html(r.message);

                // Display the returned list of files in this storage disk.
                // alert(JSON.stringify(r.file_list));
                displayFileList(r.file_list, display_div_id);

                // Do you need to Update (re-display) any of the form fields with any field changed during the upload process.
                // $file_name
                // $file_extnesion
                // $updated_at
                // $updated_by

            }

        },
        // Added this section in order to include the storage_disk in the posted data.
        sending: function(file, xhr, formData) {
            formData.append("storage_disk", storage_disk);
        },
        error: function (file, response) {

            // Note: response will contain either the the Dropzone message for invalid file types (in the root of response).
            //  - or a message returned from the upload() method on the back-end (in response.message).
            if (typeof response.message === 'undefined') {
                var msg = response;
            } else {
                var msg = response.message;
            }
            flash_msg.html('There was a problem uploading <strong>' + file.name + '</strong>. (' + msg + ')');
        }
    });


    // File upload progress bar.
    myDropzone.on("totaluploadprogress", function (progress) {
        var p = Math.round(progress);
        $('#progress-bar').attr('aria-valuenow', p).css('width', p + '%').text(p + '%');
    });


}

