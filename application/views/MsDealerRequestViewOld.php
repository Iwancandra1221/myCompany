<script>
  var msg = "<?php echo($alert);?>";

  $(document).ready(function(){
    if (msg!="") alert(msg);
    
    $("#TxtUserEmail").on('change', function() {
      var user = $("#TxtUserEmail").val();
      var note = "<i>input password login akun "+user+" ke www.bhakti.co.id</i>";
      $(".info-login").html(note);
    }); 

  });
</script>

<style>
  .info-login { font-size:12pt!important; }
  #TxtUserEmail { background-color: yellow!important; }
</style>
<link href="<?php echo base_url('css/style.css');?>" rel="stylesheet">

<div style="height:50px;"></div>
<div class="container" style="padding-left:10px;">

  <?php  if(isset($request_table)) echo ($content_html); ?>
  <?php  if(isset($request_table)) echo ($request_table); ?>

  <?php  if(isset($data)) { ?>
  <?php
    if ($data["status"]=="UNPROCESSED") {
      $request_type = "";
      if ($RequestType=="CREDIT LIMIT") {
        $request_type = "Kenaikan Credit Limit";
      } else if ($RequestType=="CBD ON") {
        $request_type = "Pengaktifan Status CBD";
      } else if ($RequestType=="CBD OFF") {
        $request_type = "Penonaktifan Status CBD";
      }

      $REQ = $data["data"];
      $request_content = "";
      $request_content.= "<br><br>Request ".$request_type." ini Berlaku S/D Tanggal <b>".date("d-M-Y", strtotime($REQ->ExpiryDate))." 23:59:59</b><br>";
      $request_content.= "Jumlah Approval Yang Dibutuhkan : <b>".$data["approved_count"]."/".$data["approval_needed"]."</b><br><br>";
      echo($request_content);

      // echo("mc=".$mc."<br>");
  
      if ($viewOnly==false) {
      ?>
        <?php echo form_open(site_url('MsDealerApproval/ApproveRejectRequest?mc='.$mc)); ?>
        <input type='text' id='TxtRequestNo' name='TxtRequestNo' value='<?php echo($RequestNo);?>' style='display:none;'>
        <input type='text' id='TxtRequestType' name='TxtRequestType' value='<?php echo($RequestType);?>' style='display:none;'>
        Catatan : <textarea id="TxtNote" name="TxtNote" rows="4" cols="50"></textarea><br>
        <b>Pilih User: 
        <select id="TxtUserEmail" name="TxtUserEmail">
          <option value='-'>-- Pilih User--</option>
        <?php foreach($approver as $a) { 
          echo("<option value='".$a->ApprovedBy."'>".$a->ApprovedByName."</option>");
        } ?>
        </select></b>
        Password : <input type='password' id='TxtUserPwd' name='TxtUserPwd'><br>
        <div class='info-login'><i>input password login akun ke www.bhakti.co.id</i></div><br><br>
        <button id='btnApprove' name="btnApprove" style='background-color:#49f53d;padding:5px;margin-right:5px;'>APPROVE</button>
        <button id='btnReject'  name="btnReject"  style='background-color:#f53333;padding:5px;margin-right:5px;'>REJECT</button>
        <br><br>
        <?php echo form_close(); ?>
      <?php } ?> <!--$viewOnly -->
  <?php } ?> <!--status UNPROCESSED -->

  <?php if ($mc==1) { ?>
    <a href="<?php echo(site_url('Dashboard'));?>"><button id='btnBack'  name="btnBack"  style='background-color:#e8e874;padding:5px;'>BACK TO DASHBOARD</button></a><br><br>
  <?php } ?>

  <?php } ?> <!--isset $data -->

</div>