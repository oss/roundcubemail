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

$(window).resize( function resize() {
    var mbox=$("#mailboxcontainer");
    var login=$("#login_time");
    try{
        // note: #login_time's bottom attribute set to 0px in .css file
        mbox.css("bottom", (login.height()+20)+"px");
    }catch(e) {}
});
