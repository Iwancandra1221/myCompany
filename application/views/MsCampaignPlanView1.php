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
	.campaign-plan-card {
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
	#regionInfo { background-color: yellow; }
</style>

<script>
	var mode = "<?php echo($mode);?>";
	var planHD = <?php echo(json_encode($planHD));?>;
	var wilayahInclude = <?php echo(json_encode($wilayahInclude));?>;

	var isDraft = <?php echo($isDraft);?>;
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

	var idx = 0; // untuk simpan nilai id unik terakhir supaya tidak ada yg double id jika ada row yg terhapus di baris tengah table
	var brs = 0;

	var Reset = function() {
		setDisable(false); // perlu remove tag disable untuk reset kembali semua value
	
		$("#txtKodeCampaign").val('');
		$("#txtNamaCampaign").val('');
		$("#StartCampaign").val("");
		$("#EndCampaign").val("");
		$("#txtJumlahHari").val("");
		$("#filterDivisiReal").val('');
		
		$("#statusDraft").html("");
		$("#status").text("");
		//$("#txtstatus").text("NEW");
		
		// $('#StartCampaign').datepicker("update", ''); // gunakan fungsi update untuk ubah value datepicker menjadi kosong secara utuh
		// $('#EndCampaign').datepicker("update", '');
		$("#filterDivisi").val('');
		//$("#txtIsEdit").val("");
		
		//$("#filterDivisi").val($("#filterDivisiReal").val());
		
		$("#btnSubmit").prop("disabled", true);
		$("#btnSubmit").attr("disabled", true);
		
		//$("#btnEmail").hide();
		
		RemoveAllBarang();
		//addRow();
		//loadWilayahExclude();
		$(".cboWilayah").attr("checked",false);

		$("#filterDivisiReal").attr("disabled", false);
		$("#filterDivisiReal").attr("prop", false);
		$("#txtKodeCampaign").attr("disabled", true);
		$("#txtKodeCampaign").attr("prop", true);
		$("#txtNamaCampaign").attr("disabled", false);
		$("#txtNamaCampaign").attr("prop", false);
		$("#StartCampaign").attr("disabled", false);
		$("#StartCampaign").attr("prop", false);
		$("#EndCampaign").attr("disabled", false);
		$("#EndCampaign").attr("prop", false);
		$("#txtJumlahHari").attr("disabled", false);
		$("#txtJumlahHari").attr("prop", false);

	}

	var RemoveBarang = function(i) {
		//Kalo Kode Barang Masih Kosong maka Langsung Remove 1 Row
		if ($("#filterBarang"+i).val()=="") {
			$("#kolumbrg" + i).remove();
			brs = brs - 1;
		} else if ($("#flagDraft_"+i).val()=="*") {
			$(".loading").show();
			var csrf_bit = $("input[name=csrf_bit]").val();
			$.post("<?php echo site_url('CampaignPlan/RemoveDraft'); ?>", {
				item_id			: i,
				kode_plan 		: $("#txtKodeCampaign").val(),
				kd_brg 			: $("#filterBarang"+i).val(),
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
		} else {
			if (confirm("Remove Barang "+$("#filterBarang"+i).val()+" ?")) {
				$(".loading").show();
				var csrf_bit = $("input[name=csrf_bit]").val();
				$.post("<?php echo site_url('CampaignPlan/RemoveItem'); ?>", {
					item_id			: i,
					kode_plan 		: $("#txtKodeCampaign").val(),
					kd_brg 			: $("#filterBarang"+i).val(),
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
	
	var setDisable = function(b){
		$('.isdisabled').each(function( i ) {
			$(this).prop("disabled", b);
			$(this).attr("disabled", b);
		 });
	}

	activateDatepicker = function() {
		$('.datepicker').datetimepicker('destroy');
		$('.datepicker').datepicker({
			altFormat: "dd-M-yy",
			format: "dd-M-yyyy",
			dateFormat: "dd-M-yy",
			autoclose: true,
			changeMonth: true,
			beforeShowDay: $.datepicker.noWeekends
		});
	}	

	
	isiTanggal = function(i) {
		var StartCampaignDT = $('#DetailStartCampaign_'+i).val();
		var EndCampaignDT = $('#DetailEndCampaign_'+i).val();

		var StartCampaignHD = $('#StartCampaign').val();
		var StartDate = new Date(StartCampaignHD);
		//var StartDate = StartCampaignHD;
		var EndCampaignHD = $('#EndCampaign').val();
		var EndDate = new Date(EndCampaignHD);

		//alert(-1*StartDate.getFullYear());

		if (StartCampaignDT=="") {
			$('#DetailStartCampaign_' + i).val($('#StartCampaign').val());
			$('#DetailStartCampaign_' + i).datepicker({
			    minDate: new Date(-1*StartDate.getFullYear(), StartDate.getMonth(), StartDate.getDate()),
			    maxDate: new Date(-1*EndDate.getFullYear(), EndDate.getMonth(), EndDate.getDate()),
			    setDate: new Date(-1*StartDate.getFullYear(), StartDate.getMonth(), StartDate.getDate())
			});
			// $('#DetailStartCampaign_' + i).datepicker("setStartDate", StartCampaignDt);
			// $('#DetailStartCampaign_' + i).datepicker("setEndDate", EndCampaignDt);
			// $('#DetailStartCampaign_' + i).datepicker("update", $('#StartCampaign').val());
		} else {
			$('#DetailStartCampaign_' + i).datepicker({
			    minDate: new Date(-1*StartDate.getFullYear(), StartDate.getMonth(), StartDate.getDate()),
			    maxDate: new Date(-1*EndDate.getFullYear(), EndDate.getMonth(), EndDate.getDate())
			});
		}

		if (EndCampaignDT!="") {
			$('#DetailEndCampaign_' + i).val($('#EndCampaign').val());
			$('#DetailEndCampaign_' + i).datepicker({
			    minDate: new Date(-1*StartDate.getFullYear(), StartDate.getMonth(), StartDate.getDate()),
			    maxDate: new Date(-1*EndDate.getFullYear(), EndDate.getMonth(), EndDate.getDate()),
			    setDate: new Date(-1*EndDate.getFullYear(), EndDate.getMonth(), EndDate.getDate())
			});
			// $('#DetailEndCampaign_' + i).datepicker("setStartDate", StartCampaignDt);
			// $('#DetailEndCampaign_' + i).datepicker("setEndDate", EndCampaignDt);
			// $('#DetailEndCampaign_' + i).datepicker("update", $('#EndCampaign').val());
			//alert("Update Detail End Campaign Done");
		} else {
			$('#DetailEndCampaign_' + i).datepicker({
			    minDate: new Date(-1*StartDate.getFullYear(), StartDate.getMonth(), StartDate.getDate()),
			    maxDate: new Date(-1*EndDate.getFullYear(), EndDate.getMonth(), EndDate.getDate())
			});
		}

		if ($("#JumlahHariCampaign_"+i).val=="") {
			if ($("#txtJumlahHari").val()!="") {
				//$('#JumlahHariCampaign_'+i).val($('#txtJumlahHari').val());
				$('#JumlahHariCampaign_'+i).attr("max", $('#txtJumlahHari').val());
				$('#JumlahHariCampaign_'+i).prop("max", $('#txtJumlahHari').val());
				//alert("Update Detail Jumlah Hari Done");
			}
		}
	}

	var addRow = function(newRow=true, indeks=0, kdbrg="", start="", end="", jmlhari=1, draft=false, status="", disabled=false) {
		//alert("adding Row");
		if (newRow==true) {
			idx += 1;
		} else {
			idx = indeks;
		}

		brs += 1;
		var i = idx;


		var tr = "<tr class='rowbrg'  id='kolumbrg" + i + "'>";
		var td = "<td>" +
			"<input type='button' onclick='RemoveBarang(\"" + i + "\")' id='btnRemoveDt" + i + "' class='isdisabled btnRemoveDt" + i + "' style='width:21px;padding:0px !important;background-color:red;'  value='-'>" +
			"</td>";
		var td0 = "<td class='dt-cell'>" +
			"<input type='text' class='filterText filterBarang' name='filterBarang[]' id='filterBarang" + i + "' idx='"+i+"' brs='"+brs+"' " + 
			"onfocus='SetAutoComplete("+i+")' onblur='productIdBlur("+i+", 1)' value='"+kdbrg+"' required>" +
			"<input type='text' id='flagDraft_"+i+"' class='draft hideMe' value='"+((draft==true)?"*":"")+"'>" +
			"</td>";
			
		var td1 = "<td class='dt-cell'>" +
			"<input type='text' style='width:150px;' class='isdisabled datepicker datepickerInput DetailStartCampaign' idx='"+i+"' "+
			"name='DetailStartCampaign[]' id='DetailStartCampaign_" + i + "' value='"+start+"' readonly required>" +
			"</td>"; //onblur='hitungJumlahHariDT("+i+")' 
		var td2 = "<td class='dt-cell'>" +
			"<input type='text' style='width:150px;' class='isdisabled datepicker datepickerInput DetailEndCampaign' idx='"+i+"' " +
			"name='DetailEndCampaign[]' id='DetailEndCampaign_" + i + "' value='"+end+"' readonly required>" +
			"</td>"; //onblur='hitungJumlahHariDT("+i+")' 
		var td3 = "<td class='dt-cell'>" +
			"<input type='number' style='width:80px;' class='isdisabled JumlahHariCampaign' idx='"+i+"' onblur='SaveDraft("+i+", 4)' " + //onfocus='hitungJumlahHariDT("+i+")' " + 
			"name='JumlahHariCampaign[]' id='JumlahHariCampaign_" + i + "' min='1' value='"+jmlhari+"' required>" +
			"</td>";
		status = "<img class='product-icon' id='product-loading-"+i+"' src='<?php echo base_url("images/loading.gif") ?>' height='30px' width='80px'>";
		status += "<img class='product-icon' id='product-success-"+i+"' src='<?php echo base_url("images/success2.png") ?>' height='30px' width='30px'>";
		var td4 = "<td class='dt-cell' id='StatusSave_"+i+"'>"+status+"</td></tr>";

		$("#tbodyBarangCampaign").append(tr + td + td0 + td1 + td2 + td3 + td4);


		if (newRow) {
			isiTanggal(i);
			//alert("isi tanggal Done");
		} else {

			var StartCampaignHD = $('#StartCampaign').val();
			var StartDate = new Date(StartCampaignHD);
			var EndCampaignHD = $('#EndCampaign').val();
			var EndDate = new Date(EndCampaignHD);

			$('#DetailStartCampaign_' + i).datepicker({
			    minDate: new Date(-1*StartDate.getFullYear(), StartDate.getMonth(), StartDate.getDate()),
			    maxDate: new Date(-1*EndDate.getFullYear(), EndDate.getMonth(), EndDate.getDate())
			});			
			$('#DetailEndCampaign_' + i).datepicker({
			    minDate: new Date(-1*StartDate.getFullYear(), StartDate.getMonth(), StartDate.getDate()),
			    maxDate: new Date(-1*EndDate.getFullYear(), EndDate.getMonth(), EndDate.getDate())
			});			

			if (draft==true) {
				$("#flagDraft_"+i).removeClass("hideMe");
			}
		}

		// if (newRow) {
		// 	alert("Adding Row..");
		// }

		if (disabled==true) {
			$("#filterBarang"+i).attr("readonly",true);
			$("#filterBarang"+i).prop("readonly",true);
		}
		activateDatepicker();
	}

	function SetAutoComplete(i) {
		$("#DetailStartCampaign_"+i).val($("#StartCampaign").val());
		$("#DetailEndCampaign_"+i).val($("#EndCampaign").val());
		$("#JumlahHariCampaign_"+i).val($("#txtJumlahHari").val());
		
		var div = $("#filterDivisiReal").val();

		if (div=="MIYAKO") {
	    	$("#filterBarang"+i).autocomplete({
      			source: BrgMiyako
			});			
		} else if (div=="MICOOK") {
	    	$("#filterBarang"+i).autocomplete({
      			source: BrgMicook
			});
		} else if (div=="RINNAI") {
	    	$("#filterBarang"+i).autocomplete({
      			source: BrgRinnai
			});
		} else if (div=="SHIMIZU") {
	    	$("#filterBarang"+i).autocomplete({
      			source: BrgShimizu
			});
		} else if (div=="CO&SANITARY") {
	    	$("#filterBarang"+i).autocomplete({
      			source: BrgCosanitary
			});
	    }
	}

	function GenerateAutonumber(div="") {
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('CampaignPlan/GenerateKodePlan'); ?>", {
			divisi 		: Divisi,
			csrf_bit	: csrf_bit
		}, function(data){
			return data;
		},'json',errorAjax);		
	}

	function productIdBlur(i, inputType=0) {

		var Divisi = $("#filterDivisiReal").val().toUpperCase();
		var KdBrg = $("#filterBarang"+i).val().toUpperCase();
		
		var IdxBrg = 0;
		var KodeBrg="";
		var Baris = 0;

		$("#filterDivisiReal").val(Divisi);
		$("#filterDivisi").val(Divisi);
		$("#filterBarang"+i).val(KdBrg);

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
							$("#filterBarang"+i).val("");
							return false;
						}						
					}
				});
			}

			if (ProductFound) {
				SaveDraft(i, inputType);
			} else {
				$("#filterBarang"+i).focus();
				alert("Kode Barang Tidak Terdaftar/Salah Divisi!");
			}
		}
	}


	function SaveDraft(i, inputType=0) {
		//alert("Save Draft");

		var Divisi = $("#filterDivisiReal").val().toUpperCase();
		var Kode = $("#txtKodeCampaign").val().toUpperCase();
		if (Kode=="") {
			Kode="AUTONUMBER";
		}
		var Nama = $("#txtNamaCampaign").val().toUpperCase();
		var StartHD = $("#StartCampaign").val();
		var EndHD = $("#EndCampaign").val();
		var JumlahHariHD = $("#txtJumlahHari").val();

		var KdBrg = $("#filterBarang"+i).val().toUpperCase();
		var StartCampaign=$("#DetailStartCampaign_"+i).val();
		var EndCampaign=$("#DetailEndCampaign_"+i).val();
		var JumlahHariCampaign=$("#JumlahHariCampaign_"+i).val();

		//alert("Saving..");
		if (Nama!="" && StartHD!="" && EndHD!="" && JumlahHariHD!="") {
			//alert("Headers are valid");

			if (KdBrg!="" && StartCampaign!="" && EndCampaign!="" && JumlahHariCampaign!="") {
				//alert("Details are Complete");
				// if (inputType==1) {
					$("#product-loading-"+i).show();
					var csrf_bit = $("input[name=csrf_bit]").val();
					$.post("<?php echo site_url('CampaignPlan/SaveDraft'); ?>", {
						item_id			: i,
						kode_plan 		: Kode,
						nama_plan 		: Nama,
						start_hd		: StartHD,
						end_hd			: EndHD,
						jumlah_hari_hd	: JumlahHariHD,
						divisi 			: Divisi,
						kd_brg 			: KdBrg,
						start_campaign	: StartCampaign,
						end_campaign	: EndCampaign,
						jumlah_hari		: JumlahHariCampaign,
						csrf_bit		: csrf_bit
					}, function(data){
						//alert(data.result + " : " + data.campaignId);
						if (data.result=="SUCCESS") {

							$("#btnSubmit").attr("disabled", false);
							$("#btnSubmit").prop("disabled", false);

							if (Kode=="AUTONUMBER" || Kode=="") {
								$("#txtKodeCampaign").attr("disabled", false);
								$("#txtKodeCampaign").prop("disabled", false);
								$("#txtKodeCampaign").val(data.campaignId);
								//$("#statusDraft").html("DRAFT");
								disableHeaders();
							}
							$("#flagDraft_"+i).val("*");
							$("#flagDraft_"+i).removeClass("hideMe");
							//$("#StatusSave_"+i).html("<font color='green'> saved</font>");
							var emptyProductFound = false;
							$(".filterBarang").each(function(){
								if ($(this).val()=="") {
									emptyProductFound = true;
								}
							})
							if (emptyProductFound==false) {
								addRow();
							} 
							$("#product-loading-"+i).hide();
							$("#product-success-"+i).show();

							//Pindah ke Baris Selanjutnya
							i = i+1;
							$("#filterBarang"+i).focus();
						} else {
							//$("#StatusSave_"+i).html("<font color='red'>save failed: "+data.errMsg+"</font>");	
						}

					},'json',errorAjax);		
					// $(".loading").hide();
				// } else {
				// 	//do nothing
				// }
			} else {
				// alert("KdBrg:"+KdBrg+"\r\n"+
				// 	  "StartHD:"+StartCampaign+"\r\n"+
				// 	  "EndHD:"+EndCampaign+"\r\n"+
				// 	  "JmlHari:"+JumlahHariCampaign);

				//alert("Something is Wrong ("+inputType+")");
			}
		}
	}

	function disableHeaders()
	{
		// $("#filterDivisiReal").attr("disabled", true);
		// $("#filterDivisiReal").attr("prop", true);
		// $("#txtKodeCampaign").attr("disabled", true);
		// $("#txtKodeCampaign").attr("prop", true);
		// if ($("#txtNamaCampaign").val()!="") {
		// 	$("#txtNamaCampaign").attr("disabled", true);
		// 	$("#txtNamaCampaign").attr("prop", true);
		// }
		// $("#StartCampaign").attr("disabled", true);
		// $("#StartCampaign").attr("prop", true);
		// $("#EndCampaign").attr("disabled", true);
		// $("#EndCampaign").attr("prop", true);
		// $("#txtJumlahHari").attr("disabled", true);
		// $("#txtJumlahHari").attr("prop", true);
	}

	function checkEmpty(obj) {
		var name = $(obj).attr("name");
		// $("." + name + "-validation").html("");
		$(obj).css("border", "");
		if ($(obj).val() == "") {
			$(obj).css("border-color", "#FF0000");
			// $("." + name + "-validation").html("Required");
			return false;
		}

		return true;
	}

	function hitungJumlahHariDT(i){
		var StartCampaignDT = $('#DetailStartCampaign_'+i).val();
		var EndCampaignDT = $('#DetailEndCampaign_'+i).val();
		
		if (StartCampaignDT!="" && EndCampaignDT!="") {
			var StartDT = new Date(StartCampaignDT);
			var EndDT = new Date(EndCampaignDT);

			var StartCampaignHD = $('#StartCampaign').val();
			var StartDate = new Date(StartCampaignHD);
			var EndCampaignHD = $('#EndCampaign').val();
			var EndDate = new Date(EndCampaignHD);
			var JumlahHariHD = $("#txtJumlahHari").val();

			var JumlahHariDT = $("#JumlahHariCampaign_"+i).val();
			var JumlahHari = 0;

			if (StartDT<StartDate || StartDate>EndDate) {
				//alert("Tanggal Mulai Di luar Periode Rencana Campaign");
				return false;
			} else if (EndDate<StartDate || EndDT>EndDate) {
				//alert("Tanggal Akhir Di Luar Periode Rencana Campaign");
				return false;
			} else {
				//alert(StartDT);
				if (StartDT==StartDate && EndDT==EndDate) {
					$('#JumlahHariCampaign_'+i).val(JumlahHariHD);
				} else {
					if(Date.parse(EndCampaignDT) && Date.parse(StartCampaignDT)){
						//alert("HitungJumlahHari");
						var days = (Date.parse(EndCampaignDT)-Date.parse(StartCampaignDT)) / (1000 * 60 * 60 * 24) + 1;
						
						if (JumlahHariDT=="") {
							//alert("#1: "+days);
							$("#JumlahHariCampaign_"+i).val(days);
						} else if (JumlahHariDT > days) {
							//alert("#2: "+days);
							$("#JumlahHariCampaign_"+i).val(days);
						} else {
							//alert("#3: "+JumlahHariDT);
							$("#JumlahHariCampaign_"+i).val(JumlahHariDT);
						}
					} else {
						//$('#JumlahHariCampaign_'+i).val("");
					}
				}

				SaveDraft(i,0);
				return true;

			}
		} else{
			//alert("#3");
			return false;
		}
	}

	function StartCampaignLostFocus() {

		var StartCampaignHD = $('#StartCampaign').val();
		var StartDate = new Date(StartCampaignHD);

		$('#EndCampaign').datepicker({
		    minDate: new Date(-1*StartDate.getFullYear(), StartDate.getMonth(), StartDate.getDate())
		});

		hitungJumlahHari();
	}

	function EndCampaignLostFocus() {

		var EndCampaignHD = $('#EndCampaign').val();
		var EndDate = new Date(EndCampaignHD);

		$('#StartCampaign').datepicker({
		    maxDate: new Date(-1*EndDate.getFullYear(), EndDate.getMonth(), EndDate.getDate())
		});

		hitungJumlahHari();
	}

	function hitungJumlahHari(){
		var JumlahHari = $("#txtJumlahHari").val();
		
		if (JumlahHari=="" || JumlahHari=="0") {
			var EndCampaignDt = $('#EndCampaign').datepicker('getDate');
			var StartCampaignDt = $('#StartCampaign').datepicker('getDate');

			//alert(StartCampaignDt+" - "+EndCampaignDt);
			if(Date.parse(EndCampaignDt) && Date.parse(StartCampaignDt)){
				//alert("HitungJumlahHari");
				var days = (Date.parse(EndCampaignDt)-Date.parse(StartCampaignDt)) / (1000 * 60 * 60 * 24) + 1;
				$('#txtJumlahHari').val(days);
				$('#txtJumlahHari').attr("max", days);
				$('#txtJumlahHari').prop("max", days);
				$('.JumlahHariCampaign').val(days);
				$('.JumlahHariCampaign').attr("max", days);
				$('.JumlahHariCampaign').prop("max", days);
			} else {
				$('#txtJumlahHari').val('');
				$('#txtJumlahHari').attr("max", 0);
				$('#txtJumlahHari').prop("max", 0);
				$('.JumlahHariCampaign').val('');
				$('.JumlahHariCampaign').attr("max", 0);
				$('.JumlahHariCampaign').prop("max", 0);
			}
		}

		validateHeader();
	}

	function validate(val = "") {
		//alert("validating..");
		$("#btnSubmit").attr("disabled", true);
		$("#btnSubmit").prop("disabled", true);
		$("#btnNew").attr("disabled", true);
		$("#btnNew").prop("disabled", true);

		//alert("Validating..");
		var valid = validateHeader();
		// if (valid) {
		// 	alert("Header Terisi");
		// } else {
		// 	alert("Header Tidak Lengkap");
		// }

		var wilayahChecked = false;
		$('.cboWilayah').each(function() {
			if ($(this).prop("checked")==true) {
				wilayahChecked = true;
			}
		});
		// if (wilayahChecked) {
		// 	alert("Wilayah Valid");
		// } else {
		// 	alert("Wilayah Tidak Valid");
		// }
		valid = valid && wilayahChecked;

		if (valid) {
			// alert("Valid");
			if (CheckEmptyProductExists()==false) {
				//alert("Tidak Ada Empty Produk");
				//addRow();
				addRow();
			} else {
				//alert("Ada Empty Product");
			}

			$("#btnSubmit").attr("disabled", false);
			$("#btnSubmit").prop("disabled", false);		
			$("#btnNew").attr("disabled", false);
			$("#btnNew").prop("disabled", false);		
		} else {
			// alert("Tidak Valid");
		}
		return valid;
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

	function PlanNameBlur() {
		var idxProduct = 0;
		var PlanName = $("#txtNamaCampaign").val();
		//alert(PlanName);
		var PlanID = $("#txtKodeCampaign").val();

		if (PlanName!="") {
			$(".filterBarang").each(function(){
				idxProduct = $(this).attr("idx");
				//alert($(this).val());
				SaveDraft(idxProduct, 1);
			})

			if (PlanID!="" && PlanID!="AUTONUMBER") {
				$("#txtNamaCampaign").attr("disabled", true);
				$("#txtNamaCampaign").attr("prop", true);
			}
		}

		validate();
	}

	function validateCheckbox(KdLokasi="", Wilayah="", Wilayah2="") {

		var isChecked = $("#Kota_"+Wilayah2).prop("checked");

		validate();
		SaveDraftWilayah(KdLokasi, Wilayah, Wilayah2, isChecked);
	}

	function validateHeader(){
		var valid = true;
		valid = checkEmpty($("#txtNamaCampaign"));
		valid = valid && checkEmpty($("#StartCampaign"));
		valid = valid && checkEmpty($("#EndCampaign"));
		valid = valid && checkEmpty($("#txtJumlahHari"));
		valid = valid && checkEmpty($("#filterDivisiReal"));

		// alert("Nama Plan: "+$("#txtNamaCampaign").val()+"\r\n"+
		// 	  "Start : "+$("#StartCampaign").val()+"\r\n"+
		// 	  "End : "+$("#EndCampaign").val()+"\r\n"+
		// 	  "JumlahHari: "+$("#txtJumlahHari").val()+"\r\n"+
		// 	  "Divisi: "+$("#filterDivisiReal").val());

		if (valid) {
			//alert("VALID");
			if (CheckEmptyProductExists()==false) {
				//alert("Check Empty Product Row Done");
				addRow();
				// alert("Adding Row Done");
			}
			// alert("Disabling Headers");
			disableHeaders();
		} else {
			//alert("TIDAK VALID");
		}

		// alert("Validating Header Done");
		return valid;
	}

	function SaveDraftWilayah(KdLokasi, Wilayah, Wil, IsChecked) {
		var KodePlan = $("#txtKodeCampaign").val();
		if (KodePlan=="") {
			alert("tombol SELECT ALL dan DESELECT ALL, baru bisa digunakan setelah Ada Kode Campaign Plan");
			$(".cboWilayah").prop("checked", false);
			$(".cboWilayah").attr("checked", false);
		} else {

			$(".loading").show();
			var csrf_bit = $("input[name=csrf_bit]").val();
			$.post("<?php echo site_url('CampaignPlan/SaveDraftWilayah'); ?>", {
				kode_plan 		: KodePlan,
				wilayah 		: Wilayah,
				kode_lokasi		: KdLokasi,
				is_checked		: ((IsChecked)?1:0),
				csrf_bit		: csrf_bit
			}, function(data){
				if (data.result=="SUCCESS") {
					if (IsChecked) {
						$("#StatusKota_"+Wil).html("<b>"+Wilayah+"</b>");
					} else {
						$("#StatusKota_"+Wil).html(""+Wilayah+"");
					}
				} else {
					$("#StatusKota_"+Wil).html(Wilayah+" <font color='red'>&nbsp;&nbsp;failed</font>");	
				}
				HitungWilayahSelected();
				$(".loading").hide();
			},'json',errorAjax);		
		}
	}

	function toggle(selectAll=true) 
	{
		var KodePlan = $("#txtKodeCampaign").val();
		if (KodePlan=="") {
			alert("tombol SELECT ALL dan DESELECT ALL, baru bisa digunakan setelah Ada Kode Campaign Plan");
			$(".cboWilayah").prop("checked", false);
			$(".cboWilayah").attr("checked", false);
		} else {
			$(".loading").show();
			var csrf_bit = $("input[name=csrf_bit]").val();
			$.post("<?php echo site_url('CampaignPlan/SaveDraftWilayah'); ?>", {
				kode_plan 		: KodePlan,
				wilayah 		: "ALL",
				kode_lokasi		: "ALL",
				is_checked		: ((selectAll)?1:0),
				csrf_bit		: csrf_bit
			}, function(data){
				// alert(data.result + " : " + data.campaignId);
				if (data.result=="SUCCESS") {

					$(".cboWilayah").prop("checked", selectAll);
					$(".cboWilayah").attr("checked", selectAll);
					$(".loading").hide();			
					HitungWilayahSelected();
				}
			},'json',errorAjax);
		}		
	}

	function HitungWilayahSelected()
	{
		var totalChecked = 0;
		$(".cboWilayah").each(function(){
			if ($(this).prop("checked")==true) {
				totalChecked += 1;
			}
		});
		$("#regionInfo").html("<b>"+totalChecked+"</b> Wilayah Dipilih");
		// alert("Total "+totalChecked+" Wilayah");
	}

	$(document).ready(function() {	
		$("#txtKodeCampaign").attr("disabled", true);
		$("#txtKodeCampaign").prop("disabled", true);

		if (mode=="edit") {
			//alert(planHD[0].Division);
			$("#filterDivisiReal").val(planHD[0].Division);
			$("#txtKodeCampaign").val(planHD[0].CampaignID);
			$("#txtNamaCampaign").val(planHD[0].CampaignName);
			$("#StartCampaign").val(planHD[0].CampaignStartHD);
			$("#EndCampaign").val(planHD[0].CampaignEndHD);
			$("#txtJumlahHari").val(planHD[0].JumlahHariHD);

			idx = 0;
			var maxIndeks = 0;
			for(var p in planHD)
			{
				idx = planHD[p].ItemID;

				if (idx>maxIndeks) {
					maxIndeks = idx;
				}

				addRow(false, idx, planHD[p].ProductID, planHD[p].CampaignStart, planHD[p].CampaignEnd, planHD[p].JumlahHari, 
					((planHD[p].IsDraft==1)?true:false), "", ((planHD[p].IsDraft==1)?false:true));
			}

			idx = maxIndeks;

			for(var w in wilayahInclude)
			{
				$("#Kota_"+wilayahInclude[w].Wil).attr("checked",true);
				$("#Kota_"+wilayahInclude[w].Wil).prop("checked",true);

				if (wilayahInclude[w].DraftAdd==1) {
					$("#StatusKota_"+wilayahInclude[w].Wil).html("<b>"+wilayahInclude[w].Wilayah+"</b>");
				}
			}

			validate();

			$("#btnEmail").hide();
			HitungWilayahSelected();
		} else {
			$("#filterDivisi").val($("#filterDivisiReal").val());
			$("#btnSubmit").prop("disabled", true);
			$("#btnSubmit").attr("disabled", true);
			$("#btnEmail").hide();
		}
		$('.datepicker').datetimepicker('destroy');
		$('.datepicker').datepicker({
			altFormat: "dd-M-yy",
			format: "dd-M-yy",
			dateFormat: "dd-M-yy",
			autoclose: true,
			changeMonth: true
		});
		$(".product-icon").hide();

		$("#filterDivisiReal").change(function() {
			var divBefore = $("#filterDivisi").val();
			var div = $(this).val();
			if (div=="") {
				//alert("Divisi Yang Anda Pilih Salah");
				$("#filterDivisiReal").val(divBefore);
			} else {
				$("#filterDivisi").val(div);
				RemoveAllBarang();
				if (validateHeader()) {
					if (CheckEmptyProductExists()==false) {
						addRow();						
					}
				}
			}
		});
		
		$("#txtJumlahHari").change(function() {
			var nw = $('#txtJumlahHari').val();
			$('.JumlahHariCampaign').each(function(i, obj) {
				var old  = this.id.split('_');
				
				var EndCampaignDt = $('#DetailEndCampaign_'+old[1]).datepicker('getDate');
				var StartCampaignDt = $('#DetailStartCampaign_'+old[1]).datepicker('getDate');
				if(Date.parse(StartCampaignDt) && Date.parse(EndCampaignDt)){
					var days = (EndCampaignDt- StartCampaignDt) / (1000 * 60 * 60 * 24) + 1;
					if(days > nw){
						$('#'+this.id).val(nw);
					}
				}
				else{
					$('#'+this.id).val(nw);
				}
			});
		});
		
		// $('.DetailStartCampaign').datepicker.change(function() {
		// 	alert('Datepicker Detail Start Campaign Change');
		// });

		$(".DetailStartCampaign").change(function(){
			var SelectedIdx = $(this).attr("idx");
			var CountJumlahHari = hitungJumlahHariDT(SelectedIdx);
		});

		$(".DetailEndCampaign").change(function(){
			var SelectedIdx = $(this).attr("idx");
			var CountJumlahHari = hitungJumlahHariDT(SelectedIdx);
		});


		$("#btnNew").click(function() {
			if (idx>0) {
				if (confirm("Batalkan Input Yang Ada?")) {
					
					$(".loading").show();
					var csrf_bit = $("input[name=csrf_bit]").val();
					$.post("<?php echo site_url('CampaignPlan/RemoveDrafts'); ?>", {
						kode_plan 		: $("#txtKodeCampaign").val(),
						csrf_bit		: csrf_bit
					}, function(data){
						if (data.result=="SUCCESS") {
							alert("Draft Berhasil Dihapus");	
							Reset();	
						} else {

						}

					},'json',errorAjax);		
					$(".loading").hide();

				}
			}
		});

		$("#btnSubmit").click(function() {
			if($('input.cboWilayah:checked').length==0){
				alert('Pilih minimal 1 wilayah!');
				return false;
			}

			$("#filterDivisiReal").removeAttr("disabled");
			$("#txtKodeCampaign").removeAttr("disabled");
			$("#txtNamaCampaign").removeAttr("disabled");
			$("#CampaignStart").removeAttr("disabled");
			$("#CampaignEnd").removeAttr("disabled");
			$("#txtJumlahHari").removeAttr("disabled");

			var isDisabled = $("#btnSubmit").prop('disabled');
			if (!isDisabled) {
				$(".filterBarang").each(function(){
					if ($(this).val()=="") {
						$(this).parent().parent().remove();
					}
				})
			
				if($("#txtIsEdit").val()=='edit'){
					$("#FormCampaignPlan").attr("action", "CampaignPlan/EditPersiapanCampaign");
				}
				$("#checkBoxContainer").find("input").each(function(index, item) {
					$(item).removeAttr('disabled');
				});

				$("#FormCampaignPlan").submit();
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

	<div class="title">PERENCANAAN CAMPAIGN</div>
	<?php echo form_open('CampaignPlan/addStep2', array("id" => "FormCampaignPlan")); ?>
	<div class="form">
		<div class="campaign-plan-card">
			<div class="row">
				<div class="col-3 col-m-4 header-row-label">Kode Rencana Campaign</div>
				<div class="col-5 col-m-4 header-row">
					<input type="text" class="form-control" name="txtKodeCampaign" id="txtKodeCampaign" placeholder="AutoNumber">
				</div>
				<div class="col-2 col-m-2" style="padding-top:3px!important;">
					<div class="btn" id="btnNew" name="btnNew" style="height:35px!important;padding-top:8px;">RESET</div>
				</div>
				<div class="col-2 col-m-2">
					<!-- <span id="statusDraft" class="draft"></span> -->
					<span id="statusDraft"></span>
				</div>
			</div>
			<div class="row">
				<div class="col-3 col-m-4 header-row-label">Nama Rencana Campaign</div>
				<div class="col-9 col-m-8 header-row">
					<input type="text" class="form-control isdisabled" name="txtNamaCampaign" id="txtNamaCampaign" placeholder="Nama Campaign" onblur="PlanNameBlur()" autocomplete="off" required>
					<input type="hidden" class="form-control" name="txtIsEdit" id="txtIsEdit" placeholder="txtIsEdit">
				</div>
			</div>
			<div class="row">
				<div class="col-3 col-m-4 header-row-label">Periode Campaign</div>
				<div class="col-9 col-m-8 header-row">
					<?php
					$attr = array(
						'class' => 'datepicker datepickerInput isdisabled',
						'id' => 'StartCampaign',
						'style' => 'width:150px;',
						'readonly' => true,
						'required' => 'required',
						'onblur' => 'StartCampaignLostFocus()'
					);
					echo BuildInput('text', 'CampaignStart', $attr);
					echo "  -  ";
					$attr = array(
						'class' => 'datepicker datepickerInput isdisabled',
						'id' => 'EndCampaign',
						'style' => 'width:150px;',
						'readonly' => true,
						'required' => 'required',
						'onblur' => 'EndCampaignLostFocus()'
					);
					echo BuildInput('text', 'CampaignEnd', $attr);
					?>
					Jumlah Hari
					<input type="number" class="isdisabled" name="txtJumlahHari" id="txtJumlahHari" style="width:100px" onblur="validate()" onfocus="hitungJumlahHari()" min="1" autocomplete="off">
					<br>
					<font color="blue">*** Barang yang berbeda Periode harap diubah Periodenya di baris Tabel</font>
				</div>
			</div>
			<div class="row">
				<div class="col-3 col-m-4 header-row-label">Divisi</div>
				<div class="col-9 col-m-8 header-row">
					<select id="filterDivisiReal" name="filterDivisiReal" class="form-control filterDropdownReal isdisabled" required>
						<option value=''>--PILIH DIVISI--</option>
						<?php
						for ($i = 0; $i < count($divisions); $i++) {
							echo ("<option value='" . $divisions[$i]["DIVISI"] . "'>" . $divisions[$i]["DIVISI"] . "</option>");
						} ?>
					</select>
					<input type="hidden" id="filterDivisi" name="filterDivisi" class="form-control" />
				</div>
			</div>
		</div>
		<div id="divProducts">
			<table id="table-primary" class="table table-striped table-bordered" cellspacing="0">
				<thead id="theadBarangCampaign">
					<tr>
						<th scope="col" width="5%" rowspan="2" align="left"></th>
						<th scope="col" width="40%" style="border:1px solid #ccc;">Kode Barang</th>
						<th scope="col" width="15%" style="border:1px solid #ccc;">StartCampaign</th>
						<th scope="col" width="15%" style="border:1px solid #ccc;">EndCampaign</th>
						<th scope="col" width="10%" style="border:1px solid #ccc;">Jumlah Hari</th>
						<th scope="col" width="15%" style="border:1px solid #ccc;"></th>
					</tr>
				</thead>
				<tbody id="tbodyBarangCampaign">
				</tbody>
			</table>
		</div>
		<div id="divRegions">
			<div class="row">
				<div class="col-4 col-m-4">
					<b style="font-size:14px;">WILAYAH INCLUDE</b>
				</div>
				<div class="col-8 col-m-8" align="right">
					<span id="regionInfo"></span>
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
					?>
						<div class="col-lg-3">
							<input
							type='checkbox'
							class="hidden kdlokasi-<?php echo trim(str_replace(' ', '', $wlyh->Kota)) ?>"
							id="KdLokasi<?php echo($x);?>" 
							name="KdLokasi[]" value="<?php echo  trim($wlyh->Kd_Lokasi)  ?>">
							
							<input
							type='checkbox'
							class="cboWilayah isdisabled kota-<?php echo trim(str_replace(' ', '', trim($wlyh->Kota))) ?>"
							id="Kota_<?php echo($wlyh->Wil);?>" 
							name="Kota[]" onclick="validateCheckbox('<?php echo($wlyh->Kd_Lokasi);?>', '<?php echo(trim($wlyh->Kota));?>', '<?php echo(trim($wlyh->Wil));?>')"
							value="<?php echo trim($wlyh->Kota)  ?>">							
							<span id="StatusKota_<?php echo($wlyh->Wil);?>"><?php echo trim($wlyh->Kota) ?></span>

						</div>
					<?php }	?>
				</div>
			</div>
			<?php foreach ($wilayah as $wlyh) { ?>
				<input type="hidden" name="KdLokasis[]" value="<?php echo trim($wlyh->Kd_Lokasi) ?>">
				<input type="hidden" name="Kotas[]" value="<?php echo trim($wlyh->Kota) ?>">
			<?php } ?>
		</div>
	</div>

	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnSubmit" name="btnSubmit">NEXT</div>
			</div>
			<div style='margin:10px;float:left;'>
				<a href="<?php echo(site_url('CampaignPlan'));?>"><div class="btn" id="btnExit" name="btnExit">EXIT</div></a>
			</div>
			<div style='margin:10px;float:left;color:white;font-size:12px;height:50px;line-height:50px;vertical-align:bottom;'>
				STEP 1 OF 2
			</div>
			<!-- <div style='margin:10px;float:left;color:white;font-size:12px;'>
				<div id="status"></div>
			</div>
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnEmail" name="btnEmail">Email Ulang</div>
			</div> -->
		</div>
	</div>
	<?php echo form_close(); ?>