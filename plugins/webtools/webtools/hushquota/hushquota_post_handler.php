<?php

require_once('plugins/webtools/webtools/functions.php');

function hushquota_post_handler() {

    $rcmail = rcmail::get_instance();
	$userinfo = get_userinfo();
    $HOME = $userinfo['home'];
    $USERNAME = $userinfo['username'];
    $RUNAS_CMD = $rcmail->config->get('RUNAS_CMD');
    $MF = $rcmail->config->get('MF');
    $STF = $rcmail->config->get('STF');
    $USERINFO = $rcmail->config->get('USERINFO');

    $response = array('message' => '', 'new_button' => '', 'new_text' => '', 'update_content' => 'yes');

    $disable = getvar('disable');

    if (!$disable) {

        $success = false;

        /* Create the file (w/ timestamp) we will check for in maildroprc */
        $user_cmd = "$MF Maildir/RU-HUSH-QUOTA";
        $exec_target = "$RUNAS_CMD $USERNAME $user_cmd";
        unipipe_exec($exec_target, date('Ymd-U')."\n");

        /* Verify the file was created (stupid way of doing it...) */
        $user_cmd = "$STF Maildir/RU-HUSH-QUOTA";
        $exec_target = "$RUNAS_CMD $USERNAME $user_cmd";
        exec($exec_target, $result, $status);
        if ($status == 0) {
            $success = true;
        }

        if ($success) {
            $response['message'] = '<div id="webtools-response" class="ui-state-highlight ui-corner-all">
                                        Thank you '.$USERNAME.',<br /><br />
                                        You will no longer receive mail when you are approaching your account\'s quota.
                                    </div>';
            $response['new_button'] = '<button class="webtools-button ui-state-default ui-corner-all" type="button" onclick="hushquota_post(true); return false;">Disable</button>';
            $response['new_text'] = 'You currently have "hush" enabled. Click "Disable" to START receiving such messages again.';
        } else {
            $response['message'] = '<div id="webtools-response" class="ui-state-error ui-corner-all">
                                        Sorry '.$USERNAME.',<br /><br />
                                        Failed to enable hush quota.
                                    </div>';
            $response['update_content'] = 'no';
        }

    } 
    else {
        
        $found = false;
        $success = false;

        if (have_file("Maildir/RU-HUSH-QUOTA",$result)) {
            $found = true;
            $file = "Maildir/RU-HUSH-QUOTA";
            $user_cmd = "find $HOME -maxdepth 2 -path $HOME/$file -exec rm -f '{}' \;";
            $exec_target = "$RUNAS_CMD $USERNAME $user_cmd";
            exec($exec_target, $result, $status);
            if ($status == 0) {
                $success = true;
            }
        }

        if ($found && $success) {
            $response['message'] = '<div id="webtools-response" class="ui-state-highlight ui-corner-all">
                                        Thank you '.$USERNAME.',<br /><br />
                                        You will now receive mail when you are approaching your account\'s quota.
                                    </div>';
            $response['new_button'] = '<button class="webtools-button ui-state-default ui-corner-all" type="button" onclick="hushquota_post(); return false;">Enable</button>';
            $response['new_text'] = 'Click "Enable" to STOP receiving such messages.';
        } 
        else {
            if (!$found) {
                $msg = "No reason to disable hushquota since it appears you never enabled it.<br />";
            } 
            else {
                $msg = "Tried to disable hushquota, but failed. <br />";
            }
                $response['message'] = '<div id="webtools-response" class="ui-state-error ui-corner-all">
                                            Sorry '.$USERNAME.',<br /><br />'.$msg.'
                                        </div>';
                $response['update_content'] = 'no';
        }
    }

    $rcmail->output->command('plugin.hushquota-post-callback', $response);
}
