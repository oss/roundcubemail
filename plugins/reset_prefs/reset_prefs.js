function fix_button ()
{
   var button = document.getElementById ("rcmbtn102");
   button.value = "Reset";
   button.onclick = "alert('test')";
   rcmail.register_button ('button_click', 'rcmbtn102', 'input', '', '', '');
   button.style.visibility = "hidden";
}

function process_response (response)
{
   alert ("test: " + response.test + " time: " + response.time);
}

function ajax_send ()
{
   rcmail.addEventListener ('plugin.ajaxactionlol', process_response);
   rcmail.http_post ('plugin.ajaxactionlol', {test: "huh", str: "lol"}, function (response) { alert ('wut'); });
}

function button_click ()
{
  var agree = confirm ("Are you sure you want to delete all of your user settings?");
  if (agree)
     return rcmail.command('save','',this);
}
