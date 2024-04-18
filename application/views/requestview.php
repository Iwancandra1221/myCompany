<script>
  $(document).ready(function(){
  });
</script>

<link href="<?php echo base_url('css/style.css');?>" rel="stylesheet">
<div class="container" style="padding-left:10px;">
  <div align="center">
    <h3>PERSETUJUAN PERISTIWA</h3>
  </div>
  <?php if ($isForm==true) { ?>
    <?php echo form_open($formURL); ?>
      <input type='text' id='TxtRequestNo' name='TxtRequestNo' value='<?php echo $RequestNo ;?>' style='display:none;'>
      <input type='text' id='TxtRequestType' name='TxtRequestType' value='<?php echo $RequestType ;?>' style='display:none;'>
      Catatan : <textarea id="TxtNote" name="TxtNote" rows="4" cols="50"></textarea><br>
      <button id='btnApprove' name="btnApprove" style='background-color:#49f53d;padding:5px;margin-right:5px;'>APPROVE</button>
      <button id='btnReject'  name="btnReject"  style='background-color:#f53333;padding:5px;margin-right:5px;'>REJECT</button>
    <?php echo form_close(); ?>
  <?php } else { ?> 
    <div class="buttons"> 
      <?php if ($button_approve!="") { ?>
      <a href="<?php  echo $button_approve; ?>"><button><div style='width:75px;background-color:#005400;color:#fff;'> APPROVE </div></button></a>
      <?php } ?>
      <?php if ($button_reject!="") { ?>
      <a href="<?php  echo $button_reject; ?>"><button><div style='width:75px;background-color:#9e0008;color:#fff;'> REJECT </div></button></a>
      <?php } ?>
      <button><a href="<?php echo site_url('Approvallist'); ?>">BACK</a></button>
    </div>
  <?php } ?> 
  <div style="height:25px"></div>
  <div class="contents">
    <?php  if(isset($content_html)) echo $content_html; ?>
    <?php  if(isset($request_table)) echo $request_table; ?>
  </div>
</div>