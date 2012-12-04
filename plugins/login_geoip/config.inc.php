<?php

/**
 * GeoIP Plugin
 *
 * @author Russell Frank
 * @license MIT
 *
 **/

//admin_from: an email address from which administrative email will be sent.
$rcmail_config['admin_from'] = "geoip@rutgers.edu";

//admin_to: an email to which administrative email will be sent.
$rcmail_config['admin_to'] = "rfranknj@jla.rutgers.edu";

//expected_country: iso country code, the expected country that the users will
//be in. It is fatal for this option to be unset
$rcmail_config['expected_country'] = "US";

//We run 3 lookups when we're having trouble getting consistent data from the
//geoip database.  The following two options determine whether or not to send
//administrative email when encountering inconsistent data from geoip.

//lookup_error_2match: bool, whether or not to send an administrative email
//when we run 3 lookups and only 2 match.
$rcmail_config['lookup_error_2match'] = TRUE;

//lookup_error_nomatch: bool, whether or not to send an administrative email
//when we run 3 lookups and none of them match.
$rcmail_config['lookup_error_nomatch'] = TRUE;

//aug_table: mysqli dsn, a table of cidr ranges and descriptions for
//custom lookup information (rather than geoip). leave this blank if you do not
//require this functionality.
$rcmail_config['aug_table'] = "mysql://root:yellow@localhost/roundcube_dev";


//admin_table: mysqli dsn, a table of all logins. should only allow insert. It
//is fatal for this option to be unset.
$rcmail_config['admin_table'] = "mysql://root:yellow@localhost/roundcube_dev";

//cleaned so only the configured # of historical entries are present. it is
//fatal for this option to be unset.
$rcmail_config['user_table'] = "mysql://root:yellow@localhost/roundcube_dev";


//exemp_table: mysqli dsn, a table with exemptions for the geoip check
$rcmail_config['exemp_table'] = "mysql://root:yellow@localhost/roundcube_dev";

//num_entries: int, the # of entries to keep in the user table
$rcmail_config['num_entries'] = 5;

//faq_link: url, link for faq in the display box
$rcmail_config['faq_link'] = "http://css.rutgers.edu/webmail/faq";

//enable or disable notifications
$rcmail_config['notifications'] = true;

//details_headline: headline for the details page
$rcmail_config['details_headline'] = "GeoIP Details";

//details_subheadline: subheadline for the details page
$rcmail_config['details_subheadline'] = "Login activity details";

//details_body: body for the details page
$rcmail_config['details_body'] = "The following is a summary of your past logons into this webmail system.";

//details_footer: goes underneath the details table on the details page
$rcmail_config['details_footer'] = "<br><a href='?'>back to inbox</a>";

//new_identity_in_expected: bool, whether or not to send out an administrative
//email when a new identity is created in the expected country
$rcmail_config['new_identity_in_expected'] = TRUE;

//new_identity_not_in_expected: bool, whether or not to send out an administrative
//email when a new identity is created out of the expected country
$rcmail_config['new_identity_not_in_expected'] = TRUE;

//reply_to_in_expected: bool, whether or not to send out an administrative email
//when an email is sent with a reply_to header and the user is connected from 
//the expected country.
$rcmail_config['reply_to_in_expected'] = TRUE;

//reply_to_in_expected: bool, whether or not to send out an administrative email
//when an email is sent with a reply_to header and the user is not connected  
//from the expected country.
$rcmail_config['reply_to_not_in_expected'] = TRUE;

//directory where i can find flag images INCLUDING TRAILING /
//this should be a path from / on your server, including the hostname/ip.
$rcmail_config['flags_dir'] = "mx.nbcs.test.rutgers.edu/roundcube/skins/rutgers/flags/";

//directory for geoip data
$rcmail_config['geoip_dir'] = "/usr/share/GeoIP/GeoIP.dat";

//ldap host for grabbing information about a user when we are sending an
//admin mail
$rcmail_config['geoip_ldap_host'] = "ldap.nbcs.rutgers.edu";

//ldap port
$rcmail_config['geoip_ldap_port'] = "389";

//ldap base dn
$rcmail_config['geoip_ldap_base_dn'] = "dc=rci,dc=rutgers,dc=edu";

?>
