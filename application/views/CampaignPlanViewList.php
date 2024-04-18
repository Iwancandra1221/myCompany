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

	.planCancelled { color:#4e5450;} 
	.planDraft { color:red; }
	.planSaved { color:#000d9c;}
	.planWaiting { color:#cf8104;}
	.planApproved { color:#17542b;}
	.planRejected { color:#a10261;}
	.hideMe { display:none; }
</style>

<script type="text/javascript">
    $(document).ready(function(){
         
        $('#TblCampaign').dataTable();
         
  	});
 </script>
 
 <?php 
 	// die($this->API_URL."/CampaignPlanView/GetCampList?api=APITES");
 	$data=json_decode(file_get_contents($this->API_URL."/CampaignPlanView/GetCampList?api=APITES"),true); 
 	// die(json_encode($data));
 ?>

<div class="container">

<div class="col-12" align="center" style="margin-top:50px;">
	<div class="page-title" align="center">RENCANA CAMPAIGN / INTERVENSI</div>
</div>

<div class="clearfix">
	<table id="TblCampaign" class="table table-striped table-bordered" cellspacing="0">
		<thead id="TblCampaignHead">
			<tr>
				<td width="15%">ID Transaksi</td>
				<td width="20%">Keterangan</td>
				<td width="15%">Divisi</td>
				<td width="15%">Status</td>
				<td width="15%">Tipe</td>
		        <td width="10%"></td>
			</tr>
		</thead>
		<tbody>
			<?php 
				if($data!=0){
					foreach ($data as $key => $d) {
						$acak=str_replace("=", "", base64_encode($d['CampaignID']));
						$acak2=str_replace("=", "", base64_encode($d['TypeTrans']));
			?>
						<tr>
							<td>
								<?php echo $d['CampaignID']; ?>
							</td>
							<td>
								<?php echo $d['CampaignName']; ?>
							</td>
							<td>
								<?php echo $d['Divisi']; ?>
							</td>
							<td>
								<?php echo 'APPROVED ('.$d['ApprovedBy'].' - '.$d['ApprovedDate'].')'; ?>
							</td>
							<td>
								<?php echo $d['TypeTrans']; ?>
							</td>
					        <td align="center">
					        	<a href="<?php echo site_url('/CampaignPlanView/view/'.$acak.'/'.$acak2); ?>">
					        		<button class="btnView" planid="8">
					        			<i class="glyphicon glyphicon-eye-open"></i>
					        		</button>
					        	</a> 
					        	<a href="<?php echo site_url('/CampaignPlanView/edit/'.$acak.'/'.$acak2); ?>">
					        		<button class="btnEdit" id="btnEdit11" idx="11" planid="11">
					        			<i class="glyphicon glyphicon-pencil" style="color:green;"></i>
					        		</button>
					        	</a>                  
					        </td>
						</tr>

			<?php
					}
				}
			?>
		</tbody>
	</table>
</div>


</div>
</div>