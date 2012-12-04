<?php

/* functions used by both the forward and vaction webtools */

require_once('plugins/webtools/webtools/functions.php');

function handle_error($err) {
  $msg = '<strong>Error: ';
  switch($err){
    case 1:
      $msg .= 'Your username is null or contains invalid characters!';
      break;
    case 2:
      $msg .= 'Your virtual username is null or contains invalid characters!';
      break;
    case 3:
      $msg .= 'Not allowed to have virtual username root!';
      break;
    case 4:
      $msg .= 'That email address does not exist or is incorrect!';
      break;
    case 5:
      $msg .= 'That forwarding address does not exist or is incorrect!';
      break;
    case 6:
      $msg .= 'That virtual user already exists!';
      break;
    case 7:
      $msg .= 'No input items were set!';
      break;
    case 8:
      $msg .= 'All input items were not set!';
      break;
    case 9:
      $msg .= 'Forgot to setup the Mail Transfer Agent in the config file!';
      break;
    case 10:
      $msg .= 'That Mail Transfer Agent is unrecognizable to webtools!';
      break;
    case 11:
      $msg .= 'The forwarding address is the same as your current email address!';
      break;
    default:
      $msg .= 'Unknown error!';
      break;
  }
  $msg .= ' Please try again!</strong';
  return $msg;
}

function getAliases() {
  
    $metaFileContents = NULL;

    $rcmail = rcmail::get_instance();
	$userinfo = get_userinfo();
    $USERNAME = $userinfo['username'];
    $HOME = $userinfo['home'];
    $RUNAS_CMD = $rcmail->config->get('RUNAS_CMD');

    $user_cmd = "find $HOME -maxdepth 1 -name $USERNAME -exec cat '{}' \;";
    $exec_target = "$RUNAS_CMD alias $user_cmd";
    unset($result);
    
    exec($exec_target, $result,$status);
    
    if ($status != 0) {
        handle_error(0);
    }
    elseif( !empty($result)) {
        $metaFileContents = $result;
    }

    return $metaFileContents;
}

/* The vacation mailfilter rule to be inserted */
function getVacationRule($subject='Away on Vacation', $vToggle=NULL) {
  
    $rcmail = rcmail::get_instance();
	$userinfo = get_userinfo();
    $USERNAME = $userinfo['username'];
    $HOME = $userinfo['home'];
    $CLUSTER = $rcmail->config->get('CLUSTER');
    $insert = NULL;
    $aliasArr = NULL;

    $insert .= "if( (!( /^From: .*mailer-daemon/ \\\n || /^From: .*postmaster/ \\\n || /^List-Post:/ \\\n || /^List-Help:/ \\\n || /^List-Subscribe:/ \\\n || /^List-Unsubscribe:/ \\\n || /^Precedence:/ \\\n || /^AutoSubmitted:/ ) \\\n || ( /^Precedence: first-class/ )) \\\n && ( /^To: .*$USERNAME/ \\\n || /^Cc: .*$USERNAME/ ) )\n{\n";
    if( isset($vToggle) ) {
        $insert .= "$vToggle\n";
    }
    else {
        $userinfo_arr = posix_getpwnam($USERNAME);
        $tmp_arr = explode(',', $userinfo_arr['gecos']);
        $personal = $tmp_arr[0];
        $mailbox = $USERNAME;
	$host = $CLUSTER.'.rutgers.edu';
        if (!eregi("[\^\(\)<>\@\.\\,;:']+", $personal)) {
            if (($pos = strpos($personal, '&')) !== false) {
                $lastname = ucfirst(strtolower($mailbox));
                $personal = str_replace('&', $lastname, $personal);
            }
            $address_str = imap_rfc822_write_address($mailbox, $host, $personal);
        } 
        else {
            $address_str = $mailbox . '@' . $host;
        }


         $rcidentity_array = ($rcmail->user->get_identity());
         $email = $rcidentity_array['email'];
         $displayname = $rcidentity_array['name'];
         $address_str = "$displayname <$email>";


        $insert .= " cc \"| /usr/local/bin/mailbot -t \$HOME/.vacation.msg -s '$subject' -d autoresponsedb -D 14 -A 'From: $address_str' /usr/lib/sendmail -t -f ''\"\n";
    }
    $insert .= "}\n";

    return $insert;
}

function is_newFormatLocalCopy($line) {
  // 'cc' means a local copy will be kept 
  if( preg_match("/^cc.*/",$line) ) {
    return TRUE;
  }
  return FALSE;
}

function is_oldFormatLocalCopy($line) {
  // Found maildrop being called
  if( preg_match("/^\|.*maildrop.*$/", $line) ) {
    return TRUE;
  }
  return FALSE;
}

// Covers empty and white-space only lines?
function is_blankline($line) {
  // Found a blank line 
  if(preg_match("/^\s*$/", $line)) {
    return TRUE;
  }
  return FALSE;
}

function is_comment($line) {
  // Found a comment
  if (preg_match("/^#.*$/", $line)) {
    return TRUE;
  }
  return FALSE;
}

function is_comment_forward($line) {
  // Found a forward comment
  if(preg_match("/^\s*#.*$/", $line)) {
    return TRUE;
  }
  return FALSE;
}

function is_continue($line) {
  // Continues a logical line 
  if (preg_match("/^\s+.*$/", $line)) {
    return TRUE;
  }
  return FALSE;
}

function is_oldFormatAddress($line, &$addresses) {
  // Found an address with an &
  if (preg_match("/^&(([a-zA-z0-9_-]|\.|@)+)\s*$/", $line, $tmp_Arr)) {
    $addresses[] = $tmp_Arr[1];
    return TRUE;
  }
  // Found an address w/out an &
  elseif (preg_match("/^(([a-zA-z0-9_-]|\.|@)+)\s*$/", $line, $tmp_Arr)) {
    $addresses[] = $tmp_Arr[1];
    return TRUE;
  }
  return FALSE;
}

function is_oldFormatAddress_forward($line, &$addresses) {
  // Found an address 
  if (preg_match("/^(([a-zA-z0-9_-]|\.|@)+)\s*$/", $line, $tmp_Arr)) {
    $addresses[] = $tmp_Arr[1];
    return TRUE;
  }
  return FALSE;
}

function is_newFormatAddress($line, &$addresses) {
  // Found an address
  if (preg_match("/^(to|cc)\s+\"?\!(([a-zA-z0-9_-]|\.|@|\s+)+)\"?\s*$/", $line, $tmp_Arr) ) {
    $addresses = preg_split("/\s+/",$tmp_Arr[2]);
    return TRUE;
  }
  return FALSE;
}

function is_oldFormatVacation($line) {
  // Found a vacation program being called
  if( preg_match("/^\|.*vacation.*$/", $line) ) {
    return TRUE;
  }
  return FALSE;
}

function is_newFormatVacation($line,&$subject) {
  if( preg_match("/.*\|.*mailbot.*-s\s+(.*)-d.*$/",$line,$tmp_Arr) ) {
    $subject = trim($tmp_Arr[1]);
    $subject = substr($tmp_Arr[1],1,strlen($subject)-2);
    return TRUE;
  }
  return FALSE;
}
?>
