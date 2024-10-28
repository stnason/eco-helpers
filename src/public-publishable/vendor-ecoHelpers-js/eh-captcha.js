/*
    Works with Eco-Helpers custom ehCaptcha class and the eh-captcha blade partial to protect form
    input and user interaction.

    Note: This is dependent on jQuery.
 */

var work_url = '/eh-captcha';

///////////////////////////////////////////////////////////////////////////////////////////
// Note: it's the responsibility of the calling form to set (and manage) validation_error (boolean).
if (typeof (validation_error) == 'undefined') {
    alert('Error 12: missing validation_error from form.');
}
///////////////////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////////////////
// If we don't have a validation error than call the captcha image refresh.
if (validation_error) {
    // If we have form validation errors then we must've passed the front-end portion of the captcha check
    // So hide the captcha section of the partial.
    $("#eh-captcha-partial").hide();
} else {
    // Hide the submit button on the initial refresh and disable the it too (so you can't hit enter to submit).
    // Remember that this is only a layer one security measure. The captcha is also checked on the back-side too.
    $("#submit-button").hide();
    $("#submit-button").attr("disabled", "disabled");
    fetchCaptcha();
}

///////////////////////////////////////////////////////////////////////////////////////////
// End-user refresh button click.
$("button#refresh-button").on('click', function (e) {
    fetchCaptcha();                 // Get a new captcha image.
    // Reset any previous error message.
    $("#captcha-status").text('');
});

///////////////////////////////////////////////////////////////////////////////////////////
// CAPTCHA INPUT FIELD
// Set the captcha image mouse-over to show a pointer (to help identify clickable behavior for end-user interaction).
$("#captcha-image").css('cursor', 'pointer');

// SUBMIT THE CAPTCHA FIELD FOR VALIDATION (front-end).
// Watch for a click on the user input field.
// This is the same as using the enter key -- 'click' or 'enter both work.
$("#captcha-image").on('click', function (e) {
    checkCaptcha($('#eh_captcha_input').val());
});
// Watch for an enter key press on the user input field.
// This is the same as clicking on the image -- 'click' or 'enter both work.
$("#eh_captcha_input").on('keypress',function(e) {
    if(e.which == 13) {
        checkCaptcha($('#eh_captcha_input').val());
    }
});


/**
 * Make a request to pull a captcha image.
 *
 */
function fetchCaptcha() {
    $.ajaxSetup({
        headers: {
            /* REMEMBER: This must be set in the calling page additional head area
                     <meta name="csrf-token" content="{{ csrf_token() }}">	*/
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: 'POST',
        url: work_url,
        success: function (response) {
            //$("#captcha-image").attr('src','data:image/jpeg;base64, '+response) -- ehCaptcha includes the "data:image..." part.
            $("#captcha-image").attr('src', response)
        },
        error: function (response) {
            console.log('fail: ' + JSON.stringify(response));
        }
    });
}

/**
 * validate the captcha field from the end-user input.
 *
 * @param captchaInput
 */
function checkCaptcha(captchaInput) {

    // In case the user presses the box before entering anything.
    if (captchaInput === "") {
        captchaInput = "x";
    }

    $.ajaxSetup({
        headers: {
            /* REMEMBER: This must be set in the calling page additional head area
                     <meta name="csrf-token" content="{{ csrf_token() }}">	*/
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: 'POST',
        url: work_url,
        datatype: "json",
        data: {
            eh_captcha_input: captchaInput
        },
        success: function (response) {
            //alert(JSON.stringify(response));
            if (response.status === true) {
                // The captcha entered by the user was checked and matches.
                // alert('looks good so ready to submit the form.')
                // $("form").submit();  // Don't submit the form, just hide the captcha box
                $("#eh-captcha-partial").hide();
                // And reset any previous error message.
                $("#captcha-status").text('');
                // Enable the submit button for the end-user.
                $("#submit-button").show();
                $("#submit-button").removeAttr('disabled');
            } else {
                // The captcha entered by the user does not match.
                $("#captcha-status").text(response.status);
                // And wipe out the user input.
                $('#eh_captcha_input').val('');
            }
        },
        error: function (response) {
            console.log('fail: ' + JSON.stringify(response));
        }
    });
}


