<?php

function make_folder_select ($id, $selected, $hide) // php func to make a folder selector
{
   $rc = rcmail::get_instance();
   $rc->imap_init (true);
   $storage = $rc->get_storage();
   $found = 0;
   foreach($storage->list_folders() as $i => $folder) 
   {
	   $folder = $storage->mod_folder($folder);
	   if ($folder == $selected) $found = 1;
   }
   $nonexist = '';
   if ($found == 0) $nonexist = $selected;
   $return = "<select id='folders-" . $id . "' name='dest-folder[]' onchange='select_check(self, \"" . $id . "\", \"$nonexist\")' ".$hide.">";
   foreach($storage->list_folders() as $i => $folder)
   {
      $folder = $storage->mod_folder($folder);
      if (strlen($folder) > 40) 
         $nicefolder = substr($folder, 0, 15) . " .. " . substr($folder, -15, strlen($folder));
      else
         $nicefolder = $folder;
      $return .= "<option value='$folder' ".($folder==$selected?"selected":"").">$nicefolder</option>";
   }
   if ($found == 0)
   {
      if (strlen($selected) > 40) 
         $nicefolder = substr($selected, 0, 15) . " .. " . substr($selected, -15, strlen($selected));
      else
         $nicefolder = $selected;
      $return .= "<option value='$nicefolder' selected>$nicefolder</option>";
      $return .= "</select>";
      $return .= "<span style='color:#EE0000; padding: 5px' id='badfolder$id'><img src=\"plugins/webtools/webtools/filter/img/warning.png\" align=\"ABSBOTTOM\" width=24 height=24>The folder \"$nicefolder\" does not exist</span>";
   }
   else
      $return .= "</select>";
   return $return;        // aaron says that i should note that the folder will be created once mail is received that matches this filter
}

function parse_old_config ($data) // parse a config made by sqmail filtering webtool
{
   $rc = rcmail::get_instance();
   $parse_char = $rc->config->get('filter_parse_character');
   $mailuniq = 0;
   $result = array();
   if (strstr($data[0], "##Begin Filter Configuration -- Please do not edit by hand, use SquirrelMail") != FALSE)
   {
      foreach ($data as $line)
      {
         if (strstr($line, "###") != FALSE) // check to see if its the ### before a rule
         {
            if (strstr($line, "MAILUNIQ") != FALSE)
            {
               $mailuniq = 1;
               continue;
            }
            $line = str_replace (array("###", "INBOX."), "", $line);
            $filter_info = explode (" ''",$line);
            foreach ($filter_info as $id=>$val) $filter_info[$id] = trim ($val); // parse it into an easier to read array
            $filters[] = $filter_info;
         }
      }
      foreach ($filters as $id => $filter)
      {
         unset ($cfg);
         $cfg = array();
         $path = ".mailfilter-oldconfig-" . $id;
         $name = "Old Filter: ";
         $cfg["rule-logic-standard"][0] = "in";
         $cfg["rule-logic-custom"][0] = "in";
         $cfg["rule-logic-custom"][1] = "in";
         $cfg["filter-logic-all"] = "or"; 
         if ($filter[0] == "Subject") { $cfg["rule-type"][0] = "subject"; $cfg["rule-standard-field"][0] = $filter[1]; $name .= "Subj "; }
         else if ($filter[0] == "From") { $cfg["rule-type"][0] = "from"; $cfg["rule-standard-field"][0] = $filter[1]; $name .= "From "; }
         else if ($filter[0] == "To") { $cfg["rule-type"][0] = "to"; $cfg["rule-standard-field"][0] = $filter[1]; $name .= "To "; }
         else if ($filter[0] == "TO_") // weird to or cc thing
         {
            $cfg["rule-type"][0] = "to";
            $cfg["rule-type"][1] = "custom";
            $cfg["rule-custom-field0"][1] = "CC";
            $cfg["rule-custom-field1"][1] = $cfg["rule-standard-field"][0] = $filter[1]; 
            $name .= "To or CC ";
         }
         else if ($filter[0] == "Cc") { $cfg["rule-type"][0] = "custom"; $cfg["rule-custom-field0"][0] = $filter[1]; $name.="Custom "; }
         $name .= $filter[1] . " ";
         $dc = 0; // dest counter
         if ($filter[3] == "c") // we are copying
         { 
            $name .= "copy to ";
            if ($filter[4] == "on") // 'folder' is checked = 'copy to folder'
            {
               $name .= $filter[2];
               $cfg["dest-type"][$dc] = "foldercc";
               $cfg["dest-folder"][$dc] = $filter[2];
               $dc++;
            }
            if ($filter[5] == "on") // 'email' is checked = 'send a copy to'
            {
               $name .= $filter[6];
               $cfg["dest-type"][$dc] = "ccto";
               $cfg["dest-field"][$dc] = $filter[6];
               $dc++;
            }
         }
         else // we are moving
         {
            $name .= "move to ";
            if ($filter[4] == "on") // 'folder' is checked = move to folder 
            {
               $name .= $filter[2];
               $cfg["dest-type"][$dc] = "folder";
               $cfg["dest-folder"][$dc] = $filter[2];
               $dc++;
            }
            if ($filter[5] == "on")
            {
               $name .= $filter[6];
               $cfg["dest-type"][$dc] = "redirect";
               $cfg["dest-field"][$dc] = $filter[6];
               $dc++;
            }
         }
         $mainconfig[] = array ("name" => $name, "enabled" => "true", "path" => $path);
         $result[$path] = make_filter_config ($cfg); // i made an array that looks like the postdata i get 'cause i have a function
      }                                              // that'll make a filter config from that format already
      $result[".mailfilter"] = make_main_config($mainconfig, $mailuniq);  // make the main config
   }
   else
      return false;
   return $result; // hash table of filenames => file contents; easy to foreach
}

function parse_main_config ($data)
{
   $rc = rcmail::get_instance();
   $parse_char = $rc->config->get('filter_parse_character');
   $result = array();
   if (strstr($data[0], '#$ID:webtools-filter-config') != FALSE) // make sure i made thi sfile
   {
      for ($i = 5; $i <= count($data); $i++)
      {
         if ($data[$i][0] == "#")
         {
            $data[$i] = str_replace ("#", "", $data[$i]);    // de-serialize data (i know its dumb to not use php serialize()
            $temp = explode($parse_char, $data[$i]);                 // i forgot about that function, okay)
            //console(print_r($temp,true));
            //console($data[$i]);
            $result[intval($temp[0])][$temp[1]] = $temp[2];
         }
      }
   }
   else 
   {
      return NULL;
   }
   return $result;
}

function make_main_config ($data, $uniq = 0)
{
   $rc = rcmail::get_instance();
   $parse_char = $rc->config->get('filter_parse_character');
   $out = "#\$ID:webtools-filter-config\n";              // nice obnoxious header
   //$out .= "####################################\n";
   //$out .= "#THIS FILE WAS CREATED BY A WEBTOOL#\n";
   //$out .= "#DO NOT EDIT IT BY HAND            #\n";
   //$out .= "####################################\n\n";
   $out .= $rc->config->get('warning_comment');
   if ($uniq != 0) {
      $out .= "#U".$parse_char."UNIQMAIL".$parse_char."true\n`reformail -D 8000 .duplicate.cache`\nif ( \$RETURNCODE == 0 )\n{\nexit\n}\n";
   }
   foreach ($data as $id => $filter)
   {
      foreach ($filter as $key => $value)
      {
         if ($key == "UNIQMAIL") continue;
         $out .= "#$id$parse_char$key$parse_char$value\n";    // serializing the data
      }
      if ($filter['enabled'] == 'true') $out .= "include ".$filter['path']."\n";
   }
   return $out;
}

function parse_filter_config ($data)
{
   $rc = rcmail::get_instance();
   $parse_char = $rc->config->get('filter_parse_character');
   $result = array();
   if (strstr($data[0], '#$ID:webtools-filter-config') != FALSE) // make sure i made this file
   {
      for ($i = 5; $i <= count($data); $i++) // start at the sixth line down since i dont want to try and parse the header
      {
         if ($data[$i][0] == "#")  // if it starts with a #
         {
            $data[$i] = str_replace ("#", "", $data[$i]);  // parsing it into  a nice array
            $temp = explode($parse_char, $data[$i]);
            $type = array_shift($temp);
            if ($type == "filter")
               $result[$type][$temp[0]] = $temp[1];
            else
               $result[$type][intval($temp[0])][$temp[1]] = $temp[2];
         }
      }
   }
   else
   {
      return NULL;
   }
   return $result;
}

function make_dest ($data)  // output a destination row w/ the parsed config data
{
   $rc = rcmail::get_instance();
   $parse_char = $rc->config->get('filter_parse_character');
   global $dest_counter, $current_dest_style;
   $hide = " style='display:none'";
   $field = ((($data['type']=="redirect")||($data['type']=="ccto"))?true:false);
   echo ("<div class= \"rowstyle" . $current_dest_style . "\" id=\"dest".$dest_counter."\">" .
        // "<input  class=\"ui-button ui-widget ui-state-default ui-corner-all\" type='button' value='-' onclick=\"remove('dest', '" . $dest_counter . "')\">" .
         "<a href=\"#\" onclick=\"remove('dest', '" . $dest_counter . "')\"><img src=\"plugins/webtools/webtools/filter/img/list-remove-cropped.png\"></a>" .
         "<select name='dest-type[]' onchange=\"change_dest_type('" . $dest_counter . "')\" id='dest-type-" . $dest_counter . "'>" .
         "<option value='redirect' ".($data['type']=="redirect"?"selected":"").">Redirect message to</option>" .
         "<option value='ccto' ".($data['type']=="ccto"?"selected":"").">Send a copy to</option>" .
         "<option value='delete' ".($data['type']=="delete"?"selected":"").">Delete message</option>" .
         "<option value='folder' ".($data['type']=="folder"?"selected":"").">Move to folder</option>" .
         "<option value='foldercc' " . ($data['type']=="foldercc"?"selected":"") . ">Copy to folder</option></select>" .
         "<input class=\"ui-button ui-widget ui-corner-all\" onkeyup=\"validate_email(this)\" type='text' id='dest-field-".$dest_counter."' name='dest-field[]' size=22 " . ($field?"":$hide) .
         "value='".$data['field']."'>" . make_folder_select($dest_counter, $data['folder'], (($data['type']=="folder")||($data['type']=="foldercc")?"":$hide)) . "</div>");
   if ($current_dest_style == 0) $current_dest_style = 1;
   else $current_dest_style = 0;
   $dest_counter++;
}

function make_rule ($data) // output a rule row w/ the parsed config data
{
   $rc = rcmail::get_instance();
   $parse_char = $rc->config->get('filter_parse_character');
   global $filter_counter, $current_filter_style;
   $hide = " style='display:none'";
   if (($data['type'] == 'subject') || ($data['type']=='from') || ($data['type']=='to')) $standard = true;
   else $standard = false;
   echo ("<div class=\"rowstyle" . $current_filter_style . "\" id=\"filter".$filter_counter."\">" .
         //"<input class='pad' type='button' value='-' onclick=\"remove('filter', '" . $filter_counter . "')\">" .
         "<a href=\"#\" onclick=\"remove('filter', '" . $filter_counter ."')\"><img src=\"plugins/webtools/webtools/filter/img/list-remove-cropped.png\"></a>" .
         "<select class='pad' name='rule-type[]' onchange=\"change_filter_type('" . $filter_counter . "')\" id='type-" . $filter_counter . "'>" .
         "<option value='subject' " . ($data['type']=='subject'?"selected":"") . ">Subject</option>" .
         "<option value='from' " . ($data['type']=='from'?"selected":"") . ">Sender</option>" .
         "<option value='to' " . ($data['type']=='to'?"selected":"") . ">Recipient</option>" .
         "<option value='size' " . ($data['type']=='size'?"selected":"") . ">Size</option>" .
         "<option value='custom' " . ($data['type']=='custom'?"selected":"") . ">Custom field...</option>" .
         "</select><span id='filter-standard-controls-" . $filter_counter . "'" . ($standard?"":$hide) . ">" .
         "<select class='pad'  name='rule-logic-standard[]'><option value='in' " . ($data['logic-standard']=='in'?"selected":"") . ">contains</option>" .
         "<option value='notin' ". ($data['logic-standard']=='notin'?"selected":"") .">not contains</option>" . 
         "<option value='eq' ". ($data['logic-standard']=='eq'?"selected":"") .">equal to</option>" .
         "<option value='neq'  ". ($data['logic-standard']=='neq'?"selected":"") .">not equal to</option>" .
         "<input class=\"ui-button ui-widget ui-corner-all pad\"   type='text' name='rule-standard-field[]' size=20 maxlength=128 value='".$data['standard-field']."'></span>" .
         "<span id='filter-size-controls-" . $filter_counter . "' ".($data['type']!='size'?$hide:"").">".
         "<select class='pad' name='rule-logic-size[]'>" .
         "<option value='under' ". ($data['logic-size']=='under'?"selected":"") .">under</option>" .
         "<option value='over' ". ($data['logic-size']=='over'?"selected":"") .">over</option>" .
         "</select><input  class=\"ui-button ui-widget ui-corner-all pad\"  type='text' name='rule-size-field[]' size=5 value='".$data['size-field']."'>MB</span>" .
         "<span id='filter-custom-controls-" . $filter_counter . "' ".($data['type']!='custom'?$hide:"").">" .
         "<input  class=\"ui-button ui-widget ui-corner-all\" type='text' name='rule-custom-field0[]' size=13 maxlength=128 value='".$data['custom-field0']."'>" .
         "<select name='rule-logic-custom[]' class='pad'>" .
         "<option value='in' ". ($data['logic-custom']=='in'?"selected":"") .">contains</option>" .
         "<option value='notin' ". ($data['logic-custom']=='notin'?"selected":"") .">not contains</option>" .
         "<option value='eq' ". ($data['logic-custom']=='eq'?"selected":"") .">equal to</option>" .
         "<option value='neq' ". ($data['logic-custom']=='neq'?"selected":"") .">not equal to</option>" .
         "<input  class=\"ui-button ui-widget ui-corner-all pad\"  type='text' name='rule-custom-field1[]' size=13 maxlength=128 value='".$data['custom-field1']."'></span></div>");
   if ($current_filter_style == 0) $current_filter_style = 1;
   else $current_filter_style = 0;
   $filter_counter++;
}

function dumpjsvars ()  // dump the variables i was using in php to javascript variables
{
   global $filter_counter, $dest_counter;
   return ("<script type=\"text/javascript\">filter_counter = " . $filter_counter . "; dest_counter = " . $dest_counter . ";</script>");
}

function make_filter_config ($data) // generate actual maildrop script crap
{
   $rc = rcmail::get_instance();
   $parse_char = $rc->config->get('filter_parse_character');
   $header = "#\$ID:webtools-filter-config\n" .
             "####################################\n" .
             "#THIS FILE WAS CREATED BY A WEBTOOL#\n" .
             "#DO NOT EDIT IT BY HAND            #\n" .
             "####################################\n" .
             "MAILDIR=\$HOME/Maildir # Default mail directory\n\n"; # Default mail directory
   $file = $header;
   $file .= "#filter".$parse_char."logic$parse_char" . $data['filter-logic-all'] . "\n"; 
   $if_statement = "if ("; // beginning of an if statement
   $dest_statement = '';
   for ($i = 0; $i < count ($data['rule-type']); $i++)
   {
      $file .= "#rules$parse_char" . $i . $parse_char . "type$parse_char" . $data['rule-type'][$i] . "\n";
      $file .= "#rules$parse_char" . $i . $parse_char . "logic-standard$parse_char" . $data['rule-logic-standard'][$i] . "\n";
      $file .= "#rules$parse_char" . $i . $parse_char . "standard-field$parse_char" . $data['rule-standard-field'][$i] . "\n";
      $file .= "#rules$parse_char" . $i . $parse_char . "logic-size$parse_char" . $data['rule-logic-size'][$i] . "\n";      
      $file .= "#rules$parse_char" . $i . $parse_char . "size-field$parse_char" . $data['rule-size-field'][$i] . "\n";           // serialize all my datas
      $file .= "#rules$parse_char" . $i . $parse_char . "custom-field0$parse_char" . $data['rule-custom-field0'][$i] . "\n";
      $file .= "#rules$parse_char" . $i . $parse_char . "custom-field1$parse_char" . $data['rule-custom-field1'][$i] . "\n";
      $file .= "#rules$parse_char" . $i . $parse_char . "logic-custom$parse_char" . $data['rule-logic-custom'][$i] . "\n";  
      if (($data['rule-type'][$i] == 'subject') || ($data['rule-type'][$i] == 'from') || ($data['rule-type'][$i] == 'to'))
      {
         $if_statement .= "/^";   // regex for beginning of a line
         if ($data['rule-type'][$i] == 'subject') $if_statement .= "Subject: ";
         else if ($data['rule-type'][$i] == 'from') $if_statement .= "From: ";
         else if ($data['rule-type'][$i] == 'to') $if_statement .= "To: ";
         $data['rule-standard-field'][$i] .= "$/";                          // added to the end of standard-field cause this must end the regex
         if ($data['rule-logic-standard'][$i] == 'in') $if_statement .= ".*";         // contains, so there can be * between To: or whatevre and the text the user supplied
         else if ($data['rule-logic-standard'][$i] == 'notin') { $if_statement .= ".*"; $data['rule-standard-field'][$i] .= " == 0"; } // for notin i just put == 0 at the end of the regex
         else if ($data['rule-logic-standard'][$i] == 'neq') $data['rule-standard-field'][$i] .= " == 0";                              // it appeared to work while i was testing
         $if_statement .= $data['rule-standard-field'][$i]; 
      }
      else if ($data['rule-type'][$i] == 'size')
      {
         $if_statement .= "\$SIZE ";
         if ($data['rule-logic-size'][$i] == 'under') $if_statement .= "<";
         else if ($data['rule-logic-size'][$i] == 'over') $if_statement .= ">";
         $if_statement .= " " . (floatval($data['rule-size-field'][$i])*1000);  // maildrop wants a value in bytes
      }
      else if ($data['rule-type'][$i] == 'custom')
      {
         $if_statement .= "/^" . $data['rule-custom-field0'][$i] . ":";
         $data['rule-custom-field1'][$i] .= "/";
         if ($data['rule-logic-custom'][$i] == 'in') $if_statement .= ".*";
         else if ($data['rule-logic-custom'][$i] == 'notin') { $if_statement .= ".*"; $data['rule-custom-field1'][$i] .= " == 0"; }
         else if ($data['rule-logic-custom'][$i] == 'neq') $data['rule-custom-field1'][$i] .= " == 0";
         $if_statement .= $data['rule-custom-field1'][$i];
      }
      if ($i < count($data['rule-type'])-1)
      {
         if ($data['filter-logic-all'] == 'and') $if_statement .= " && ";     // add on a separator if this isn't the last filter
         else if ($data['filter-logic-all'] == 'or') $if_statement .= " || ";
      }
   }
   for ($i = 0; $i < count($data['dest-type']); $i++)
   {
      $file .= "#dests$parse_char" . $i . $parse_char . "type$parse_char" . $data['dest-type'][$i] . "\n";
      $file .= "#dests$parse_char" . $i . $parse_char . "field$parse_char" . $data['dest-field'][$i] . "\n";
      $file .= "#dests$parse_char" . $i . $parse_char .  "folder$parse_char" . $data['dest-folder'][$i] . "\n";
      if ($data['dest-folder'][$i] == "INBOX") $data['dest-folder'][$i] = "";
      if ($data['dest-type'][$i] == 'folder') $dest_statement .= "to \"\$MAILDIR/." . $data['dest-folder'][$i] . "\"\n";
      else if ($data['dest-type'][$i] == 'ccto') $dest_statement .= "cc \"!".$data['dest-field'][$i]."\"\n";
      else if ($data['dest-type'][$i] == 'redirect') $dest_statement .= "to \"!".$data['dest-field'][$i]."\"\n";
      else if ($data['dest-type'][$i] == 'delete') $dest_statement .= "to \"\$MAILDIR/.Trash\"\n";
      else if ($data['dest-type'][$i] == 'foldercc') $dest_statement .= "cc \"\$MAILDIR/." . $data['dest-folder'][$i]. "\"\n";
   }
   $file .= "\n$if_statement)\n{\n$dest_statement}\n";  // put it all together; the header + serialized data is in $file already, add if statmeent and dest. statements
   return $file;
}

function sanitize_filter_name($input) {
	if(strcmp($input, '') == 0) return true;
	if(!is_array($input))
		$input=array($input);
	foreach ($input as $value) {
		if(preg_match("/[^a-zA-Z0-9@_.-]/", $value)) return false;
	}
	return true;
}

function sanitize_filter_email($email) {
	$result=true;
	$emailValid=email_valid($email);
	if(!$emailValid) $result=false;
	$mailhost=explode('@',$email);
	$mailhost=$mailhost[1];
	$hostValid=host_valid($mailhost);
	if(!$hostValid) $result=false;
	return $result;
}

?>
