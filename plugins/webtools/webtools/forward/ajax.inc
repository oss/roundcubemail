<?php

require_once ('functions.inc');

function forward_post_handler ()
{
   $rcmail = rcmail::get_instance();
   switch ($_POST['func'])
   {
      case 'del':
         if (have_file ("Maildir/mailfilter-forward", $result))
         {
            $data = parse_forward_file ($result);
            foreach ($data['emails'] as $id => $email)
               if ($email == $_POST['value'])
                  unset ($data['emails'][$id]);
            if (count($data['emails']) == 0)
               $data = '';
            $new = make_forward_file ($data, implode("\n", $result));
            write_file ("Maildir/mailfilter-forward", $new);
            if (empty($new))
            {
               remove_file ("Maildir/mailfilter-forward");
            }
         }
         $id = str_replace (array('@', '.'), '', $_POST['value']);
         $rcmail->output->command('plugin.forward-post-callback', array('func' => 'del', 'value' => $_POST['value'], 'id'=>$id));
      break;
      case 'add':
         $rcmail = rcmail::get_instance();
         $id = $rcmail->user->get_identity();
         $email = trim(strtolower($id['email']));
         
         $domain_name = preg_split('/@/',trim(strtolower($_POST['value'])));
         $domain_name = $domain_name[1];

         if (host_valid($domain_name) == false)
         {
            $rcmail->output->command('plugin.forward-post-callback', array('func' => 'add', 'no_mxr' => 'true'));
            return;
         }

		 if (email_valid($email) == false) {
            $rcmail->output->command('plugin.forward-post-callback', array('func' => 'add', 'no_mxr' => 'true'));
            return;
         }

         if (trim(strtolower($_POST['value'])) == $email)
         {
            $rcmail->output->command('plugin.forward-post-callback', array('func' => 'add', 'youremail'=>'true'));
            return;
         }
         if (have_file ("Maildir/mailfilter-forward", $result))
         {
            $data = parse_forward_file ($result);
            $data['type'] = $_POST['check']=='true'?'cc':'to';
            foreach ($data['emails'] as $id => $email)
               if ($email == $_POST['value'])
               {
                  $rcmail->output->command('plugin.forward-post-callback', array('func' => 'add', 'alreadyexists' => 'true'));
                  return;
               }
            $data['emails'][] = $_POST['value'];
            $new = make_forward_file ($data, implode("\n", $result));
            write_file ("Maildir/mailfilter-forward", $new);
         }
         else
         {
            $data['emails'][] = $_POST['value'];
            $data['type'] = $_POST['check']=='true'?'cc':'to';
            $new = make_forward_file ($data, implode("\n", $result));
            write_file ("Maildir/mailfilter-forward", $new);
         }
         $id = str_replace (array('@', '.'), '', $_POST['value']);
         $rcmail->output->command('plugin.forward-post-callback', array('func' => 'add', 'value' => $_POST['value'], 'id'=>$id));
      break;
      case 'chtype':
         if ((have_file ("Maildir/mailfilter-forward", $result)))
         {
            $data = parse_forward_file ($result);
            if (!empty($data))
            {
               if ($_POST['value'] == 'true')
                  $data['type'] = 'cc';
               else
                  $data['type'] = 'to';
               $new = make_forward_file ($data, implode("\n", $result));
               write_file ("Maildir/mailfilter-forward", $new);
            }
         }
         $rcmail->output->command('plugin.forward-post-callback', array('func' => 'chtype', 'value' => $_POST['value']));
      break;
      case 'delall':
         if (have_file ("Maildir/mailfilter-forward", $result))
         {
            $data = parse_forward_file ($result);
            $new = make_forward_file ('', implode("\n", $result));
            write_file ("Maildir/mailfilter-forward", $new);
            if (empty($new))
            {
               remove_file ("Maildir/mailfilter-forward");
            }
         }
         $rcmail->output->command('plugin.forward-post-callback', array('func' => 'delall'));
      break;
   }
}

?>
