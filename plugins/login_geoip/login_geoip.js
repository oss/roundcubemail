function make_popup (msg)
{
   // run the notification
   $.pnotify ({
      pnotify_title: 'Login Activity',
      pnotify_text: msg,
      pnotify_type: 'error',
      pnotify_hide: false,
      pnotify_stack:pstack_toppush
   });
}

if(rcmail.env.task == 'mail'){
	$(window).resize( function resize() {
		var mbox=$("#mailboxcontainer");
		$("#login_time").css("top", (mbox.position().top + mbox.height() + 10)+"px");
	});
}
