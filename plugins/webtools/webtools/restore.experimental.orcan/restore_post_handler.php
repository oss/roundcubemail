<?php

function restore_post_handler() {

    $rcmail = rcmail::get_instance();

    $USERNAME = $rcmail->config->get('USERNAME');
    $HOME = $rcmail->config->get('HOME');
    $RUNAS_CMD = $rcmail->config->get('RUNAS_CMD');
    $MF = $rcmail->config->get('MF');

    $response = array('message' => '', 'folder_select' => '', 'update_folders' => 'no');
    
    $restore = getvar('restore'); 
    $path = getvar('path');

    if (!$restore) {
        
        $path = getvar('path');
        $date = getvar('date');

        if (isset($path)) {
            $user_cmd = "find $path -maxdepth 0 -type d -exec ls -a '{}' \;";
            $exec_target = "$RUNAS_CMD $USERNAME $user_cmd";
            unset($result);
            exec($exec_target, $result, $status);

            $selects = NULL;
            for ($i = 0, $numF = count($result); $i < $numF; $i++) {
                if (strcmp($result[$i], '.') != 0 && strcmp($result[$i], '..') != 0) {
                    if (strcmp(substr($result[$i], 0, 1), '.') == 0) {
                        $entry = substr($result[$i], 1);
                        $entry = preg_replace("/\./", "/", $entry);
                        if (strcmp($entry, 'RESTORE') == 0) {
                            continue;
                        }
			$cutat = 10;
			$max2display = 25;
			if(strlen($entry) > $max2display)
			  {
			    $entrycut = substr($entry, 0, $cutat) . " ... " . substr($entry, -$cutat);
			    $selects .= '<option value="' . $entry . '">' . $entrycut . '</option>';
			  }
			else
			  $selects .= '<option value="'.$entry.'">'.$entry.'</option>';
                    }
                }
            }

            $response['folder_select'] = '<form id="folder_form" onsubmit="restore_post(true); return false;">
                                              <label for="folder" style="display: block; margin-bottom: 10px; color: #BD0E2E;">
                                                  '.$date.'
                                              </label>
                                              <select name="folder" id="foldermenu" class="webtools-select">
                                                  <option value="INBOX">INBOX</option>'
                                                  .$selects.
                                              '</select>
                                               <input type="hidden" name="path" value="'.$path.'" />
                                               <input type="hidden" name="restore" value="1" />
                                               <button type="submit" class="ui-state-default ui-corner-all webtools-button" style="margin-top: 30px; width: 14em;">
                                                    Restore folder
                                               </button>
                                               <button type="button" class="ui-state-default ui-corner-all webtools-button" style="margin-top: 30px; width: 14em;" onclick="show_dates(); return false;">
                                                   Change date
                                               </button>
                                          </form>';
            
            $response['update_folders'] = 'yes';
        } 
        else {
            $response['message'] = '<div id="webtools-response" class="ui-state-error ui-corner-all">
                                        Error: "path" parameter missing!
                                    </div>';
        }
    }
    else {

        $RESTORE = $rcmail->config->get('RESTORE');

        $folder = getvar('folder');

        $success = TRUE;
        $msg = '';

        if (isset($path) && isset($folder)) {
            if (strcmp($folder, 'INBOX') != 0) {
                $newfolder = ".$folder";
                $newfolder = preg_replace("/\//", ".", $newfolder);
            } 
            else {
                $newfolder = NULL;
            }

            if (strcmp($folder, 'INBOX') != 0) {
                $newfolder = ".$folder";
                $newfolder = preg_replace("/\//", ".", $newfolder);
           	//$newfolder = preg_replace("/ /", "\\ ", $newfolder); 
	   } else {
                $newfolder = NULL;
            }

            // Handle any folders that contain spaces
            $snapshot_dir = escapeshellarg("$path/$newfolder");
            $user_cmd = "$RESTORE $snapshot_dir";
            $exec_target = "$RUNAS_CMD $USERNAME $user_cmd";
            unset($result_arr);
            exec($exec_target, $result_arr, $status);
            $result_str = implode($result_arr);
            if (strpos($result_str, 'OK:') !== false) {
                $success = TRUE;
		$rcmail->imap_init(true);
		$imap = $rcmail->imap;
		$imap->subscribe('RESTORE');
                $msg = 'Your mail folder "'.$folder.'" has now been restored. Use your favorite mail program to 
                        look for the new folder named "RESTORE", or just click on the Rutgers logo above to view it in webmail. You are now subscribed to the new folder.';
            } 
            else {
                $success = FALSE;
                $msg = 'The folder "'.$folder.'" could not be restored.';
            }
        } 
        else {
            $msg = 'Missing parameters... Please try again.';
            $success = FALSE;
        }
    
        if ($success) {
            $response['message'] = '<div id="webtools-response" class="ui-state-highlight ui-corner-all">
                                        Thank you '.$USERNAME.',<br /><br />'.$msg.'
                                    </div>';
        } 
        else {
           $response['message'] = '<div id="webtools-response" class="ui-state-error ui-corner-all">
                                       Sorry '.$USERNAME.',<br /><br />'.$msg.'
                                   </div>'; 
        }
    }
    
    $rcmail->output->command('plugin.restore-post-callback', $response);

}
