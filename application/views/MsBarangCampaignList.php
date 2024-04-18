<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
	.btnEdit, .btnDelete, .btnView {
		cursor: pointer;
	}
	#formTitle {
		font-weight: bold;
		font-size:20pt;
	}
</style>
<script>
	var mode='';

	$(document).ready(function(){
	
	});
</script>

<div class="container">
<div class="form_title">PERSIAPAN BARANG CAMPAIGN</div>
<div class="clearfix">
</div>
<div class="clearfix">
	<table id="tblCampaignPlan" class="table table-striped table-bordered" cellspacing="0">
		<thead>
			<tr>
				<th scope="col" width="25%">Plan</th>
				<th scope="col" width="20%">Periode</th>
				<th scope="col" width="15%">Status</th>
				<th scope="col" width="15%" class='hideOnMobile'>CreationInfo</th>
				<th scope="col" width="15%" class='hideOnMobile'>ApprovalInfo</th>
		        <th scope="col" width="10%"></th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($CampaignPlans as $p)
		{
			echo("<tr>");
			echo("	<td><b>".$p->CampaignName."</b><br>".$p->Division."<br>".$p->CampaignID."</td>");
			echo("	<td>".date("d-M-Y", strtotime($p->CampaignStart))." s/d<br>".date("d-M-Y", strtotime($p->CampaignEnd))."<br>Jumlah Hari Kerja: ".$p->JumlahHari."</td>");
			echo("	<td>".$p->CampaignStatus."</td>");
			echo("	<td class='hideOnMobile'>".$p->CreatedBy."</td>");
			if ($p->ApprovedBy==null) {
				echo("	<td class='hideOnMobile'></td>");				
			} else {
				echo("	<td class='hideOnMobile'>".$p->ApprovedBy."</td>");				
			}

			echo("	<td>");
            if($_SESSION["can_read"] == 1) {
            	echo("	<a href='PersiapanBarangCampaign/view?trxid=".urlencode($p->CampaignID)."', target='_blank'><button class='btnView' planid='".$p->CampaignID."'><i class='glyphicon glyphicon-eye-open'></i></button></a>");
            }
            if($_SESSION["can_update"] == 1) {
            	echo("	<button class='btnEdit' planid='".$p->CampaignID."'><i class='glyphicon glyphicon-pencil'></i></button>");
            }
            if ($_SESSION["can_delete"]==1) {
             	echo "	<button class='btnDelete' planid='".$p->CampaignID."'><i class='glyphicon glyphicon-trash'></i></button>"; 
            }
            echo("	</td>");
			echo("</tr>");
		}?>
		</tbody>
	</table>
</div>


</div>
	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<div style='margin:10px;float:left;'>
			<?php if($_SESSION["can_create"]==1) { ?><div class="btn btnAdd" id="btnAdd">NEW PLAN</div><?php } ?>
			</div>
		</div>
	</div>
</div>