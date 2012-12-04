<?php

// Driver - 'file' or 'sql'
$rcmail_config['squirrelmail_driver'] = 'sql';

// full path to the squirrelmail data directory
$rcmail_config['squirrelmail_data_dir'] = '';

// 'mysql://dbuser:dbpass@localhost/database'
$rcmail_config['squirrelmail_dsn'] = 'mysql://root:yellow@127.0.0.1/ngavini_sqmaildb';

$rcmail_config['squirrelmail_address_table'] = 'address';
$rcmail_config['squirrelmail_userprefs_table'] = 'userprefs';

// dialog box for the new user
$rcmail_config['new_user_dialog_title'] = 'Greetings';
$rcmail_config['new_user_dialog_body'] = 'Welcome to the new web mail interface for Rutgers University.';
$rcmail_config['new_user_dialog_button'] = 'Close';

?>
