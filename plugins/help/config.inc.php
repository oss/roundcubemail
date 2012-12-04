<?php

// Help content iframe source
// $rcmail_config['help_source'] = 'http://trac.roundcube.net/wiki';

$CLUSTER = strtolower(rcmail::get_instance()->config->get('CLUSTER'));

$rcmail_config['help_source'] = "/content/$CLUSTER.html";

?>
  
