<?php

require_once('plugins/webtools/webtools/functions.php');

$rcmail = rcmail::get_instance();
$HOME = $rcmail->config->get('HOME');
$USERNAME = $rcmail->config->get('USERNAME');
$RUNAS_CMD = $rcmail->config->get('RUNAS_CMD');
$MF = $rcmail->config->get('MF');
$STF = $rcmail->config->get('STF');
$USERINFO = $rcmail->config->get('USERINFO');

$subhead = $rcmail->config->get('hush_subhead');
$enable_msg = $rcmail->config->get('hush_enable_msg');
$disable_msg = $rcmail->config->get('hush_disable_msg');

?>

<h2>Hush Quota</h2>

<div id="webtools-subhead">
    <?=$subhead?>
</div>

<?php

$result = NULL;
$hush_enabled = have_file("Maildir/RU-HUSH-QUOTA", $result);

$button = '';
$text = '';

if ($hush_enabled) {
    $button = '<button class="webtools-button ui-state-default ui-corner-all" type="button" onclick="hushquota_post(true); return false;">Disable</button>';
    $text = $disable_msg;
}
else {
    $button = '<button class="webtools-button ui-state-default ui-corner-all" type="button" onclick="hushquota_post(); return false;">Enable</button>';
    $text = $enable_msg; 
}

?>

<script type="text/javascript" src="plugins/webtools/webtools/hushquota/hushquota_post.js"></script>

<div id="result"></div>

<div id="webtools-innerbox" class="ui-widget ui-widget-content ui-corner-all">
    <div id="text" style="margin-top: 1em; color: #BD0E2E;">
        <?=$text?>
    </div>
    <form style="margin: 20px 0;">
        <div id="button">
            <?=$button?>
        </div>
    </form>
</div>

<div id="webtools-instructions" class="webtools-accordion">
    <div>
        <h3><a id="instr-header" href="#">Help</a></h3>
        <div id="instr-content">
            <?=$rcmail->config->get('hush_help')?>
        </div>
    </div>
</div>
