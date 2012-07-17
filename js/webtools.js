$(function() {
    $('.webtools-button').hover(
        function() { $(this).addClass('ui-state-hover'); }, 
        function() { $(this).removeClass('ui-state-hover'); }
    );
});

$(function() {
    $('.webtools-checkbox').checkbox();
});

$(function() {
    $('.webtools-select').selectmenu( { style: 'dropdown' } );
});

$(function() {
   //making it jquery-like
   //var browser = BrowserDetect.browser;
   //var version = BrowserDetect.version;
   //if (!((browser=="Explorer") && (version==7)))
   if (!(($.browser.msie) && ($.browser.version))) 
     $('.webtools-accordion').accordion( { header: 'h3', collapsible: 'true', active: 'false' } );
});

rcmail.addEventListener('plugin.webtools.vacation.notify.response', 'vacation_notify');

function vacation_notify(data) {
	console.log("got here");
	if(data.status) {
		$.pnotify({
			pnotify_title: "Alert",
			pnotify_text: "Your vacation message is currently enabled",
			pnotify_type: "error",
			pnotify_animation: "slide",
			pnotify_height: "100px"
		});
	}
}
