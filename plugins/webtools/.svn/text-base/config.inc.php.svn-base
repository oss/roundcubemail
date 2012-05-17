<?php

$RUNAS_CMD = '/usr/local/lib64/webtools/bin/runas';
$USERNAME = $_SESSION['username'];
$MF = 'makefile';
$STF = 'statfile';
$MV = 'move';
$CLUSTER = 'mysql-test1';
$USERINFO = 'userinfo';
$RESTORE = 'restoremail';
$SCRIPT_LOG_FACILITY = 'user';

$rcmail_config['RUNAS_CMD'] = $RUNAS_CMD;
$rcmail_config['USERNAME'] = $USERNAME;
$rcmail_config['MF'] = $MF;
$rcmail_config['MV'] = $MV;
$rcmail_config['STF'] = $STF;
$rcmail_config['CLUSTER'] = $CLUSTER;
$rcmail_config['USERINFO'] = $USERINFO;
$rcmail_config['RESTORE'] = $RESTORE;
$rcmail_config['SCRIPT_LOG_FACILITY'] = $SCRIPT_LOG_FACILITY;
$rcmail_config['rutgers_quota_info_url'] = 'http://quota.rutgers.edu';

/* Get $USERNAME home directory */
if ($USERNAME)  // rfranknj sep 29 2010: make sure $USERNAME is set before running it
{
   $exec_target = "$RUNAS_CMD $USERNAME $USERINFO";
   unset($result);
   exec($exec_target, $result, $status);

   if ($status == 0) 
       $rcmail_config['HOME'] = $result[0];

}

$rcmail_config['webtools'] = <<<EOF
<b>Rutgers webtools allow you to easily manage your account.</b>
Using the tabs above you can setup forwarding, vacation messages, filtering, and spam filtering.
You can also view detailed disk usage information and restore lost e-mail from backups.
<ul>
<li><b><a href="?_task=dummy&_action=plugin.webtoolsforward">Forwarding</a></b>: forward incoming mail to another email account</li>
<li><b><a href="?_task=dummy&_action=plugin.webtoolsvacation">Vacation</a></b>: setup an automatic vacation response</li>
<li><b><a href="?_task=dummy&_action=plugin.webtoolsfilter">Filters</a></b>: create custom mail filters</li>
<li><b><a href="?_task=dummy&_action=plugin.webtoolsspam">Spam</a></b>: configure the spam filter</li>
<li><b><a href="?_task=dummy&_action=plugin.webtoolsquota">Quota</a></b>: check your disk space usage</li>
<li><b><a href="?_task=dummy&_action=plugin.webtoolshushquota">Hush Quota</a></b>: enable or disable quota notification emails</li>
<li><b><a href="?_task=dummy&_action=plugin.webtoolsrestore">Restore Email</a></b>: restore email from backups</li>
</ul>
EOF;

/* Subheadlines */
$rcmail_config['forward_subhead'] = "";

$rcmail_config['vacation_subhead'] = "";

$rcmail_config['filtering_subhead'] = "";

$rcmail_config['spam_subhead'] = '<h4>This webtool allows you to adjust the settings related to the '. $CLUSTER . ' spam filter. If you are unfamiliar with '. $CLUSTER .' spam filtering, we strongly recommend reading the full description in the Help section below prior to adjusting these settings.</h4>';

$rcmail_config['quota_subhead'] = '<h4>Here is detailed information about your '. $CLUSTER . ' quota usage.</h4>'; 

$rcmail_config['hush_subhead'] = '<h4>Use this tool to control whether or not this account receives messages warning that it is near or at its quota limit.</h4>';

$rcmail_config['restore_subhead'] = "<h4>Your $CLUSTER email is stored in multiple buildings to reduce the risk of data loss. As a convenience, $CLUSTER users may access their backups 24x7 using this webtool. Even if you've accidently deleted a message, you can retrieve it using this webtool.</h4>";

/* Help */
$rcmail_config['forward_help'] = "Mail forwarding allows you to choose additional email address(es) where your" . strtolower($rcmail_config['CLUSTER']) . " mail should be sent. This can be useful if you have multiple email addresses and wish to access all of your email in a single INBOX.
<br/><br/>
To configure mail forwarding, enter the email address in the box above and choose the Add button [SMALL PICTURE MAYBE CAPTIONED]. To remove an address, click the Remove button [SMALL PICTURE MAYBE CAPTIONED].
<br/><br/>
If you check \"Copy forwarded messages to my local account,\" new messages sent to " . strtolower($rcmail_config['CLUSTER']) . "will go to both the address(es) you have entered and to your" . strtolower($rcmail_config['CLUSTER']) . " account. If this option is unchecked, your " . strtolower($rcmail_config['CLUSTER']) . "account will not receive new messages: only the address(es) you have entered will receive these messages.
<br/><br/>
<b>WARNING: Quota warnings for this account are special messages which cannot be forwarded to another account. Even if you forward all your other mail off of this account, you will only see these messages if you look at this account's INBOX.</b>";

$rcmail_config['vacation_help'] = "This application can be used to answer your mail automatically when you go away on vacation or are trying to communicate a new email address (for example, after graduation) to those who contact you.
<br/><br/>
To configure a vacation message, choose a Subject and enter your message, then click Submit. The message \"You currently have vacation auto-response enabled\" will appear when the vacation webtool is in effect.
<br/><br/>
When you no longer need the automatic response, click the \"Disable vacation\" link. The message will no longer appear when the tool is disabled.";

$rcmail_config['convert_dialog'] = "Your configuration file is in an older format.  Would you like to convert it to be compatible with the new tool?";

$rcmail_config['filtering_help'] = "This is a new webtool; the interface is a work-in-progress. Documentation will follow as the interface stabilizes. To get started,
         <ul>
            <li>Checkbox enables/disables</li>
            <li>Pencil/paper icon edits a filter</li>
            <li>Delete icon removes a filter</li>
         </ul>";

$rcmail_config['spam_help'] = "Spam is a name for unwanted email, usually advertisements. However, what is unwanted to one person may be welcome to another, so there are no universal criteria for filtering out spam system-wide. Contrast this with email viruses, which are always unwelcome and have unique patterns. Our implementation of spam filtering does not actually filter the spam, but instead only tags each mail message with a score for how much it looks like spam.
<br/><br/>
This tool allows you to look at the score a message received and decides what to do with messages that have a score value higher than the value you chose. For example, we recommend that people start off setting this filter level to 5 and have any messages that score a 5 or higher delivered into a folder called AUTO-DELETED-SPAM. You can look at this folder to see if any false positives have occurred - that is, to see if any wanted mail has been placed here by accident. You can then adjust the filter trigger value as needed, depending on your personal definition of what score would constitute spam. You can also allow specific users or domains to not be considered spam, or to always be allowed. This is known as whitelisting or blacklisting by user or by domain. Note that whitelisting or blacklisting takes precedence, i.e. if you say \"ALWAYS treat mail from domain.com as SPAM\" then those messages will be filtered regardless of the score.
<br/><br/>
Every night, we will delete all messages that are older than a certain number of days from an account's AUTO-DELETED-SPAM folder. You can set the number of days, between 1 and 30, in the filter. You can, of course, delete the messages in this folder yourself more frequently.
<br/><br/>
As you gain more experience in how the spam filter operates, you may choose to have it automatically delete messages that score above a certain value, or those from a certain domain, or user. Be warned that, if you cause a legitimate message to be deleted immediately, we have no way of getting that message back. For this reason, you should use the \"Delete the spam\" option cautiously.
<br/><br/>
The spam filter software we have installed is based on the popular SpamAssassin program. All incoming messages are scored according to complex formulas as to the likelihood of their being spam. For example, if a message Subject mentions Viagra, it gets a score of +2.896. If it was sent by the Pine mail program (which spammers hardly ever use), it gets a +0.001. These scores are all added together and a final value assigned to the message. A list of the criteria used and scores assigned may be found on the SpamAssassin web site.
<br/><br/>
If you have any questions about the use of the filter, you may write to the help address (help@" . strtolower($rcmail_config['CLUSTER']) . ".rutgers.edu), or you may call the OIT Help Desk at 732-445-4357 (HELP).";

$rcmail_config['quota_help'] = "";

$rcmail_config['hush_help'] = "The \"hush\" feature is disabled by default.  That is, if you take no action,
            you will receive an e-mail warning when you are approaching your account's
            quota. Use this tool only if you want to stop such messages (\"hush\" them).
            <br /><br />
            The quota warnings are special messages which cannot be forwarded to
            another account.  Even if you forward all your other mail off of this
            account, you will only see these messages if you look at this account's
            Inbox.";

$rcmail_config['restore_help'] = "This tool will restore one old mail folder at a time to a new folder called \"RESTORE.\"
            <br /><br />
            If you already have a \"RESTORE\" folder, using this tool will replace it with the new restored folder
            you have selected. If you need to restore mail from more than one folder, restore one folder
            first, retrieve the mail you need from it, then come back here and restore the next folder.
            <br /><br />
            Folders are available from the nightly backups of the past week, and the weekly backups of the past two months.";


$rcmail_config['hush_enable_msg']='Click "Enable" to STOP receiving quota related messages.';

$rcmail_config['hush_disable_msg']='You currently have "hush" enabled. Click "Disable" to START receiving such messages again.';

$rcmail_config['filter_parse_character'] = "\06";

$rcmail_config['warning_comment'] = <<<EOF
#############################################
# THIS FILE WAS CREATED BY A WEBTOOL.       #
# DO NOT EDIT THIS FILE BY HAND.            #
#############################################\n
EOF;

?>
