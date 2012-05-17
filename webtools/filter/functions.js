origtext = {};
alreadyrenaming = {};

function remove(type, id) // remove a dest or filter type from the list
{
   var divider = document.getElementById(type + id);
   var divider_parent = divider.parentNode;
   $('#' + type + id).hide("slow", function(){ this.parentNode.removeChild(this); }); // hide it slowly then remove it completely
}

function rename_filter(id)
{
   fkid = id.replace (".", ''); // remove dots because they confuse things when used in element IDs
   if (alreadyrenaming[fkid] == 1) return;
   alreadyrenaming[fkid] = 1;
   var span = $('#'+fkid+' #filter_name');  // grab the area where the name of the filter is
   var name = span.html(); // grab the original name
   origtext[fkid] = name; // save the orig name so that we can use it when canceling
   span.html("<input type='text' style='font-size:15px' value='"+name+"' size=64 onkeydown=\"rename_filter_input_kp(event, '"+id+"')\"/>" +
             "<a href='#' onclick=\"rename_filter_go('"+id+"')\">" +
             "<img id='save' style='vertical-align:middle;padding:2px' src='plugins/webtools/webtools/filter/img/save.png' /></a>" +
             "<a href='#' onclick=\"rename_filter_cancel('"+fkid+"')\">" +
             "<img style='vertical-align:middle;padding:2px' src='plugins/webtools/webtools/filter/img/cancel.png'/></a>");
   span.children('input').focus(); // focus the new input
}

function rename_filter_cancel (id)
{
   id = id.replace (".", '');
   var span = $('#'+id+' #filter_name');
   span.html(origtext[id]); // set the span to the original name
   alreadyrenaming[id] = 0;
}

function rename_filter_input_kp (e, rid)
{
   var key = e.keyCode || e.which;
   if (key == 13)
   {
      rename_filter_go (rid);
   }
   else if (key == 27)
   {
      rename_filter_cancel (rid);
   }
}

function rename_filter_go (rid)
{
   var span = $('#'+fkid+' #filter_name');
   var value = span.children('input').val();
   $('#'+fkid+' #filter_name #save').attr('src', 'plugins/webtools/webtools/filter/img/load.gif');
   rcmail.http_post ("plugin.webtools.filter.rename", { id: rid, name: value });
}


function rename_response(data)
{
	if(data.status) {
      $.pnotify({
         pnotify_title: 'Rename Successful',
         pnotify_text: 'The filter has been successfully renamed to "' + data.name + '".',
         pnotify_type: 'success',
         pnotify_animation: 'slide',
         pnotify_height: '100px'
      });
   }else {
	  $.pnotify({
         pnotify_title: "Warning",
		 pnotify_text: "Mail filters can only contain alphanumeric characters",
		 pnotify_type: "error",
		 pnotify_animation: "slide",
		 pnotify_height: "100px"
	  });
   $('#'+fkid+' #filter_name #save').attr('src', 'plugins/webtools/webtools/filter/img/save.png');
   return;
   } 
   $('#'+data.id+' #filter_name').html(data.name);
   alreadyrenaming[data.id] = 0;
}


function validate_email(obj, type){
    var a = obj.value;
	var filter = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(?:[a-zA-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)$/;
    if(filter.test(a)) {
        $(obj).removeClass('ui-state-error');
        return true;
    }   
    else {
        $(obj).addClass('ui-state-error');
        return false;
    }   
}

function validate_name(obj, type) {
	var a = obj.value;
	var filter = /[^a-zA-Z0-9_ @.]/;
	if(!filter.test(a)) {
		$(obj).removeClass('ui-state-error');
		return true;
	}else {
		$(obj).addClass('ui-state-error');
		return false;
	}
}

function convert_dialog()
{
   $('#convert-dialog').dialog
   ({
      modal:true, 
      resizable:false, 
      buttons: 
      {
         'Convert' : function()
         {           
            var filter_form = document.getElementById("edit-filter");
            var field_name = document.createElement("input");
            field_name.setAttribute("type", "hidden");
            field_name.setAttribute("name", "convert");
            field_name.setAttribute("value", "go");
            filter_form.appendChild(field_name);
            filter_form.submit();
         },
         'Leave Alone':function()
         {
            window.location = "?_task=dummy&_action=plugin.webtools";
         }
      },
      close : function () { window.location = "?_task=dummy&_action=plugin.webtools"; }
   });
}

function delete_response(response) // ajax response to delete action
{  
   $('#' + response.deleted).hide("slow");     // hide the div
   $('#' + response.deleted).attr('id', 'deleted-' + response.deleted); // change its id so it doesnt confuse other stuff
}

function add_response(response) // ajax response to add action
{
   if (response.status==true) {
      var newfilter = document.createElement ('div');
      var filter_id = response.path;
      var current_row_color;
      if ($('#filterlist div:visible:last').attr('class') == "rowstyle1") current_row_color = 0;
      else current_row_color = 1; 
      filter_id = filter_id.replace (".", '');
      newfilter.setAttribute('id', filter_id);
      newfilter.setAttribute('style', 'height:32px;padding-top:15px;');
      newfilter.setAttribute('class', 'rowstyle' + current_row_color);
      newfilter.innerHTML =  "<span style='float:left'><input type='checkbox' checked" +
      " id='enabled-"+response.path+"' onclick=\"enable_filter('"+response.path+"')\">" + 
      "<span id=\"filter_name\" ondblclick=\"rename_filter('"+response.path+"')\">" + response.name + "</span>" +
      "</span><span style='float:right'>" +
      "<a href=\"#\" onclick=\"rename_filter('" + response.path + "')\"> " +
      "<img src='plugins/webtools/webtools/filter/img/rename.png' /> </a>" +
      "<a href=\"#\" onclick=\"edit_filter('" + response.name + "','"+ response.path +"', 'edit')\">" +
      "<img src='plugins/webtools/webtools/filter/img/edit-icon.png'></a>" +
      "<a href=\"#\" onclick=\"delete_filter('" + response.path + "')\">" +
      "<img src='plugins/webtools/webtools/filter/img/delete-icon.png'></a>" +
      "</span></div>";
      var referencenode = document.getElementById ('filterlist');
      referencenode.appendChild (newfilter);
      $('#' + filter_id).hide();
      $('#' + filter_id).show("slow");
   }else {
	  $.pnotify({
         pnotify_title: "Warning",
		 pnotify_text: "Mail filters can only contain alphanumeric characters",
		 pnotify_type: "error",
		 pnotify_animation: "slide",
		 pnotify_height: "100px"
	  });
	  return;
   } 
}

function enable_response(response) // dont really need to respond to this ajax action.
{
}

function enable_filter(filtername) // send ajax request to enable a filter
{
   var state = $('#' + filtername + ' input').attr('checked');
   if (filtername == "uniq-filter")
      rcmail.http_post ('plugin.webtools.filter.enable', {filter: "uniq", uniq: "true", value: state});
   else
      rcmail.http_post ('plugin.webtools.filter.enable', {filter: "." + filtername, value: state});
}

function select_check (obj, id, bad)
{
   var obj2 = document.getElementById("folders-"+id);
   if (obj2.options[obj2.selectedIndex].value == bad)
      $('#badfolder'+id).fadeIn();
   else
      $('#badfolder'+id).fadeOut();
}

function check_filter_form ()
{
   var bad_inputs = 0;
   $("#destination-actions-list input:visible").each(function(i, obj){ if ($(obj).hasClass("ui-state-error")) bad_inputs = 1; });
   $("#filter0 input:visible").each(function(i, obj){ if ($(obj).hasClass("ui-state-error")) bad_inputs = 2 });
   if (bad_inputs == 0)
      return true;
   if (bad_inputs == 1) {
      $.pnotify({
         pnotify_title: 'Invalid Input',
         pnotify_text: 'You must enter a valid email address.',
         pnotify_type: 'error',
         pnotify_animation: 'slide',
         pnotify_height: '100px'
      });
   }else if (bad_inputs == 2) {
	   $.pnotify({
		   pnotify_title: 'Invalid input',
		   pnotify_text: 'Queries can anly be alphanumeric.',
		   pnotify_type: 'error',
		   pnotify_animation: 'slide',
		   pnotify_height: '100px'
	   });
   }
   return false;
}

function new_filter()  // send an ajax request to create a new filter
{
   var filter_name = document.getElementById('new-filter').value;
   if (filter_name == '')
   {
      $.pnotify({
         pnotify_title: 'Invalid',
         pnotify_text: 'You must enter a filter name.',
         pnotify_type: 'error',
         pnotify_animation: 'slide',
         pnotify_height: '100px'
      });
      return;
   }
   rcmail.http_post ('plugin.webtools.filter.add', {filter: filter_name});
   document.getElementById('new-filter').value = '';
}

function delete_filter(filtername) // send an ajax request to delete a filter
{
   //var confirmation = confirm ('Are you sure you wish to delete this filter?');
   //if (confirmation) rcmail.http_post ('plugin.webtools.filter.delete', {filter: filtername});
   $("#delete-confirm").attr('filter', filtername).dialog('open');
}

$(document).ready(function() {
   $("#delete-confirm").dialog ({
      autoOpen:false,
      resizable: false,
      height: 140,
      modal: true,
      buttons: { 'Delete': function () { rcmail.http_post ('plugin.webtools.filter.delete', {filter: $(this).attr('filter')}); $(this).dialog('close'); } ,
                 'Cancel': function () { $(this).dialog('close'); } }
   });
});

rcmail.addEventListener ('plugin.webtools.filter.delete.response', delete_response);
rcmail.addEventListener ('plugin.webtools.filter.add.response', add_response);             // add event listeners for ajax responses
rcmail.addEventListener ('plugin.webtools.filter.enable.response', enable_response);
rcmail.addEventListener ('plugin.webtools.filter.rename.response', rename_response);

function edit_filter(name, path, page)   // post to the next page to edit a filter
{
   var filter_form = document.getElementById("edit-filter");
   var field_name = document.createElement("input");
   var field_path = document.createElement("input");
   var field_page = document.createElement("input");
   field_page.setAttribute("type", "hidden");
   field_name.setAttribute("type", "hidden");
   field_path.setAttribute("type", "hidden");
   field_name.setAttribute("name", "name");
   field_path.setAttribute("name", "path");
   field_page.setAttribute("name", "page");
   field_name.setAttribute("value", name);
   field_path.setAttribute("value", path);
   field_page.setAttribute("value", page);
   filter_form.appendChild(field_name);
   filter_form.appendChild(field_path);
   filter_form.appendChild(field_page);
   filter_form.submit();
}

function add_filter()   // stick a new filter onto the page
{
   var style;
   if ($('#filter-logic-divider div:last').attr('class') == "rowstyle1") style = "rowstyle0";
   else style = "rowstyle1";
   var filter = document.createElement ('div');
   filter.setAttribute('id', 'filter' + filter_counter);
   filter.setAttribute('class', style);
   filter.innerHTML = //"<input type='button' class='pad' value='-' onclick=\"remove('filter', '" + filter_counter + "')\">" +
                      "<a href=\"#\" onclick=\"remove('filter', '" + filter_counter +"')\"><img src=\"plugins/webtools/webtools/filter/img/list-remove-cropped.png\"></a>" +
                      "<select class='pad' name='rule-type[]' onchange=\"change_filter_type('" + filter_counter + "')\" id='type-" + filter_counter + "'>" +
                      "<option value='subject'>Subject</option>" +
                      "<option value='from'>Sender</option>" +
                      "<option value='to'>Recipient</option>" +
                      "<option value='size'>Size</option>" +
                      "<option value='custom'>Custom field...</option>" +
                      "</select><span id='filter-standard-controls-"+filter_counter+"'>" +
                      "<select class='pad' name='rule-logic-standard[]'><option value='in'>contains</option>" +
                      "<option value='notin'>not contains</option><option value='eq'>equal to</option>" +
                      "<option value='neq'>not equal to</option>" +
                      "<input class=\"ui-button ui-widget ui-corner-all\" type='text' name='rule-standard-field[]' size=20 maxlength=128 onkeyup=\"validate_name(this)\"></span>" +
                      "<span id='filter-size-controls-" + filter_counter + "' style='display:none'><select class='pad' name='rule-logic-size[]'>" +
                      "<option value='under'>under</option><option value='over'>over</option>" +
                      "</select>&nbsp;<input class=\"ui-button ui-widget ui-corner-all\" type='text' name='rule-size-field[]' size=5>MB</span>" +
                      "<span id='filter-custom-controls-" + filter_counter + "' style='display:none'><input type='text'" +
                      "name='rule-custom-field0[]' size=13 maxlength=128 class=\"ui-button ui-widget ui-corner-all\" onkeyup=\"validate_name(this)\">" +
                      "<select class='pad' name='rule-logic-custom[]'><option value='in'>contains</option>" +
                      "<option value='notin'>not contains</option><option value='eq'>equal to</option>" +
                      "<option value='neq'>not equal to</option>" +
                      "<input type='text' class=\"pad ui-button ui-widget ui-corner-all\" name='rule-custom-field1[]' size=13 maxlength=128 onkeyup=\"validate_name(this)\"></span>";
   var referencenode = document.getElementById('filter-logic-divider');
   referencenode.appendChild(filter);
   $('#filter' + filter_counter).hide();
   $('#filter' + filter_counter).show("slow");
   change_filter_type(filter_counter);
   filter_counter++;
}

function add_dest()  // stick a new destination onto the page
{
   var style;
   if ($('#destination-actions-list div:last').attr('class') == "rowstyle1") style = "rowstyle0";
   else style = "rowstyle1";
   var dest = document.createElement ('div');
   dest.setAttribute('id', 'dest' + dest_counter);
   dest.setAttribute('class', style);
   dest.innerHTML = //"<input type='button' value='-' onclick=\"remove('dest', '" + dest_counter + "')\">" +
                    "<a href=\"#\" onclick=\"remove('dest', '" + dest_counter +"')\"><img src=\"plugins/webtools/webtools/filter/img/list-remove-cropped.png\"></a>" +
                    "<select name='dest-type[]' onchange=\"change_dest_type('" + dest_counter + "')\" id='dest-type-" + dest_counter + "'>" +
                    "<option value='redirect'>Redirect message to</option>" +
                    "<option value='ccto'>Send a copy to</option>" +
                    "<option value='delete'>Delete message</option>" +
                    "<option value='folder'>Move to folder</option><option value='foldercc'>Copy to folder</option></select>" +
                    "<input type='text'  class=\"ui-button ui-widget ui-corner-all ui-state-error\" onkeyup=\"validate_email(this)\" id='dest-field-"+dest_counter+"' name='dest-field[]' size=22>" + make_folder_select(dest_counter);
   var referencenode = document.getElementById('destination-actions-list');
   referencenode.appendChild(dest);
   $('#dest' + dest_counter).hide();
   $('#dest' + dest_counter).show("slow");
   dest_counter++;
}

function change_dest_type (id)  // change the destination type (show correct fields)
{
   var select_box = document.getElementById ("dest-type-" + id);
   var dest_field = document.getElementById ("dest-field-" + id);
   var dest_emails = document.getElementById ("folders-" + id);
   if ((select_box.value == "redirect") || (select_box.value == "ccto"))
   {
      dest_field.style.display = '';
      dest_emails.style.display = 'none';
   }
   else if ((select_box.value == "folder") || (select_box.value == "foldercc"))
   {
      dest_field.style.display = 'none';
      dest_emails.style.display = '';
   }
   else if (select_box.value == "delete")
   {
      dest_field.style.display = 'none';
      dest_emails.style.display = 'none';
   }
}

function change_filter_type(id) // change the type of a filter (set correct controls to visible)
{
   var select_box = document.getElementById ("type-" + id);
   var standard_controls = document.getElementById ("filter-standard-controls-" + id);
   var size_controls = document.getElementById ("filter-size-controls-" + id);
   var custom_controls = document.getElementById ("filter-custom-controls-" + id);
   if ((select_box.value == "subject") || (select_box.value == "from") || (select_box.value == "to"))
   {
      standard_controls.style.display = '';
      size_controls.style.display = 'none';
      custom_controls.style.display = 'none';
   }
   else if (select_box.value == "size")
   {
      custom_controls.style.display = 'none';
      standard_controls.style.display = 'none';
      size_controls.style.display = '';
   }
   else if (select_box.value == "custom")
   {
      size_controls.style.display = 'none';
      custom_controls.style.display = '';
      standard_controls.style.display = 'none';
   }
}

function validateFilter() {
	var filter_name=$("#new-filter");
	var a = filter_name.val();
	var filter_ok = /^[A-Za-z0-9_ ]+$/i;
	if(filter_ok.test(a)){
		filter_name.removeClass('ui-state-error');
		return true;
	}else {
		filter_name.addClass('ui-state-error');
		return false;
	}
}


rcmail.addEventListener ();
$(document).ready( function()
{
   $("#new-filter").bind("keyup", function(e) {
	   validateFilter();
	   var key = e.keyCode || e.which;
	   if(key==13) new_filter();
   });
   $("#new-filter").change(validateFilter);
   $("#destination-actions-list input").each(function(i, obj){ validate_email(obj); });
   $("#filter0 input:visible").each(function(i, obj){ validate_name(obj); });
});
