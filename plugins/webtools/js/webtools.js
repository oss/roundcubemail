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
	var myUserAgent = navigator.userAgent; 
	if (myUserAgent.indexOf("MSIE") !== -1) {
		// IE6 background flicker fix
		try	{
			document.execCommand('BackgroundImageCache', false, true);
		} catch (e) {}

		if (!document.namespaces["v"]) {
			$("head").prepend("<xml:namespace ns='urn:schemas-microsoft-com:vml' prefix='v' />");
			$("head").prepend("<?import namespace='v' implementation='#default#VML' ?>");
		}
	}
   //making it jquery-like
   //var browser = BrowserDetect.browser;
   //var version = BrowserDetect.version;
   //if (!((browser=="Explorer") && (version==7)))
	
	
/*
    if (!(($.browser.msie) && ($.browser.version))) 
      $('.webtools-accordion').accordion( { header: 'h3', collapsible: 'true', active: 'false' } );
*/
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
