function fixTHEAD(){
  // globals
  this.width = new Array();
  this.fontsize = '11px';
  
  // debug
  this.dump = function (obj){
    var out = '';
    for (var i in obj){
      out += i + ": " + obj[i] + "\n";
    }
    alert(out);
    // or, if you wanted to avoid alerts...
    /*var pre = document.createElement('pre');
    pre.innerHTML = out;
    document.body.appendChild(pre)*/
  }
  
  // restore
  this.restore = function(){
    $('#messagelist thead').css('position','');
    $('#messagelist').css('table-layout','fixed');
    $('#messagelist tbody td.subject').css('width','99%');
    $('#messagelist_empty_row').remove();
    $('#messagelist thead tr td').each(function(){
      var elem = $(this);
      elem.attr('style','');
    });
    $('#messagelist tbody tr td').each(function(){
      var elem = $(this);
      elem.attr('style','');
    });
  }
  
  // subject title
  this.long_subject_title = function(elem, indent){
    if(elem.title){
      elem.title = '';
    }
    else{
      var $elem = $(elem);
      $('#messagelist_empty_row td').html($elem.text());
      if ($('#messagelist_empty_row td').width() + indent * 15 > $('#messagelist tbody tr td.subject').width()){
        elem.title = $elem.text();
      }
      $('#messagelist_empty_row td').html('');
    }
  }
  
  // main function
  this.adjust = function(trigger){
    var last_row = 0;
    // empty folder?
    try{
      last_row = $('#messagelist > tbody > tr:last').position().top;
    }
    catch(e){
    }
    // restore css
    fixTHEAD.restore();
    // check if messagelist has vertical scrollbar
    if(last_row <= ($('#messagelistcontainer').height() - $('#messagelist > tbody > tr:first').height())){
      return;
    }
    // window has vertical scrollbars?
    var docHeight = $(document).height();
    var scroll    = $(window).height() + $(window).scrollTop();
    if (!(docHeight == scroll)){
      return;
    }
    // add empty row
    $('<tr id="messagelist_empty_row" height="' + $('#messagelist thead').height() + '"><td style="border-bottom:0px;">&nbsp;</td></tr>').insertBefore('#messagelist > tbody > tr:first');
    // read default column widths
    var elem, temp, cname, selector;
    $('#messagelist thead tr td').each(function(){
      elem = $(this);
      temp = elem.attr('class');
      temp = temp.split(' ');
      cname = temp[0];
      fixTHEAD.width[cname] = elem.width();
      if(cname == 'from')
        fixTHEAD.width['to'] = elem.width();
      if(cname == 'to')
        fixTHEAD.width['from'] = elem.width();
    });
    $('#messagelist thead tr td').each(function(){
      elem = $(this);
      temp = elem.attr('class');
      if(temp){
        temp = temp.split(' ');
        cname = temp[0];
        elem.attr('style','width:'+fixTHEAD.width[cname]+'px;');
      }
    });
    // now fix thead and adjust column widths
    $('#messagelist thead').css('position','fixed');
    // adjust width of tbody fields
    $('#messagelist tbody tr td').each(function(){
      elem = $(this);
      temp = elem.attr('class');
      if(temp){
        temp = temp.split(' ');
        cname = temp[0];
        if(fixTHEAD.width[cname]){
          elem.attr('style','width:'+fixTHEAD.width[cname]+'px;');
        }
      }
    });
    for(var i in this.width){
      if(i == 'subject'){
        selector = '.' + i + ' a';
      }
      else{
        selector = '.' + i;
      }
      $('#messagelist tbody tr td' + selector).each(function(){
        elem = $(this);
        if(i == 'subject'){
          var depth = elem.attr('onmouseover');
          if(depth){
            depth = depth.split(',');
            depth = depth[1].substr(0,1);
            elem.attr('onmouseover', 'fixTHEAD.long_subject_title(this.firstChild,' + depth + ')');
          }
        }
        temp = elem.attr('class');
        if(temp){
          temp = temp.split(' ');
          cname = temp[0];
        }
        else{
          cname = 'subject';
        }
        // inject html to cut text overflow
        if(elem.children().hasClass("fixTHEAD") == false &&
            (cname == 'subject' ||
             cname == 'from' ||
             cname == 'to' ||
             cname == 'replyto' ||
             cname == 'cc' ||
             cname == 'size' ||
             cname == 'date')
        ){
           if(cname == 'subject'){
              elem.html(
                    '<span>'+elem.html()+'</span>'
                    );
           }else{
              elem.html(
                    '<span style="padding-left:5px">'+elem.html()+'</span>'
                    );
           }
        }
        else{
          elem.width(fixTHEAD.width[cname]);
        }
      });
    }
    // adjust css
    if($('#messagelist').css('table-layout') == 'fixed'){
      $('#messagelist tbody tr td.subject a').css('display','inline-block');
      $('#messagelist').css('table-layout','auto');
    }
  }
}

var fixTHEAD = new fixTHEAD();
  
$(document).ready(function(){
  rcmail.addEventListener('listupdate', function(evt) {
    // add border-spacing style for IE to avoid unwanted spaces in fixed thead
    $('#messagelist').attr('style', 'border-spacing:0');
    
    // set font size
    fixTHEAD.fontsize = $('#messagelist tbody tr td').css('font-size');
    
    // initial messagelist table adjustments
    fixTHEAD.adjust('listupdate');

    // adjust if a new message arrives
    rcmail.addEventListener('insertrow', function(evt) {
      if(rcmail.env.fixTHEAD_timer)
        window.clearTimeout(rcmail.env.fixTHEAD_timer);
      rcmail.env.fixTHEAD_timer = window.setTimeout("fixTHEAD.adjust('insertrow');",500);
    });
    
    // remove empty row on drag start
    rcmail.message_list.addEventListener('column_dragstart', function(evt) {
      $('#messagelist_empty_row').remove();
    });
    
    // add empty row on drag end
    rcmail.message_list.addEventListener('column_dragend', function(evt) {
      if(!document.getElementById('messagelist_empty_row')){
        $('<tr id="messagelist_empty_row" height="' + $('#messagelist thead').height() + '"><td>&nbsp;</td></tr>').insertBefore('#messagelist > tbody > tr:first');
      }
    });
    
    // adjust messagelist table on resize
    $(window).resize(function(evt){
      if(rcmail.env.fixTHEAD_timer)
        window.clearTimeout(rcmail.env.fixTHEAD_timer);
      rcmail.env.fixTHEAD_timer = window.setTimeout("fixTHEAD.adjust('resize');",100);
      if(bw.mz){
        $('.subject .fixTHEAD').each(function(){
          elem = $(this);
          elem.attr('style', elem.attr('style') + 'vertical-align:middle');
        });
      }
    });
    
    // adjust messagelist table if threads are expanded or collased
    $('#messagelist tbody tr td').each(function(){
      var elem = $(this);
      if(elem.attr('class') == 'threads'){
        elem.click(function(){
          fixTHEAD.adjust('threads');
        });
      }
    });
  });
});
