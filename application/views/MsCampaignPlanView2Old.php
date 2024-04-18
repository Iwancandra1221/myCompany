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

	// function hitungTotAvg($index, $kota, $bedahari) {
	// 	$('#tot_avg_'+$index+'_'+$kota).val($('#avg_rounded_'+$index+'_'+$kota).val()*$bedahari);
	// 	// validate();
	// }
	
	// function hitungAvgRounded($index, $kota, $bedahari) {
	// 	// $('#avg_rounded_'+$index+'_'+$kota).val(Math.floor($('#tot_avg_'+$index+'_'+$kota).val()/$bedahari));
	// 	$('#avg_rounded_'+$index+'_'+$kota).val($('#tot_avg_'+$index+'_'+$kota).val()/$bedahari);
	// 	// validate();
	// }
	
	function validate() {
		$("#btnSave").attr("disabled",true);
		$("#btnSave").prop("disabled",true);

		var valid = true;
		for(var h in CampaignHDs) {
			if (CampaignHDs[h].IsSelected==false) {
				valid = valid && false;
			}
		}

		$(".tbodyBreakdownWilayah tr").each(function() {
			//alert('masuk');
			var flag = $(this).find('input[type="number"]');
			if (flag.val()=="") {
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

	function radiobuttonClicked(Idx, TrxId) 
	{
		var camp;
		for(var c in campaigns) {
			if (campaigns[c].Id==TrxId) {
				camp = campaigns[c];
			}
		}
		for(var h in CampaignHDs) {
			if (CampaignHDs[h].ProductID==camp.Kd_Brg) {
				CampaignHDs[h].IsSelected = true;
			}
		}

		$(".group-"+Idx).prop("checked", false);
		$("#DetailCampaign_"+TrxId).prop("checked", true);
		
		var brs = "";
		var breakdowns = camp.Breakdown_Per_Wilayah;
		var TotalPersentase = 0;
		var TotalQty = 0;
		var IdxBreakdown = 0;
		var status = "";

		for(var b in breakdowns) {
			IdxBreakdown += 1;
			breakdowns[b].IdxBreakdown = IdxBreakdown;
			TotalPersentase += Number(breakdowns[b].Persentase);
			TotalQty += Number(breakdowns[b].TotalQty);

		 	// alert(breakdowns[b].Wilayah);
			brs+= "<tr>";
			brs+= "	<td>"+IdxBreakdown+"</td>";
			brs+= "	<td id='Wilayah_"+TrxId+"_"+IdxBreakdown+"'>"+breakdowns[b].Wilayah+"</td>";
			if (camp.Jns_Trx!="" && camp.Jns_Trx!=null) {
				brs+= "	<td>"+breakdowns[b].Persentase+" %</td>";
				brs+= "	<td>"+number_format(breakdowns[b].TotalQty)+"</td>";
			} else {
				/*brs+= " <td><input type='number' style='width:80px;' class='isdisabled persentase-dt persentase-"+TrxId+"' "+
							"idx='"+IdxBreakdown+"' trxid='"+TrxId+"' wilayah='"+breakdowns[b].Wil+"' "+ 
							"name='Persentase[]' id='Persentase_"+TrxId+"_"+IdxBreakdown+"' min='1' value='"+breakdowns[b].Persentase+"' "+
							"onblur='persentaseChanged("+TrxId+", "+IdxBreakdown+")' readonly/></td>"; */
				brs+= " <td id='Persentase_"+TrxId+"_"+IdxBreakdown+"'>"+breakdowns[b].Persentase+" %</td>"; 
				brs+= " <td>
							<input type='number' style='width:80px;' class='isdisabled qty-dt total-qty-"+TrxId+"' "+
							"idx='"+IdxBreakdown+"' trxid='"+TrxId+"' wilayah='"+breakdowns[b].Wil+"' "+ 
							"name='TotalQty[]' id='TotalQty_"+TrxId+"_"+IdxBreakdown+"' min='1' value='"+((breakdowns[b].TotalQty==0)?"":breakdowns[b].TotalQty)+"' "+
							"onblur='totalQtyChanged("+TrxId+", "+IdxBreakdown+", "+Idx+")' />
						</td>";
				// brs+= " <td class='StatusBreakdown_"+TrxId+"' id='StatusBreakdown_"+TrxId+"_"+IdxBreakdown+"'></td>";
			}
			status = "<img class='product-icon' id='product-loading-"+TrxId+"-"+IdxBreakdown+"' src='<?php echo base_url("images/loading.gif") ?>' height='30px' width='80px'>";
			status += "<img class='product-icon' id='product-success-"+TrxId+"-"+IdxBreakdown+"' src='<?php echo base_url("images/success2.png") ?>' height='30px' width='30px'>";
			brs+= "	<td>"+status+"</td>";
			brs+= "</tr>";
		}
		brs+= "<tr>";
		brs+= "	<th colspan='2'>Total</th>";
		brs+= "	<th id='RowTotalPersentase_"+TrxId+"'>"+number_format(TotalPersentase,2)+" %</th>";
		brs+= "	<th id='RowTotalQty_"+TrxId+"'>"+number_format(TotalQty)+"</th>";
		brs+= " <th></th>";
		brs+= "</tr>";

		$("#tbodyBreakdownWilayah-"+Idx).html(brs);
		$(".product-icon").hide();
		SaveDraft(Idx, TrxId);
	}

	function SaveDraft(Idx, TrxId)
	{
		var camp;
		var breakdowns;
		var TotalHariPlanCamp = 0;
		var QtyAvgCamp = 0;
		var TotalQtyCamp = 0; 

		for(var c in campaigns) {
			if (campaigns[c].Id==TrxId) {
				camp = campaigns[c];
				TotalHariPlanCamp = camp.Total_Hari_Plan;
				QtyAvgCamp = camp.Avg;
				TotalQtyCamp = camp.TotalAvg; 
				breakdowns = camp.Breakdown_Per_Wilayah;
			}
		}
		var isSuccess = 1;
		var errMsg = "";
		//alert("saveDraft");
		validate();


		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('CampaignPlan/SaveDraftDT'); ?>", {
			kode_plan 			: CampaignID,
			kode_barang			: camp.Kd_Brg,
			previous_campaign_id: TrxId,
			total_hari_plan 	: TotalHariPlanCamp,
			avg_jual			: QtyAvgCamp,
			total_avg_jual		: TotalQtyCamp,
			csrf_bit			: csrf_bit
		}, function(data){
			//alert(data.result + " : " + data.campaignId);
			if (data.result=="SUCCESS") {

				for(var b in breakdowns) {
					if (isSuccess==true) {
						// $(".loading").show();
						$("#product-loading-"+TrxId+"-"+breakdowns[b].IdxBreakdown).show();
						var csrf_bit = $("input[name=csrf_bit]").val();
						$.post("<?php echo site_url('CampaignPlan/SaveDraftBreakdown'); ?>", {
							kode_plan 			: CampaignID,
							previous_campaign_id: TrxId,
							kode_barang			: camp.Kd_Brg,
							wilayah 			: breakdowns[b].Wilayah,
							kode_lokasi			: breakdowns[b].Kd_Lokasi,
							total_qty_campaign	: breakdowns[b].TotalQtyCampaign,
							total_qty			: breakdowns[b].TotalQty,
							csrf_bit			: csrf_bit
						}, function(data){
							if (data.result=="SUCCESS") {
								isSuccess = true;
								$("#product-loading-"+TrxId+"-"+breakdowns[b].IdxBreakdown).hide();
								$("#product-success-"+TrxId+"-"+breakdowns[b].IdxBreakdown).show();
							} else {
								isSuccess = false;
								$("#product-loading-"+TrxId+"-"+breakdowns[b].IdxBreakdown).hide();
								errMsg = data.errMsg;
							}
						},'json',errorAjax);
						// $(".loading").hide();		
					}
				}
			} else {
				errMsg = data.errMsg;
			}

			$(".StatusDt_"+Idx).html("");

			if (isSuccess==true) {
				$("#StatusDt_"+TrxId).html("<font color='green'>saved</font>")
			} else {
				$("#StatusDt_"+TrxId).html("<font color='red'>save failed: "+data.errMsg+"</font>");	
			}

		},'json',errorAjax);		
		$(".loading").hide();
	}

	function valueAvgChanged(TrxId, Idx)
	{
		//TrxId : Id dari Previous Campaign
		//Idx: Id Dari Product (Posisi Product)

		if ($("#DetailCampaign_"+TrxId).prop("checked")==false) {
			$(".group-"+Idx).prop("checked", false);
			$("#DetailCampaign_"+TrxId).prop("checked",true);
		}
		//alert("Value Avg Changed");
		var TotalAvg = 0;
		var Avg = $("#AvgCampaign_"+TrxId).val();
		var camp;

		for(var c in campaigns) {
			if (campaigns[c].Id==TrxId) {
				camp = campaigns[c];
				//alert(camp.Total_Hari_Plan);
				TotalAvg = camp.Total_Hari_Plan * Avg;
				//alert(TotalAvg);
				$("#TotalAvg_"+TrxId).val(TotalAvg);

				campaigns[c].Avg = Avg;
				campaigns[c].Total_Avg = TotalAvg;

				hitungQtyPerWilayah(TrxId);
			}
		}
		SaveDraft(Idx, TrxId);
	}

	function totalAvgChanged(TrxId, Idx) 
	{
		if ($("#DetailCampaign_"+TrxId).prop("checked")==false) {
			$(".group-"+Idx).prop("checked", false);
			$("#DetailCampaign_"+TrxId).prop("checked",true);
			radiobuttonClicked(Idx, TrxId);
		}
		//alert("Total Avg Changed");

		hitungQtyPerWilayah(TrxId);

		var camp;
		var TotalAvg = $("#TotalAvg_"+TrxId).val();
		var Avg = 0;
		for(var c in campaigns) {
			if (campaigns[c].Id==TrxId) {
				camp = campaigns[c];
				Avg = Math.round(TotalAvg/camp.Total_Hari_Plan);
				$("#AvgCampaign_"+TrxId).val(Avg);

				campaigns[c].Avg = Avg;
				campaigns[c].TotalAvg = TotalAvg;
			}
		}
		SaveDraft(Idx, TrxId);
	}	

	function hitungQtyPerWilayah(TrxId)
	{
		var camp;
		var breakdowns;
		for(var c in campaigns) {
			if (campaigns[c].Id==TrxId) {
				camp = campaigns[c];
				breakdowns = camp.Breakdown_Per_Wilayah;
			}
		}

		var TotalAvg = $("#TotalAvg_"+TrxId).val();
		var IdxBreakdown = "";
		var Wilayah = "";
		var Persentase = 0;
		var Qty = 0;
		var TotalPersentase = 0;
		var TotalQty = 0;


		$(".total-qty-"+TrxId).each(function(){
			IdxBreakdown = $(this).attr("idx");
			Wilayah = $(this).attr("wilayah");
			//alert(IdxBreakdown);
			Persentase = $("#Persentase_"+TrxId+"_"+IdxBreakdown).html();
			//alert(Persentase);
			Persentase = Number(Persentase.replace(" %",""));
			TotalPersentase += Persentase;

			Qty = Math.round((Persentase * TotalAvg) / 100);
			TotalQty += Qty;
			if (Qty>0) {
				$("#TotalQty_"+TrxId+"_"+IdxBreakdown).val(Qty);
				$("#StatusBreakdown_"+TrxId+"_"+IdxBreakdown).html("saved");
			}

			for(var b in breakdowns) {
				if (breakdowns[b].Wil==Wilayah) {
					breakdowns[b].TotalQty = Qty;
				}
			}
		});

		for(var b in breakdowns) {
			breakdowns[b].TotalQtyCampaign = TotalQty;
		}
		
		for(var c in campaigns) {
			if (campaigns[c].Id==TrxId) {
				campaigns[c].Breakdown_Per_Wilayah = breakdowns;
			}
		}

		$("#TotalAvg_"+TrxId).val(TotalQty);
		$("#RowTotalPersentase_"+TrxId).html(number_format(TotalPersentase, 2)+" %");
		$("#RowTotalQty_"+TrxId).html(number_format(TotalQty));
	}

	function totalQtyChanged(TrxId, IdxB, Idx) 
	{
		//Function ini menghandle Perubahan Qty Per Wilayah
		//TrxID : Id Campaign yg Dipilih
		//IdxB : Id Qty Per Wilayah
		var camp;
		var breakdowns;
		for(var c in campaigns) {
			if (campaigns[c].Id==TrxId) {
				camp = campaigns[c];
				breakdowns = camp.Breakdown_Per_Wilayah;
			}
		}

		var Qty = 0;
		var TotalQty = 0;
		var Wilayah = "";
		Wilayah = $("#Wilayah_"+TrxId+"_"+IdxB).text();
		Qty = $("#TotalQty_"+TrxId+"_"+IdxB).val();

		alert("Wilayah : "+Qty);

		$(".total-qty-"+TrxId).each(function(){
			IdxBreakdown = $(this).attr("idx");
			Wilayah = $(this).attr("wilayah");
			Qty = Number($(this).val());
			TotalQty += Qty;

			//Update Ulang Qty Per Wilayah dari array breakdowns
			//Sesuai Qty dari textbox TotalQty
			for(var b in breakdowns) {
				if (breakdowns[b].Wilayah==Wilayah) {
					breakdowns[b].TotalQty = Qty;
				}
			}
		});		

		//TotalQty Seluruh Wilayah Diupdate Ulang ke TotalQtyCampaign dr Array Breakdown
		for(var b in breakdowns) {
			breakdowns[b].TotalQtyCampaign = TotalQty;
		}
		
		//Hitung Ulang Average Per Hari dan Total Average di Tabel Product Per Campaign
		$("#RowTotalQty_"+TrxId).html(number_format(TotalQty));
		$("#TotalAvg_"+TrxId).val(TotalQty);
		var Avg = 0;
		Avg = Math.round(TotalQty / camp.Total_Hari_Plan);
		$("AvgCampaign_"+TrxId).val(Avg); 

		for(var c in campaigns) {
			if (campaigns[c].Id==TrxId) {
				campaigns[c].Breakdown_Per_Wilayah = breakdowns;
				camp.Avg = Avg;
				camp.TotalAvg = TotalQty; 
			}
		}

		SaveDraft(Idx, TrxId);
		$(".StatusBreakdown_"+TrxId).html("saved");
		$("#StatusBreakdown_"+TrxId+"_"+IdxB).html("<b>saved</b>");

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

		for(var c in CampaignHDs) {
	 		CampaignHDs[c].IsSelected = false;
		}
		validate();

		$('input:radio[type="radio"]').each(function() {
			var idx=0;
			if (this.checked==true) {
				idx = $(this).attr("idx");
				radiobuttonClicked(idx, $(this).val());
			}
		});
		
		var c = 0;
		$("#table-primary tbody tr").each(function() {
			var $item = $("td:first", this);
			var $prev = $(this).prev().find('td:first');
			
			if ($prev.length && $prev.text().trim() != $item.text().trim()) {
				c = 1 - c;
			}
			$(this).addClass(['zebra-even', 'zebra-odd'][c]);
		});
				
		$("#btnRequest").click(function(){
			var isDisabled = $("#btnRequest").prop('disabled');
			if(!isDisabled){
				$("#FormCampaignPlan").submit();
			}
		});
		
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
					<?php //echo(json_encode($campaigns)."<br>");?>
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
									<th scope="col" width="5%" class="" style="border:1px solid #ccc;">Pilih</th>
									<!-- <th width="5%" class="" style="border:1px solid #ccc;"></th> -->
								</tr>
							</thead>
							<tbody id="tbodyBarangCampaign-<?php echo $counter; ?>" class="tbodyBarangCampaign" idx="<?php echo($counter);?>">
							<?php
							for($i=0; $i<count($campaigns);$i++) {	
								if (strtoupper($campaigns[$i]["Kd_Brg"])==strtoupper($tab)) {
									$value=0;
									$isChecked="";

									$BRG = strtoupper($campaigns[$i]["Kd_Brg"]);
									$TRX = ($campaigns[$i]["Jns_Trx"]=="")?"-":"<b>".$campaigns[$i]["Nm_Trx"]."</b><br>".$campaigns[$i]["Jns_Trx"];
									$TOTAL_HARI = $campaigns[$i]["Total_Hari"];
									$TOTAL_JUAL = number_format($campaigns[$i]["Total_Jual"]);
									if ($campaigns[$i]["Jns_Trx"]=="" || $campaigns[$i]["Jns_Trx"]==null) {
										$AVG = "<input type='number' style='width:80px;' class='isdisabled' ". 
												"name='AvgCampaign[]' id='AvgCampaign_".$campaigns[$i]["Id"]."' min='1' ".
												"value='".$campaigns[$i]["Avg"]."' ".
												"onblur='valueAvgChanged(".$campaigns[$i]["Id"].",".$counter.")'/>";
									} else {
										$AVG = number_format($campaigns[$i]["Avg"]);
									}
									// $AVG_NORMAL = "0";
									$TOTAL_HARI_PLAN = $campaigns[$i]["Total_Hari_Plan"];
									if ($campaigns[$i]["Jns_Trx"]=="" || $campaigns[$i]["Jns_Trx"]==null) {
										$TOTAL_AVG = "<input type='number' style='width:80px;' class='isdisabled' ". 
												"name='TotalAvg[]' id='TotalAvg_".$campaigns[$i]["Id"]."' min='1' ".
												"value='".$campaigns[$i]["Total_Avg"]."' ".
												"onblur='totalAvgChanged(".$campaigns[$i]["Id"].",".$counter.")'/>";
									} else {
										$TOTAL_AVG = number_format($campaigns[$i]["Total_Avg"]);
									}

									$brs = "<tr>";
									$brs.= "	<td class='hideOnMobile'>".$BRG."</td>";
									$brs.= "	<td class='hideOnMobile'>".$TRX."</td>";
									$brs.= "	<td class='hideOnMobile'>".$TOTAL_HARI."</td>";
									$brs.= "	<td class='hideOnMobile'>".$TOTAL_JUAL."</td>";
									$brs.= "	<td class='hideOnMobile'>".$AVG."</td>";
									// $brs.= "	<td class='hideOnMobile'>".$AVG_NORMAL."</td>";
									$brs.= "	<td class='hideOnMobile'>".(string)$TOTAL_HARI_PLAN."</td>";
									$brs.= "	<td class='hideOnMobile'>".$TOTAL_AVG."</td>";
									$brs.= "	<td class='colMobile'>".$TRX."<br>".
											"TotalJual:".$TOTAL_JUAL."<br>".
											"TotalHari:".$TOTAL_HARI." Hari<br>".
											"AvgJual: ".$AVG."<br>";
									$brs.=  "TotalHari: ".(string)$TOTAL_HARI_PLAN."<br>".
											"TotalQty: ".$TOTAL_AVG."</td>";
									$brs.= "	<td><input type='hidden'
											class='old-detail-campaign'
											id='OldDetailCampaign_".$campaigns[$i]["Id"]."'
											value='".$campaigns[$i]["Id"]."'/>

											<input type='radio'
											class='detail-campaign group-".$counter."'
											id='DetailCampaign_".$campaigns[$i]["Id"]."'
											value='".$campaigns[$i]["Id"]."' idx='".$counter."' kdbrg='".$campaigns[$i]["KdBrg"]."'
											onclick='radiobuttonClicked(".$counter.", ".$campaigns[$i]["Id"].")' ".
											$view_only." ".(($campaigns[$i]["IsSelected"]==1)?"checked":"")."/></td>";
									// $brs.= "	<td class='StatusDt_".$counter."' id='StatusDt_".$campaigns[$i]["Id"]."'></td>";
									$brs.="</tr>";
									echo($brs);
								}
							}?>
							</tbody>
						</table>
						<table id="table-breakdown-<?php echo($counter);?>" class="table table-striped table-bordered" cellspacing="0">
							<thead id="theadBreakdownWilayah-<?php echo($counter);?>">
								<tr>
									<th scope="col" width="5%" style="border:1px solid #ccc;">No</th>
									<th scope="col" width="30%" style="border:1px solid #ccc;">Wilayah</th>
									<th scope="col" width="15%" style="border:1px solid #ccc;">Persentase</th>
									<th scope="col" width="15%" style="border:1px solid #ccc;">TotalQty</th>
									<th scope="col" width="35%" style="border:1px solid #ccc;"></th>
								</tr>
							</thead>
							<tbody id="tbodyBreakdownWilayah-<?php echo($counter);?>" class="tbodyBreakdownWilayah">
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
		</div>
	</div>
</div>
<?php echo form_close(); ?>
