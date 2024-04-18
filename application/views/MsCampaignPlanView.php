<style>
	.draft {
		color:white; background-color:red;
		margin-left:25px; width:100px;
		text-align: center; padding-left:5px;padding-right:5px;
	}
	#table-primary>tbody {
	font: normal medium/1.4 sans-serif;
	}
	
	#table-primary {
	border-collapse: collapse;
	width: 100%;
	}
	
	#table-primary th,
	td {
	padding: 0.25rem;
	text-align: left;
	border: 1px solid #ccc;
	}
	
	#table-primary th {
	background: #bfbfbf;
	}
	
	#table-primary>tbody>tr.zebra-even {
	background-color: #fff;
	}
	
	#table-primary>tbody>tr.zebra-odd {
	background-color: #EEEEEE;
	}
	
	.filterDropdown {
	width: 100%;
	background-color: #ffffcc;
	}
	
	.filterText {
	width: 75%;
	background-color: #ffffcc;
	}
	
	.btn {
		font-size:12px!important;
	}

	.title {
	font-size: 15pt;
	font-weight: bold;
	text-align: center;
	}
	
	.text-right{
		text-align: right;
	}

	.campaign-plan-card {
		padding-left:20px;padding-right:20px;margin-top:20px; margin-bottom:20px;
	}

	.div-content {
		font-size:14px; font-weight: bold; color:navy;
		border-bottom:1px solid #ccc; padding-bottom:0px!important;
	}
	.subdiv-content {
		font-size:11px; font-weight: bold; color:navy;
		border-bottom:1px solid #ccc;
	}
</style>

<script>
	var CampaignID = "<?php echo($CampaignID);?>";
	var campaigns = <?php echo(json_encode($campaigns));?>;
	

  	function number_format(number, decimals, decPoint, thousandsSep){
      decimals = decimals || 0;
      number = parseFloat(number);

      if(!decPoint || !thousandsSep){
          decPoint = '.';
          thousandsSep = ',';
      }

      var roundedNumber = Math.round( Math.abs( number ) * ('1e' + decimals) ) + '';
      // add zeros to decimalString if number of decimals indicates it
      roundedNumber = (1 > number && -1 < number && roundedNumber.length <= decimals)
              ? Array(decimals - roundedNumber.length + 1).join("0") + roundedNumber
              : roundedNumber;
      var numbersString = decimals ? roundedNumber.slice(0, decimals * -1) : roundedNumber.slice(0);
      var checknull = parseInt(numbersString) || 0;
  
      // check if the value is less than one to prepend a 0
      numbersString = (checknull == 0) ? "0": numbersString;
      var decimalsString = decimals ? roundedNumber.slice(decimals * -1) : '';
      
      var formattedNumber = "";
      while(numbersString.length > 3){
          formattedNumber = thousandsSep + numbersString.slice(-3) + formattedNumber;
          numbersString = numbersString.slice(0,-3);
      }

      return (number < 0 ? '-' : '') + numbersString + formattedNumber + (decimalsString ? (decPoint + decimalsString) : '');
  	}  
 
	$(document).ready(function() {

		$("#btnRequest").click(function(){
			var isDisabled = $("#btnRequest").prop('disabled');
			if(!isDisabled){
				$("#FormCampaignPlan").submit();
			}
		});
								
		<?php
			if($status=='view'){
			?>
			$("#btnBack").hide();
			$("#btnClose").show();
			<?php
				}else{
			?>
			$("#btnBack").show();
			$("#btnClose").show();
			<?php
			}
		?>

	});
</script>

<?php
	$view_only = ($status=='view') ? "disabled" : "";
	$plan_status = $headers[0]->CampaignStatus;
?>

<?php echo form_open('CampaignPlan/CreateRequest?trxid='.urlencode($CampaignID), array("id" => "FormCampaignPlan", "target"=>"_blank")); ?>

<div class="container">	
	<div class="title">PERENCANAAN CAMPAIGN</div>
	<div class="campaign-plan-card">
		<div class="row">
			<div class="col-3 col-m-4">Nama Rencana</div>
			<div class="col-9 col-m-8 div-content"><?php echo ($headers[0]->CampaignName); ?></div>			
		</div>
		<div class="row">
			<div class="col-3 col-m-4">Kode Rencana</div>
			<div class="col-9 col-m-8 div-content"><?php echo ($headers[0]->CampaignID); ?></div>			
		</div>
		<div class="row">
			<div class="col-3 col-m-4">Periode</div>
			<div class="col-9 col-m-8 div-content"><?php echo (date("d-M-Y", strtotime($headers[0]->CampaignStart))); ?> s/d <?php echo (date("d-M-Y", strtotime($headers[0]->CampaignEnd)));?></div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4">Jumlah Hari Kerja</div>
			<div class="col-9 col-m-8 div-content"><?php echo ($headers[0]->JumlahHari); ?></div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4">Divisi</div>
			<div class="col-9 col-m-8 div-content"><?php echo ($headers[0]->Division); ?></div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4">Status</div>
			<div class="col-9 col-m-8 div-content">
				<?php 
				if ($headers[0]->CampaignStatus=="APPROVED") {
				echo ("<font color='green'>".$headers[0]->CampaignStatus."</font> [ ".$headers[0]->ApprovedByName." - ".date("d-M-Y H:i:s", strtotime($headers[0]->ApprovedDate))." ] ");
				} else if ($headers[0]->CampaignStatus=="REJECTED") {
				echo ("<font color='green'>".$headers[0]->CampaignStatus."</font> [ ".$headers[0]->ApprovedByName." - ".date("d-M-Y H:i:s", strtotime($headers[0]->ApprovedDate))." ] ");	
				} else {
				echo ($headers[0]->CampaignStatus);		
				}
				?>
			</div>
		</div>
		<?php if ($headers[0]->IsApproved==2) {?>
		<div class="row">
			<div class="col-3 col-m-4"></div>
			<div class="col-9 col-m-8 div-content">Alasan : <?php echo (($headers[0]->CancelNote=="")?"-":$headers[0]->CancelNote);?></div>
		</div>
		<?php } ?>
		<div class="row">
			<div class="col-3 col-m-4">Daftar Wilayah</div>
			<div class="col-9 col-m-8">
			<?php
			$sisaCol = 6; 
			foreach($wilayahs as $w) {
				if ($sisaCol==6) {
					echo("<div class='row'>");
				}
				echo("	<div class='col-2 col-m-2'>- ".$w->Wilayah."</div>");
				if ($sisaCol==1) {
					echo("</div>");
					$sisaCol = 6;
				} else {
					$sisaCol-= 1;
				}
			}?>
			</div>
		</div>
	</div>

	<div class="form">
		<ul class="nav nav-tabs">
			<?php foreach ($barang as $index => $tab) :
				if ($index == 0) { ?>
				<li class="active"><a data-toggle="tab" href="#menu<?php echo $index; ?>"><?php echo $tab; ?></a></li>
				<?php } else { ?>
				<li><a data-toggle="tab" href="#menu<?php echo $index; ?>"><?php echo $tab; ?></a></li>
				<?php }
			endforeach; ?>
		</ul>
		
		<div class="tab-content">
			<?php
			$counter = 0;  
			foreach ($barang as $index => $tab) :
				$counter += 1;
			?>					
				<div id="menu<?php echo $index; ?>" class="tab-pane fade <?php if ($index == 0) echo "in active" ?>">
					<?php //echo(json_encode($headers)."<br>");?>
				<!-- <div id="menu<?php echo $index; ?>"> -->
					<div class="campaign-plan-card">
						<div class="row">
							<div class="col-m-4 col-m-4">
								Produk: <span class="subdiv-content"><?php echo($tab);?></span>
							</div>
							<div class="col-m-4 col-m-5">
								Periode:<span class="subdiv-content"><?php echo (date("d-M-Y", strtotime($headers[$index]->CampaignStart))); ?> - <?php echo (date("d-M-Y", strtotime($headers[$index]->CampaignEnd))); ?></span>
							</div>
							<div class="col-m-4 col-m-3">
								Jumlah Hari:<span class="subdiv-content"><?php echo ($headers[$index]->JumlahHari); ?></span>
							</div>
						</div>
						<table id="table-product" class="table table-striped table-bordered" cellspacing="0">
							<thead id="theadBarangCampaign">
								<tr>
									<th scope="col" width="20%" class="hideOnMobile" style="border:1px solid #ccc;">Kode Barang</th>
									<th scope="col" width="30%" class="hideOnMobile" style="border:1px solid #ccc;">Nama Transaksi</th>
									<th scope="col" width="5%"  class="hideOnMobile" style="border:1px solid #ccc;">Jumlah Hari</th>
									<th scope="col" width="10%" class="hideOnMobile" style="border:1px solid #ccc;">Qty</th>
									<th scope="col" width="5%"  class="hideOnMobile" style="border:1px solid #ccc;">Avg Per Hari</th>
									<th scope="col" width="5%"  class="hideOnMobile" style="border:1px solid #ccc;">Jumlah Hari Rencana</th>
									<th scope="col" width="10%" class="hideOnMobile" style="border:1px solid #ccc;">Total Qty Rencana</th>
									<th scope="col" width="90%" class="colMobile" style="width:100%;height:40px;line-height:40px;vertical-align:bottom;">Data</th>
									<!-- <th width="5%" class="" style="border:1px solid #ccc;">Pilih</th> -->
								</tr>
							</thead>
							<tbody id="tbodyBarangCampaign2">
							<?php
							$breakdowns = array();
							for($i=0; $i<count($campaigns);$i++) {	
								if (strtoupper($campaigns[$i]["Kd_Brg"])==strtoupper($tab)) {
									$breakdowns = $campaigns[$i]["Breakdown_Per_Wilayah"];
									$value=0;
									$isChecked="";

									$BRG = strtoupper($campaigns[$i]["Kd_Brg"]);
									$TRX = ($campaigns[$i]["Jns_Trx"]=="")?"-":"<b>".$campaigns[$i]["Nm_Trx"]."</b><br>".$campaigns[$i]["Jns_Trx"];
									$TOTAL_HARI = $campaigns[$i]["Total_Hari"];
									$TOTAL_JUAL = number_format($campaigns[$i]["Total_Jual"]);
									$AVG = number_format($campaigns[$i]["Avg"]);
									// $AVG_NORMAL = "0";
									$TOTAL_HARI_PLAN = $campaigns[$i]["Total_Hari_Plan"];
									$TOTAL_AVG = number_format($campaigns[$i]["Total_Avg"]);

									$brs = "<tr>";
									$brs.= "	<td class='hideOnMobile'>".$BRG."</td>";
									$brs.= "	<td class='hideOnMobile'>".$TRX."</td>";
									$brs.= "	<td class='hideOnMobile'>".$TOTAL_HARI."</td>";
									$brs.= "	<td class='hideOnMobile'>".$TOTAL_JUAL."</td>";
									$brs.= "	<td class='hideOnMobile'>".$AVG."</td>";
									$brs.= "	<td class='hideOnMobile'>".$TOTAL_HARI_PLAN."</td>";
									$brs.= "	<td class='hideOnMobile'>".$TOTAL_AVG."</td>";
									$brs.= "	<td class='colMobile'>".$TRX."<br>".
											"TotalJual:".$TOTAL_JUAL."<br>".
											"TotalHari:".$TOTAL_HARI." Hari<br>".
											"AvgJual: ".$AVG."<br>";
									$brs.=  "TotalHari: ".$TOTAL_HARI_PLAN."<br>".
											"TotalQty: ".$TOTAL_AVG."</td>";
									$brs.="</tr>";
									echo($brs);
								}
							}?>
							</tbody>
						</table>
						<table id="table-breakdown-<?php echo($counter);?>" class="table table-striped table-bordered" cellspacing="0">
							<thead id="theadBreakdownWilayah-<?php echo($counter);?>">
								<tr>
									<th scope="col" style="border:1px solid #ccc;">Wilayah</th>
									<th scope="col" style="border:1px solid #ccc;">Persentase</th>
									<th scope="col" style="border:1px solid #ccc;">TotalQty</th>
								</tr>
							</thead>
							<tbody id="tbodyBreakdownWilayah-<?php echo($counter);?>">
							<?php 
								$TotalPersentase = 0;
								$TotalQty = 0;
								for($i=0;$i<count($breakdowns);$i++) {
									$TotalPersentase+=$breakdowns[$i]["Persentase"];
									$TotalQty+=$breakdowns[$i]["TotalQty"];
							?>
								<tr>
									<td><?php echo($breakdowns[$i]["Wilayah"]);?></td>
									<td><?php echo($breakdowns[$i]["Persentase"]);?> %</td>
									<td><?php echo($breakdowns[$i]["TotalQty"]);?></td>
								</tr>
							<?php } ?>
								<tr>
									<td><b>Total</b></td>
									<td><b><?php echo(number_format($TotalPersentase,2));?> %</b></td>
									<td><b><?php echo(number_format($TotalQty));?></b></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="clearfix" style="height:60px"></div>
	
	
	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<?php if ($plan_status!="APPROVED" && $plan_status!="REJECTED" && $plan_status!="CANCELLED") {?>
			<div style='margin:10px;float:left;'>
				<a href="edit?trxid=<?php echo(urlencode($headers[0]->CampaignID));?>">
				<div class="btn" id="btnEdit" name="btnEdit" style="width:100px;">Edit</div>
				</a>
			</div>
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnRequest" name="btnRequest" style="width:200px;">Send Request</div>
			</div>
			<?php } ?>

			<?php if ($approval==0) {?>

				<?php if ($plan_status=="APPROVED") {?>			
					<div style='margin:10px;float:left;'>
						<a href="Excel?trxid=<?php echo(urlencode($headers[0]->CampaignID));?>" target="blank">
						<div class="btn" id="btnExcel" name="btnExcel" style="width:200px;">Excel Breakdown</div>
						</a>	
					</div>
				<?php }?>

			<?php }
			else if ($approval==1) {?>

				<?php if ($headers[0]->ApprovedByEmail==$_SESSION["logged_in"]["useremail"]) {?>

					<?php if ($plan_status=="WAITING FOR APPROVAL") {?>	

						<div style='margin:10px;float:left;'>
							<a href="<?php echo(site_url("CampaignPlanApproval/Approved?campaignid=".urlencode($headers[0]->CampaignID)."&approvedby=".urlencode($headers[0]->ApprovedBy)));?>">
							<div class="btn btnApprove" id="btnApprove" name="btnApprove" style="background-color:#18400b;">APPROVE</div>
							</a>	
						</div>

						<div style='margin:10px;float:left;'>
							<a href="<?php echo(site_url("CampaignPlanApproval/Rejected?campaignid=".urlencode($headers[0]->CampaignID)."&approvedby=".urlencode($headers[0]->ApprovedBy)));?>">
							<div class="btn btnReject" id="btnReject" name="btnReject" style="background-color:#6b0202;">REJECT</div>
							</a>	
						</div>

					<?php } ?>

				<?php } ?>

			<?php } ?>

			<div style='margin:10px;float:left;'>
				<a href="<?php echo(site_url("CampaignPlan"));?>"><div class="btn" id="btnClose" name="btnClose">Exit</div></a>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
