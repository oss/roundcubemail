<style>
   .rowstyle0 { background: #DEDEDE; padding: 10px; font-size:15px; }
   .rowstyle1 { background: #EEEEEE; padding: 10px; font-size:15px; }
   .rowstyle2 { background: #D0D0D0; padding: 10px; font-size:15px; }
   .tablestyle { background: #FafaFa; padding: 10px; font-size: 16px; }
</style>

<?php
require_once('plugins/webtools/webtools/functions.php');
require_once('functions.inc');
$rcmail = rcmail::get_instance();
$subhead = $rcmail->config->get('filtering_subhead');
$convert_dialog = $rcmail->config->get('convert_dialog');
?>

<h2 align="center">Filters</h2>
<div id="webtools-subhead">
    <?=$subhead?>
</div>

<div id="webtools-innerbox" style="padding:25px;" class="ui-widget ui-widget-content ui-corner-all">
<div id="convert-dialog" style="display:none" title="Configuration File Conversion"><?=$convert_dialog?></div>
<?php
if ((empty($_POST['page'])) || ($_POST['page'] == "Edit Filters") || ($_POST['page'] == "Submit") || ($_POST['page'] == "Go Back"))
{
if ($_POST['convert'] == "go")
{
   have_file (".mailfilter", $result);
   write_file (".mailfilter-sqmail-backup", implode ("\n", $result));
   if (($oldconf = parse_old_config ($result)) != false) // check to see if .mailfilter is from the old sqmail tool.
   {
      foreach ($oldconf as $filename => $contents)   // if it is write out all the new files
         write_file ($filename, $contents);
      echo ("<div id='successdiv' style='border:1px solid #11aa00;margin:15px;padding:10px;background:#aaff99;font-size:15px;text-align:center;'>Your configuration file" .
      " has been converted to be compatible with the new tool.</div>" .
      "<script type=\"text/javascript\">setTimeout(\"$('#successdiv').hide('slow')\",50000);</script>"); // make a pretty little box tellin' the user what just happened
      $parsed = parse_main_config (explode("\n", $oldconf['.mailfilter'])); // parse the newly generated main config
   }
}
if ($_POST['page'] == "Submit") // we're coming from the filter editor page, so make a filter conf and write it out.
{
   $fields = array(
      "1"	=>	$_POST['rule-standard-field'][0],
	  "2"	=>	$_POST['rule-custom-field0'][0],
	  "3"	=>	$_POST['rule-custom-field1'][0],
   );
   if(sanitize_filter_name($fields)) { //if valid query
      if(sanitize_filter_email($_POST['dest-field'][0])  || (strcmp($_POST['dest=type'][0], 'redirect') != 0 && strcmp($_POST['dest-type'][0], 'ccto') != 0) ){ //if valid email
         $file = make_filter_config ($_POST);
         write_file ($_POST['filter-path'], $file);
         echo ("<div id='successdiv' style='border:1px solid #11aa00;margin:15px;padding:10px;background:#aaff99;font-size:15px;text-align:center;'>Filter successfully updated!</div>" .
         "<script type=\"text/javascript\">setTimeout(\"$('#successdiv').hide('slow')\",5000);</script>"); // pretty little div
      }
   }
}
if (have_file (".mailfilter", $result))
   if (($oldconf = parse_old_config ($result)) != false) // check to see if .mailfilter is from the old sqmail tool.
      echo ("<script type=\"text/javascript\">$(document).ready(function(){convert_dialog();});</script>");
   else 
   {
      $parsed = parse_main_config($result); // its not the old config so just parse it
   }
?>
<form method="post" action="?_task=dummy&_action=plugin.webtoolsfilter" id="edit-filter">
</form> <!-- made this so i can easily post to the edit page, kindof a nasty little hack but it works --> 
<div style="border:0px solid black;margin-bottom:5px">
<div id="delete-confirm" style="display:none" title="Are you sure?">Are you sure you wish to delete this filter?</div>
   <div style="margin:15px"><h3>Filter List</h3></div>
   <div class="roundbox tablestyle" id="filterlist">
   Add a filter: <input type='text' class="ui-button ui-widget ui-corner-all" name='new-filter' id='new-filter' size=15 maxlength=128 style='margin-bottom:8px'>
   <input type='button' onclick="new_filter()" value='Add' class="roundbox ui-button ui-widget ui-state-default ui-corner-all">
   <div class='roundbox rowstyle2' style='height:32px;padding-top:15px;' id="uniq-filter">
      <span style='float:left'><input type='checkbox' name='enabled-uniq' onclick="enable_filter('uniq-filter')" <?php if ($parsed[0]['UNIQMAIL'] == "true") { echo "checked"; } ?>>Delete Non-Unique Mail</span>
   </div>
   <?php
      $table_style = 0;
      foreach ($parsed as $filter)
      { // outputting all of the filters in the config file
         if ($filter['id'] == "U") continue;
         echo ("<div class='rowstyle" . $table_style . "' style='height:32px;padding-top:15px;' id='".str_replace('.','',$filter['path'])."'");
         echo (" >");
         echo ("<span style='float:left'><input type='checkbox' name='enabled-" . $filter['name'] . "' value='true'" . ($filter['enabled']=="true"?" checked":""));
         echo (" id='enabled-".$filter['path']."' onclick=\"enable_filter('".str_replace('.','',$filter['path'])."')\">");
         echo ("<span id=\"filter_name\" ondblclick=\"rename_filter('".$filter['path']."')\">" . $filter['name']);
         echo ("</span></span><span style='float:right'>");
         echo ("<a href=\"#\" onclick=\"rename_filter('".$filter['path']."')\">" .
               "<img src='plugins/webtools/webtools/filter/img/rename.png' title=\"Rename\" alt=\"Rename\"></a>" .
               "<a href=\"#\" onclick=\"edit_filter('".$filter['name']."','".$filter['path']."', 'edit')\">" .
               "<img src='plugins/webtools/webtools/filter/img/edit-icon.png' title=\"Advanced\" alt=\"Advanced\"></a>" .
               "<a href=\"#\" onclick=\"delete_filter('".$filter['path']."')\">" .
               "<img src='plugins/webtools/webtools/filter/img/delete-icon.png' title=\"Delete\" alt=\"Delete\"></a>");
         echo ("</span></div>");
         if ($table_style == 0) $table_style = 1;
         else $table_style = 0; // switching to the other table row color thing
      }
   ?>
   </div>
</div>
<?php
} 
else if ($_POST['page'] == "edit") // we're on the edit page for a particular filter
{
if (have_file($_POST['path'], $result))
{
   $config = parse_filter_config ($result);  // load it in and parse the conf
}
global $filter_counter, $current_filter_style, $dest_counter, $current_dest_style;
$filter_counter = 0;        // these vars are used in functions.inc to keep track of what dest and filter we're on
$current_filter_style = 0;  // as well as the style of the rows. later they're passed to js to keep everything
$dest_counter = 0;          // consistent
$current_dest_style = 0;
?>
<script type="text/javascript">
   function make_folder_select (id)  // js func to make a folder select with all the correct folder names which have to be retreived server side
   {
      return "<select id='folders-"+id+"' name='dest-folder[]' style='display:none'>" +
      <?php $rc = rcmail::get_instance(); $rc->imap_init(true);
            foreach($rc->imap->list_unsubscribed() as $folder)
            {
               if (strlen($folder) > 40) 
                  $nicefolder = substr($folder, 0, 15) . " .. " . substr($folder, -15, strlen($folder));
               else
                  $nicefolder = $folder;
               echo ("\"<option value='$folder'>$nicefolder</option>\"+");
            }?>
      + "</select>";
   }
</script>
<form method="post" onSubmit= "return check_filter_form()"action="?_task=dummy&_action=plugin.webtoolsfilter">
   <input type="hidden" name="filter-path" class="ui-button ui-widget ui-state-default ui-corner-all"  value='<?=$_POST['path']?>'>
   <div style="margin-bottom:10px">
      <b>Filter Name:</b> <?=$_POST['name']?>
      <input class="ui-button ui-widget ui-corner-all" type="hidden" name="filter-name" size=20 disabled='true' value='<?=$_POST['name']?>'>
   </div>
   <div style="border:1px solid black;padding:0px;margin-bottom:15px;" id="filters">
      <div style="margin:15px;margin-bottom:15px;font-size:16px;" id="filters-title">
         &nbsp;<b>For incoming mail:</b>
      </div>
      <div class="tablestyle" id="filter-logic-divider">
         <input type="radio" name="filter-logic-all" value="and" <?=($config['filter']['logic']=='and'?"checked":"")?>>
         matching all of the following rules
         <input type="radio" name="filter-logic-all" value="or" <?=($config['filter']['logic']=='or'?"checked":"")?>>
         matching any of the following rules
         <!--<input type="radio" name="filter-logic-all" value="all" <? #($config['filter']['logic']=='all'?"checked":"")?>>
         all messages<br><br>!-->
         <?php 
		 foreach ($config['rules'] as $rule) make_rule ($rule); // output all of the old rules ?>
      </div>
      <!--<input class="ui-button ui-widget ui-state-default ui-corner-all" type="button" value="+" onclick="add_filter()">-->
      <div style="margin:10px 10px 10px 18px; width:32px" class="ui-button ui-widget ui-state-default ui-corner-all">
         <a href="#" onclick="add_filter()"><img  src="plugins/webtools/webtools/filter/img/list-add.png"></a>
      </div>
   </div>
   <div style="border:1px solid black;padding:0px;" id="destination-actions">
      <div style="margin:15px;margin-bottom:15px;font-size:16px" id="destination-actions-title">
         <!--<input class="ui-button ui-widget ui-state-default ui-corner-all"  type="button" value="+" onclick="add_dest()">-->
         &nbsp;<b>Execute the following actions:</b>
      </div>
      <div id="destination-actions-list" class="tablestyle">
         <?php foreach ($config['dests'] as $dest) make_dest ($dest); //output all of the old destination things ?>
      </div>
      <div style="margin:10px 10px 10px 18px; width:32px" class="ui-button ui-widget ui-state-default ui-corner-all">
         <a href="#" onclick= "add_dest()"><img  src="plugins/webtools/webtools/filter/img/list-add.png"></a>
      </div>
   </div>
<p><input class="ui-button ui-widget ui-state-default ui-corner-all"  style="font-size:16px;" type='submit' name='page' value='Submit'> <input class="ui-button ui-widget ui-state-default ui-corner-all" style="font-size:16px;"  type='submit' name='page' value='Go Back'></p>
<?=dumpjsvars() // dump vars to javascript ?>
</form>
<?php 
}
?>
</div>
<div id="webtools-instructions" class="webtools-accordion">
   <div>
      <h3 class="roundbox helpbox"><a id="instr-header" href="#">Help</a></h3>
      <div id="instr-content" class="helptext">
          <?=$rcmail->config->get('filtering_help')?>
      </div>
   </div>
</div>

