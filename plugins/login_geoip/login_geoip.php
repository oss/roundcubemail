<?php

/**
 * GeoIP Plugin
 *
 * @author Russell Frank
 * @license MIT
 * @version 0.2
 * 
 **/

include ("geoip.inc");

class login_geoip extends rcube_plugin {

private $rc;
private $userdb;
private $loc;
private $desc;
private $host;
private $flag;
private $exemption;

function init ()
{
   // get a handle to the rcmail object
   $this->rc = rcmail::get_instance();
   // RU: load webtools config file for $cluster
   $this->load_config('../webtools/config.inc.php');
   $this->load_config('config.inc.php');
   // include our js file with the make_popup function
   $this->include_script('login_geoip.js');
   // hook the template object, which is that little box on the main page
   $this->add_hook ('template_object_loginactivity', array($this, 'template_object_loginactivity'));
   // hook logins, so login_after will be called after the login is successfull
   $this->add_hook ('login_after', array($this, 'login_after'));
   // for checking if the user is within their expected country when creating/modifying an identity
   $this->add_hook ('create_identity', array($this, 'identity_create'));
   $this->add_hook ('identity_update', array($this, 'identity_create'));
   $this->add_hook ('update_identity', array($this, 'identity_create'));
   // for checking if the user is within their expected country when sending mail with a reply-to header
   $this->add_hook ('message_sent', array($this, 'message_sent'));
   // register the xhr action for the details pge
   $this->register_action ('plugin.login_activity', array($this, 'show_details'));
   // load our configuration
   $this->load_config ('config.inc.php');
   $this->include_stylesheet("login_geoip.css");
   $user_dsn = $this->rc->config->get('user_table');
   if (empty($user_dsn))
      die ('login_geoip: user table option must be set');
   // open the user db
   $this->userdb = new rcube_mdb2 ($user_dsn);
   $this->userdb->db_connect('w');
   $this->lookedup = FALSE;
}

function get_ldap_info ()
{  // get ldap info for those admin mails
   // $con = ldap_connect ($this->rc->config->get['geoip_ldap_host'], $this->rc->config->get['geoip_ldap_port']);
   $con = ldap_connect ($this->rc->config->get('geoip_ldap_host'), $this->rc->config->get('geoip_ldap_port'));
   //if (!$con)
   //   $this->send_admin_mail ('ldap_connect failed', 'ldap error', true);
   $result = ldap_search ($con, $this->rc->config->get('geoip_ldap_base_dn'), 'uid='.$this->user);
   //if (!$result)
   //   $this->send_admin_mail ('ldap_search failed.', 'ldap error', true);
   $info = ldap_get_entries ($con, $result);
   return $info[0]['uid'][0] . ":" . $info[0]['userpassword'][0] . ":" .
          $info[0]['uidnumber'][0] . ":" . $info[0]['gidnumber'][0] . ":" .
          $info[0]['gecos'][0] . ":" . $info[0]['homedirectory'][0] . ":" .
          $info[0]['loginshell'][0];
}

function list_identities ($new=FALSE)
{
   $r = "";
   if ($new != FALSE)
   {
      $identity = $new;
      $r .= "NEW/MODIFIED IDENTITY: " . $identity['name'] . "\n";
      $r .= "FROM: " . $identity['email'] . "\n";
      $r .= "REPLY-TO: " . $identity['reply-to'] . "\n";
      $r .= "BCC: " . $identity['bcc'] . "\n";
      $r .= "ORGANIZATION: " . $identity['organization'] . "\n";
      $r .= "SIGNATURE: " . $identity['signature'] . "\n";
      $r .= "DEFAULT: " . ($identity['standard'] ? "Yes" : "No") . "\n\n";
      $r .= "The identites previously associated with this account were:\n\n";
   }
   else
      $r .= "The following identities are associated with this account:\n\n";
   foreach ($this->rc->user->list_identities() as $identity)
   {
      $r .= "Name: " . $identity['name'] . "\n";
      $r .= "Last Changed: " . date("r",strtotime($identity['changed'])) . "\n";
      $r .= "From: " . $identity['email'] . "\n";
      $r .= "Reply-To: " . $identity['reply-to'] . "\n";
      $r .= "Bcc: " . $identity['bcc'] . "\n";
      $r .= "Organization: " . $identity['organization'] . "\n";
      $r .= "Signature: " . $identity['signature'] . "\n";
      $r .= "Default: " . ($identity['standard'] ? "Yes" : "No") . "\n\n";
   }
   return $r;
}

function message_sent ($args)   // hook on when messages are sent
{
   $this->lookup();
   // if we're in the expected country
   $in_expected = ($this->loc == $this->rc->config->get('expected_country'));
   $international = ($in_expected?"a domestic":"an international");             
   $send_in_expected = $this->rc->config->get ("reply_to_in_expected");   
   $send_not_in_expected = $this->rc->config->get("reply_to_not_in_expected");
   $this->send_admin_mail  // send an admin mail
   (
      "This is the RoundCube Mail plugin login_geoip on " . php_uname("n") . 
         ".\nMail was sent with a reply-to header from " . $international .
         " origin.\n\n" .
         $this->get_ldap_info() . "\n" .
         "USER: " . $this->user . " (" . $this->rc->config->get ('CLUSTER') . ")\n" .
         "DATE: " . date("r") . "\n" . 
         "HOST: " . $this->host . " (" . $this->ip . ")\n" .
         "LOCATION: " . $this->desc . " (" . $this->loc . ")\n\n".
         "REPLY-TO: " . $args['headers']['Reply-To'] . "\n\n" .
         $this->list_identities(),
      "RoundCube Mail on " . php_uname("n")  . ": Mail sent with reply-to " .
         " header user " . $this->user,
      ($args['headers']['Reply-To'] != "") &&
         ((!$in_expected && $send_not_in_expected) || $send_in_expected)  
   );
}

function identity_create ($args)
{
   if (isset($args['login'])) return;   // if this is set it means that this is
                                        // an identity being created on an
                                        // initial login. we want to ignore
                                        // these
   $this->lookup();
   $in_expected = ($this->loc == $this->rc->config->get('expected_country')); 
   $international = ($in_expected?"a domestic":"an international");             
   $send_in_expected = $this->rc->config->get ("new_identity_in_expected");
   $send_not_in_expected = $this->rc->config->get ("new_identity_not_in_expected");
   if ((!$in_expected && $send_not_in_expected) || $send_in_expected)
   $this->send_admin_mail  // send an admin mail
   (
      "This is the RoundCube Mail plugin login_geoip on " . php_uname("n") . 
         ".\nAn identity was created/modified from " . $international .
         " origin.\n\n" .
         $this->get_ldap_info() . "\n" .
         "USER: " . $this->user . " (" . $this->rc->config->get ('CLUSTER') . ")\n" .
         "DATE: " . date("r") . "\n" . 
         "HOST: " . $this->host . " (" . $this->ip . ")\n" .
         "LOCATION: " . $this->desc . " (" . $this->loc . ")\n\n".
         $this->list_identities($args['record']),
      "RoundCube Mail on " . php_uname("n")  . ": New/Modified Identity, " .
         "user " . $this->user,
      ((!$in_expected && $send_not_in_expected) || $send_in_expected)
   );
   return $args;
}

function code_to_country ($code)
{
   // convert a country code to a country name.  uses geoip.inc
   $gi = new GeoIP();
   $id = $gi->GEOIP_COUNTRY_CODE_TO_NUMBER[$code];
   return $gi->GEOIP_COUNTRY_NAMES[$id];
}

function send_admin_mail ($message, $subj, $config_check=TRUE)
{
   if (($config_check != TRUE) && ($config_check != FALSE))
      $config_check = $this->rc->config->get($config_check);
   if ($config_check)
   {
      if (!isset($this->rc->smtp)) $this->rc->smtp_init();
      $this->rc->smtp->connect ();
      $headers = "Subject: " . $subj . "\r\n" . "To: " . $this->rc->config->get('admin_to') . "\r\n";
      $this->rc->smtp->send_mail ($this->rc->config->get('admin_from'), $this->rc->config->get('admin_to'), $headers, $message);
   }
}

function location_code ($ip)
{
   // open up the geoip db
   $geoip = geoip_open ($this->rc->config->get('geoip_dir'), GEOIP_STANDARD);
   $loc = array();
   $loc[] = geoip_country_code_by_addr ($geoip, $ip);
   $loc[] = geoip_country_code_by_addr ($geoip, $ip);
   // we do multiple lookups because we sometimes get inconsistent data from
   // geoip database
   if ($loc[0] != $loc[1])
   {
      // if our first two results didn't match run a third
      $loc[] = geoip_country_code_by_addr ($geoip, $ip);
      // grab only unique values so we can tell how many identical looks we got
      $uniq = array_unique($loc);
      if (count($uniq) == 2) // two of them are the same
      {
         $m = "3 geoip lookups were performed, only two of which match.  Lookup results:\n" .
              $loc[0] . ", " . $loc[1] . ", " . $loc[2]. "\nIdentities:\n";
         foreach ($this->rc->user->list_identities() as $identity)
            $m .= $identity['email'] . "\n";
         $m .= "Current time: " . date("r");
         $this->send_admin_mail($m, "RoundCube Mail on ".$_SERVER['SERVER_NAME'].": login_geoip lookup error", 'lookup_error_2match');
      }
      else if (count($uniq) == 3) // all three are different
      {
         //todo: send email
         $uniq[0] = "Unknown";
      }
   }
   else
      $uniq = $loc;
   geoip_close ($geoip);
   return $uniq[0];
}

function hostname ($ip)
{
   // reverse the ip for the reverse dns lookup
   $rev = preg_replace('/^(\\d+)\.(\\d+)\.(\\d+)\.(\\d+)$/', '$4.$3.$2.$1', $ip);
   // get ptr records for the ip
   $ptrs = dns_get_record("{$rev}.IN-ADDR.ARPA.", DNS_PTR);
   // if no ptr records are returned
   if (empty($ptrs))
      return "Unknown";
   // looping thru the ptr records to perform the forward confirmation
   foreach ($ptrs as $x)
      if (isset($x['target']))
      {
         // grab the A record(s) to get the IP for the forward check
         $a = dns_get_record($x['target'], DNS_A);
         if (is_array($a)) 
            foreach ($a as $y)              // loop thru any A records found 
               if (isset($y['ip']))
                  if ($ip == $y['ip'])      // check to see if it equals our input
                     return($x['target']);  // if so, it's forward confirmed; return
      }
   return "Unknown";
   // based on code from:
   // http://blog.sjinks.pro/php/125-forward-confirmed-reverse-dns-zombie-wont-pass/ (russian)
}

function exemp_lookup ()
{
   $ip = $this->ip;
   $exempdsn = $this->rc->config->get('exemp_table');
   if (!empty($exempdsn))
   {
      $identity = $this->rc->user->get_identity();
      $identity = $identity['email'];
      $exempdb = new rcube_mdb2($exempdsn);
      $exempdb->db_connect('w');
      $result = $exempdb->query("select loc from geoip_exemption where (start < NOW()) AND (end > NOW()) AND (netid = '".$this->user."') AND (identity = '$identity')");
      if ($result == 1)
      {
         $result = $exempdb->fetch_assoc();
         return $result['loc'];
      }
      else
         return 0;
   }
}

function aug_lookup ($ip)
{
   $row = FALSE;
   // check to see if this is a local ip in our cidr db.
   $augdsn = $this->rc->config->get('aug_table');
   if (!empty($augdsn))
   {
      $augdb = new rcube_mdb2($augdsn);
      $augdb->db_connect ('w');
      $result = $augdb->query("select * from geoip_cidr_real where INET_ATON('$ip') between net and (net + POW(2,32-mask)-1) order by net desc limit 1;");
      if ($result == 1)
         $row = $augdb->fetch_assoc();
   }
   return $row;
}

function lookup ()
{
   // perform lookups and cache data
   $this->user = $this->rc->user->data['username'];
   $this->ip = $_SERVER['REMOTE_ADDR'];
//   $this->ip = "68.192.171.208"; // russ's home ip
 //  $this->ip = "129.90.4.4"; // venezuelan ip
 //  $this->ip = "27.116.56.0"; // asia/pacific region
 //  $this->ip = "94.76.239.95"; // uk ip
 //  $this->ip = "118.194.1.157"; // chinese ip
 //  $this->ip = "196.202.55.2"; // egypt
 //  $this->ip = "172.17.5.2";
   $aug = $this->aug_lookup($this->ip); // try the aug. db lookup
   if ($aug == FALSE)
   {
      $this->loc = $this->location_code($this->ip);
      $this->host = $this->hostname($this->ip);
      $this->desc = $this->code_to_country($this->loc);
      $this->flag = strtolower($this->loc) . ".gif";    // all of the
   }
   else
   {
      $this->flag = $aug['flag'];
      $this->loc = "US";
      $this->host = $aug['host'];
      $proper_hostname = $this->hostname($this->ip);
      if ($proper_hostname == "Unknown")   // if we can get a hostname
         $this->host = $aug['host'];       // via fcrdns use that instead
      else                                 // of the generic one in the
         $this->host = $proper_hostname;   // cidr db
      $this->desc = $aug['description'];
   }
   if ($this->loc != $this->rc->config->get('expected_country'))
   {
      $exemption = $this->exemp_lookup();
      console ($exemption);
      if ($this->loc == $exemption)
         $this->exemption = 1;
      else
         $this->exemption = 0;
   }
   else
      $this->exemption = 1;
}

function login_after ()
{
   $this->lookup();
   // begin with logging to the admin database if one is configured
   $admindsn = $this->rc->config->get('admin_table');
   if (!empty($admindsn))
   {
      $admindb = new rcube_mdb2 ($admindsn);
      $admindb->db_connect('w');
      $admindb->query("INSERT INTO geoip_admin_real (ip, host, username, loc, flag, description, time, exemption) VALUES" .
                      "(INET_ATON('$this->ip'), '$this->host', '$this->user', '$this->loc', '$this->flag', '$this->desc', NOW(), $this->exemption)"); // todo: or email error
   }
   // now log to user database and clean it up
   $this->userdb->query("INSERT INTO geoip_user_real (ip, host, username, loc, flag, description, time, exemption) VALUES" . 
                        "(INET_ATON('$this->ip'), '$this->host', '$this->user', '$this->loc', '$this->flag', '$this->desc', NOW(), $this->exemption)"); // todo: or email error
   // clean up user db
   $this->userdb->query("DELETE FROM geoip_user_real USING geoip_user_real
       LEFT JOIN  (SELECT id FROM geoip_user_real WHERE
       username='".$this->user."' ORDER BY id DESC LIMIT 
       ".$this->rc->config->get('num_entries').") dt ON
       geoip_user_real.id = dt.id WHERE dt.id IS NULL AND
       username='".$this->user."'"); // todo: or email error
}

function make_popup ($data)
{
   if ($_SESSION['login_geoip_annoyed'] == TRUE) return ""; // if we annoyed the user already don't make a popup
   $_SESSION['login_geoip_annoyed'] = TRUE;
   if ($this->rc->config->get('notifications') == false) return "";
   array_shift ($data);
   foreach ($data as $id => $row)
   {
      if ($row['loc'] != $this->rc->config->get('expected_country') && $row['exemption'] == 0)
         $countries[] = $row['description'];
   }
   $data = array_unique($countries);
   if (empty($data)) return "";
   $text = "You recently logged in from ";
   $num = count($data);
   $i=0;
   foreach($data as $country)
   {
      $text .= $country;
      if ($i == $num-1) $text .= ".";
      elseif (($i == $num-2) && ($num == 2)) $text .= " and ";
      elseif ($i == $num-2) $text .= ", and ";
      else $text .= ", ";
      $i++;
   }
   $text .= "  Please check your <a href=\'?_task=dummy&_action=plugin.login_activity\'>account activity details</a> immediately.";
   $r = "<script type=\"text/javascript\">$(document).ready(function(){make_popup('$text');});</script>";
   return $r;
}

function template_object_loginactivity ($args)
{
   // grab information from db. apparently this function isn't run in the same instance of the plugin object as
   // the login itself, so we have to retreive the data out of the db.
   $this->lookup();
   if ($this->userdb->query("select INET_NTOA(ip), host, flag, description, loc, time, exemption from geoip_user_real where username='".$this->user."' order by time desc;"))
   {
      $res = array();
      while ($result = $this->userdb->fetch_assoc()) { $res[] = $result; }
   }
	//Changes so flags are now stored relative to the plugin directory.
   //$flagsdir = ($_SERVER['SERVER_PORT'] == 443?"https://":"http://") . $this->rc->config->get('flags_dir');
	$flagsdir = $this->urlbase . "flags/";
   $cur = $res[0]; $prev = $res[1];
   $r  = "\n<div id='login_time' class='uibox listbox' style='position: absolute'>\n" .
         //"\n<div style='padding: 5px; font-size:8pt' class='ui-widget-header ui-corner-top'>Account Activity</div>" .
         //"<div style='border-left: 1px solid #999; border-right: 1px solid #999; padding:5px; background-color:#F9F9F9;" .
		 "<div style='padding:5px;" .
         "font-size:8pt'><b>Current Login:</b><table>";
   $r .= "<tr><td style='padding:3px;' width='24'><img src=\"" . $flagsdir . $cur['flag'] . "\"/></td>" .
         "<td style='font-size:8pt'>". ($cur['host']=="Unknown"?$cur['inet_ntoa(ip)']:$cur['host']) . " (".$cur['loc'].")<br>" . $cur['description'] .
         "</td></tr></table>";
   $r .= "</table><hr size='1px' color='#999'>\n\n<b>Previous Login:</b>";
   // old login
   if ($prev)
      $r .= "<table><tr><td style='padding:3px;' width='24'><img src=\"" . $flagsdir . $prev['flag'] . "\"/></td>" .
            "<td style='font-size:8pt'>". ($prev['host']=="Unknown"?$prev['inet_ntoa(ip)']:$prev['host']) . " (".$prev['loc'].")<br>" . $prev['description'] .
            "</td></tr></table>";
   else
      $r .= "<br>No Previous Login";
//   $r .= "</div><div id='login_time_foot' class='ui-corner-bottom' style='background-color:#E9E6DC;" .
   $r .= "</div><div id='login_time_foot' class='ui-corner-bottom' style='" .
         "border-top: 1px solid #999; padding:5px; font-size:8pt;'>" .
         "\n<a href='?_task=dummy&_action=plugin.login_activity' style='text-decoration:none;color:#06C;'>Details</a> | " .
         "\n<a href='".$this->rc->config->get('faq_link')."' style='text-decoration:none;color:#06C;' target='_blank'> FAQ </a>" .
         "</div></div>\n";
   $r .= $this->make_popup ($res);
   $args['content'] = $r;
   return $args;
}

function show_details ()
{
   // handle the template object
   $this->rc->output->set_pagetitle ("Login Details");
   $this->rc->output->add_handlers(array('loginactivitycontent' => array($this, 'show_details_content')));
   $this->rc->output->send('loginactivity');
}

function show_details_content()
{
   $this->lookup();
   $flagsdir = ($_SERVER['SERVER_PORT'] == 443?"https://":"http://") . $this->rc->config->get('flags_dir');
   // generate the content for the details page. start by getting the last couple entries out of the user db
   if ($this->userdb->query("select INET_NTOA(ip), host, flag, description, loc, time from geoip_user_real where username='".$this->user."' order by time desc;"))
   {
      // draw headline and sub header an dstuff
      $r = "<b><h2>" . $this->rc->config->get('details_headline') . "</h2><h3>" . $this->rc->config->get('details_subheadline') .
           "</h3><p>" . $this->rc->config->get('details_body') . "</p><table class='login_history ui-corner-top'><tr class='ui-widget-header'>" .
           "<th class='ui-corner-tl'>Host</th><th>Location</th><th class='ui-corner-tr'>Date/Time</th></tr>";
      $i = 0;
      // draw the table
      while ($info = $this->userdb->fetch_assoc())
      {
         if ($info)
            $r .= "<tr><td>" .
            $info['host'] . " (" . $info['inet_ntoa(ip)'] . ")" .
            "</td><td>" .
            "<img src=\"" . $flagsdir . $info['flag'] . "\"/>" .
            $info['description'] . "</td><td>" . 
            ($i == 0
               ? "Current Login"
               : date("Y-m-d g:i A",strtotime($info['time'])))
            . "</td></tr>";
         $i++;
      }
      $r .= "</table>";
      $r .= $this->rc->config->get('details_footer');
      return $r;
   }
   else
      return "error accessing db"; // should never happen
}

}

?>
