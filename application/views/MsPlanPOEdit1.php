<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
	.header-row, .header-row-label {
		padding:4px!important; font-size:12px!important;
	}
	.header-row-label { font-size:14px!important;}
	.header-row input, .header-row select {
		font-size:12px!important;
		text-transform: uppercase;
	}
	.draft {
		font-size:10px;margin-left:10px;padding:1px;width:25px;border:0px;background-color: transparent;
		/*color:white;font-size:12px;margin-left:10px;padding:1px;background-color:red;width:75px;*/
	}
	.smalldraft {
		color:white;font-size:10px;margin-left:10px;padding:1px;background-color:#070a91;width:75px;
	}
	.hideMe {
		display:none;
	}
	.plan-po-card {
		padding-left:50px;padding-right:50px;margin-top:20px; margin-bottom:20px;
	}

	.dt-cell {
		background-color: #fdff8f;
	}
	.filterDropdown {
		width: 100%;
		background-color: #ffffcc;
	}

	.filterText {
		width: 75%;
		background-color: #ffffcc;
		text-transform: uppercase;
	}

	.title {
		font-size: 15pt;
		font-weight: bold;
		text-align: center;
	}

	.btn {
		font-size:12px!important;
	}

	.e-message {
		color: #fff;
		font-size: 13px;
		font-style: italic;
		padding: 10px 15px;
		border-radius: 5px;
		margin-bottom: 20px;
	}

	.e-message.error {
		background-color: #dc3545;
	}

	.e-message.success {
		background-color: #218838;
	}

	.hidden {
		display: none;
	}
	
	table.dataTable thead .sorting:after {
    opacity: 0.2;
    content: "";
	}
	
	table.dataTable thead .sorting_asc:after {
    content: "";
	}
	table.dataTable thead .sorting_desc:after {
    content: "";
	}
</style>

<script>
	var ListMonth = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"];
	var mode = "<?php echo($mode);?>";
	// alert(mode);
	var planHD = <?php echo(json_encode($planHD));?>;
	var dtPeriods = <?php echo(json_encode($dtPeriods));?>;
	var dtProducts = <?php echo(json_encode($dtProducts));?>;
	var dtRegions = <?php echo(json_encode($dtRegions));?>;
	// alert(dtProducts.length);
	var ListWilayah = <?php echo(json_encode($wilayah));?>;
	var WilayahCount = ListWilayah.length;
	// alert("Jumlah Wilayah : "+WilayahCount);

	// var isDraft = <?php echo($isDraft);?>;
	var ListMiyako = <?php echo(json_encode($list_miyako));?>;
	var ListMicook = <?php echo(json_encode($list_micook));?>;
	var ListRinnai = <?php echo(json_encode($list_rinnai));?>;
	var ListShimizu = <?php echo(json_encode($list_shimizu));?>;
	var ListCosanitary = <?php echo(json_encode($list_cosanitary));?>;

	var BrgMiyako = <?php echo(json_encode($miyako));?>;
	var BrgMicook = <?php echo(json_encode($micook));?>;
	var BrgRinnai = <?php echo(json_encode($rinnai));?>;
	var BrgShimizu = <?php echo(json_encode($shimizu));?>;
	var BrgCosanitary = <?php echo(json_encode($cosanitary));?>;

	var curMonth = <?php echo($curMonth);?>;
	var curYear = <?php echo($curYear);?>;
	var curPeriod = <?php echo($curPeriod);?>;

	var idx = 0; // untuk simpan nilai id unik terakhir supaya tidak ada yg double id jika ada row yg terhapus di baris tengah table
	var brs = 0;
	var idxP = 0;
	var brsP = 0;

	var RemoveBarang = function(i) {
		//Kalo Kode Barang Masih Kosong maka Langsung Remove 1 Row
		if ($("#filterBarang_"+i).val()=="") {
			$("#kolumbrg" + i).remove();
			brs = brs - 1;
		// } else if ($("#flagDraft_"+i).val()=="*") {
		// 	$(".loading").show();
		// 	var csrf_bit = $("input[name=csrf_bit]").val();
		// 	$.post("<?php echo site_url('PlanPO/RemoveDraftProduct'); ?>", {
		// 		id 				: i,
		// 		kode_plan 		: $("#txtPlanCode").val(),
		// 		kode_barang		: $("#filterBarang_"+i).val(),
		// 		csrf_bit		: csrf_bit
		// 	}, function(data){
		// 		//alert(data.result + " : " + data.campaignId);
		// 		if (data.result=="SUCCESS") {
		// 			$("#kolumbrg" + i).remove();
		// 			brs = brs - 1;
		// 		} else {
		// 			$("#StatusSave_"+i).html("<font color='red'>save failed: "+data.errMsg+"</font>");	
		// 		}

		// 	},'json',errorAjax);		
		// 	$(".loading").hide();
		} else {
			if (confirm("Remove Barang "+$("#filterBarang_"+i).val()+" ?")) {
				$(".loading").show();
				var csrf_bit = $("input[name=csrf_bit]").val();
				$.post("<?php echo site_url('PlanPO/RemoveProduct'); ?>", {
					id 				: i,
					kode_plan 		: $("#txtPlanCode").val(),
					kode_barang		: $("#filterBarang_"+i).val(),
					csrf_bit		: csrf_bit
				}, function(data){
					//alert(data.result + " : " + data.campaignId);
					if (data.result=="SUCCESS") {
						$("#kolumbrg" + i).remove();
						brs = brs - 1;
					} else {
						$("#StatusSave_"+i).html("<font color='red'>save failed: "+data.errMsg+"</font>");	
					}

				},'json',errorAjax);		
				$(".loading").hide();
			}
		}
		if (CheckEmptyProductExists()==false) {
			addRow();
		}
	}

	var RemoveAllBarang = function(i) {
		$('.rowbrg').each(function(i, obj) {
			$("#" + this.id ).remove();
		});
		idx = 0;
		brs = 0;
	}
	
	var setPeriods = function() {
		//ValidasiPeriode 
		//NaN : Not-a-Number
		var PP1 = parseInt($("#cboPeriodeP1").val());
		if (isNaN(PP1)==true) PP1 = 0;							
		// alert("PP1: "+PP1);
		var PBl1 = parseInt($("#cboPeriodeBl1").val());
		if (isNaN(PBl1)==true) PBl1 = 0;		
		// alert("PBl1: "+PBl1);
		var PTh1 = parseInt($("#cboPeriodeTh1").val());
		if (isNaN(PTh1)==true) PTh1 = 0;		
		// alert("PTh1: "+PTh1);
		var PP2 = parseInt($("#cboPeriodeP2").val());
		if (isNaN(PP2)==true) PP2 = 0;		
		// alert("PP2: "+PP2);
		var PBl2 = parseInt($("#cboPeriodeBl2").val());
		if (isNaN(PBl2)==true) PBl2 = 0;		
		// alert("PBl2: "+PBl2);
		var PTh2 = parseInt($("#cboPeriodeTh2").val());
		if (isNaN(PTh2)==true) PTh2 = 0;		
		// alert("PTh2: "+PTh2);
		
		if ((PP1!=0 && PBl1!=0 && PTh1!=0) && (PP2!=0 && PBl2!=0 && PTh2!=0)) {
			// alert("Semua Tidak Nol");

			if ((PTh1>PTh2) || (PTh1==PTh2 && PBl1>PBl2) ||
				(PTh1==PTh2 && PBl1==PBl2 && PP1>PP2)) {
				// alert("Cek Ulang Range Periode");
			} else {
				var done = false;
				var indeks = 0;

				while(done==false) {
					//alert("P:"+PP1+" BL:"+PBl1+" TH:"+PTh1);
					indeks += 1;
					if (PP1!=PP2 || PBl1!=PBl2 || PTh1!=PTh2) {
						if (PP1==1) {
							PP1 = 2;
						} else if (PBl1<PBl2) {
							PP1 = 1;
							PBl1 += 1;
						} else if (PTh1<PTh2) {
							PP1 = 1;
							PBl1 = 1;
							PTh1 += 1;
						} else {
							done = true;
						}
					} else {
						done = true;
					}
				}

				if (indeks>7) {
					alert("Maksimum 7 Periode!!\nLebih Dari 7 Bagi menjadi 2 Plan Berbeda");
				} else {
					$("#tbodyPeriode").html("");
					done = false;
					indeks = 0;
					PP1 = parseInt($("#cboPeriodeP1").val());
					PBl1 = parseInt($("#cboPeriodeBl1").val());
					PTh1 = parseInt($("#cboPeriodeTh1").val());
					
					while(done==false) {
						//alert("P:"+PP1+" BL:"+PBl1+" TH:"+PTh1);
						indeks += 1;
						addPeriodRow(true, indeks, PP1, PBl1, PTh1);

						// var msg = "PP1: "+PP1+"\n";
						// msg += "PBL1: "+PBl1+"\n";
						// msg += "PTh1: "+PTh1+"\n\n";
						// msg += "PP2: "+PP2+"\n";
						// msg += "PBL2: "+PBl2+"\n";
						// msg += "PTh2: "+PTh2+"\n\n";
						// alert(msg);

						if (PP1!=PP2 || PBl1!=PBl2 || PTh1!=PTh2) {
							if (PP1==1) {
								PP1 = 2;
							} else if (PBl1<PBl2) {
								PP1 = 1;
								PBl1 += 1;
							} else if (PTh1<PTh2) {
								PP1 = 1;
								PBl1 = 1;
								PTh1 += 1;
							} else {
								done = true;
							}
						} else {
							done = true;
						}
					}
					validate();
				}
			}
		}
	}	

	var addPeriodRow = function(newRow=true, indeks=0, periodP=0, periodBl=0, periodTh=0, draft=true, disabled=false) {
		// alert("adding Row");
		if (newRow==false) {
			idxP += 1;
		}

		brsP += 1;
		var planCode = $("#txtPlanCode").val();
		
		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('PlanPO/GetPOPeriod'); ?>", {
			p				: periodP,
			bl				: periodBl,
			th  			: periodTh,
			planNo 			: planCode,
			csrf_bit		: csrf_bit
		}, function(data){
			var P = data.data;
			var i = P.PeriodId;

			var tr = "<tr class='rowPeriode'  id='rowPeriode" + i + "'>";
			var td = "<td>" +
				"<input type='button' onclick='RemovePeriode(\"" + i + "\")' id='btnRemovePeriode" + i + "' class='isdisabled btnRemovePeriode' " +
				"style='width:21px;padding:0px!important;background-color:red;'  value='-'>" +
				"</td>";
			// alert("td: "+td);
			var td1 = "<td class='dt-cell'>" +
				"<input type='text' class='filterText Periode' name='dtPeriode[]' id='dtPeriode" + i + "' idx='"+i+"' brs='"+brsP+"' " + 
				"p='"+periodP+"' bl='"+periodBl+"' th='"+periodTh+"' "+
				"onblur='periodBlur("+i+", 1)' value='"+P.PeriodName+"' required disabled>" +
				"<input type='text' class='hidden filterText' name='dtPeriodeId[]' id='dtPeriodeId" + i + "' idx='"+i+"' brs='"+brsP+"' " + 
				"p='"+periodP+"' bl='"+periodBl+"' th='"+periodTh+"' "+
				"value='"+i+"' required>" +
				"</td>";
			// alert("td1: "+td1);
				
			var td2 = "<td class='dt-cell'>" +
				"<input type='text' style='width:80px;' class='isdisabled jmlHari' idx='"+i+"' onblur='savePeriod("+i+", 4)' style='width:400px;'" + 
				"name='jmlHari[]' id='jmlHari" + i + "' min='1' value='"+P.DayCount+"' initval='"+P.DayCount+"' existed='"+data.existed+"' required>" +
				"</td>";
			// alert("td2: "+td2);
			var td3 = "</tr>";
			// alert("td3: "+td3);

			$("#tbodyPeriode").append(tr + td + td1 + td2 + td3);			
			validate();

		},'json',errorAjax);		
		$(".loading").hide();

		// alert("Setelah GetPOPeriod");
	}

	function savePeriod(i, inputType=0) {
		var periodName = $("#dtPeriode"+i).val();
		var periodId = $("#dtPeriode"+i).attr("idx");
		var periodP = $("#dtPeriode"+i).attr("p");
		var periodBl = $("#dtPeriode"+i).attr("bl");
		var periodTh = $("#dtPeriode"+i).attr("th");
		var jmlHari = $("#jmlHari"+i).val();
		var jmlHariAwal = $("#jmlHari"+i).attr("initval");

		if (jmlHari!=jmlHariAwal) {
			$(".loading").show();
			var csrf_bit = $("input[name=csrf_bit]").val();
			$.post("<?php echo site_url('PlanPO/SavePOPeriod'); ?>", {
				periodId		: periodId,
				periodName 		: periodName,
				p 		 		: periodP,
				bl 		 		: periodBl,
				th 		 		: periodTh,
				dayCount		: jmlHari,
				csrf_bit		: csrf_bit
			}, function(data){
				$("#jmlHari"+i).attr("initval", jmlHari);
			},'json',errorAjax);		
			$(".loading").hide();
		}
	}

	var addRow = function(newRow=true, indeks=0, kdbrg="", ket="", draft=false, status="", disabled=false) {
		// alert("adding Row");
		if (newRow==true) {
			idx += 1;
		} else {
			idx = indeks;
		}

		brs += 1;
		var i = idx;


		var tr = "<tr class='rowbrg'  id='kolumbrg" + i + "'>";
		// alert("tr: "+tr);
		var td = "<td>" +
			"<input type='button' onclick='RemoveBarang(\"" + i + "\")' id='btnRemoveDt" + i + "' class='isdisabled btnRemoveDt" + i + "' style='width:21px;padding:0px !important;background-color:red;'  value='-'>" +
			"</td>";
		// alert("td: "+td);
		var td1 = "<td class='dt-cell'>" +
			"<input type='text' class='filterText filterBarang' name='filterBarang[]' id='filterBarang_" + i + "' idx='"+i+"' brs='"+brs+"' " + 
			"onfocus='SetAutoComplete("+i+")' onblur='productIdBlur("+i+", 1)' value='"+kdbrg+"' required>" +
			"<input type='text' id='flagDraft_"+i+"' class='draft hideMe' value='"+((draft==true)?"*":"")+"'>" +
			"</td>";
		// alert("td1: "+td1);
		var td2 = "<td class='dt-cell'>" +
			"<input type='text' style='width:80px;' class='isdisabled KeteranganDt' idx='"+i+"' onblur='keteranganDtBlur("+i+", 4)' style='width:50%!important;'" + 
			"name='KeteranganDt[]' id='KeteranganDt_" + i + "' min='1' value='"+ket+"' initval='"+ket+"' required>" +
			"</td>";
		// alert("td2: "+td2);
		var td3 = "<td class='dt-cell' id='StatusSave_"+i+"'>"+status+"</td></tr>";
		// alert("td3: "+td3);
		$("#tbodyProducts").append(tr + td + td1 + td2 + td3);

		validate();
	}

	function SetAutoComplete(i) {		
		var div = $("#cboDivision").val();

		if (div=="MIYAKO") {
	    	$("#filterBarang_"+i).autocomplete({
      			source: BrgMiyako
			});			
		} else if (div=="MICOOK") {
	    	$("#filterBarang_"+i).autocomplete({
      			source: BrgMicook
			});
		} else if (div=="RINNAI") {
	    	$("#filterBarang_"+i).autocomplete({
      			source: BrgRinnai
			});
		} else if (div=="SHIMIZU") {
	    	$("#filterBarang_"+i).autocomplete({
      			source: BrgShimizu
			});
		} else if (div=="CO&SANITARY") {
	    	$("#filterBarang_"+i).autocomplete({
      			source: BrgCosanitary
			});
	    }
	}

	function productIdBlur(i, inputType=0) {

		var Divisi = $("#cboDivision").val().toUpperCase();
		var KdBrg = $("#filterBarang_"+i).val().toUpperCase();
		
		var IdxBrg = 0;
		var KodeBrg="";
		var Baris = 0;

		$("#cboDivision").val(Divisi);
		$("#filterBarang_"+i).val(KdBrg);

		//alert("productIdBlur");
		var ProductFound = false;
		if (KdBrg!="") {
			if (Divisi=="MIYAKO") {
				for(var b in ListMiyako)
				{
					if (ListMiyako[b].KD_BRG==KdBrg) {
						ProductFound=true;
						break;
					}
				}
			} else if (Divisi=="MICOOK") {
				for(var b in ListMicook)
				{
					if (ListMicook[b].KD_BRG==KdBrg) {
						ProductFound=true;
						break;
					}
				}
			} else if (Divisi=="RINNAI") {
				for(var b in ListRinnai)
				{
					if (ListRinnai[b].KD_BRG==KdBrg) {
						ProductFound=true;
						break;
					}
				}
			} else if (Divisi=="SHIMIZU") {
				for(var b in ListShimizu)
				{
					if (ListShimizu[b].KD_BRG==KdBrg) {
						ProductFound=true;
						break;
					}
				}
			} else if (Divisi=="CO&SANITARY") {
				for(var b in ListCosanitary)
				{
					if (ListCosanitary[b].KD_BRG.trim()==KdBrg.trim()) {
						ProductFound=true;
						break;
					}
				}
			}

			if (ProductFound) {
				$(".filterBarang").each(function(){
					Baris += 1;
					IdxBrg = $(this).attr("idx");
					KodeBrg = $(this).val().toUpperCase();

					if (IdxBrg!=i) {
						if (KodeBrg==KdBrg) {
							alert("Kode Barang Sudah Ada di Baris #"+Baris);
							ProductFound=false;
							$("#filterBarang_"+i).val("");
							return false;
						}						
					}
				});
			}

			if (ProductFound) {
				SaveDraftProduct(i, inputType);
			} else {
				$("#filterBarang_"+i).focus();
				alert("Kode Barang Tidak Terdaftar/Salah Divisi!");
			}
		}
	}

	function keteranganDtBlur(i, inputType=0) {
		var ket = $("#KeteranganDt_"+i).val().toUpperCase();
		var initVal = $("#KeteranganDt_"+i).attr("initval");
		if (ket!=initVal) {
			SaveDraftProduct(i, inputType);
		}
	}

	function SaveDraftProduct(i, inputType=0) {
		// alert("Saving Draft");

		var Divisi = $("#cboDivision").val().toUpperCase();
		var Kode = $("#txtPlanCode").val().toUpperCase();
		if (Kode=="") {
			Kode="AUTONUMBER";
		}

		var KetHd = $("#txtPlanNote").val().toUpperCase();
		var Status = $("#txtPlanStatus").val().toUpperCase();

		var PP1  = parseInt($("#cboPeriodeP1").val());
		var PBl1 = parseInt($("#cboPeriodeBl1").val());
		var PTh1 = parseInt($("#cboPeriodeTh1").val());
		var PP2  = parseInt($("#cboPeriodeP2").val());
		var PBl2 = parseInt($("#cboPeriodeBl2").val());
		var PTh2 = parseInt($("#cboPeriodeTh2").val());

		var KdBrg = $("#filterBarang_"+i).val().toUpperCase();
		var KetDt = $("#KeteranganDt_"+i).val().toUpperCase();

		if (Divisi!="" && Kode!="" && KdBrg!="") {
			// alert("Calling Function SaveDraft on Controller PlanPO");
			// if (inputType==1) {
			$(".loading").show();
			var csrf_bit = $("input[name=csrf_bit]").val();

			$.post("<?php echo site_url('PlanPO/SaveDraftProduct'); ?>", {
				item_id			: i,
				mode			: mode,
				kodePlan 		: Kode,
				divisi 			: Divisi,
				ketHd			: KetHd,
				status 			: Status,
				p1				: PP1,
				bl1				: PBl1,
				th1 			: PTh1,
				p2 				: PP2,
				bl2 			: PBl2,
				th2 			: PTh2,
				kodeBarang		: KdBrg,
				ketDt			: KetDt,
				csrf_bit		: csrf_bit
			}, function(data) {
				// alert("Calling Function Done");

				if (data.result=="SUCCESS") {

					$("#btnSubmit").attr("disabled", false);
					$("#btnSubmit").prop("disabled", false);

					if (Kode=="AUTONUMBER" || Kode=="") {
						$("#txtPlanCode").attr("disabled", false);
						$("#txtPlanCode").prop("disabled", false);
						$("#txtPlanCode").val(data.planId);
						$("#txtPlanCode").attr("disabled", true);
						$("#txtPlanCode").prop("disabled", true);
					}
					$("#flagDraft_"+i).val("*");
					$("#flagDraft_"+i).removeClass("hideMe");

					// var emptyProductFound = false;
					// $(".filterBarang_").each(function(){
					// 	if ($(this).val()=="") {
					// 		emptyProductFound = true;
					// 	}
					// })
					// if (emptyProductFound==false) {
					if (CheckEmptyProductExists()==false) {
						addRow();
					} 
					i = i+1;
					$("#filterBarang_"+i).focus();
				} else {
					//$("#StatusSave_"+i).html("<font color='red'>save failed: "+data.errMsg+"</font>");	
				}

			},'json',errorAjax);		
			$(".loading").hide();
			// } else {
			// 	//do nothing
			// }
		} else {
			// alert("KdBrg:"+KdBrg+"\r\n"+
			// 	  "StartHD:"+StartPlan+"\r\n"+
			// 	  "EndHD:"+EndPlan+"\r\n"+
			// 	  "JmlHari:"+JumlahHariCampaign);

			//alert("Something is Wrong ("+inputType+")");
		}
		// alert("Saving Draft Complete");
	}

	function SaveDraft2(i, inputType=0) {
	}

	function validate(val = "") {
		if (validateHeader()==false) {
			// alert("Not Valid");
			$("#btnSubmit").prop("disabled", true);
			$("#btnSubmit").attr("disabled", true);
		} else {
			// alert("Valid");
			$("#btnSubmit").prop("disabled", false);
			$("#btnSubmit").attr("disabled", false);
		}
	}

	function CheckEmptyProductExists(){
		var emptyProductFound = false;
		$(".filterBarang").each(function(){
			if ($(this).val()=="") {
				emptyProductFound = true;
			}
		});
		return emptyProductFound;
	}

	function validateHeader(){
		var Result = true;

		// alert("Check Divisi");
		if ($("#cboDivision").val().toUpperCase()=="") return false;
		// if var KetHd = $("#txtPlanNote").val().toUpperCase();
		// var Status = $("#txtPlanStatus").val().toUpperCase();

		// alert("Check Periode P1 : "+$("#cboPeriodeP1").val());
		if (isNaN(parseInt($("#cboPeriodeP1").val()))==true || $("#cboPeriodeP1").val()==null) return false;
		// alert("Check Periode BL1: "+$("#cboPeriodeBl1").val());
		if (isNaN(parseInt($("#cboPeriodeBl1").val()))==true || $("#cboPeriodeBl1").val()==null) return false;
		// alert("Check Periode TH1: "+$("#cboPeriodeTh1").val());
		if (isNaN(parseInt($("#cboPeriodeTh1").val()))==true || $("#cboPeriodeTh1").val()==null) return false;
		// alert("Check Periode P2: "+$("#cboPeriodeP2").val());
		if (isNaN(parseInt($("#cboPeriodeP2").val()))==true || $("#cboPeriodeP2").val()==null) return false;
		// alert("Check Periode BL2: "+$("#cboPeriodeBl2").val());
		if (isNaN(parseInt($("#cboPeriodeBl2").val()))==true || $("#cboPeriodeBl2").val()==null) return false;
		// alert("Check Periode TH2: "+$("#cboPeriodeTh2").val());
		if (isNaN(parseInt($("#cboPeriodeTh2").val()))==true || $("#cboPeriodeTh2").val()==null) return false;

		// alert("Check Wilayah");
		if($('input.cboWilayah:checked').length==0){
			return false;
		}

		// alert("Check Product");
		var product_exists = false;
		$(".filterBarang").each(function(){
			if ($(this).val()!="") {
				product_exists = true;
			}
		});
		if (product_exists==false) return false;

		// alert("Check Periode");
		var period_exists = false;
		$(".Periode").each(function(){
			if ($(this).val()!="") {
				period_exists = true;
			}
		});
		if (period_exists==false) return false;
	}

	function toggle(selectAll=true) 
	{
		$(".cboWilayah").prop("checked", selectAll);
		$(".cboWilayah").attr("checked", selectAll);
		$(".cboKdLokasi").prop("checked", selectAll);
		$(".cboKdLokasi").attr("checked", selectAll);

		for(var r in ListWilayah)
		{
			var xKdLokasi = ListWilayah[r].Kd_Lokasi;
			var xWilayah = ListWilayah[r].Kota;
			var xWilayah2 = ListWilayah[r].Wil;
			// alert("Kode Lokasi : "+xKdLokasi+"\nWilayah : "+xWilayah+"\nWilayah2: "+xWilayah2);

			SaveDraftWilayah(xKdLokasi, xWilayah, xWilayah2, selectAll);
		}

	}

	function validateCheckbox(KdLokasi="", Wilayah="", Wilayah2="") {
		// alert("Kode Lokasi : "+KdLokasi+"\nWilayah : "+Wilayah+"\nWilayah2: "+Wilayah2);
		var isChecked = $("#Kota_"+Wilayah2).prop("checked");
		$("#KdLokasi_"+Wilayah2).prop("checked", isChecked);
		SaveDraftWilayah(KdLokasi, Wilayah, Wilayah2, isChecked);
		validate();
	}

	function SaveDraftWilayah(KdLokasi, Wilayah, Wil, IsChecked) {
		var KodePlan = $("#txtPlanCode").val();
		//alert(Wilayah + " " + ((IsChecked)?"Checked":"Not Checked"));

		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('PlanPO/SaveDraftWilayah'); ?>", {
			mode			: mode,
			kode_plan 		: KodePlan,
			wilayah 		: Wilayah,
			kode_lokasi		: KdLokasi,
			is_checked		: ((IsChecked)?1:0),
			csrf_bit		: csrf_bit
		}, function(data){
			//alert(data.result + " : " + data.campaignId);
			if (data.result=="SUCCESS") {
				if (IsChecked) {
					//$("#StatusKota_"+Wil).html("<b>"+Wilayah+"</b> <smalldraft>DRAFT</smalldraft>");
					$("#StatusKota_"+Wil).html("<b>"+Wilayah+"</b>");
				} else {
					$("#StatusKota_"+Wil).html(""+Wilayah+"");
				}
			} else {
				$("#StatusKota_"+Wil).html(Wilayah+" <font color='red'>&nbsp;&nbsp;failed</font>");	
			}

		},'json',errorAjax);		
		$(".loading").hide();
	}

	$(document).ready(function() {	
		$("#txtPlanCode").attr("disabled", true);
		$("#txtPlanCode").prop("disabled", true);
		$("#cboDivision").val("");

		if (mode=="edit") {
			// alert("Edit");
			planHD.PlanStatus = (planHD.PlanStatus=="NEW REQUEST")? "DRAFT" : planHD.PlanStatus;

			$("#cboDivision").val(planHD.Division);
			$("#cboDivision").attr("disabled", true);
			$("#cboDivision").prop("disabled", true);

			$("#txtPlanCode").val(planHD.PlanNo);
			$("#cboPeriodeP1").val(planHD.PeriodP1);
			$("#cboPeriodeBl1").val(planHD.PeriodBl1);
			$("#cboPeriodeTh1").val(planHD.PeriodTh1);
			$("#cboPeriodeP2").val(planHD.PeriodP2);
			$("#cboPeriodeBl2").val(planHD.PeriodBl2);
			$("#cboPeriodeTh2").val(planHD.PeriodTh2);
			$("#txtPlanNote").val(planHD.PlanNote);
			$("#txtPlanStatus").val(planHD.PlanStatus);
			// alert("Filling Header Done");

			idx = 0;
			var maxIndeks = 0;
			setPeriods();

			//{"Id":"2","PlanNo":"RI\/202106\/0001","ProductId":"RI-522C","ProductNote":"","IsDraft":1,"SavedBy":"INDAH","SavedDate":"2021-06-23 12:44:25.000"}
			for(var p in dtProducts)
			{
				// alert(dtProducts[p].ProductId);
				// idx = planHD[p].ItemID;
				// if (idx>maxIndeks) {
				// 	maxIndeks = idx;
				// }

				//var addRow = function(newRow=true, indeks=0, kdbrg="", ket="", draft=false, status="", disabled=false) 
				// addRow(false, dtProducts[p].Id, dtProducts[p].ProductId, dtProducts[p].ProductNote, ((dtProducts[p].IsDraft==1)? true:false), "", ((planHD[p].IsDraft==1)? false:true));
				addRow(false, dtProducts[p].Id, dtProducts[p].ProductId, dtProducts[p].ProductNote);
			}
			addRow();

			// // idx = maxIndeks;

			for(var w in dtRegions)
			{
				// alert(dtRegions[w].BranchId);
				$("#KdLokasi_"+dtRegions[w].Wil).attr("checked",true);
				$("#KdLokasi_"+dtRegions[w].Wil).prop("checked",true);
				$("#Kota_"+dtRegions[w].Wil).attr("checked",true);
				$("#Kota_"+dtRegions[w].Wil).prop("checked",true);
				$("#StatusKota_"+dtRegions[w].Wil).html("<b>"+dtRegions[w].Region+"</b>");
				//$("#StatusKota_"+dtRegions[w].Wil).html("<b>"+dtRegions[w].Wilayah+"</b> <smalldraft>DRAFT</smalldraft>");
			}

			validate();

		} else {
			// alert("set initial Period");
			$("#btnSubmit").prop("disabled", true);
			$("#btnSubmit").attr("disabled", true);
			
			$("#cboPeriodeP1").val(curPeriod);
			$("#cboPeriodeP2").val(curPeriod);

			curMonth += 2;
			if (curMonth>12) {
				curMonth = curMonth - 12;
				curYear = curYear + 1;
			}

			$("#cboPeriodeBl1").val(curMonth);
			$("#cboPeriodeBl2").val(curMonth);
			$("#cboPeriodeTh1").val(curYear);
			$("#cboPeriodeTh2").val(curYear);

			// var Periode = "Periode: "+curPeriod+"\n";
			// Periode += "Bulan: "+curMonth+"\n";
			// Periode += "Tahun: "+curYear;
			// alert(Periode);

			setPeriods();				
		}

		$("#cboDivision").change(function() {
			var divBefore = $(this).attr("initval");
			var div = $(this).val();

			if (div=="") {
				//alert("Divisi Yang Anda Pilih Salah");
				$("#cboDivision").val(divBefore);
			} else {
				// alert("Removing All Barang");
				RemoveAllBarang();
				// alert("Validating Header");
				// if (validateHeader()) {
					// alert("Check any Empty Product Row");
					if (CheckEmptyProductExists()==false) {
						// alert("Adding Row");
						addRow();						
					} else {
						// alert("Empty Product Row Found");
					}
				// } else {
				// 	// alert("Header not valid");
				// }
				$("#cboDivision").attr("initval", div);
			}

			validate();
		});

		$("#btnSubmit").click(function() {
			if($('input.cboWilayah:checked').length==0){
				alert('Pilih minimal 1 wilayah!');
				return false;
			}

			// $("#filterDivisiReal").removeAttr("disabled");
			$("#txtPlanCode").removeAttr("disabled");
			$("#cboDivision").removeAttr("disabled");
			$(".Periode").removeAttr("disabled");

			var isDisabled = $("#btnSubmit").prop('disabled');
			if (!isDisabled) {
				$(".filterBarang").each(function(){
					if ($(this).val()=="") {
						$(this).parent().parent().remove();
					}
				})
			
				if(mode=='edit'){
					$("#FormPlanPO").attr("action", "editPlanPO");
				}
				// $("#checkBoxContainer").find("input").each(function(index, item) {
				// 	$(item).removeAttr('disabled');
				// });
				$("#FormPlanPO").submit();
			}
		});
	});
</script>

<div class="container">
	<div class="alert">
		<?php if ($this->session->flashdata('success_message') != '') { ?>
			<div class="e-message success"><?php echo $this->session->flashdata('success_message'); ?></div>
		<?php
		}
		if ($this->session->flashdata('err_message') != '') {
		?>
			<div class="e-message error"><?php echo $this->session->flashdata('err_message'); ?></div>
		<?php } ?>
	</div>
	<?php 
	// echo(json_encode($miyako));
	// echo("<br>");
	?>
	<div class="title">PERENCANAAN PO</div>
	<?php echo form_open('PlanPO/editStep2', array("id" => "FormPlanPO")); ?>
	<div class="form">
		<div class="plan-po-card">
			<div class="row" id="rowPlanCode">
				<div class="col-3 col-m-4 header-row-label">Kode Rencana PO</div>
				<div class="col-5 col-m-4 header-row">
					<input type="text" class="form-control" name="txtPlanCode" id="txtPlanCode" placeholder="AutoNumber">
				</div>
				<div class="col-2 col-m-2" style="padding-top:3px!important;">
					<div class="btn" id="btnNew" name="btnNew" style="height:35px!important;padding-top:8px;">RESET</div>
				</div>
				<div class="col-2 col-m-2">
					<span id="statusDraft"></span>
				</div>
			</div>
			<div class="row" id="rowPlanDivision">
				<div class="col-3 col-m-4 header-row-label">Divisi</div>
				<div class="col-9 col-m-8 header-row">
					<select id="cboDivision" name="cboDivision" class="form-control filterDropdownReal isdisabled" initval="" required>
						<option value=''>--PILIH DIVISI--</option>
						<?php
						for ($i = 0; $i < count($divisions); $i++) {
							echo ("<option value='" . $divisions[$i]["DIVISI"] . "'>" . $divisions[$i]["DIVISI"] . "</option>");
						} ?>
					</select>
				</div>
			</div>
			<div class="row" id="rowPeriod">
				<div class="col-3 col-m-4 header-row-label">Periode PO</div>
				<div class="col-9 col-m-8 header-row">
					<select id="cboPeriodeP1" name="cboPeriodeP1" onblur="setPeriods()">
						<option value="1">P1</option>
						<option value="2">P2</option>
					</select>
					<select id="cboPeriodeBl1" name="cboPeriodeBl1" onblur="setPeriods()">
						<option value="1">JAN</option>
						<option value="2">FEB</option>
						<option value="3">MAR</option>
						<option value="4">APR</option>
						<option value="5">MAY</option>
						<option value="6">JUN</option>
						<option value="7">JUL</option>
						<option value="8">AUG</option>
						<option value="9">SEP</option>
						<option value="10">OCT</option>
						<option value="11">NOV</option>
						<option value="12">DEC</option>
					</select>
					<select id="cboPeriodeTh1" name="cboPeriodeTh1" onblur="setPeriods()">
						<option value="2024">2024</option>
						<option value="2023">2023</option>
						<option value="2022">2022</option>
						<option value="2021">2021</option>
					</select>
					S/D
					<select id="cboPeriodeP2" name="cboPeriodeP2" onblur="setPeriods()">
						<option value="1">P1</option>
						<option value="2">P2</option>
					</select>
					<select id="cboPeriodeBl2" name="cboPeriodeBl2" onblur="setPeriods()">
						<option value="1">JAN</option>
						<option value="2">FEB</option>
						<option value="3">MAR</option>
						<option value="4">APR</option>
						<option value="5">MAY</option>
						<option value="6">JUN</option>
						<option value="7">JUL</option>
						<option value="8">AUG</option>
						<option value="9">SEP</option>
						<option value="10">OCT</option>
						<option value="11">NOV</option>
						<option value="12">DEC</option>
					</select>
					<select id="cboPeriodeTh2" name="cboPeriodeTh2" onblur="setPeriods()">
						<option value="2024">2024</option>
						<option value="2023">2023</option>
						<option value="2022">2022</option>
						<option value="2021">2021</option>
					</select>
				</div>
			</div>
			<div class="row" id="rowPlanNote">
				<div class="col-3 col-m-4 header-row-label">Keterangan</div>
				<div class="col-9 col-m-8 header-row">
					<input type="text" class="form-control isdisabled" name="txtPlanNote" id="txtPlanNote" placeholder="Keterangan" autocomplete="off">
				</div>
			</div>
			<div class="row" id="rowPlanStatus">
				<div class="col-3 col-m-4 header-row-label">Status</div>
				<div class="col-9 col-m-8 header-row">
					<input type="text" class="form-control isdisabled" name="txtPlanStatus" id="txtPlanStatus" placeholder="DRAFT" value="DRAFT" disabled>
				</div>
			</div>
		</div>
		<div>
			<table id="table-primary" class="table table-striped table-bordered" cellspacing="0">
				<thead id="theadPeriode">
					<tr>
						<th width="5%"  align="left"></th>
						<th width="40%" style="border:1px solid #ccc;">Periode</th>
						<th width="55%" style="border:1px solid #ccc;">Jumlah Hari</th>
					</tr>
				</thead>
				<tbody id="tbodyPeriode">
				</tbody>
			</table>
		</div>
		<div>
			<table id="table-primary" class="table table-striped table-bordered" cellspacing="0">
				<thead id="theadProducts">
					<tr>
						<th width="5%"  align="left"></th>
						<th width="40%" style="border:1px solid #ccc;">Kode Barang</th>
						<th width="45%" style="border:1px solid #ccc;">Keterangan</th>
						<th width="10%" align="left"></th>
					</tr>
				</thead>
				<tbody id="tbodyProducts">
				</tbody>
			</table>
		</div>
		<div>
			<div class="row">
				<div class="col-6 col-m-6">
					<!-- <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false"  aria-controls="collapseExample" style="width:150px!important;font-size:16px!important;"> -->
					<b style="font-size:14px;">WILAYAH INCLUDE</b>
					<!-- </button> -->
				</div>
				<div class="col-6 col-m-6" align="right">
					<input type="button" class="isdisabled" onclick="toggle(true)" value="Select all">
					<input type="button" class="isdisabled" onclick="toggle(false)" value="Deselect all">
				</div>
			</div>
			<!-- <div class="collapse" id="collapseExample"> -->
			<div>
				<div class="card card-body" id="checkBoxContainer">
					<?php
						$x = 0; 
						foreach ($wilayah as $wlyh) { 
							$x += 1;
							//$strKota = trim(str_replace(' ', '', $wlyh->Kota));
							$strKota = trim($wlyh->Wil);
					?>
						<div class="col-lg-3">
							<input
							type='checkbox'
							class="cboKdLokasi hidden kdlokasi-<?php echo($strKota); ?>"
							id="KdLokasi_<?php echo($strKota);?>" 
							name="KdLokasi[]" value="<?php echo trim($wlyh->Kd_Lokasi)  ?>">
							<?php //echo(trim($wlyh->Kd_Lokasi)); ?>

							<input
							type='checkbox'
							class="cboWilayah isdisabled kota-<?php echo($strKota); ?>"
							id="Kota_<?php echo($strKota);?>" kdlokasi="<?php echo($wlyh->Kd_Lokasi);?>" kota="<?php echo($wlyh->Wil);?>"
							name="Kota[]" onclick="validateCheckbox('<?php echo($wlyh->Kd_Lokasi);?>', '<?php echo(trim($wlyh->Kota));?>', '<?php echo(trim($wlyh->Wil));?>')"
							value="<?php echo trim($wlyh->Kota) ?>">							
							<span id="StatusKota_<?php echo($wlyh->Wil);?>"><?php echo trim($wlyh->Kota) ?></span>

						</div>
					<?php }	?>
				</div>
			</div>
			<?php foreach ($wilayah as $wlyh) { ?>
				<!-- <input type="hidden" name="Kotas[]" value="<?php echo trim($wlyh->Kota) ?>"> -->
			<?php } ?>
		</div>
	</div>

	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnSubmit" name="btnSubmit">NEXT</div>
			</div>
			<div style='margin:10px;float:left;'>
				<a href="<?php echo(site_url('PlanPO'));?>"><div class="btn" id="btnExit" name="btnExit">EXIT</div></a>
			</div>
			<div style='margin:10px;float:left;color:white;font-size:12px;height:50px;line-height:50px;vertical-align:bottom;'>
				STEP 1 OF 2
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>


	<?php echo form_open(); ?>
	<?php echo form_close(); ?>