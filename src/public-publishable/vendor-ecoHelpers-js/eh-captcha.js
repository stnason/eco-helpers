/*
    Works with Eco-Helpers custom ehCaptcha class and the eh-captcha blade partial to protect form
    input and user interaction.

    Note: This is dependent on jQuery, so the eh-captcha-partial template
          will check that and load it if needed.
 */

var work_url = '/eh-captcha';

///////////////////////////////////////////////////////////////////////////////////////////
$("#submit-button").hide();
fetchCaptcha();

$("button#refresh-button").on('click', function (e) {
    fetchCaptcha();                 // get a new captcha image.
    //$("#submit-button").hide();
});

$("#captcha-image").on('click', function (e) {
    checkCaptcha($('#eh-captcha-input').val());
});



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
                alert('looks good so ready to submit the form.')
                $("form").sumbit();
            } else {
                // The captcha entered by the user does not match.
                $("#captcha-status").text(response.status);
            }

        },
        error: function (response) {
            console.log('fail: ' + JSON.stringify(response));
        }
    });
}


