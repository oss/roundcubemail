function forward_add ()
{
	if (validateEmail())
	{
		var email = $('#email').attr('value');
		var state = $('#localcopy-check').attr('checked');
		rcmail.http_post('plugin.webtoolsforward-post', { 'func':'add', 'value':email, 'check':state });
	}
	else
	{
		$.pnotify({
			pnotify_title: 'Invalid',
			pnotify_text: 'You must enter a valid email address.',
			pnotify_type: 'error',
			pnotify_animation: 'slide',
			pnotify_height: '100px'

		});
	}
}

function forward_remove_all ()
{
	rcmail.http_post('plugin.webtoolsforward-post', { 'func':'delall' });
}

function forward_remove (email)
{
	rcmail.http_post('plugin.webtoolsforward-post', { 'func':'del', 'value':email });
}

function forward_chtype ()
{
	var state = $('#localcopy-check').attr('checked');
	rcmail.http_post('plugin.webtoolsforward-post', { 'func':'chtype', 'value':state });
}

function delall_dialog()
{
	$('#dialog-test').dialog('open');
}

function forward_post_callback (data)
{
	if (data.func == 'add')
	{
		if (data.alreadyexists == 'true')
		{
			//var stack_bottomright = {"dir1":"up", "dir2":"left", "firstpos1":15, "firstpos2":15};
			$.pnotify({
				pnotify_title: 'Failed',
				pnotify_text: 'You\'re already forwarding to that address!',
				pnotify_type: 'error',
				pnotify_animation: 'slide',
				pnotify_height: '100px'
			});
			return;
		}
		if (data.youremail == 'true')
		{
			$.pnotify({
				pnotify_title: 'Failed',
				pnotify_text: 'You can\'t forward to your own address!',
				pnotify_type: 'error',
				pnotify_animation: 'slide',
				pnotify_height: '100px'
			});
			return;
		}

		if (data.no_mxr == 'true')
		{   
			$.pnotify({
				pnotify_title: 'Failed',
				pnotify_text: 'Mail server doesn\'t seem to exist!',
				pnotify_type: 'error',
				pnotify_animation: 'slide',
				pnotify_height: '100px'
			});
			return;
		}

		var rowstyle;
		var last = $('#localcopy-row');
		if (last.prev().attr('class') == "rowstyle0") rowstyle = 'rowstyle1';
		else rowstyle = 'rowstyle0';
		last.before("<tr style='display:none' class=\""+rowstyle+"\" id=\""+data.id+"\"><td>" +
				"<a href=\"#\" onclick=\"forward_remove('"+data.value+"')\">" +
				"<img src=\"plugins/webtools/webtools/forward/delete.png\" />" +
				"</a></td><td><p class=\"emailstyle\">"+data.value+"</p></td></tr>");
		$('#'+data.id).fadeIn();
		$('#email').attr('value','');
		num_forwards++;
	}
	else if (data.func =='del')
	{
		$('#'+data.id).fadeOut();
		num_forwards--;
	}
	else if (data.func == 'chtype')
	{
		$.pnotify({
			pnotify_title: 'Success',
			pnotify_notice_icon: 'ui-icon ui-icon-check',
			pnotify_text: 'Setting saved!',
			//        pnotify_type: 'success',
			pnotify_animation: 'slide',
			pnotify_height: '100px'
		});
	}
	else if (data.func == 'delall')
	{
		$('#forward-table tr').each( function(index) {
			if ((this.getAttribute('class') == 'rowstyle0') || (this.getAttribute('class') == 'rowstyle1')) {
				$(this).fadeOut();
			}
		});
		$.pnotify({
			pnotify_title: 'Success',
			pnotify_notice_icon: 'ui-icon ui-icon-check',
			pnotify_text: 'Your forward(s) have been removed successfully.',
			//      pnotify_type: 'success',
			pnotify_animation: 'slide',
			pnotify_height: '100px'
		});
		num_forwards = 0;
	}
	if (num_forwards == 0)
	{
		$('#removeall').hide();
		$('label[for=localcopy-check],#localcopy-check').hide();
		//$('#localcopy-check').attr('disabled', true);
		$('#localcopy-check').attr('checked', false);
	}
	else
	{
		$('#removeall').show();
		$('label[for=localcopy-check],#localcopy-check').show();
		//$('#localcopy-check').removeAttr('disabled');
	}
}

function validateEmail(){
	var email = $('#email');
	var a = email.val();
	var filter = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(?:[a-zA-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)$/;
	if(filter.test(a)) {
		email.removeClass('ui-state-error');
		return true;
	} else {
		email.addClass('ui-state-error');
		return false;
	}
}

rcmail.addEventListener ('plugin.forward-post-callback', forward_post_callback);
$(document).ready( function() {
	$("#email").bind("keyup",function(e) {
		validateEmail();
		var key = e.keyCode || e.which;
		if (key == 13) {
			forward_add();
		}
		});

	$("#email").change(validateEmail);

	$('#dialog-test').dialog (
	{  
		modal:true, 
		resizable:false, 
		autoOpen:false,
		buttons: 
		{   
			'Delete All' : function()
			{
				forward_remove_all ();
				$(this).dialog('close');
			},  
			'Cancel':function()
			{  
				$(this).dialog('close'); 
			}   
		}   
	}); 
});

