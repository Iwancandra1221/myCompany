<style>
	.draft {
		color:white; background-color:red;
		margin-left:25px; width:100px;
		text-align: center; padding-left:5px;padding-right:5px;
	}

	table>tbody {
	font: normal medium/1.4 sans-serif;
	font-size: 10pt;
	}
	
	table {
	border-collapse: collapse;
	width: 100%;
	}
	
	table th, td {
	padding: 0.25rem;
	text-align: left;
	border: 1px solid #ccc;
	}
	
	table th {
	background: #bfbfbf;
	}
	
	table>tbody>tr.zebra-even {
	background-color: #fff;
	}
	
	table>tbody>tr.zebra-odd {
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

	.po-plan-card {
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
	var PlanID = "<?php echo($PlanID);?>";
	var PlanHD = <?php echo(json_encode($PlanHD));?>;
	var PlanDT = <?php echo(json_encode($PlanDT));?>;
	var ProductSummary = <?php echo(json_encode($productSummary));?>;

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

	function QtyRegionChanged(PeriodId=0, Region="", Region2="", ProductId="", ProductId2="")
	{
		// alert(ProductId+" "+Region+" Changed");
		// var PeriodId = parseInt($("#QtyRegion_"+Region2+"_"+ProductId2).attr("periodid"));
		var Qty = $("#QtyRegion_"+Region2+"_"+ProductId2+"_"+PeriodId).val();
		if (Qty=="") {
			Qty = 0;
		}
		// alert("QtyRegion_"+Region2+"_"+ProductId2+": "+Qty);
		var TotalQty = 0;

		var x = true;
		x = SaveDraftDT(PeriodId, Region, Region2, ProductId, ProductId2, Qty);

		for(var d in PlanDT) {
			// alert(PlanDT[d].ProductId+" | "+ProductId+"\n"+PlanDT[d].PeriodId+" | "+PeriodId);
			if (PlanDT[d].ProductId==ProductId && PlanDT[d].PeriodId==PeriodId) {
				if (PlanDT[d].Region==Region) {
					// alert(Qty);
					PlanDT[d].QtyRegionTotal = Qty;
				}
				TotalQty += parseFloat(PlanDT[d].QtyRegionTotal);
				// alert("Qty: "+Qty+"\nTotal: "+PlanDT[d].QtyRegionTotal);
			}
		}
		// alert(TotalQty);

		for(var d in PlanDT) {
			if (PlanDT[d].ProductId==ProductId && PlanDT[d].PeriodId==PeriodId) {
				PlanDT[d].SalesQtyTotal = TotalQty;
			}
		}


		$("#TotalSalesQty_"+ProductId2+"_"+PeriodId).val(TotalQty);
		$("#TotalQty_"+ProductId2+"_"+PeriodId).html(number_format(TotalQty));
		Validate();
	}

	function SaveDraftDT(PeriodId, Region, RegionId, ProductId, ProductId2, Qty)
	{
		//alert("SaveDraftDT");

		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('PlanPO/SaveDraftDT'); ?>", {
			PlanNo 			: PlanID,
			ProductId		: ProductId,
			Region 			: Region,
			PeriodId 		: PeriodId,
			Qty				: Qty,
			csrf_bit		: csrf_bit
		}, function(data){
			//alert(data.result + " : " + data.campaignId);
			if (data.result=="SUCCESS") {
				// alert("SUKSES");
				$(".loading").hide();
				return true;
			} else {
				$(".loading").hide();
				alert("SIMPAN GAGAL: "+data.errMsg);
				return false;
			}
		},'json',errorAjax);	
	}

	function TotalSalesQtyChanged(ProductId="", ProductId2="", PeriodId=0)
	{
		// alert(ProductId+" Changed");		
		var RQtyTotal = 0;
		var QtyTotal = 0;
		var Qty = 0;

		for(var d in PlanDT) {
			if (PlanDT[d].ProductId==ProductId && PlanDT[d].PeriodId==PeriodId) {
				RQtyTotal = PlanDT[d].RSalesQtyTotal;
				QtyTotal = PlanDT[d].SalesQtyTotal;
			}
		}
		Qty = $("#TotalSalesQty_"+ProductId2+"_"+PeriodId).val();
		// alert("Qty Baru : "+Qty+"\nQty Lama : "+QtyTotal);

		var x = true;

		if (Qty != QtyTotal) {
			if (Qty > RQtyTotal && 0==1) {
				alert("Hanya Boleh Intervensi Turun!\nQty Tidak Boleh Lebih Besar dari "+RQtyTotal);
				$("#TotalSalesQty_"+ProductId2+"_"+PeriodId).val(QtyTotal);
			} else {
				QtyTotal = Qty;
				TotalQty = 0;

				$(".loading").show();
				var csrf_bit = $("input[name=csrf_bit]").val();
				$.post("<?php echo site_url('PlanPO/SaveDraftDTTotalQty'); ?>", {
					PlanNo 			: PlanID,
					ProductId		: ProductId,
					PeriodId 		: PeriodId,
					Qty				: QtyTotal,
					csrf_bit		: csrf_bit
				}, function(data){
					if (data.result=="SUCCESS") {
						var DETAIL = data.planDT;
						TotalQty = DETAIL[0].SalesQtyTotal;

						for(var p=0; p<DETAIL.length; p++) {
							$("#QtyRegion_"+DETAIL[p].Region2+"_"+ProductId2+"_"+PeriodId).val(DETAIL[p].QtyRegionTotal);
							
							for(var d in PlanDT) {
								if (PlanDT[d].ProductId==ProductId && PlanDT[d].PeriodId==PeriodId && PlanDT[d].Region==DETAIL[p].Region) {
									PlanDT[d].QtyRegionTotal = DETAIL[p].QtyRegionTotal;	
									PlanDT[d].SalesQtyTotal = DETAIL[p].TotalQty;	
								}				
							}
						}
	
						$("#TotalSalesQty_"+ProductId2+"_"+PeriodId).val(TotalQty);
						$("#TotalQty_"+ProductId2+"_"+PeriodId).html(number_format(TotalQty));

						// alert("Total Qty Barang "+ProductId+" : "+TotalQty);
						$(".loading").hide();
						// return true;
					} else {
						$(".loading").hide();
						alert("SIMPAN GAGAL: "+data.errMsg);
						// return false;
					}
				},'json',errorAjax);			
			}
			Validate();
		}
	}

	function Validate()
	{
		if (ValidateHeader()==false) {
			// alert("Not Valid");
			$("#btnSave").prop("disabled", true);
			$("#btnSave").attr("disabled", true);
		} else {
			// alert("Valid");
			$("#btnSave").prop("disabled", false);
			$("#btnSave").attr("disabled", false);
		}
	}

	function ValidateHeader()
	{
		for(var x in ProductSummary) {
			if (isNaN($("#TotalSalesQty_"+ProductSummary[x].ProductId2+"_"+ProductSummary[x].PeriodId).val())==true || $("#TotalSalesQty_"+ProductSummary[x].ProductId2+"_"+ProductSummary[x].PeriodId).val()=="") {
				if (isNaN($("#TotalSalesQty_"+ProductSummary[x].ProductId2+"_"+ProductSummary[x].PeriodId).val())==true) {
					alert("#TotalSalesQty_"+ProductSummary[x].ProductId2+"_"+ProductSummary[x].PeriodId+" value is NaN");
				} else {
					alert("#TotalSalesQty_"+ProductSummary[x].ProductId2+"_"+ProductSummary[x].PeriodId+" value is empty");
				}
				return false;
			}
		}

		for(var x in PlanDT) {
			var Region = PlanDT[x].Region2;
			var ProductId = PlanDT[x].ProductId2;
			var PeriodId = PlanDT[x].PeriodId;

			if (isNaN($("#QtyRegion_"+Region+"_"+ProductId+"_"+PeriodId).val())==true || $("#QtyRegion_"+Region+"_"+ProductId+"_"+PeriodId).val()=="") {
				if (isNaN($("#QtyRegion_"+Region+"_"+ProductId+"_"+PeriodId).val())==true) {
					alert("#QtyRegion_"+Region+"_"+ProductId+"_"+PeriodId+" value is NaN");
				} else {
					alert("#QtyRegion_"+Region+"_"+ProductId+"_"+PeriodId+" value is empty");
				}
				return false;
			}
		}

		return true;
	} 


	$(document).ready(function() {
		$("#btnSave").click(function(){
			var isDisabled = $("#btnSave").prop('disabled');
			if(!isDisabled){
				$("#FormPOPlan").submit();
			}
		});

		$("#btnRequest").click(function(){
			var isDisabled = $("#btnRequest").prop('disabled');
			if(!isDisabled){
				$("#FormPOPlan").submit();
			}
		});
			
	});
</script>

<?php
	$view_only = ($mode=='view') ? "disabled" : "";
?>

<?php 
	if ($mode=="edit") {
		echo form_open("PlanPO/FinalSave?trx=".$PlanHD->PlanNo, array("id" => "FormPOPlan"));
	} else {
		echo form_open("PlanPO/createRequest?trxid=".$PlanHD->PlanId, array("id" => "FormPOPlan", "target"=>"_blank"));
	}
?>

<div class="container">	
	<div class="title">PERENCANAAN PO</div>
	<div class="po-plan-card">
		<div class="row">
			<div class="col-3 col-m-4">NO Rencana</div>
			<div class="col-9 col-m-8 div-content"><?php echo ($PlanHD->PlanNo); ?></div>			
		</div>
		<div class="row">
			<div class="col-3 col-m-4">Periode</div>
			<div class="col-9 col-m-8 div-content"><?php echo ($PlanHD->Periode1); ?> s/d <?php echo ($PlanHD->Periode2);?></div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4">Divisi</div>
			<div class="col-9 col-m-8 div-content"><?php echo ($PlanHD->Division); ?></div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4">Keterangan</div>
			<div class="col-9 col-m-8 div-content"><?php echo (($PlanHD->PlanNote=="")? "-":$PlanHD->PlanNote); ?></div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4">Status</div>
			<div class="col-9 col-m-8 div-content"><?php echo ($PlanHD->PlanStatus); ?></div>
		</div>
		<?php if ($PlanHD->IsApproved==2) { ?>
		<div class="row">
			<div class="col-3 col-m-4">Catatan Reject</div>
			<div class="col-9 col-m-8 div-content"><?php echo (($PlanHD->ApprovalNote=="")? "-":$PlanHD->ApprovalNote); ?></div>
		</div>
		<?php } ?>
		<div class="row">
			<div class="col-3 col-m-4">Daftar Wilayah</div>
			<div class="col-9 col-m-8">
			<?php
			$sisaCol = 6; 
			foreach($dtRegions as $r) {
				if ($sisaCol==6) {
					echo("<div class='row'>");
				}
				echo("	<div class='col-2 col-m-2'>- ".$r->Region."</div>");
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
	<?php //echo(json_encode($barang)."<br>");?>

	<div class="form">
		<ul class="nav nav-tabs">
			<?php 
			$i = 0;
			foreach($dtProducts as $p) {
				if ($i == 0) { ?>
				<li class="active"><a data-toggle="tab" href="#menu<?php echo $i; ?>"><?php echo $p->ProductId; ?></a></li>
				<?php } else { ?>
				<li><a data-toggle="tab" href="#menu<?php echo $i; ?>"><?php echo $p->ProductId; ?></a></li>
				<?php }
				$i = $i+1;
			} ?>
		</ul>
		
		<div class="tab-content">
			<?php
			$counter = 0;  
			$i = 0;
			foreach($dtProducts as $p) {
				$counter += 1;
			?>					
				<div id="menu<?php echo $i; ?>" class="tab-pane fade <?php if ($i == 0) echo "in active" ?>">
					<div class="po-plan-card">
						<div class="row">
							<div class="col-m-4 col-m-4">
								Produk: <span class="subdiv-content"><?php echo($p->ProductId);?></span>
							</div>
						</div>						
						<table id="table-product-<?php echo $counter; ?>" class="table table-striped table-bordered" cellspacing="0">
							<thead id="theadBarang-<?php echo $counter; ?>">
								<tr>
									<th width="20%" class="hideOnMobile" style="border:1px solid #ccc;">Kode Barang</th>
									<th width="30%" class="hideOnMobile" style="border:1px solid #ccc;">Periode</th>
									<!-- <th width="10%" class="hideOnMobile" style="border:1px solid #ccc;">Average Normal</th> -->
									<th width="20%"  class="hideOnMobile" style="border:1px solid #ccc;">Rata2 Penjualan / Periode **</th>
									<th width="10%"  class="hideOnMobile" style="border:1px solid #ccc;">Jumlah Hari</th>
									<!-- <th width="5%"  class="hideOnMobile" style="border:1px solid #ccc;">Average Intervensi</th> -->
									<th width="20%" class="hideOnMobile" style="border:1px solid #ccc;">Qty Penjualan Yang Diinginkan</th>
									<!-- <th width="90%" class="colMobile" style="width:100%;height:40px;line-height:40px;vertical-align:bottom;">Data</th> -->
								</tr>
							</thead>
							<tbody id="tbodyBarang-<?php echo $counter; ?>" class="tbodyBarangCampaign" idx="<?php echo($counter);?>">
								<?php
									// $x = 0; 
									foreach($productSummary as $dt) {
										if ($dt->ProductId==$p->ProductId) {
								?>
								<tr>
									<td class="hideOnMobile" style="border:1px solid #ccc;"><?php echo($dt->ProductId);?></td>
									<td class="hideOnMobile" style="border:1px solid #ccc;"><?php echo($dt->PeriodName);?></td>
									<!-- <td class="hideOnMobile" style="border:1px solid #ccc;"><?php echo(number_format($dt->RSalesQtyAverage,2));?></td> -->
									<td class="hideOnMobile" style="border:1px solid #ccc;"><?php echo(number_format($dt->RSalesQtyTotal));?></td>
									<td class="hideOnMobile" style="border:1px solid #ccc;"><?php echo($dt->DayCount);?></td>
									<!-- <td class="hideOnMobile" style="border:1px solid #ccc;"><?php echo(number_format($dt->SalesQtyAverage,2));?></td> -->
									<!-- <td class="hideOnMobile" style="border:1px solid #ccc;"><?php echo(number_format($dt->SalesQtyTotal));?></td> -->
									<?php if ($mode=="edit") { ?>
									<td class="hideOnMobile" style="border:1px solid #ccc;">
										<input type="number" style="width:100px;" class="isdisabled" 
											name="TotalSalesQty[]" id="TotalSalesQty_<?php echo($dt->ProductId2);?>_<?php echo($dt->PeriodId);?>" min="1" 
											value="<?php echo($dt->SalesQtyTotal);?>" 
											onblur="TotalSalesQtyChanged('<?php echo($dt->ProductId);?>', '<?php echo($dt->ProductId2);?>', <?php echo($dt->PeriodId);?>)"/>
									</td>
									<?php } else { ?>
									<td class="hideOnMobile" style="border:1px solid #ccc;"><?php echo(number_format($dt->SalesQtyTotal));?></td>
									<?php } ?>
								</tr>
								<?php
									
									}
								} ?>
							</tbody>
						</table>
						<table id="table-breakdown-<?php echo($counter);?>" class="table table-striped table-bordered" cellspacing="0">
							<thead id="theadBreakdownWilayah-<?php echo($counter);?>">
								<tr>
									<th width="10%">Wilayah</th>
									<th width="10%">Persentase</th>
									<th width="10%">Avg Jual<br>12 Periode</th>
								<?php foreach($dtPeriods as $pd) { ?>
									<th width="10%">Total Qty<br><?php echo($pd->PeriodName);?></th>
								<?php } ?>
								</tr>
							</thead>
							<tbody id="tbodyBreakdownWilayah-<?php echo($counter);?>" class="tbodyBreakdownWilayah">
								<?php
								$TotalPersentase = 0;
								$TotalAvg = 0;
								$TotalQty = array();
								foreach($dtPeriods as $pd) {
									$TotalQty[$pd->PeriodId] = 0; 
								}

								foreach($dtRegions as $r) { 
									$Persentase = 0;
									$AvgJual = 0;
								?>

								<tr>
									<td><?php echo($r->Region);?></td>
									<td>
										<?php foreach($PlanDT as $dt) { 
											if ($dt->ProductId==$p->ProductId && $dt->Region==$r->Region) {
												$Persentase = $dt->RPercentages;
											}
										}
										echo($Persentase);?> %
									</td>
									<td>
										<?php foreach($PlanDT as $dt) { 
											if ($dt->ProductId==$p->ProductId && $dt->Region==$r->Region && $dt->PeriodId==$dtPeriods[0]->PeriodId) {
												$AvgJual = $dt->RQtyRegionTotal;
												$TotalAvg += $AvgJual;
											}
										}
										echo(number_format($AvgJual));?>
									</td>
								<?php 
									foreach($dtPeriods as $pd) {
										$Qty = 0; 
										foreach($PlanDT as $dt) {
											if ($dt->ProductId==$p->ProductId && $dt->Region==$r->Region && $dt->PeriodId==$pd->PeriodId) {
												$Qty = $dt->QtyRegionTotal;
												$TotalQty[$dt->PeriodId]+=$Qty;
											}
										}
										if ($mode=="edit") { 
								?>
									<td>
										<input type="number" style="width:100px;" class="isdisabled qty-region" 
										name="QtyRegion[]" id="QtyRegion_<?php echo($r->Region2);?>_<?php echo($p->ProductId2);?>_<?php echo($pd->PeriodId);?>" 
										min="0" persentase="<?php echo($Persentase);?>" value="<?php echo($Qty);?>" 
										onblur="QtyRegionChanged(<?php echo($pd->PeriodId);?>, '<?php echo($r->Region);?>', '<?php echo($r->Region2);?>', '<?php echo($p->ProductId);?>', '<?php echo($p->ProductId2);?>')"/>
										<!-- onfocus="QtyRegionFocused(<?php echo($pd->PeriodId);?>, '<?php echo($r->Region);?>', '<?php echo($r->Region2);?>', '<?php echo($p->ProductId);?>', '<?php echo($p->ProductId2);?>')" -->
									</td>
								<?php 	} else { ?>
									<td><?php echo(number_format($Qty));?></td>
								<?php
										} 
									} 
								?>
								</tr>
								<?php
								$TotalPersentase+=$Persentase; 
								} 
								?>
								<tr>
									<th>TOTAL</th>
									<th><?php echo(number_format($TotalPersentase,2));?> %</td>
									<th><?php echo(number_format($TotalAvg));?></td>
								<?php foreach($dtPeriods as $pd) { ?>
									<th id="TotalQty_<?php echo($p->ProductId2);?>_<?php echo($pd->PeriodId);?>"><?php echo(number_format($TotalQty[$pd->PeriodId]));?></th>
								<?php } ?>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			<?php 
				$i = $i+1;
			} ?>
			</div>
		</div>
	</div>	
	
	<div class="clearfix" style="height:60px"></div>
	
	
	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<?php if ($mode=="edit") { ?>
		<div style="clear:both;">
			<div style='margin:10px;float:left;'>
				<a href="<?php echo(site_url('PlanPO/edit?trxid='.urlencode($PlanHD->PlanId)));?>">
					<div class="btn" id="btnBack" name="btnBack">Back</div>
				</a>
			</div>
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnSave" name="btnSave">Save</div>
			</div>
			<div style='margin:10px;float:left;'>
				<a href="<?php echo(site_url('PlanPO'));?>"><div class="btn" id="btnClose" name="btnClose">Exit</div></a>
			</div>
			<div style='margin:10px;float:left;color:white;font-size:12px;height:50px;line-height:50px;vertical-align:bottom;'>
				STEP 2 OF 2
			</div>
		</div>
		<?php } else { ?>
			<div style="clear:both;">

			<?php if ($approval==0) {?>

				<?php if ($PlanHD->PlanStatus!="APPROVED" && $PlanHD->PlanStatus!="REJECTED" && $PlanHD->PlanStatus!="CANCELLED") {?>
					<div style='margin:10px;float:left;'>
						<a href="edit?trxid=<?php echo(urlencode($PlanHD->PlanId));?>">
						<div class="btn" id="btnEdit" name="btnEdit" style="width:100px;">Edit</div>
						</a>
					</div>
					<div style='margin:10px;float:left;'>
						<div class="btn" id="btnRequest" name="btnRequest" style="width:200px;">Send Request</div>
					</div>
				<?php } ?>

			<?php }
			else if ($approval==1) {?>

				<?php if ($PlanHD->ApprovedBy==$_SESSION["logged_in"]["useremail"]) {?>

					<?php if ($PlanHD->PlanStatus!="APPROVED" && $PlanHD->PlanStatus!="REJECTED" && $PlanHD->PlanStatus!="CANCELLED") {?>

						<div style='margin:10px;float:left;'>
							<a href="<?php echo(site_url("PlanPOApproval/Approved?trxid=".urlencode($PlanHD->PlanNo)."&approvedby=".urlencode($PlanHD->ApprovedBy)));?>">
							<div class="btn btnApprove" id="btnApprove" name="btnApprove" style="background-color:#18400b;">APPROVE</div>
							</a>	
						</div>

						<div style='margin:10px;float:left;'>
							<a href="<?php echo(site_url("PlanPOApproval/Rejected?trxid=".urlencode($PlanHD->PlanNo)."&approvedby=".urlencode($PlanHD->ApprovedBy)));?>">
							<div class="btn btnReject" id="btnReject" name="btnReject" style="background-color:#6b0202;">REJECT</div>
							</a>	
						</div>

					<?php } ?>

				<?php } ?>

			<?php } ?>

			<div style='margin:10px;float:left;'>
				<a href="<?php echo(site_url('PlanPO'));?>"><div class="btn" id="btnClose" name="btnClose">Exit</div></a>
			</div>

			</div>
		<?php } ?>
	</div>
</div>
<?php echo form_close(); ?>
