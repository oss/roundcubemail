<?php
require_once('plugins/webtools/webtools/functions.php');
$rcmail = rcmail::get_instance();
?>

<script type="text/javascript">
   $(document).ready(
      function()
      {

         rcmail.http_post('plugin.webtools.quota.load', {});

      }
   );

   function load_response (response)
   {
      $('#webtools-innerbox').html(response.html);
      $('#loading-bar').slideUp();
      $('#webtools-innerbox').slideDown();
   }

   rcmail.addEventListener('plugin.webtools.quota.load.response', load_response);
</script>

<h2>Quota</h2>

<div id="webtools-subhead">
    <?=$rcmail->config->get('quota_subhead')?>
</div>

<div style="width:100%;text-align:center" id="loading-bar">
<img src="plugins/webtools/webtools/quota/load.gif" />
</div>

<div id="webtools-innerbox" class="ui-widget ui-widget-content ui-corner-all" style="display:none">
</div>

<div id="webtools-instructions" class="webtools-accordion">
    <div>
        <h3><a id="instr-header" href="#">Help</a></h3>
        <div id="instr-content">
        	          <?=$rcmail->config->get('quota_help')?>
  	</div>
    </div>
</div>

