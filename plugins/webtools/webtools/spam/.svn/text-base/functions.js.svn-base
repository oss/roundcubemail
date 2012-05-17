var input_counter = 0;

// dumb email validator for spam blacklist/whitelist addys
// richton says we should be a bit more lenient; as such, any tld is
// allowed even if it's invalid.
function validate_email_dumb(obj){
    var a = obj.value;
    var filter = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(?:[a-zA-Z]*)$/;
    if(filter.test(a)) {
        $(obj).removeClass('ui-state-error');
        return true;
    }
    else {
        $(obj).addClass('ui-state-error');
        return false;
    }
}

// dumb domain validator for spam blacklist/whitelist domains
// just to remove the possibility of spaces or other invalid characters
// in domain names.
function validate_domain_dumb (obj)
{
   var a = obj.value;
   var filter = /^[a-zA-Z0-9.-]*$/;
   if (filter.test(a))
   {
      $(obj).removeClass('ui-state-error');
      return true;
   }
   else
   {
      $(obj).addClass('ui-state-error');
      return false;
   }
}

function add_input(name, validate_function)
{
   input_counter += 1;
   var new_box = document.createElement ('div');
   var new_box_class = "rowstyle1";
   if ($('#' + name + ' div:visible:last').prev().attr('class') == "rowstyle0") new_box_class = "rowstyle1";
   else new_box_class = "rowstyle0";
   new_box.setAttribute('class', new_box_class);
   new_box.setAttribute('id', name+input_counter);
   new_box.innerHTML = "<a href=\"#\" onclick=\"rem_input('"+name+input_counter+"')\">"+
                       "<img src=\"plugins/webtools/webtools/spam/img/list-remove-cropped.png\"></a>";
   var temp_var = "<input type='text' name='"+name+"[]' size=40 maxlength=129 ";
   if (validate_function != "") temp_var += 'onkeyup="'+validate_function+'(this)"';
   temp_var += ">";
   new_box.innerHTML += temp_var;
   $('#' + name + '-button').before(new_box); 
   $('#' + name + input_counter).hide().show('slow');
} 

function validate_edit ()
{
   var form = document.getElementById("spam-form");
   if ($('#radio-spam-folder').attr('checked'))
      if ($('#input-spam-folder').attr('value') == "")
      {
         $('#webtools-box').scrollTo(10, { duration: 1000 });
         $('#input-spam-folder').css('background-color', '#ffff88');
         return false;
      }
   var bad_inputs = 0;
   $("#blacklist-address input:visible").each(function(i, obj){ validate_email_dumb(obj); if ($(obj).hasClass("ui-state-error")) bad_inputs = 1; });
   $("#whitelist-address input:visible").each(function(i, obj){ validate_email_dumb(obj); if ($(obj).hasClass("ui-state-error")) bad_inputs = 1; });
   if (bad_inputs != 0)
   {
      $.pnotify({
         pnotify_title: 'Invalid Input',
         pnotify_text: 'You must enter a valid email address.',
         pnotify_type: 'error',
         pnotify_animation: 'slide',
         pnotify_height: '100px'
      }); 
      return false;
   }
   $("#blacklist-domain input:visible").each(function(i, obj){ validate_domain_dumb(obj); if($(obj).hasClass("ui-state-error")) bad_inputs = 1; });
   $("#whitelist-domain input:visible").each(function(i, obj){ validate_domain_dumb(obj); if($(obj).hasClass("ui-state-error")) bad_inputs = 1; });
   if (bad_inputs != 0)
   {
      $.pnotify({
         pnotify_title: 'Invalid Input',
         pnotify_text: 'You must enter a valid domain name.',
         pnotify_type: 'error',
         pnotify_animation: 'slide',
         pnotify_height: '100px'
      }); 
      return false;
   }
   return true;
}

function rem_input(id)
{
   $('#'+id).hide('slow', function(){$(this).remove()});
 //  setTimeout("$('#"+id+" input').first().attr('value','')", 500);
 //  $('#'+id).remove();
}

$(document).ready(function(){
   //$('select').selectmenu({style:'dropdown'});
});

