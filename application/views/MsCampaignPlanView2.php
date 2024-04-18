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
	var CampaignHDs = <?php echo(json_encode($headers));?>;
	var ProductBreakdowns = <?php echo(json_encode($ProductBreakdowns));?>;

	function validate() {
		$("#btnSave").attr("disabled",true);
		$("#btnSave").prop("disabled",true);

		var valid = true;
		// for(var h in CampaignHDs) {
		// 	if (CampaignHDs[h].IsSelected==false) {
		// 		valid = valid && false;
		// 	}
		// }

		$(".tbodyBreakdownWilayah tr").each(function() {
			//alert('masuk');
			var flag = $(this).find('input[type="number"]');
			if (flag.val()=="") {
				// alert(flag.attr("trxid")+" "+flag.attr("wilayah"));
				valid = valid && false;
			}
		});
		
		if(valid) {
			$("#btnSave").attr("disabled",false);
			$("#btnSave").prop("disabled",false);
		}	
	}
	
	function checkEmpty(obj) {
		var name = $(obj).attr("name");
		$("."+name+"-validation").html("");	
		$(obj).css("border","");
		if($(obj).val() == "" || $(obj).val() < 0) {
			$(obj).css("border","#FF0000 1px solid");
			$("."+name+"-validation").html("Required");
			return false;
		}
		
		return true;	
	}

	function SaveDraft(KdBrg, Wil="ALL")
	{
		var camp;
		var breakdowns;
		var TotalHariPlanCamp = 0;
		var QtyAvgCamp = 0;
		var TotalQtyCamp = 0; 
		var ProductID="";

		for(var p in ProductBreakdowns) {
			if (ProductBreakdowns[p].KdBrg==KdBrg) {
				ProductID = ProductBreakdowns[p].KodeBarang;
				TotalHariPlanCamp = ProductBreakdowns[p].JumlahHari;
				QtyAvgCamp = ProductBreakdowns[p].QtyAverage;
				TotalQtyCamp = ProductBreakdowns[p].TotalQty; 
				breakdowns = ProductBreakdowns[p].Breakdown_Per_Wilayah;
			}
		}

		var isSuccess = true;
		var errMsg = "";
		//alert("saveDraft");
		validate();

		// $(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('CampaignPlan/SaveDraftDT'); ?>", {
			kode_plan 			: CampaignID,
			kode_barang			: ProductID,
			previous_campaign_id: 0,
			total_hari_plan 	: TotalHariPlanCamp,
			avg_jual			: QtyAvgCamp,
			total_avg_jual		: TotalQtyCamp,
			csrf_bit			: csrf_bit
		}, function(data){
			// alert(data.result + " : " + data.campaignId);
			if (data.result=="SUCCESS") {
				if (Wil=="ALL" || Wil=="") {
					for(var b in breakdowns) {
						Wil = breakdowns[b].Wil;

						if (isSuccess==true) {
							saveQtyPerWilayah(KdBrg, Wil, ProductID, breakdowns[b].Wilayah, breakdowns[b].Kd_Lokasi, breakdowns[b].TotalQty);
						}
					}
				} else {
					for(var b in breakdowns) {
						if (breakdowns[b].Wil==Wil) {
							saveQtyPerWilayah(KdBrg, Wil, ProductID, breakdowns[b].Wilayah, breakdowns[b].Kd_Lokasi, breakdowns[b].TotalQty);
						}
					}
				}
			} else {
				errMsg = data.errMsg;
			}
			// $(".loading").hide();
			// $(".StatusDt_"+Idx).html("");

			// if (isSuccess==true) {
			// 	$("#StatusDt_"+TrxId).html("<font color='green'>saved</font>")
			// } else {
			// 	$("#StatusDt_"+TrxId).html("<font color='red'>save failed: "+data.errMsg+"</font>");	
			// }

		},'json',errorAjax);		
	}

	function saveQtyPerWilayah(KdBrg, Wil, ProductID, Wilayah, KdLokasi, TotalQty)
	{
		if (TotalQty==0) {
			$("#product-success-"+KdBrg+"-"+Wil).show();
		} else {
			var errMsg = "";
			// $(".loading").show();
			$("#product-success-"+KdBrg+"-"+Wil).hide();
			$("#product-loading-"+KdBrg+"-"+Wil).show();
			var csrf_bit = $("input[name=csrf_bit]").val();
			$.post("<?php echo site_url('CampaignPlan/SaveDraftBreakdown'); ?>", {
				kode_plan 			: CampaignID,
				previous_campaign_id: 0,
				kode_barang			: ProductID,
				wilayah 			: Wilayah,
				kode_lokasi			: KdLokasi,
				total_qty_campaign	: 0,
				total_qty			: TotalQty,
				csrf_bit			: csrf_bit
			}, function(data){
				if (data.result=="SUCCESS") {
					isSuccess = true;
					$("#product-loading-"+KdBrg+"-"+Wil).hide();
					$("#product-success-"+KdBrg+"-"+Wil).show();
				} else {
					isSuccess = false;
					$("#product-loading-"+KdBrg+"-"+Wil).hide();
					errMsg = data.errMsg;
				}
			},'json',errorAjax);
			// $(".loading").hide();
		}
	}

	function valueAvgChanged(KdBrg)
	{
		//TrxId : Id dari Previous Campaign
		//Idx: Id Dari Product (Posisi Product)

		// if ($("#DetailCampaign_"+TrxId).prop("checked")==false) {
		// 	$(".group-"+Idx).prop("checked", false);
		// 	$("#DetailCampaign_"+TrxId).prop("checked",true);
		// }
		// alert(KdBrg);

		var TotalAvg = 0;
		var Avg = $("#AvgCampaign_"+KdBrg).val();
		var camp;

		for(var c in ProductBreakdowns) {
			if (ProductBreakdowns[c].KdBrg==KdBrg) {
				camp = ProductBreakdowns[c];
				//alert(camp.Total_Hari_Plan);
				TotalAvg = camp.JumlahHari * Avg;
				//alert(TotalAvg);
				$("#TotalAvg_"+KdBrg).val(TotalAvg);
				// alert("Qty Baru : "+Avg+"\r\n"+"Total Qty : "+TotalAvg);

				hitungQtyPerWilayah(KdBrg);
				SaveDraft(KdBrg, "ALL");
			}
		}
		// SaveDraft(Idx, TrxId);
	}


	function totalAvgChanged(KdBrg) 
	{
		// alert("Total Avg Changed");
		var camp;
		var TotalAvg = $("#TotalAvg_"+KdBrg).val();
		var Avg = 0;
		for(var c in ProductBreakdowns) {
			if (ProductBreakdowns[c].KdBrg==KdBrg) {
				camp = ProductBreakdowns[c];
				Avg = Math.round(TotalAvg/camp.JumlahHari);
				TotalAvg = Avg * camp.JumlahHari;

				$("#AvgCampaign_"+KdBrg).val(Avg);
				$("#TotalAvg_"+KdBrg).val(TotalAvg);

				hitungQtyPerWilayah(KdBrg);
				SaveDraft(KdBrg, "ALL");
			}
		}
	}	

	function hitungQtyPerWilayah(KdBrg)
	{
		var camp;
		var breakdowns;
		for(var c in ProductBreakdowns) {
			if (ProductBreakdowns[c].KdBrg==KdBrg) {
				camp = ProductBreakdowns[c];
				breakdowns = camp.Breakdown_Per_Wilayah;
			}
		}

		var Avg = $("#AvgCampaign_"+KdBrg).val();
		var TotalAvg = $("#TotalAvg_"+KdBrg).val();
		var IdxBreakdown = "";
		var Wil = "";
		var Persentase = 0;
		var Qty = 0;
		var TotalPersentase = 0;
		var TotalQty = 0;


		$(".total-qty-"+KdBrg).each(function(){
			IdxBreakdown = $(this).attr("idx");
			Wil = $(this).attr("wilayah");
			//alert(IdxBreakdown);
			Persentase = $("#Persentase_"+KdBrg+"_"+Wil).html();
			//alert(Persentase);
			Persentase = Number(Persentase.replace(" %",""));
			TotalPersentase += Persentase;

			Qty = Math.round((Persentase * TotalAvg) / 100);
			TotalQty += Qty;
			if (Qty>0) {
				$("#TotalQty_"+KdBrg+"_"+Wil).val(Qty);
				// $("#StatusBreakdown_"+KdBrg+"_"+Wil).html("saved");
			} else {
				$("#TotalQty_"+KdBrg+"_"+Wil).val("");
			}

			for(var b in breakdowns) {
				if (breakdowns[b].Wil==Wil) {
					breakdowns[b].TotalQty = Qty;
				}
			}
		});

		for(var b in breakdowns) {
			breakdowns[b].TotalQtyCampaign = TotalQty;
		}
		
		for(var c in ProductBreakdowns) {
			if (ProductBreakdowns[c].KdBrg==KdBrg) {
				ProductBreakdowns[c].QtyAverage = Avg;
				ProductBreakdowns[c].TotalQty = TotalQty;
				ProductBreakdowns[c].Breakdown_Per_Wilayah = breakdowns;
			}
		}

		$("#TotalAvg_"+KdBrg).val(TotalQty);	
		// $("#RowTotalPersentase_"+KdBrg).html(number_format(TotalPersentase, 2)+" %");
		$("#RowTotalQty_"+KdBrg).html(number_format(TotalQty));
	}

	function totalQtyChanged(KdBrg, Wil, Idx) 
	{
		//Function ini menghandle Perubahan Qty Per Wilayah
		//TrxID : Id Campaign yg Dipilih
		//IdxB : Id Qty Per Wilayah
		var breakdowns;
		var ProductID="";
		var JumlahHari=0;
		for(var c in ProductBreakdowns) {
			if (ProductBreakdowns[c].KdBrg==KdBrg) {
				ProductID = ProductBreakdowns[c].KodeBarang;
				JumlahHari = ProductBreakdowns[c].JumlahHari;
				breakdowns = ProductBreakdowns[c].Breakdown_Per_Wilayah;
			}
		}

		var Qty = 0;
		var TotalQty = 0;
		var Wilayah = "";
		Wilayah = $("#Wilayah_"+KdBrg+"_"+Wil).text();
		Qty = $("#TotalQty_"+KdBrg+"_"+Wil).val();

		for(var b in breakdowns) {
			if (breakdowns[b].Wil==Wil) {
				if (breakdowns[b].TotalQty==Qty){
					validate(); // lakukan validasi walaupun TotalQty =0 dan qty=0
					return;
				}
			}
		}


		$(".total-qty-"+KdBrg).each(function(){
			IdxBreakdown = $(this).attr("idx");
			
			Wilayah = $(this).attr("wilayah");
			Qty = Number($(this).val());
			TotalQty += Qty;

			//Update Ulang Qty Per Wilayah dari array breakdowns
			//Sesuai Qty dari textbox TotalQty
			for(var b in breakdowns) {
				if (breakdowns[b].Wil==Wilayah) {
					breakdowns[b].TotalQty = Qty;
					// alert(KdBrg+" "+Wilayah+" "+breakdowns[b].TotalQty);
				}
			}
		});		

		//TotalQty Seluruh Wilayah Diupdate Ulang ke TotalQtyCampaign dr Array Breakdown
		for(var b in breakdowns) {
			breakdowns[b].TotalQtyCampaign = TotalQty;
		}
		
		//Hitung Ulang Average Per Hari dan Total Average di Tabel Product Per Campaign
		$("#RowTotalQty_"+KdBrg).html(number_format(TotalQty));
		$("#TotalAvg_"+KdBrg).val(TotalQty);
		var Avg = 0;
		Avg = Math.round(TotalQty / JumlahHari);
		$("AvgCampaign_"+KdBrg).val(Avg); 

		for(var c in ProductBreakdowns) {
			if (ProductBreakdowns[c].KdBrg==KdBrg) {
				ProductBreakdowns[c].Breakdown_Per_Wilayah = breakdowns;
				ProductBreakdowns[c].QtyAverage = Avg;
				ProductBreakdowns[c].TotalQty = TotalQty; 
			}
		}

		// alert("here");
		for(var b in breakdowns) {
			if (breakdowns[b].Wil==Wil) {
				saveQtyPerWilayah(KdBrg, Wil, ProductID, breakdowns[b].Wilayah, breakdowns[b].Kd_Lokasi, breakdowns[b].TotalQty);
			}
		}	
		
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('CampaignPlan/SaveDraftDT'); ?>", {
			kode_plan 			: CampaignID,
			kode_barang			: ProductID,
			previous_campaign_id: 0,
			total_hari_plan 	: JumlahHari,
			avg_jual			: Avg,
			total_avg_jual		: TotalQty,
			csrf_bit			: csrf_bit
		}, function(data){
			// alert(data.result + " : " + data.campaignId);
			if (data.result=="SUCCESS") {
			}

		},'json',errorAjax);

		validate();
		//SaveDraft(KdBrg, Wil);
		// $(".StatusBreakdown_"+TrxId).html("saved");
		// $("#StatusBreakdown_"+TrxId+"_"+IdxB).html("<b>saved</b>");
	}


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
		<?php
			if($status=='view'){
			?>
			$("#btnBack").hide();
			$("#btnSave").hide();
			$("#btnClose").show();
			<?php
				}else{
			?>
			$("#btnBack").show();
			$("#btnSave").show();
			$("#btnClose").show();
			<?php
			}
		?>

		validate();
		$(".product-icon").hide();

		var c = 0;

		
		$("#btnSave").click(function(){
			var isDisabled = $("#btnSave").prop('disabled');
			if(!isDisabled){
				$("#FormCampaignPlan").submit();
			}
		});
	});
</script>

<?php
	$view_only = ($status=='view') ? "disabled" : "";
	// echo(json_encode($ProductBreakdowns)."<br><br>");
?>

<?php echo form_open('CampaignPlan/SimpanCampaignPlan?trxid='.urlencode($CampaignID), array("id" => "FormCampaignPlan")); ?>

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
			<div class="col-9 col-m-8 div-content"><?php echo ($headers[0]->CampaignStatus); ?></div>
		</div>
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
		<div class="row">
			 <input type='hidden' name='productJson' id='productJson' value='<?php echo(json_encode($ProductBreakdowns));?>'>
		</div>
	</div>

	<?php //echo(json_encode($barang)."<br>");?>

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
					<div class="campaign-plan-card">
						<div class="row">
							<div class="col-m-4 col-m-4">
								Produk: <span class="subdiv-content"><?php echo($tab);?></span>
								<?php for($i=0;$i<count($ProductBreakdowns);$i++) { 
									if ($ProductBreakdowns[$i]["KodeBarang"]==$tab) {
								    	echo("<input type='hidden' name='KdBrg[]' id='KdBrg_".$ProductBreakdowns[$i]["KdBrg"]."' value='".$ProductBreakdowns[$i]["KdBrg"]."'>");
								    	echo("<input type='hidden' name='KodeBarang_".$ProductBreakdowns[$i]["KdBrg"]."' id='KodeBarang_".$ProductBreakdowns[$i]["KdBrg"]."' value='".$ProductBreakdowns[$i]["KodeBarang"]."'>");
									} 
								} ?>
							</div>
							<div class="col-m-4 col-m-5">
								Periode:<span class="subdiv-content"><?php echo (date("d-M-Y", strtotime($headers[$index]->CampaignStart))); ?> - <?php echo (date("d-M-Y", strtotime($headers[$index]->CampaignEnd))); ?></span>
							</div>
							<div class="col-m-4 col-m-3">
								Jumlah Hari:<span class="subdiv-content"><?php echo ($headers[$index]->JumlahHari); ?></span>
							</div>
						</div>
						<table id="table-product-<?php echo $counter; ?>" class="table table-striped table-bordered" cellspacing="0">
							<thead id="theadBarangCampaign-<?php echo $counter; ?>">
								<tr>
									<th scope="col" width="20%" class="hideOnMobile" style="border:1px solid #ccc;">Kode Barang</th>
									<th scope="col" width="30%" class="hideOnMobile" style="border:1px solid #ccc;">Nama Transaksi</th>
									<th scope="col" width="5%"  class="hideOnMobile" style="border:1px solid #ccc;">Jumlah Hari</th>
									<th scope="col" width="10%" class="hideOnMobile" style="border:1px solid #ccc;">Qty</th>
									<th scope="col" width="5%"  class="hideOnMobile" style="border:1px solid #ccc;">Avg Per Hari</th>
									<!-- <th width="5%" style="border:1px solid #ccc;">Avg Harian Per Thn</th> -->
									<th scope="col" width="5%"  class="hideOnMobile" style="border:1px solid #ccc;">Jumlah Hari Rencana</th>
									<th scope="col" width="10%" class="hideOnMobile" style="border:1px solid #ccc;">Total Qty Rencana</th>
									<th scope="col" width="90%" class="colMobile" style="width:100%;height:40px;line-height:40px;vertical-align:bottom;">Data</th>
									<!-- <th width="5%" class="" style="border:1px solid #ccc;">Pilih</th> -->
									<!-- <th width="5%" class="" style="border:1px solid #ccc;"></th> -->
								</tr>
							</thead>
							<tbody id="tbodyBarangCampaign-<?php echo $counter; ?>" class="tbodyBarangCampaign" idx="<?php echo($counter);?>">

								<?php
								$ProductDT = array();
								for($i=0; $i<count($ProductBreakdowns);$i++) {	
									if (strtoupper($ProductBreakdowns[$i]["KodeBarang"])==strtoupper($tab)) {
										$ProductDT = $ProductBreakdowns[$i];
									}
								}
								?>
								<!-- <tr> -->
									<!-- <td colspan='8'><?php echo(json_encode($ProductDT));?></td> -->
								<!-- </tr> -->

								<?php
								for($i=0; $i<count($campaigns); $i++) {	
									if (strtoupper($campaigns[$i]["Kd_Brg"])==strtoupper($tab)) {
										$value=0;
										$isChecked="";

										$BRG = strtoupper($campaigns[$i]["Kd_Brg"]);
										$TRX = ($campaigns[$i]["Jns_Trx"]=="")?"-":"<b>".$campaigns[$i]["Nm_Trx"]."</b><br>".$campaigns[$i]["Jns_Trx"];
										$TOTAL_HARI = $campaigns[$i]["Total_Hari"];
										$TOTAL_JUAL = number_format($campaigns[$i]["Total_Jual"]);
										$TOTAL_HARI_PLAN = $campaigns[$i]["Total_Hari_Plan"];

										$brs = "<tr>";
										$brs.= "	<td class='hideOnMobile'>".$BRG."</td>";
										$brs.= "	<td class='hideOnMobile'>".$TRX."</td>";
										$brs.= "	<td class='hideOnMobile'>".$TOTAL_HARI."</td>";
										$brs.= "	<td class='hideOnMobile'>".$TOTAL_JUAL."</td>";
										echo $brs;
										if ($campaigns[$i]["Jns_Trx"]=="" || $campaigns[$i]["Jns_Trx"]==null) { 
										?>
										<td class='hideOnMobile'>
											<input type='number' style='width:80px;' class='isdisabled'  
											name='AvgCampaign_<?php echo($campaigns[$i]["KdBrg"]);?>' id='AvgCampaign_<?php echo($campaigns[$i]["KdBrg"]);?>' min='1'
											value='<?php echo($ProductDT["QtyAverage"]);?>' 
											onblur='valueAvgChanged("<?php echo($campaigns[$i]["KdBrg"]);?>")'/>
										</td>
										<?php 
										} else {
											echo("<td class='hideOnMobile'>".number_format($campaigns[$i]["Avg"])."</td>");
										}

										$brs = "	<td class='hideOnMobile'>".(string)$TOTAL_HARI_PLAN."</td>";
										echo($brs);
										if ($campaigns[$i]["Jns_Trx"]=="" || $campaigns[$i]["Jns_Trx"]==null) {
										?>
											<td class='hideOnMobile'>
												<input type='number' style='width:80px;' class='isdisabled'  
													name='TotalAvg_<?php echo($campaigns[$i]["KdBrg"]);?>' id='TotalAvg_<?php echo($campaigns[$i]["KdBrg"]);?>' min='1' 
													value='<?php echo($ProductDT["TotalQty"]);?>' 
													onblur='totalAvgChanged("<?php echo($campaigns[$i]["KdBrg"]);?>")'/>
											</td>
										<?php } else {
											echo("<td class='hideOnMobile'>".number_format($campaigns[$i]["Total_Avg"])."</td>");
										}


										$brs = "	<td class='colMobile'>".$TRX."<br>".
												"TotalJual:".$TOTAL_JUAL."<br>".
												"TotalHari:".$TOTAL_HARI." Hari<br>".
												"AvgJual: ";
										echo($brs);

										if ($campaigns[$i]["Jns_Trx"]=="" || $campaigns[$i]["Jns_Trx"]==null) { 
										?>
											<input type='number' style='width:80px;' class='isdisabled'  
											name='AvgCampaign_<?php echo($campaigns[$i]["KdBrg"]);?>' id='AvgCampaign_<?php echo($campaigns[$i]["KdBrg"]);?>' min='1'
											value='<?php echo($ProductDT["QtyAverage"]);?>' 
											onblur='valueAvgChanged("<?php echo($campaigns[$i]["KdBrg"]);?>")'/>
										<?php } else {
											echo(number_format($campaigns[$i]["Avg"]));
										}
										$brs =  "<br>".
												"TotalHari: ".(string)$TOTAL_HARI_PLAN."<br>".
												"TotalQty: ";
										echo($brs);
										if ($campaigns[$i]["Jns_Trx"]=="" || $campaigns[$i]["Jns_Trx"]==null) { 
										?>
											<input type='number' style='width:80px;' class='isdisabled'  
												name='TotalAvg_<?php echo($campaigns[$i]["KdBrg"]);?>' id='TotalAvg_<?php echo($campaigns[$i]["KdBrg"]);?>' min='1' 
												value='<?php echo($ProductDT["TotalQty"]);?>' 
												onblur='totalAvgChanged("<?php echo($campaigns[$i]["KdBrg"]);?>")'/>
										<?php } else {
											echo(number_format($campaigns[$i]["Total_Avg"]));
										}
										$brs ="	</td>";
										$brs.="</tr>";
										echo($brs);
									}
								}
								?>
							</tbody>
						</table>
						<table id="table-breakdown-<?php echo($counter);?>" class="table table-striped table-bordered" cellspacing="0">
							<thead id="theadBreakdownWilayah-<?php echo($counter);?>">
								<tr>
									<th scope="col" width="5%" style="border:1px solid #ccc;">No</th>
									<th scope="col" width="30%" style="border:1px solid #ccc;">Wilayah</th>
									<th scope="col" width="15%" style="border:1px solid #ccc;">Persentase</th>
									<th scope="col" width="15%" style="border:1px solid #ccc;">TotalQty</th>
									<th scope="col" width="35%" style="border:1px solid #ccc;">Status Save In Background</th>
								</tr>
							</thead>
							<tbody id="tbodyBreakdownWilayah-<?php echo($counter);?>" class="tbodyBreakdownWilayah">
							<?php
								$TotalPersentase = 0;
								$TotalQty = 0;
								
								// for($i=0; $i<count($ProductBreakdowns);$i++) {	
								// 	if (strtoupper($ProductBreakdowns[$i]["KodeBarang"])==strtoupper($tab)) {
								$TrxId = $ProductDT["KdBrg"];			
								// TrxId ini unik untuk setiap Barang (walaupun dari Previous Campaign yang sama)
								$breakdowns = $ProductDT["Breakdown_Per_Wilayah"];
								$IdxBreakdown = 0;
								$brs = "";
								// array_push($breakdowns, array("Wilayah"=>trim($b->Kota), "Wil"=>$this->replaceSymbolChars(trim($b->Kota)), 
								// 		"Kd_Lokasi"=>$b->Kd_Lokasi, 
								// 		"AvgJual"=>$b->Avg_Jual, "TotalAvgAll"=>$b->Total_Avg_Jual,
								// 		"Persentase"=>$b->Persentase_Jual, "TotalQtyCampaign"=>$b->Total_Qty_Campaign, 
								// 		"TotalQty"=>$b->Total_Qty, "IsDraft"=>(($b->IsDraft==null)?0:$b->IsDraft), 
								// 		"IsSelected"=>(($b->IsSelected==null)?0:$b->IsSelected)));

								for($w=0; $w<count($breakdowns); $w++) {
									$IdxBreakdown += 1;
									$Wil = $breakdowns[$w]["Wil"];
									$TotalPersentase += $breakdowns[$w]["Persentase"];
									$TotalQty += $breakdowns[$w]["TotalQty"];
							?>
									<tr>
										<td><?php echo($IdxBreakdown);?></td>
										<td id='Wilayah_<?php echo($TrxId."_".$Wil);?>'><?php echo($breakdowns[$w]["Wilayah"]);?></td>
										<td id='Persentase_<?php echo($TrxId."_".$Wil);?>'><?php echo($breakdowns[$w]["Persentase"]);?> %</td>
										<td>
											<input type='number' style='width:80px;' class='isdisabled qty-dt total-qty-<?php echo($TrxId);?>' 
											idx='<?php echo($IdxBreakdown);?>' trxid='<?php echo($TrxId);?>' wilayah='<?php echo($breakdowns[$w]["Wil"]);?>' 
											name='TotalQty_<?php echo($TrxId."_".$Wil);?>' id='TotalQty_<?php echo($TrxId."_".$Wil);?>' min='1' 
											value='<?php echo(($breakdowns[$w]["TotalQty"]==0)? "" : $breakdowns[$w]["TotalQty"]);?>' 
											onblur='totalQtyChanged("<?php echo($TrxId);?>", "<?php echo($Wil);?>", <?php echo($counter);?>)' />
										</td>
								<?php
									$status = "<img class='product-icon' id='product-loading-".$TrxId."-".$Wil."' src='".base_url("images/loading.gif")."' height='30px' width='80px'>";
									$status.= "<img class='product-icon' id='product-success-".$TrxId."-".$Wil."' src='".base_url("images/success2.png")."' height='30px' width='30px'>";
								?>
										<td><?php echo($status);?></td>
									</tr>
							<?php
								}

								$brs.= "<tr>";
								$brs.= "	<th colspan='2'>Total</th>";
								$brs.= "	<th id='RowTotalPersentase_".$TrxId."'>".number_format($TotalPersentase,2)." %</th>";
								$brs.= "	<th id='RowTotalQty_".$TrxId."'>".number_format($TotalQty)."</th>";
								$brs.= " 	<th></th>";
								$brs.= "</tr>";
								echo($brs);								
							?>
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
			<div style='margin:10px;float:left;'>
				<a href="<?php echo(site_url('CampaignPlan/edit?trxid='.urlencode($CampaignID)));?>">
					<div class="btn" id="btnBack" name="btnBack">Back</div>
				</a>
			</div>
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnSave" name="btnSave">Save</div>
			</div>
			<div style='margin:10px;float:left;'>
				<a href="<?php echo(site_url('CampaignPlan'));?>"><div class="btn" id="btnClose" name="btnClose">Exit</div></a>
			</div>
			<div style='margin:10px;float:left;color:white;font-size:12px;height:50px;line-height:50px;vertical-align:bottom;'>
				STEP 2 OF 2
			</div>
			<div style='margin:10px;float:left;color:yellow;font-size:14px;height:50px;line-height:50px;vertical-align:bottom;'>
				29 SEP 2021: Bisa Save Tanpa Menunggu Save In Background Selesai
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
