<?php
/* Reset Prefs Plugin
 *
 * Plugin to reset preferences by removing the user from the RC database.
 *
 * @version 0.1
 * @author Russell Frank
 */

class reset_prefs extends rcube_plugin
{
   public $task = 'settings';
   private $redir = false;
   private $editbutton = false;   

   function init()
   {
      $this->include_script('reset_prefs.js');
      $this->add_hook('list_prefs_sections', array($this, 'preflist'));
      $this->add_hook('user_preferences', array($this, 'userprefs'));
      $this->add_hook('save_preferences', array($this, 'saveprefs'));
   }

   function saveprefs ($args)
   {
      if ($args['section'] == 'resetprefs')
      {
         $rc = rcmail::get_instance();
         $db = $rc->get_dbh();
         $user = $_SESSION['username'];
         $db->query ("delete from contacts where user_id=ANY(select user_id from users where username='" . $user . "');");
         $db->query ("delete from identities where user_id=ANY(select user_id from users where username = '" . $user . "');");
         $db->query ("delete from users where username='" . $user . "';");
         $this->redir = true;
         $rc->kill_session();
         $rc->logout_actions();
      }
      return $args;
   }

   function userprefs($args)
   {
      if ($args['section'] == 'resetprefs')
      {
         $args['blocks']['main']['name'] = "Reset Preferences";
         $js = 'rcmail.add_onload(fix_button);';
         $button = '<br /><br /><input id="rcmbtn999" class="button mainaction" type="button" value="Reset" onclick="button_click()"/>';
         $text  = 'Pressing this "reset" button will delete all of the settings in this webmail interface associated with your NetID.  You will be ';
         $text .= 'logged out once this process is complete.  This operation cannot be undone.';
         if ($this->redir)
         { 
            $js .= 'parent.window.location = "?";';
         }
         $args['blocks']['main']['options']['resetprefs']['content'] = $text . $button . '<script type="text/javascript">' . $js . '</script>';
      }
      return $args;
   }

   function preflist($args)
   {
      $temp['resetprefs']['id'] = 'resetprefs';
      $temp['resetprefs']['section'] = 'Reset Preferences';
      $args['list'] += $temp;
      return $args;
   } 
}

?>
