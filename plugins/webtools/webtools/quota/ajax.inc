<?php

require_once('plugins/webtools/webtools/functions.php');

function quota_handle_load ()
{
   $rcmail = rcmail::get_instance();

   $userinfo = get_userinfo();
   $HOME = $userinfo['home'];
   $USERNAME = $userinfo['username'];
   $RUNAS_CMD = $rcmail->config->get('RUNAS_CMD');
   $MF = $rcmail->config->get('MF');
   $STF = $rcmail->config->get('STF');
   $USERINFO = $rcmail->config->get('USERINFO');


   // begin generating quota data 
   $output = '';
   $spaces = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

   $user_cmd = "$USERINFO";
   $target_cmd = "$RUNAS_CMD $USERNAME $user_cmd";
   unset($result);
   exec($target_cmd, $result, $status);
   $home = $result[0];
   $realhome = realpath($home);

   $user_cmd = 'check-criteria';
   $target_cmd = "$RUNAS_CMD $USERNAME $user_cmd";
   unset($result);
   exec($target_cmd, $result, $status);

   $count = 0;
   foreach ($result as $val) {
      $count++;
      if (ereg("^NOTE", $val)) {
         continue;
      }
      if (ereg("/WARNING/", $val)) {
         $output .= $spaces. '<span style="color: #DC5757; font-weight: bold;">' . $val. '</span><br />';
         continue;
      }
      if (preg_match("/^\s+([0-9]+.*[K|M|G|T]{1}B\s+\(\s*[0-9]{1,3}\%\))(.*$)/", $val, $myarr)) {
         $output .= $spaces . '<b>' . $myarr[1] . '</b>' . $myarr[2] . '<br />';
         continue;
      }
      if (preg_match("/^\s+([0-9]+.*[K|M|G|T]{1}B)(.*$)/", $val, $myarr)) {
         $output .= $spaces . '<b>' . $myarr[1] . '</b>' . $myarr[2] . '<br />';
         continue;
      }
      if ($count == 2) {
         $output .= substr($val, 0, -1) . ',';
         continue;
      }
      $output .= $val . '<br />';
   }

   console($output);
   $rcmail->output->command('plugin.webtools.quota.load.response', array('html' => $output));
   // end generating quota data
}

?>
