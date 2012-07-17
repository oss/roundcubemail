<?php

require_once('plugins/webtools/webtools/functions.php');

// Roundcube-style variables
$rcmail = rcmail::get_instance();

$userinfo = get_userinfo();
$USERNAME = $userinfo['username'];
$HOME = $userinfo['home'];
$RUNAS_CMD = $rcmail->config->get('RUNAS_CMD');
$MF = $rcmail->config->get('MF');

$subhead = $rcmail->config->get('vacation_subhead');
?>

<h2>Vacation</h2>
<div id="webtools-subhead">
    <?=$subhead?>
</div>

<?php

// Initialize variables
$oldForwardFileExist = FALSE;
$oldForwardFileConvert = FALSE;
$oldForwardFileResult = NULL;
$oldVacationMsg = NULL;
$oldForwardAddresses = NULL;
$oldFormatAddr_Arr = NULL;

$newForwardFileExist = FALSE;
$newForwardFileResult = NULL;
$newFormatVacation = NULL;
$newFormatAddr_Arr = NULL;

$newVacationFileExist = FALSE;
$newVacationFileResult = NULL;

$vacationExist = FALSE;
$vacationEnabled = FALSE;
$localCopyExist = FALSE;
$vacationMsg = NULL;
$vacMsgLines = NULL;
$textbox = NULL;
$hiddenInputStatic = '';
$hiddenInputTemp = '';
$result = NULL;
$type = NULL;
$subject = NULL;
$subjects = '';

$dynamicFormContent = '';

if (have_file('.forward', $result)) {
    $oldForwardFileExist = TRUE;
    $oldForwardFileResult = $result;
    $type = 'f';
}
elseif (have_file('.qmail', $result)) {
    $oldForwardFileExist = TRUE;
    $oldForwardFileResult = $result;
    $type = 'q';
}
    
// Do they have a mailfilter-vacation file?
if (have_file('Maildir/mailfilter-forward', $result)) {
    $newVacationFileExist = TRUE;
    foreach ($newVacationFileResult as $line) {
        if (is_newFormatVacation($line, $subject)) {
            $vacationEnabled = TRUE;
            continue;
        }
    }
 //   $hiddenInputTemp .= '<input type="hidden" name="newVacationFileExist" value="1" />';
}

//Do they have a mailfilter-forward file?
if (have_file('Maildir/mailfilter-forward', $result)) {
    $newForwardFileExist = TRUE;
    $newForwardFileResult = $result;
    foreach ($newForwardFileResult as $line) {
        if (is_newFormatLocalCopy($line)) {
            $hiddenInputStatic .= '<input type="hidden" name="localCopy" value="1" />';
        }
        if (is_newFormatAddress($line, $newFormatAddr_Arr)) {
            continue;
        }
        if (is_newFormatVacation($line, $subject)) {
            $vacationEnabled = TRUE;
            continue;
        }
    } 
    $hiddenInputStatic .= '<input type="hidden" name="newForwardFileExist" value="1" />';
}

// Show the subject box
if (empty($subject)) {
    $subject = 'Away on vacation';
}

// Is the old forward file one we recognize (no weird procmail stuff, etc)
if ($oldForwardFileExist) {
    $oldForwardFileConvert = TRUE;
    $oldVacationMsg = '<strong>You have a vacation file that is no longer being supported!</strong><br />';
    switch ($type) {
        case 'q':
            $firstLine = TRUE;
            foreach ($oldForwardFileResult as $line) {
                if ($firstLine) {
                    $firstLine = FALSE;
                    if (preg_match("/^\s*$/", $line)) {
                        $oldForwardFileConvert = FALSE;
                        break;
                    }
                }
                if (is_blankline($line) || is_comment($line)) {
                    continue;
                }
                if (is_oldFormatVacation($line)) {
                    $vacationExist = TRUE;
                    continue;
                }
                if (is_oldFormatLocalCopy($line)) {
                    $localCopyExist = TRUE;
                    $hiddenInputStatic .= '<input type="hidden" name="localCopy" value="1" />';
                    continue;
                }
                // Found something bizarre
                if (!is_oldFormatAddress($line, $oldFormatAddr_Arr)) {
                    $oldForwardFileConvert = FALSE;
                    break;
                }
            }
            break;
        case 'f':
        foreach ($oldForwardFileResult as $line) {
            if (is_blankline($line) || is_comment_forward($line)) {
                continue;
            }
            if (is_oldFormatVacation($line)) {
                $vacationExist = TRUE;
                continue;
            }
            if (is_oldFormatLocalCopy($line)) {
                $localCopyExist = TRUE;
                $hiddenInputStatic .= '<input type="hidden" name="localCopy" value="1" />';
                continue;
            }
            // Found something bizarre
            if (!is_oldFormatAddress_forward($line, $oldFormatAddr_Arr)) {
                $oldForwardFileConvert = FALSE;
                break;
            }
        }
        break;
   }
}

// Are we converting?
if ($oldForwardFileConvert) {
    $oldVacationMsg .= '<strong>Submitting this form will convert the old format to the new supported format!</strong><br />';
    // Let the user know where their mail is stored,forwarded, and/or vacationed
    if ($vacationExist) {
        $oldVacationMsg .= 'You have vacation enabled, please complete the form below to continue using the vacation feature.<br />';
    }
    if (is_array($oldFormatAddr_Arr)) {
        $localCopyText = $localCopyExist ? ', keeping a local copy' : '';
        $oldVacationMsg .= "You are forwarding your mail to the addresses below$localCopyText. 
                            If you would like to edit this behavior, please use the <i>forward</i> webtool.<br />";
        $oldFormatAddr_S = base64_encode(serialize($oldFormatAddr_Arr));
        $hiddenInputStatic .= '<input type="hidden" name="oldFormatAddr_S" value="'.$oldFormatAddr_S.'" />';
        $oldForwardAddresses = '<ul>';
        foreach ($oldFormatAddr_Arr as $address) {
            $oldForwardAddresses .= "<li>$address</li>";
        }
        $oldForwardAddresses .= '</ul>';
    }
    $hiddenInputTemp .= '<input type="hidden" name="forwardType" value="'.$type.'" />';
}
elseif ($oldForwardFileExist) {
    $oldVacationMsg .= '<strong>However, we cannot convert the file to the new supported file format 
                        since it seems your file has some extra content we can\'t understand! Submitting 
                        the form below will add and enable vacation functionality to a file in the new 
                        supported format!</strong><br>';
}

// Pass forward addresses from newForward file as hidden input 
if(isset($newFormatAddr_Arr)) {
    $newFormatAddr_S = base64_encode(serialize($newFormatAddr_Arr));
    $hiddenInputStatic .= '<input type="hidden" name="newFormatAddr_S" value="'.$newFormatAddr_S.'" />';
}

if ($vacationEnabled) {
    $dynamicFormContent .= 'You currently have vacation auto-response enabled. <a href="#" onclick="vacation_post(true); return false;">Disable vacation</a><br /><br />';
}

if ($newForwardFileExist || $oldForwardFileExist || $newVacationFileExist) {
    $dynamicFormContent .= $oldVacationMsg . $oldForwardAddresses . $newForwardMsg;
}

$textbox = array('Away on vacation', 'Not available', 'Out of town', 'Will return next week', 'Auto Reply', 'New Address', 'I have moved', 'I have graduated', 'Out of the Office');
for($i = 0; $i < count($textbox); $i++) {
    $selected = ($textbox[$i] == $subject) ? 'selected="selected"' : '';
    $subjects .= "<option value=\"$textbox[$i]\" $selected>$textbox[$i]</option>";
}

if(have_file('.vacation.msg', $result)) {
    $vacationMsg = 'You have the option to keep the message you already have (provided below) or edit it:';
    $vacMsgLines = implode("\r\n", $result);
    $vacMsgLines = rtrim($vacMsgLines);
	//make vacation message safe for display.
    $vacMsgLines = Q($vacMsgLines);
}
else {
    $vacationMsg = 'You have the option to keep the default message (provided below) or edit it:';
    $vacMsgLines = "I will not be reading my mail for a while.\r\nYour mail will be read when I return.\n";
}

// This is input that will not last after a post request
$dynamicFormContent .= $hiddenInputTemp;

?>

<script type="text/javascript" src="plugins/webtools/webtools/vacation/vacation_post.js"></script>

<style type="text/css">
    .webtools-select {
        width: 17em;
    }
</style>

<div id="result"></div>

<div id="webtools-innerbox" class="ui-widget ui-widget-content ui-corner-all">
    <form id="webtools-form" onsubmit="vacation_post(); return false;">
        <?=$hiddenInputStatic?>
        <span id="dynamic_content">
            <?=$dynamicFormContent?>
        </span>
        <label for="subject" style="display: block; margin-bottom: 5px;">Subject</label>
        <select name="subject" class="webtools-select"><?=$subjects?></select>
        <div id="vacation_msg" style="margin-top: 20px; font-size: medium;">
            <?=$vacationMsg?>
        </div>
        <textarea class="ui-corner-all-4px" style="display: block; margin: 20px 0;" name="message" cols="60" rows="15"><?=$vacMsgLines?></textarea>
        <button class="webtools-button ui-state-default ui-corner-all" type="submit">Submit</button>
        <button class="webtools-button ui-state-default ui-corner-all" type="reset">Reset</button>
    </form>
</div>

<div id="webtools-instructions" class="webtools-accordion">
    <div>
        <h3><a id="instr-header" href="#">Help</a></h3>
        <div id="instr-content">
		<?= $rcmail->config->get('vacation_help');?>
        </div>
    </div>
</div>

