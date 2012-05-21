<?php

function make_box ($type, $data, $validate_function)
{
   global $input_counters;
   $input_counters[$type]++;
   $row_style = "rowstyle" . $input_counters[$type] % 2;
   $return = "<div class='".$row_style."' id='saved-".$type.$input_counters[$type]."'>" .
             "<a href=\"#\" onclick=\"rem_input('saved-".$type.$input_counters[$type]."')\">" .
             "<img src=\"plugins/webtools/webtools/spam/img/list-remove-cropped.png\"></a>" .
             "<input type='text' name='".$type."[]' size=40 maxlength=129 value=\"".$data."\"" . 
             ($validate_function==''?'':"onkeyup=\"$validate_function(this)\"") . "></div>";
   return $return;
}

function make_folder_select_spam ($selected) // php func to make a folder selector
{
   rcmail::get_instance()->imap_init(true);
   $return = "<select id='folders' name='spam-folder' class='webtools-select'>";
   foreach(rcmail::get_instance()->imap->list_unsubscribed() as $folder)
   {  
      if (strlen($folder) > 40)
         $nicefolder = substr($folder, 0, 15) . " .. " . substr($folder, -15, strlen($folder));
      else
         $nicefolder = $folder;
      $return .= "\"<option value='$folder' ".($folder==$selected?"selected":"").">$nicefolder</option>\"+";
   }   
   $return .= "</select>";
   return $return;
}

function validate_spam_domain($domain) {
	return (eregi('^' . '[a-zA-Z0-9]*' . '.' . '[a-zA-Z0-9]*' . '$', $domain));
}

?>
