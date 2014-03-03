<?php

require_once('plugins/webtools/webtools/functions.php'); 

$rcmail = rcmail::get_instance();
$userinfo = get_userinfo();
$USERNAME = $userinfo['username'];
$HOME = $userinfo['home'];
$RUNAS_CMD = $rcmail->config->get('RUNAS_CMD');
$STF = $rcmail->config->get('STF');
$RESTORE = $rcmail->config->get('RESTORE');

$subhead = $rcmail->config->get('restore_subhead');

?>

<h2>Restore Email</h2>
<div id="webtools-subhead">
    <?=$subhead?>
</div>

<?php

/* This is where we decided the old snapshots will be. Brylon needs to fix this and make it configurable. */
//$oldhome = preg_replace('|/rci/u|', '/oldrci/u', $HOME);
$oldhome = $HOME;

$exec_target = "$RUNAS_CMD $USERNAME $STF -A $oldhome/.snapshot";
unset($result);
exec($exec_target, $result, $status);

/* Does the old .snapshot directory still exist? */
if ($status == 0 && strcmp($result[0], 'type=directory') == 0) {

    $user_cmd = "find $oldhome/.snapshot/ -maxdepth 0 -type d -exec ls -ult --time-style='+%b %d %Y %H:%M' '{}' \;";
    $exec_target = "$RUNAS_CMD $USERNAME $user_cmd";

    unset($result2);
    exec($exec_target, $result2, $status);

    $result2=array_unique($result2);
   
    /* List old .snapshot/{nightly.*, weekly.*} */
    if ($status == 0) {
        $options = NULL;
        foreach ($result2 as $_ => $val) {
            $tmp = preg_split("/\s+/", $val);
            if (count($tmp) < 9) {
                continue;
            }
            $date = "$tmp[5] $tmp[6] $tmp[7]";
	    $today = date("M d Y");
	    /* Convert time to 12-hour format: */
	    $hr_min = DATE("g:i A", STRTOTIME($tmp[8]));
            $dir = $tmp[9];
            if ((eregi('night', $dir) == 1) || (eregi('week', $dir) == 1) || (eregi('hour', $dir) == 1)) {
                if (eregi('hour', $dir) == 1) {
                    $options .= "<option value=\"$oldhome/.snapshot/$dir/Maildir\">$date $hr_min</option>";
                }
		elseif ($today == $date) {
		  $options .= "<option value=\"$oldhome/.snapshot/$dir/Maildir\">Today $hr_min</option>";
		}
                else {
                    $options .= "<option value=\"$oldhome/.snapshot/$dir/Maildir\">$date</option>";
                }
            }
        }
    }
}

?>
<!-- mod by orcan: this was        width: 15em;-->
<style type="text/css">
    .webtools-select {
       width: 30em;
    }
</style>
<script type="text/javascript" src="plugins/webtools/webtools/restore/restore_post.js"></script>

<div id="result"></div>
    
<div id="webtools-innerbox" class="ui-widget ui-widget-content ui-corner-all">
    <div style="margin-left: 10%; margin-right: 10%;">
        <form id="date_form" onsubmit="restore_post(); return false;">
            <label for="path" style="display: block; margin-bottom: 10px;">
                Restore mail from
            </label>
            <select id="pathselect" name="path" class="webtools-select">
                <?=$options?>
            </select>
            <button type="submit" class="roundbox ui-state-default ui-corner-all webtools-button" style="margin-top: 30px; width: 14em;">Show folders</button>
        </form>
   </div>
     <div id="folders-select" style="margin-left: 10%; margin-right: 10%;"></div>
</div>

<div id="webtools-instructions" class="webtools-accordion">
    <div>
        <h3 class="roundbox helpbox"><a id="instr-header" href="#">Help</a></h3>
        <div id="instr-content" class="helptext">
        	<?=$rcmail->config->get('restore_help')?>
	</div>
    </div>
</div>
