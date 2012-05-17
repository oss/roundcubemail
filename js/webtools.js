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
