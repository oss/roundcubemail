<?php

/**
 * Rutgers Webtools Plugin
 *
 * @author Naveen Gavini
 * @licence GNU GPL
 *
 * Configuration (see config.inc.php.dist)
 * 
 **/

require_once('plugins/webtools/webtools/forward_vacation_functions.php');
//require_once('plugins/webtools/webtools/forward/forward_post_handler.php');
require_once('plugins/webtools/webtools/vacation/vacation_post_handler.php');
require_once('plugins/webtools/webtools/restore/restore_post_handler.php');
require_once('plugins/webtools/webtools/hushquota/hushquota_post_handler.php');
require_once('plugins/webtools/webtools/filter/ajax.inc');
require_once('plugins/webtools/webtools/forward/ajax.inc');
require_once('plugins/webtools/webtools/quota/ajax.inc');

error_reporting(E_ALL);

class webtools extends rcube_plugin
{
    function init()
    {
      $this->load_config();

      $this->add_texts('localization/', false);
      // register actions
      $this->register_action('plugin.webtools', array($this, 'action'));
      $this->register_action('plugin.webtoolsforward', array($this, 'action'));
      $this->register_action('plugin.webtoolsvacation', array($this, 'action'));
      $this->register_action('plugin.webtoolsfilter', array($this, 'action'));
      $this->register_action('plugin.webtoolsspam', array($this, 'action'));
      $this->register_action('plugin.webtoolsquota', array($this, 'action'));
      $this->register_action('plugin.webtoolshushquota', array($this, 'action'));
      $this->register_action('plugin.webtoolsrestore', array($this, 'action'));
      $this->register_action('plugin.webtoolsrestore-show', array($this, 'action'));


      // actions for ajax stuff
      $this->register_action('plugin.webtoolsforward-post', 'forward_post_handler');
      $this->register_action('plugin.webtoolsvacation-post', 'vacation_post_handler');
      $this->register_action('plugin.webtoolsrestore-post', 'restore_post_handler');
      $this->register_action('plugin.webtoolshushquota-post', 'hushquota_post_handler');
      $this->register_action('plugin.webtools.filter.delete', 'filter_handle_delete');
      $this->register_action('plugin.webtools.filter.enable', 'filter_handle_enable');
      $this->register_action('plugin.webtools.filter.add', 'filter_handle_add');
      $this->register_action('plugin.webtools.filter.rename', 'filter_handle_rename');
      $this->register_action('plugin.webtools.quota.load', 'quota_handle_load');

      // add taskbar button
      $this->add_button(array(
	    'name' 	=> 'webtoolstask',
	    'class'	=> 'button-webtools',
	    'label'	=> 'webtools.webtools',
		 'href'	=> './?_task=dummy&_action=plugin.webtools',
      ), 'taskbar');

      $skin = rcmail::get_instance()->config->get('skin');
      if (!file_exists($this->home."/skins/$skin/webtools.css"))
	  $skin = 'default';
      
      $rutgers_skin = '../../skins/rutgers';

	  $jqueryui = '../jqueryui/js';

      // add style for taskbar button (must be here) and Help UI    
      $this->include_stylesheet("skins/$skin/webtools.css");
      $this->include_stylesheet("$rutgers_skin/jquery-ui-1.7.2.custom.css");
      $this->include_stylesheet("$rutgers_skin/ui.checkbox.css");
      $this->include_stylesheet("$rutgers_skin/ui.selectmenu.css");
      $this->include_script("$jqueryui/jquery-ui-1.8.18.custom.min.js");
      $this->include_script("$rutgers_skin/ui.checkbox.js");
      $this->include_script("$rutgers_skin/ui.selectmenu.js");
      $this->include_script("js/webtools.js");
      $this->include_script('webtools/spam/functions.js');
      $this->include_script('webtools/filter/functions.js');
      $this->include_script('webtools/forward/functions.js');
      
      //jquery pnotify
      $this->include_script("$rutgers_skin/jquery.pnotify.min.js");
      $this->include_stylesheet("$rutgers_skin/jquery.pnotify.default.css");
    }

    function action()
    {
      $rcmail = rcmail::get_instance();

      //$this->load_config();

      // register UI objects
      $rcmail->output->add_handlers(array(
	    'webtoolscontent' => array($this, 'content'),
      ));
      //include ($this->home.'/webtools/webtools/filter/ajax.php');
      if ($rcmail->action == 'plugin.webtoolsforward')
	    $rcmail->output->set_pagetitle($this->gettext('forward'));
      else if ($rcmail->action == 'plugin.webtoolsvacation')
        $rcmail->output->set_pagetitle($this->gettext('vacation'));
      else if ($rcmail->action == 'plugin.webtoolsfilter')
        $rcmail->output->set_pagetitle($this->gettext('filter'));
      else if ($rcmail->action == 'plugin.webtoolsspam')
        $rcmail->output->set_pagetitle($this->gettext('spam'));
      else if ($rcmail->action == 'plugin.webtoolsquota')
        $rcmail->output->set_pagetitle($this->gettext('quota'));
      else if ($rcmail->action == 'plugin.webtoolshushquota')
        $rcmail->output->set_pagetitle($this->gettext('hushquota'));
      else if ($rcmail->action == 'plugin.webtoolsrestore')
        $rcmail->output->set_pagetitle($this->gettext('restore'));
      else
        $rcmail->output->set_pagetitle($this->gettext('webtools'));

      $rcmail->output->send('webtools.webtools');
    }
    
   function content($attrib)
    {
      $rcmail = rcmail::get_instance();

      if ($rcmail->action == 'plugin.webtools') {
        return get_include_contents($this->home.'/webtools/webtools.php');
      }
      else if ($rcmail->action == 'plugin.webtoolsforward') {
	    return get_include_contents($this->home.'/webtools/forward/index.php');
      }
      else if ($rcmail->action == 'plugin.webtoolsvacation') {
        return get_include_contents($this->home.'/webtools/vacation/index.php');
      }
      else if ($rcmail->action == 'plugin.webtoolsfilter') {
        return get_include_contents($this->home.'/webtools/filter/index.php');
      }
      else if ($rcmail->action == 'plugin.webtoolsspam') {
        return get_include_contents($this->home.'/webtools/spam/index.php');
      }
      else if ($rcmail->action == 'plugin.webtoolsquota') {
        return get_include_contents($this->home.'/webtools/quota/index.php');
      }
      else if ($rcmail->action == 'plugin.webtoolshushquota') {
        return get_include_contents($this->home.'/webtools/hushquota/index.php');
      }
      else if ($rcmail->action == 'plugin.webtoolsrestore') {
	    return get_include_contents($this->home.'/webtools/restore/index.php');
      }

      // default content: iframe
      /*if ($src = $rcmail->config->get('webtools_source'))
            $attrib['src'] = $src;*/

      if (empty($attrib['id']))
        $attrib['id'] = 'rcmailwebtoolscontent';
    
      // allow the following attributes to be added to the <iframe> tag
      $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style', 'src', 'width', 'height', 'frameborder'));
      $framename = $attrib['id'];

      $out = sprintf('<iframe name="%s"%s></iframe>'."\n", $framename, $attrib_str);
    
      return $out;
    }
    
}

function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
}

?>
