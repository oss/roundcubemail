<?php

/**
 * Bounce
 *
 * Allow to redirect (a.k.a. "bounce") mail messages to other
 * Ticket #1485774 http://trac.roundcube.net/ticket/1485774
 *
 * @version 1.0
 * @author Denis Sobolev
 */
class bounce extends rcube_plugin
{
	public $task = 'mail';

	function init()
	{
		$this->add_texts('localization');
		$rcmail = rcmail::get_instance();

		$this->register_action('plugin.bounce', array($this, 'request_action'));

		if ($rcmail->task == 'mail' && ($rcmail->action == '' || $rcmail->action == 'show'))
		{
			$skin_path = $this->local_skin_path();

			$this->include_script('bounce.js');

			// rfranknj sept 30 : button added by skin

			//$this->add_button(
			//  array(
			//      'command' => 'plugin.bounce.box',
			//      'title'   => 'bouncemessage',
			//      'domain'  =>  $this->ID,
			//      'imagepas' => $skin_path.'/bounce_pas.png',
			//      'imageact' => $skin_path.'/bounce_act.png',
			//      'class' => 'bounce-ico'
			//  ),
			//  'toolbar');
			$this->add_hook('render_page', array($this, 'render_box'));

		}
	}

	function request_action()
	{
		$this->add_texts('localization');

		$msg_uid = get_input_value('_uid', RCUBE_INPUT_POST);

		$rcmail = rcmail::get_instance();
		//$rcmail->output->reset();

		$mailto_regexp = array('/[,;]\s*[\r\n]+/', '/[\r\n]+/', '/[,;]\s*$/m', '/;/');
		$mailto_replace = array(', ', ', ', '', ',');

		// replace new lines and strip ending ', '
		$mailto = preg_replace($mailto_regexp, $mailto_replace, get_input_value('_to', RCUBE_INPUT_POST));
		$mailcc = preg_replace($mailto_regexp, $mailto_replace, get_input_value('_cc', RCUBE_INPUT_POST));
		$mailbcc  = preg_replace($mailto_regexp, $mailto_replace, get_input_value('_bcc', RCUBE_INPUT_POST));

		$headers_old = $rcmail->imap->get_headers($msg_uid);

		$a_recipients = array();
		$a_recipients['To'] = $mailto;
		if (!empty($mailcc))
			$a_recipients['Cc'] = $mailcc;
		if (!empty($mailbcc))
			$a_recipients['Bcc'] = $mailbcc;

		$recent = array();
		$recent['From'] = $headers_old->to." <".$headers_old->to.">";
		$recent['To'] = $mailto;
		if (!empty($mailcc))
			$recent['Cc'] = $mailcc;
		if (!empty($mailbcc))
			$recent['Bcc'] = $mailcc;
		$recent['Message-Id'] = sprintf('<%s@%s>', md5(uniqid('rcmail'.mt_rand(),true)), $rcmail->config->mail_domain($_SESSION['imap_host']));
		$recent['Date'] = date('r');
		if ($rcmail->config->get('useragent'))
			$recent['User-Agent'] = $rcmail->config->get('useragent');

		foreach($recent as $k=>$v){
			$recent_headers .= "Resent-$k: $v\n";
		}

		$msg_body = $rcmail->imap->get_raw_body($msg_uid);
		$headers = $recent_headers.$rcmail->imap->get_raw_headers($msg_uid);
		$a_body = preg_split('/[\r\n]+$/sm', $msg_body);
		for ($i=1,$body='';$i<=count($a_body);$body .= trim($a_body[$i])."\r\n\r\n",$i++)

			/* need decision for DKIM-Signature */
			/* $headers = preg_replace('/^DKIM-Signature/sm','Content-Description',$headers); */

			if (!is_object($rcmail->smtp))
				$rcmail->smtp_init(true);

		$sent = $rcmail->smtp->send_mail('', $a_recipients, $headers, $body);
		$smtp_response = $rcmail->smtp->get_response();
		$smtp_error = $rcmail->smtp->get_error();

		if (!$sent) {
			if ($smtp_error)
				$rcmail->output->show_message($smtp_error['label'], 'error', $smtp_error['vars']);
			else
				$rcmail->output->show_message('sendingfailed', 'error');
			$rcmail->output->send();
		} else {
			if ($rcmail->config->get('smtp_log')) {
				$log_entry = sprintf("User %s [%s]; Message for %s; %s",
					$rcmail->user->get_username(),
					$_SERVER['REMOTE_ADDR'],
					$mailto,
					"SMTP status: ".join("\n", $smtp_response));
				write_log('sendmail', $log_entry);
			}
			$rcmail->output->command('display_message', $this->gettext('messagebounced'), 'confirmation');
			$rcmail->output->send();
		}
	}

	function render_box($p) {
		$this->add_texts('localization');
		$rcmail = rcmail::get_instance();

		if (!$attrib['id']) {
			$attrib['id'] = 'bounce-box';
			$attrib['title'] = 'Bounce this message';
		}

		$table = new html_table();

		$table->add_row();
		
		$table->add(NULL, html::tag('label', array('for' => 'bounce_to'), rcube_label('to')));
	    $table->add(NULL, html::tag('input', array('type' => "text", 'id' => 'bounce_to', 'name' => '_to', 'value' => '', 'maxlength' => '50', 'class' => 'field' , 'onclick' => 'rcmail.message_list.blur()')));
		 
		$table->add_row();
		$table->add(NULL, html::tag('label', array('for' => 'bounce_cc'), rcube_label('cc')));
		$table->add(NULL, html::tag('input', array('type' => "text", 'id' => 'bounce_cc', 'name' => '_cc', 'value' => '', 'maxlength' => '50', 'class' => 'field' , 'onclick' => 'rcmail.message_list.blur()')));
			
		$table->add_row();
		$table->add(NULL, html::tag('label', array('for' => 'bounce_bcc'), rcube_label('bcc')));
		$table->add(NULL, html::tag('input', array('type' => "text", 'id' => 'bounce_bcc', 'name' => '_bcc', 'value' => '', 'maxlength' => '50', 'class' => 'field' , 'onclick' => 'rcmail.message_list.blur()')));


		$rcmail->output->add_footer(html::div($attrib,
			$rcmail->output->form_tag(array('name' => 'bounceform', 'method' => 'post', 'action' => './', 'enctype' => 'multipart/form-data'),
			html::tag('input', array('type' => "hidden", 'name' => '_action', 'value' => 'bounce')) .
			$table->show()
		)));
		$rcmail->output->add_label('norecipientwarning');
		$rcmail->output->add_gui_object('bouncebox', $attrib['id']);
		$rcmail->output->add_gui_object('bounceform', 'bounceform');

		$this->include_stylesheet('bounce.css');

	}
}
