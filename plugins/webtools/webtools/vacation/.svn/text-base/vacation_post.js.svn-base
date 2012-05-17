rcmail.addEventListener('plugin.vacation-post-callback', vacation_post_callback);

function vacation_post(disable_vacation) {
    
    var data = $('#webtools-form').serialize();

    if (disable_vacation) {
        data += '&disable=1';
    }

    rcmail.http_post('plugin.webtoolsvacation-post', data);

    return true;
}
        
function vacation_post_callback(response) {
    if (response.update_content === 'yes') {
        $('#dynamic_content').html(response.new_content);
        $('#vacation_msg').html(response.new_vacmsg);
    }
    $('#result').fadeOut();
    $('#result').html(response.message);
    $('#result').fadeIn();
}
