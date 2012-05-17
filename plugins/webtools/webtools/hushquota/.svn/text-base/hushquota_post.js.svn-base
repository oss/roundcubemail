rcmail.addEventListener('plugin.hushquota-post-callback', hushquota_post_callback);

function hushquota_post(disable) {
    
    var data = '';

    if (disable) {
        data = 'disable=1';
    }
        
    rcmail.http_post('plugin.webtoolshushquota-post', data);

    return true;
}
        
function hushquota_post_callback(response) {
    
    $('#result').html(response.message);
    
    if (response.update_content === 'yes') {
        
        $('#button').html(response.new_button);
        
        $('.webtools-button').hover(
            function() { $(this).addClass('ui-state-hover'); },
            function() { $(this).removeClass('ui-state-hover'); }
        );

        $('#text').html(response.new_text);

    }
}
