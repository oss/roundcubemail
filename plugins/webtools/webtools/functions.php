<?php

/* functions.php */

// Does the file filename exist
// Only works for relative paths
function have_file($filename, &$contents){

	$rcmail = rcmail::get_instance();
	$userinfo = get_userinfo();
	$USERNAME = $userinfo['username'];
	$HOME = $userinfo['home'];
	$RUNAS_CMD = $rcmail->config->get('RUNAS_CMD');

	$user_cmd = "find $HOME/$filename -exec cat '{}' \;";
	$target_cmd = "$RUNAS_CMD $USERNAME $user_cmd";
	unset($result);
	exec($target_cmd, $result, $status);
	if (($status != 0) || empty($result)) {
		return FALSE;
	}
	$contents = $result;
	return TRUE;
}

function host_valid($mailhost){
	$host = $mailhost;
	// Check for trailing ., we want one so DNS does not append local domain
	$numchar = strlen($mailhost);
	$dotpos = strrpos($mailhost,'.');
	if($dotpos != $numchar - 1){
		$mailhost .= '.';
	}
	$hasMX = getmxrr($host,$_);
	$hostip = gethostbyname($host);
	//console ("host_valid got mx " . print_r($hasMX, true) . " and hostip $hostip for host $host");
	if( !$hasMX && $hostip == $host ){
		return FALSE;
	} 
	return TRUE;
}

/* Is the email address valid? */
function emailAddressValid($str,$errnum){
	$result = TRUE;
	$emailValid=email_valid($str);
	if(!$emailValid){
		$result = handle_error($errnum);
	}
	$mailhost=explode('@',$str);
	$mailhost = $mailhost[1];
	$hostValid = host_valid($mailhost);
	if(!$hostValid){
		$result = handle_error($errnum);
	}
	return $result;
}

function getVar($var){
	if( isset($_POST[$var]) ){
		return $_POST[$var];
	}
	else{
		return NULL;
	}
}

//brylon - Validate email
function email_valid($email){
	if(!eregi(  "^" . "[a-z0-9]+([_\\.-][a-z0-9]+)*" . "@" . "([a-z0-9]+([\.-][a-z0-9]+)*)+" . "\\.[a-z]{2,}" . "$", $email)) {
		return false;
	}		
	return true;
}

//brylon - Validate username chars
function is_validuser($username){
	$validuser=true;
	if( !(is_null($username)) && ($username != "") ){
		for($i=0; $i<strlen($username); $i++){
			if($validuser == true){
				$ch = substr($username,$i,1);
				$ch = ord($ch);
				$validuser = is_alphanumeric($ch);
			}
			else{
				break;
			}
		}
	}
	else{
		$validuser = false;
	}
	return $validuser;
}


//brylon - Validate certain strings (ie, username) are alphanumeric
function is_alphanumeric($input){
	if( (($input >= ord('a')) && ($input <= ord('z'))) || (($input >= ord('A')) && ($input <= ord('Z'))) || (($input >= ord('0')) && ($input <= ord('9'))) || $input == ord('-') || $input == ord('_') || $input == ord('.') ){
		return true;
	}
	return false;
}

//brylon - Execute another program 
/** This function writes to standard input via a unidirectional pipe. returns true on success and false on failure. */
function unipipe_exec($exec_target, $args){
	$fp = popen("$exec_target", 'w');
	if($fp === false) return false;
	$result = fputs($fp, $args);
	if ($result === false) return false;
	$result = pclose($fp);
	// pclose === -1 on error and 0 on success.
	if ($result != 0) {
		return false;
	}
	return true;
}

function myecho($str) {
	$rcmail = rcmail::get_instance();
	$DEBUG = $rcmail->config->get('DEBUG');
	if($DEBUG) {
		echo "$str" , '<br>';     
	}
}

function fix_name($name){
	$replace_dot_name = str_replace(".", "_", $name);
	$replace_dot_slash_name = str_replace("/", ".", $replace_dot_name);
	$name = $replace_dot_slash_name;
	if(stristr($name,'inbox')){
		$name = preg_replace('/inbox\./i', 'inbox_', "$name");
		$name = preg_replace('/inbox/i', 'old_inbox', "$name");
	}
	return $name;
}

//rfranknj - tue jun 15 - function to remove a file
function remove_file ($filename)
{
	$rcmail = rcmail::get_instance();
	$userinfo = get_userinfo();
	$MF = $rcmail->config->get('MF');
	$RUNAS_CMD = $rcmail->config->get('RUNAS_CMD');
	$USERNAME = $userinfo['username'];
	$HOME = $userinfo['home'];
	$user_cmd = "find $HOME/$filename -exec rm -f '{}' \;";
	$exec_target = "$RUNAS_CMD $USERNAME $user_cmd";
	exec($exec_target, $result, $status);
	//console ("remove file exec returned status $status result $result");
	return $status;
}

//rfranknj - tue jan 19 - function to write out a file
function write_file ($filename, $contents)
{
	$rcmail = rcmail::get_instance();
	$userinfo = get_userinfo();
	$MF = $rcmail->config->get('MF');
	$RUNAS_CMD = $rcmail->config->get('RUNAS_CMD');
	$USERNAME = $userinfo['username'];
	$user_cmd = "$MF " . $filename;
	$exec_target = "$RUNAS_CMD $USERNAME $user_cmd";
	return unipipe_exec($exec_target, $contents);
}

function get_userinfo() {
		$rcmail = rcmail::get_instance();
		$username = $_SESSION['username'];
		$home = null;
		if($username) {
			$userinfo = $rcmail->config->get('USERINFO');
			$runas_cmd = $rcmail->config->get('RUNAS_CMD');
			$exec_target = "$runas_cmd $username $userinfo";
			unset($result);
			exec($exec_target, $result, $status);
			if($status==0)
				$home = $result[0];
		}
		return array('username' => $username, 'home' => $home);
	}

?>
