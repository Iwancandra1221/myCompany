    <link href='<?php echo base_url(); ?>css/datetimepicker.css' rel='stylesheet' type='text/css'>
    <script src="<?php echo base_url()?>js/vendor/jquery-1.10.2.min.js"></script>
    <script src="<?php echo base_url()?>js/vendor/datatable/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url()?>js/vendor/jquery.datetimepicker.js"></script>
    <script src="<?php echo base_url()?>js/vendor/phpjs.js"></script>
    <script src="<?php echo base_url()?>js/lib.js"></script>
    <script src="<?php echo base_url()?>js/main.js"></script>
    <link rel="icon" href="<?php echo base_url();?>images/icon.png" type="image/x-icon">

    <!-- Bootstrap -->
    <link href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css');?>" rel="stylesheet"><!-- * -->
    <script src="<?php echo base_url('assets/bootstrap/js/bootstrap.min.js');?>"></script>
    <link href="<?php echo base_url('assets/bootstrap/css/datatables.bootstrap.min.css');?>" rel="stylesheet"><!-- * -->
    <script src="<?php echo base_url('assets/bootstrap/js/datatables.bootstrap.min.js');?>"></script>
    <script src="<?php echo base_url('assets/bootstrap/js/datatables.min.js');?>"></script>
 
<script>
  var msg = "<?php echo($ALERT);?>";

  activateDatepicker = function(){
    $('.datepicker').datetimepicker({
      scrollMonth:false,
      lang:'en',
      timepicker:false,
      format:'m/d/Y',
      formatDate:'m/d/Y',
      todayButton:true
    });
  }

  $(document).ready(function(){
    if (msg!="") alert(msg);
    activateDatepicker();
    $('.datepicker').datetimepicker({minDate:0});
    $('.datepicker').datetimepicker({maxDate:'<?php echo($REQUEST->DefaultEndDateStr2);?>',formatDate:'m/d/Y'});

    $("#TxtUserEmail").on('change', function() {
      var user = $("#TxtUserEmail").val();
      var note = "<i>input password login akun "+user+" ke www.bhakti.co.id</i>";
      $(".info-login").html(note);
    }); 

  });
</script>

<link rel="stylesheet" href="<?php echo base_url()?>css/style.css">

<style>
  .info-login { font-size:12pt!important; }
  #EndDate { width:150px!important;background-color: yellow; }
  td { padding:5px; font-size:11pt!important;vertical-align: top; }
</style>

<?php //echo(json_encode($REQBY));?>
<div class="container">
  <div style="padding:15px;font-size:12pt;margin-top:50px">
  <b><?php echo($REQBY->NAME);?></b> mengajukan request unlock dealer terkunci<br>
  Nama Dealer : <b><?php echo($REQUEST->NmPlg);?></b><br>
  Kode Dealer : <b><?php echo($REQUEST->KdPlg);?></b><br>
  Wil Dealer : <b><?php echo($REQUEST->Wilayah);?></b><br>
  Catatan : <b><?php echo($REQUEST->RequestNote);?></b><br>
  ----------<br>
  ID Request : <b><?php echo($REQUEST->RequestID);?></b><br>
  Direquest Tgl : <b><?php echo(date("d-M-Y H:i:s",strtotime($REQUEST->RequestDate)));?></b><br>
  Direquest Oleh : <b><?php echo($REQUEST->ReqName);?></b><br>
  Status Request : <b><?php echo(($REQUEST->IsCancelled==1)?"CANCELLED":(($REQUEST->IsApproved==1)?"APPROVED":(($REQUEST->IsApproved==2)?"REJECTED":"UNPROCESSED")));?></b><br>
  ----------<br>
  <?php if ($REQUEST->IsCancelled==1) { ?>
  <font color="#8c8c91">
  Dibatalkan Oleh : <b><?php echo($REQUEST->CanName);?></b><br>
  Dibatalkan Tgl : <b><?php echo(date("d-M-Y H:i:s",strtotime($REQUEST->CancelledDate)));?></b><br>
  Alasan Batal: <b><?php echo($REQUEST->CancelledNote);?></b><br>
  </font>
  <?php } else if ($REQUEST->IsApproved==1) { ?>
  <font color="#030891">
  Diapproved Oleh : <b><?php echo($REQUEST->AppName);?></b><br>
  Diapproved Tgl : <b><?php echo(date("d-M-Y H:i:s",strtotime($REQUEST->ApprovedDate)));?></b><br>
  Catatan : <b><?php echo($REQUEST->ApprovalNote);?></b><br>
  </font>
  <?php } else if ($REQUEST->IsApproved==2) { ?>
  <font color="#d91a47">
  Direject Oleh : <b><?php echo($REQUEST->AppName);?></b><br>
  Direject Tgl : <b><?php echo(date("d-M-Y H:i:s",strtotime($REQUEST->ApprovedDate)));?></b><br>
  Catatan : <b><?php echo($REQUEST->ApprovalNote);?></b><br>
  </font>
  <?php } ?>
  <?php if ($REQUEST->IsApproved==0 && $REQUEST->IsCancelled==0) { ?>
  <?php if ($EXPIRED==true) { ?>
    <font color="red"><b>Request ini Sudah Expired, Harus Diajukan Ulang<b></font>
  <?php } else { ?>
    ----------<br>
    <?php //echo form_open(site_url('MsDealer/ApproveRejectUnlock'), array("target"=>"_blank")); ?>
    <?php echo form_open(site_url('MsDealerApproval/ApproveRejectUnlock?mc='.$mc)); ?>
    <table>
      <tr>
        <td colspan="2">
          <input type='text' id='TxtRequestNo' name='TxtRequestNo' value='<?php echo($REQUEST->RequestID);?>' style='display:none;'>
          <input type='text' id='TxtUserEmail' name='TxtUserEmail' value='<?php echo($APPROVER);?>' style='display:none;'>
        </td>
      </tr>
      <tr>
        <td>Catatan </td>
        <td><textarea id="TxtNote" name="TxtNote" rows="2" cols="50"><?php echo($NOTE)?></textarea></td>
      </tr>
      <tr>
        <td>Unlock S/D Tgl </td>
        <td><input type='text' class='form-control datepicker datepickerInput' id='EndDate' name='EndDate' required value='<?php echo(($ENDDATE!="")?$ENDDATE:$REQUEST->DefaultEndDateStr2);?>'></td>
      </tr>
  
	<?php
		if((ISSET($_SESSION['logged_in'])) && ($_SESSION['logged_in']['useremail']==$APPROVER)){
		}
		else{
	?>
      <tr>
        <td>Password </td>
        <td><input type='password' id='TxtUserPwd' name='TxtUserPwd'>
            <div class='info-login'><i>input password login akun ke www.bhakti.co.id</i></div>
        </td>
      </tr>
	<?php } ?>
    </table>
    <button id='btnApprove' name="btnApprove" style='background-color:#49f53d;padding:5px;margin:5px;'>APPROVE</button>
    <button id='btnReject'  name="btnReject"  style='background-color:#f53333;padding:5px;margin:5px;'>REJECT</button>
    <?php echo form_close(); ?>
  <?php } ?>
  <?php } ?>

  <?php if ($mc==1) { ?>
    <a href="<?php echo(site_url('Dashboard'));?>"><button id='btnBack'  name="btnBack"  style='background-color:#e8e874;padding:5px;margin:5px;'>BACK TO DASHBOARD</button></a><br><br>
  <?php } ?>

  </div>
</div>