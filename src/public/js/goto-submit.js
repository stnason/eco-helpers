
/**
 * The "goto" submit handler.
 * Currently this uses a redirect to issue a GET to the new page
 * Use: 'auto_submit'=>"gotoSubmit(true)", - in the Control to force the POST
 *
 * Note: The calling page must setup the global intended goto url -> var goto_url = 'form/route'
 *
 *
 * @param post
 */
function gotoSubmit(post) {

    if (typeof post === "undefined") {
        post = false;       // If not specified, then default to a GET request.
    }


    // Determine the #id of the element that triggered this request and get its value.
    // This should be the onchange="goto_submit()" attribute of the drop-down select menu for this page.

    var target = event.target || event.srcElement;
    var triggered_by = target.id;
    var id = $('#'+triggered_by).val();


    // 6/15/2022; enhancement:
    // With a JQuery selector handle for the form, we could submit the form too if we needed a POST
    // form.attr('action',goto_url+'/'+id).submit();  // This could be used to POST the form.
    if (post) {
        // Leave off the id when submitting -- all variables should just be POSTed.
        $("form").attr("action", goto_url).submit();    // This could be used to POST the form.
    } else {
        // Redirect does a GET, so it doesn't post data. (Request is empty)
        $(location).attr('href',goto_url+'/'+id);
    }



}
