<?php

require_once ('functions.inc');
if (have_file ('Maildir/mailfilter-forward', $result))
   $data = parse_forward_file ($result);

$rcmail = rcmail::get_instance();
$CLUSTER = $rcmail->config->get('CLUSTER');
$subhead = $rcmail->config->get('forward_subhead');

?>

<style type="text/css">
   .emailstyle
   {
      font-size: 16px;
      margin: 0px;
      padding: 0px;
   }
   .tablestyle
   {
      padding: 10px;
      margin: auto;
      width: 500px;
      background-color: #FeFeFe;
   }
   .tablestyle td
   {
      padding: 5px;
      margin: 0px;
   }
   .tablestyle tr
   {
      margin: 0px;
   }
   .tablestyle input
   {
      font-size: 20px;
   }
   .tablestyle label
   {
      font-size: 16px;
   }
   .rowstyle0
   {
      background-color: #DEDEDE;
   }
   .rowstyle1
   {
      background-color: #EEEEEE;
   }
   .cbutton
   {
      width: 32px;
      height: 32px;
   }
</style>

<h2>Forwarding</h2>

<div id="webtools-subhead">
    <?=$subhead?>
</div>

<div id="dialog-test" style="display:none" title="Confirm">Are you sure you would like to remove all of your forwards?</div>

<div id="webtools-innerbox" class="ui-widget ui-widget-content ui-corner-all">
 <!--  <form id="forward-form" onsubmit="forward_add(); return false">-->
      <table class="tablestyle" id="forward-table">
         <tr>
            <td>
               <div class="roundbox  buttonstyle ui-button ui-widget ui-state-default ui-corner-all">
                  <a href="#" onclick="forward_add()"><img src="plugins/webtools/webtools/forward/list-add.png" /></a>
               </div>
            </td>
            <td>
               <input id="email" name="email" class="ui-corner-all" type="text" size="40" maxlength="129"  />
	    </td>
         </tr>
         <?php
            if (isset($data))
               foreach ($data['emails'] as $email)
               {
                  $id = str_replace (array('@', '.'), '', $email);
                  if ($rowstyle == 'rowstyle0') $rowstyle = 'rowstyle1';
                  else $rowstyle = 'rowstyle0';
                  echo ('<tr class="'.$rowstyle.'" id="'.$id.'"><td><a href="#" onclick="forward_remove(\''.$email.'\')">'.
                        '<img src="plugins/webtools/webtools/forward/delete.png" />' .
                        '</a></td><td><p class="emailstyle">'.$email.'</p></td></tr>');
               }
            $num_forwards = count($data['emails']);
            echo ("<script type=\"text/javascript\">num_forwards = $num_forwards;");
            if ($num_forwards == 0)
            {
               echo ("$(document).ready( function(){
                  $('#removeall').hide();
                  $('label[for=localcopy-check],#localcopy-check').hide();})");
				  //$('#localcopy-check').attr('disabled', true);});");
            }

            echo ("</script>");
         ?>
         <tr id="localcopy-row">
            <td>
               <input type="checkbox" id="localcopy-check" name="localcopy-check" onclick="forward_chtype()" <?=($data['type']=='cc'?"checked":"")?> />
            </td>
            <td>
               <label for="localcopy-check">Copy forwarded messages to my local account</label>
            </td>
         </tr>
         <tr> <td></td>
            <td>
               <div style="text-align:right;"><button id="removeall" onclick="delall_dialog()" style="font-size:16px" class="buttonstyle ui-button ui-widget ui-state-default ui-corner-all">Remove All Forwards</button></div>
            </td>
         </tr>
      </table>
   <!--</form>-->
</div>

<div id="webtools-instructions" class="webtools-accordion">
    <div>
        <h3 class="roundbox helpbox"><a id="instr-header" href="#">Help</a></h3>
        <div id="instr-content" class="helptext">
	         <?=$rcmail->config->get('forward_help')?>
	</div>
    </div>
</div>

