rcmail.addEventListener('plugin.restore-post-callback', restore_post_callback);

function restore_post(restore) {

    var data;

    if (!restore) {
        data = $('#date_form').serialize();
        var datestring = $('#pathselect').find(':selected').text();
        data += '&date=' + encodeURIComponent(datestring);
        $('#date_form').hide();
        $('#folders-select').html('<span style="color: #BD0E2E; font-weight: bold;">Please wait...</span>');
    }
    else {
        data = $('#folder_form').serialize();
		$('#loading').show();
    }
        
    rcmail.http_post('plugin.webtoolsrestore-post', data);

    return true;
}

function show_dates() {
    $('#date_form').show();
    $('#folders-select').html('');
}
        
function restore_post_callback(response) {

    $('#result').html(response.message);
    $('#loading').hide();
    if (response.update_folders === 'yes') {

        $('#folders-select').html(response.folder_select);
        
        $('.webtools-button').hover(
            function() { $(this).addClass('ui-state-hover'); },
            function() { $(this).removeClass('ui-state-hover'); }
        );

        $('#foldermenu').selectmenu({style:'dropdown'});

    }
}
