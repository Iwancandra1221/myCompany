<style>
	.filterDropdown {
		width: 100%;
		background-color: #ffffcc;
	}

	.filterText {
		width: 75%;
		background-color: #ffffcc;
	}

	.title {
		font-size: 15pt;
		font-weight: bold;
		text-align: center;
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
	var idx = 1; // untuk simpan nilai id unik terakhir supaya tidak ada yg double id jika ada row yg terhapus di baris tengah table
	
	var BarangTerpilih = function(kdbrg, i) {
		if(checkKode(kdbrg)){
			alert('Kode barang sudah dipilih!');
		}
		else{
			$("#filterBarang" + i).val(kdbrg.trim());
			$('#barang_modal').modal('hide');
			validate();
		}
	}
		
	var Reset = function() {
		setDisable(false); // perlu remove tag disable untuk reset kembali semua value
	
		$("#txtKodeCampaign").val('');
		$("#txtNamaCampaign").val('');
		$("#txtNamaCampaign").attr("readonly", false);
		
		$("#FormBarang").attr("action", "PersiapanBarangCampaign/SimpanPersiapanCampaign");
		$("#status").text("");
		
		$("#txtstatus").text("NEW");
		
		$('#StartCampaign').datepicker("update", ''); // gunakan fungsi update untuk ubah value datepicker menjadi kosong secara utuh
		$('#EndCampaign').datepicker("update", '');
		$('#txtJumlahHari').val('');
		$("#filterDivisi").val('');
		$("#filterDivisiReal").val('');
		// $("#filterDivisiReal").attr("disabled", true);
		$("#txtIsEdit").val("");
		
		$("#filterDivisi").val($("#filterDivisiReal").val());
		
		$("#btnEdit").prop("disabled", true);
		$("#btnEdit").attr("disabled", true);
		
		$("#btnSubmit").prop("disabled", true);
		$("#btnSubmit").attr("disabled", true);
		
		$("#btnView").prop("disabled", true);
		$("#btnView").attr("disabled", true);
		
		
		$("#btnEmail").hide();
		
		
			
		RemoveAllBarang();
		addRow();
		loadWilayahExclude();
	}

	var TrxTerpilih = function(CampaignID, isApproved, CampaignName, Division, CampaignStart, CampaignEnd, JumlahHari, i, dt) {
		$("#txtKodeCampaign").val(CampaignID);
		$("#txtNamaCampaign").val(CampaignName);
		
		// $("#StartCampaign").val(CampaignStart);
		// $("#EndCampaign").val(CampaignEnd);
		
		$('#StartCampaign').datepicker("update", CampaignStart); // gunakan fungsi update untuk ubah value datepicker menjadi kosong secara utuh
		$('#EndCampaign').datepicker("update", CampaignEnd);
		
		let StartCampaignDt = $('#StartCampaign').datepicker('getDate');
		let EndCampaignDt = $('#EndCampaign').datepicker('getDate');

		let d = (EndCampaignDt- StartCampaignDt) / (1000 * 60 * 60 * 24) + 1;
		
		console.log(JumlahHari);
		
		$('#txtJumlahHari').val(JumlahHari);
		$('#txtJumlahHari').prop('max', d);
		$('#txtJumlahHari').attr('max', d);
		
		$("#filterDivisi").val(Division);
		$("#filterDivisiReal").val(Division);
		$("#filterDivisiReal").attr("disabled", true);
		
		$("#txtIsEdit").val("view");
		
		$("#btnView").prop("disabled", false);
		$("#btnView").attr("disabled", false);

		if (isApproved == 0) {
			$("#btnNew").attr("disabled", false);
			$("#btnNew").prop("disabled", false);
			$("#btnSubmit").prop("disabled", true);
			$("#btnSubmit").attr("disabled", true);
			$("#btnEdit").prop("disabled", false);
			$("#btnEdit").attr("disabled", false);
			$("#status").text("UNPROCESSED");
			$("#btnEmail").show();
			$("#FormBarang").attr("action", "PersiapanBarangCampaign/EditPersiapanCampaign");

		} else if (isApproved == 1) {
			$("#txtNamaCampaign").prop("readonly", "readonly");
			$("#btnNew").attr("disabled", false);
			$("#btnNew").prop("disabled", false);
			$("#btnSubmit").prop("disabled", true);
			$("#btnSubmit").attr("disabled", true);
			$("#btnEdit").prop("disabled", true);
			$("#btnEdit").attr("disabled", true);
			$("#status").text("APPROVED");
			$("#btnEmail").hide();
			// $("#FormBarang").attr("action", "PersiapanBarangCampaign/EditPersiapanCampaign");
		} else if (isApproved == 2) {
			$("#btnNew").attr("disabled", false);
			$("#btnNew").prop("disabled", false);
			$("#btnSubmit").prop("disabled", true);
			$("#btnSubmit").attr("disabled", true);
			// $("#btnEdit").prop("disabled", false);
			// $("#btnEdit").attr("disabled", false);
			$("#btnEdit").prop("disabled", true);
			$("#btnEdit").attr("disabled", true);
			$("#status").text("REJECTED");
			$("#btnEmail").hide();
			$("#FormBarang").attr("action", "PersiapanBarangCampaign/EditPersiapanCampaign");
		}
		
		if(dt==0){
			$("#btnView").prop("disabled", true);
			$("#btnView").attr("disabled", true);
			$("#btnEmail").hide();
			$("#status").text("BELUM ISI DETAIL CAMPAIGN");
		}
		
		LoadTrxDetail(CampaignID);
		
		// setDisable(true);
		$('#trx_modal').modal('hide');
	}

	var LoadTrxDetail = function(CampaignID) {
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('PersiapanBarangCampaign/GetTrxDetail'); ?>", {
			CampaignID: CampaignID,
			csrf_bit: csrf_bit
		}, function(data) {

			var detail = JSON.parse(data.detail)
			var wilayahInclude = JSON.parse(data.wilayahInclude)

			console.log(detail.length);

			if (data.result == "sukses") {
			
				toggle(false);
				
				for (var i = 0; i < wilayahInclude.length; i++) {
					// alert($(".kota-" + wilayahInclude[i]['Wilayah'].trim().replace(/\s/g, '')).val());
					$(".kota-" + wilayahInclude[i]['Wilayah'].trim().replace(/\s/g, '')).prop("checked", true);
					$(".kdlokasi-" + wilayahInclude[i]['Wilayah'].trim().replace(/\s/g, '')).prop("checked", true);
				}
				
				$("#tbodyBarangCampaign").empty();
				idx=0;
				for (var i = 0; i < detail.length; i++) {
					addRow();
				}
				var z = 1;
				for (var i = 0; i < detail.length; i++) {
					const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
					let CampaignStart = new Date(detail[i]['CampaignStart'])
					let CampaignEnd = new Date(detail[i]['CampaignEnd'])
					let JumlahHari = detail[i]['JumlahHari']
					
					console.log(JumlahHari);
					
					let tglawal = CampaignStart.getDate() + "-" + months[(CampaignStart.getMonth())] + "-" + CampaignStart.getFullYear();
					let tglakhir = CampaignEnd.getDate() + "-" + months[(CampaignEnd.getMonth())] + "-" + CampaignEnd.getFullYear();

					$("#filterBarang" + z).val(detail[i]['ProductID']);
					
					// $("#DetailStartCampaign_" + z).val(tglawal);
					// $("#DetailEndCampaign_" + z).val(tglakhir);
					
					$("#DetailStartCampaign_" + z).datepicker("update",tglawal);
					$("#DetailEndCampaign_" + z).datepicker("update",tglakhir);
					
					// aliat 20-11-2020 --- start
					
					var StartCampaignDt = $('#DetailStartCampaign_'+z).datepicker('getDate');
					var EndCampaignDt = $('#DetailEndCampaign_'+z).datepicker('getDate');

					let days = (EndCampaignDt- StartCampaignDt) / (1000 * 60 * 60 * 24) + 1;
					
					$("#JumlahHariCampaign_" + z).val(JumlahHari);
					$("#JumlahHariCampaign_" + z).prop('max',days);
					$("#JumlahHariCampaign_" + z).attr('max',days);
					// aliat 20-11-2020 --- end
					
					z++;
				}

				setDisable(true);

			} else {
				alert('Gagal fetch');
			}
		}, 'json', errorAjax);
	}

	var LoadTrxList = function(z) {
		var url = "<?php echo site_url('PersiapanBarangCampaign/GetTrxList'); ?>"
		$('#tabel-data-trx').DataTable({
			'processing': true,
			'serverSide': true,
			'destroy': true,
			'serverMethod': 'post',
			"pageLength": 10,
			"order": [
				[0, "asc"]
			],
			'ajax': {
				'url': url,
				'data': {
					api: 'APITES',
					index: z
				},
				'type': 'POST',
				'dataSrc': 'Data'
			},
			'columns': [{
					data: 'btn'
				},
				{
					data: 'CampaignID'
				},
				{
					data: 'CampaignName',
				},
				{
					data: 'Divisi'
				}
			]
		});
	}

	var RemoveBarang = function(i) {
		$("#kolumbrg" + i).remove();
	}

	var RemoveAllBarang = function(i) {
		$('.rowbrg').each(function(i, obj) {
			$("#" + this.id ).remove();
		});
		idx = 0;
	}
	
	var setDisable = function(b){
		$('.isdisabled').each(function( i ) {
			$(this).prop("disabled", b);
			$(this).attr("disabled", b);
		 });
	}
	
	var LoadBarangList = function(z) {
	
		var table = $('#tabel-data').DataTable();
		table.clear();
		
		divisi = $("#filterDivisiReal").val();
		var url = "<?php echo site_url('PersiapanBarangCampaign/GetBarangList'); ?>";
	
		$('#tabel-data').DataTable({
			'processing': true,
			'serverSide': true,
			'destroy': true,
			'serverMethod': 'post',
			"pageLength": 10,
			"order": [
				[0, "asc"]
			],
			'ajax': {
				'url': url,
				'data': {
					api: 'APITES',
					divisi: divisi,
					index: z
				},
				'type': 'POST',
				'dataSrc': 'Data'
			},
			'columns': [{
					data: 'btn'
				},
				{
					data: 'KD_BRG'
				},
				{
					data: 'NM_BRG'
				},
				{
					data: 'Divisi'
				},
				{
					data: 'MERK'
				},
				{
					data: 'JNS_BRG'
				},
				{
					data: 'AKTIF'
				}
			]
		});
	}

	activateDatepicker = function() {
		// $('.datepicker').datetimepicker({
			// scrollMonth: false,
			// lang: 'en',
			// timepicker: false,
			// format: 'd-M-Y',
			// formatDate: 'd-M-Y'
		// });
		
		$('.datepicker').datepicker({
			format: "dd-M-yyyy",
			autoclose: true,
			}).on('changeDate', function(e) { 
			var p = this.id.split('_');
			var StartCampaignDt = $('#DetailStartCampaign_'+p[1]).datepicker('getDate');
			var EndCampaignDt = $('#DetailEndCampaign_'+p[1]).datepicker('getDate');
			
			if(Date.parse(StartCampaignDt) && Date.parse(EndCampaignDt)){
				if(StartCampaignDt > EndCampaignDt){
					alert('Tanggal mulai jangan melebihi tanggal akhir');
					if(p[0]=='DetailStartCampaign'){
						$('#'+this.id).val($('#StartCampaign').val()).datepicker("update");
					}
					else{
						$('#'+this.id).val($('#EndCampaign').val()).datepicker("update");
					}
				}
				var days = (EndCampaignDt- StartCampaignDt) / (1000 * 60 * 60 * 24) + 1;
				$('#JumlahHariCampaign_'+p[1]).val(days);
				$('#JumlahHariCampaign_'+p[1]).attr("max", days);
				$('#JumlahHariCampaign_'+p[1]).prop("max", days);
			}
		});
	}	
	
	isiTanggal = function(i) {
	
		var StartCampaignDt = $('#StartCampaign').datepicker('getDate');
		var EndCampaignDt = $('#EndCampaign').datepicker('getDate');
			
			
		if ($('#StartCampaign').val()) {
			// $('#DetailStartCampaign_' + i).val($('#StartCampaign').val());
			$('#DetailStartCampaign_' + i).datepicker("setStartDate", StartCampaignDt);
			$('#DetailStartCampaign_' + i).datepicker("setEndDate", EndCampaignDt);
			$('#DetailStartCampaign_' + i).datepicker("update", $('#StartCampaign').val());
			
		}
		if ($('#EndCampaign').val()) {
			// $('#DetailEndCampaign_' + i).val($('#EndCampaign').val());
			$('#DetailEndCampaign_' + i).datepicker("setStartDate", StartCampaignDt);
			$('#DetailEndCampaign_' + i).datepicker("setEndDate", EndCampaignDt);
			$('#DetailEndCampaign_' + i).datepicker("update", $('#EndCampaign').val());
		}
		$('.JumlahHariCampaign').val($('#txtJumlahHari').val());
		
		$('.JumlahHariCampaign').attr("max", $('#txtJumlahHari').val());
		$('.JumlahHariCampaign').prop("max", $('#txtJumlahHari').val());
	}
	

	var addRow = function() {
		// var rows = document.getElementById("tbodyBarangCampaign").getElementsByTagName("tr").length;
		// var i = rows + 1
		idx += 1
		var i = idx;

		var tr = "<tr class='rowbrg'  id='kolumbrg" + i + "'>";
		var td = "<td>" +
			"<input type='button' onclick='RemoveBarang(\"" + i + "\")' id='btnRemoveDt" + i + "' class='isdisabled btnRemoveDt" + i + "' style='width:21px;padding:0px !important;background-color:red;'  value='-'>" +
			"</td>";
		var td0 = "<td style='background-color:#F08080;'>" +
			"<input type='text' class='filterText' name='filterBarang[]' id='filterBarang" + i + "' readonly required>" +
			"<button class='isdisabled' style='margin-left: 4px;' onclick='LoadBarangList(\"" + i + "\")' data-toggle='modal' data-target='#barang_modal' type='button'>...</button></td>";
		var td1 = "<td style='background-color:#F08080;'>" +
			"<input type='text' style='width:150px;' class='isdisabled datepicker datepickerInput DetailStartCampaign' name='DetailStartCampaign[]' id='DetailStartCampaign_" + i + "' readonly required>" +
			"</td>";
		var td2 = "<td style='background-color:#F08080;'>" +
			"<input type='text' style='width:150px;' class='isdisabled datepicker datepickerInput DetailEndCampaign' name='DetailEndCampaign[]' id='DetailEndCampaign_" + i + "' readonly required>" +
			"</td>";
		var td3 = "<td style='background-color:#F08080;'>" +
			"<input type='number' style='width:80px;' class='isdisabled JumlahHariCampaign' name='JumlahHariCampaign[]' id='JumlahHariCampaign_" + i + "' min='1' required>" +
			"</td>";
		$("#tbodyBarangCampaign").append(tr + td + td0 + td1 + td2 + td3);
		activateDatepicker();
		isiTanggal(i);
	}

	function validate(val = "") {
		
		var valid = true;
		valid = checkEmpty($("#txtNamaCampaign"));
		valid = valid && checkEmpty($("#StartCampaign"));
		valid = valid && checkEmpty($("#EndCampaign"));

		$("#btnSubmit").attr("disabled", true);
		$("#btnSubmit").prop("disabled", true);
		$("#btnEdit").attr("disabled", true);
		$("#btnEdit").prop("disabled", true);
		
		
		$('.filterText').each(function(i, obj) {
			valid = valid && checkEmpty($('#'+this.id));
		});

		if (valid) {
			// if($("#txtIsEdit").val() == ""){
				// $("#btnSubmit").attr("disabled", false);
				// $("#btnSubmit").prop("disabled", false);
			// }else{
				// $("#btnEdit").attr("disabled", false);
				// $("#btnEdit").prop("disabled", false);
				// $("#btnNew").attr("disabled", false);
				// $("#btnNew").prop("disabled", false);
			// }
			
			$("#btnSubmit").attr("disabled", false);
			$("#btnSubmit").prop("disabled", false);
		
		}
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
	
	
	function checkKode(kode) {
		var ada = false;
		$('.filterText').each(function(i, obj) {
			if ($('#'+this.id).val() == kode.trim()) {
				ada = true;
			}
		});
		return ada;
	}

	function toggle(val) {
		$("#checkBoxContainer").find("input").each(function(index, item) {
			// alert($(item).val());
			// if($(item).val()=='JAKARTA' || $(item).val()=='OUTLIER'){
			// 	$(item).prop("checked",val);
			// 	$(item).attr("checked", val);
			// }
			$(item).prop("checked", val);
			$(item).attr("checked", val);
		});
	}

	loadWilayahExclude = function() {
		$("#checkBoxContainer").find("input").each(function(index, item) {

			// if($(item).val()!='JAKARTA' && $(item).val()!='OUTLIER' && $(item).val()!='JKT' && $(item).val()!='DM' && $(item).val()!='MODERN OUTLET'){
			// 	$(item).prop("checked",false);
			// 	$(item).attr("checked", false);
			// 	// $(item).prop("disabled",true);
			// }else{
			// 	$(item).prop("checked",true);
			// 	$(item).attr("checked", true);
			// }
			/*
			if ($(".kdlokasi-" + $(item).val().replace(/ +/g, "")).val() != undefined && $(".kota-" + $(item).val().replace(/ +/g, "")).val() != undefined) {
				if ($(".kdlokasi-" + $(item).val().replace(/ +/g, "")).val() == 'JKT' && $(".kota-" + $(item).val().replace(/ +/g, "")).val() == 'JAKARTA') {
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", true);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("checked", true);
				}else if ($(".kdlokasi-" + $(item).val().replace(/ +/g, "")).val() == 'JKT' && $(".kota-" + $(item).val().replace(/ +/g, "")).val() == 'OUTLIER') {
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", true);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("checked", true);
				}else if ($(".kdlokasi-" + $(item).val().replace(/ +/g, "")).val() == 'JKT' && $(".kota-" + $(item).val().replace(/ +/g, "")).val() == 'DM') {
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("disabled", true);
				} else if ($(".kdlokasi-" + $(item).val().replace(/ +/g, "")).val() == 'JKT' && $(".kota-" + $(item).val().replace(/ +/g, "")).val() == 'MODERNOUTLET') {
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("disabled", true);
				} else if ($(".kdlokasi-" + $(item).val().replace(/ +/g, "")).val() == 'DMI' && $(".kota-" + $(item).val().replace(/ +/g, "")).val() == 'JAKARTA') {
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", true);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("checked", true);
				}else if ($(".kdlokasi-" + $(item).val().replace(/ +/g, "")).val() == 'DMI' && $(".kota-" + $(item).val().replace(/ +/g, "")).val() == 'OUTLIER') {
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", true);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("checked", true);
				}else if ($(".kdlokasi-" + $(item).val().replace(/ +/g, "")).val() == 'DMI' && $(".kota-" + $(item).val().replace(/ +/g, "")).val() == 'DM') {
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("disabled", true);
				} else if ($(".kdlokasi-" + $(item).val().replace(/ +/g, "")).val() == 'DMI' && $(".kota-" + $(item).val().replace(/ +/g, "")).val() == 'MODERNOUTLET') {
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("disabled", true);
				}else{
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
					$(".kota-" + $(item).val().replace(/ +/g, "")).prop("disabled", true);
				}
			}
			*/
			
			$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", true); // check all 
			$(".kota-" + $(item).val().replace(/ +/g, "")).prop("checked", true); // check all 
			
			$(".kota-" + $(item).val().replace(/ +/g, "")).change(function() {
				if ($(this).is(":checked")) {
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", true);
				} else {
					$(".kdlokasi-" + $(item).val().replace(/ +/g, "")).prop("checked", false);
				}
			});

		});
	}

	function setStartCampaign(maxdate=null){
		console.log('setStartCampaign');
		$('#StartCampaign').datepicker('destroy');
		$('#StartCampaign').datepicker({
			format: "dd-M-yyyy",
			autoclose: true,
			endDate: maxdate,
			}).on('changeDate', function(e) { //changeDate
			var StartCampaignDt = $('#StartCampaign').datepicker('getDate');
			$('#EndCampaign').datepicker("setStartDate", StartCampaignDt);

			$('.DetailStartCampaign').datepicker("setStartDate", StartCampaignDt);
			$('.DetailEndCampaign').datepicker("setStartDate", StartCampaignDt);

			$('.DetailStartCampaign').datepicker("update", StartCampaignDt);

			validate();
			hitungJumlahHari();
			
		});
		
	}
		
	function setEndCampaign(mindate=null){
		console.log('setEndCampaign');
		$('#EndCampaign').datepicker('destroy');
		$('#EndCampaign').datepicker({
			format: "dd-M-yyyy",
			autoclose: true,
			startDate: mindate,
			}).on('changeDate', function(e) {
			var EndCampaignDt = $('#EndCampaign').datepicker('getDate');
			$('#StartCampaign').datepicker("setEndDate", EndCampaignDt);
			$('.DetailStartCampaign').datepicker("setEndDate", EndCampaignDt);
			$('.DetailEndCampaign').datepicker("setEndDate", EndCampaignDt);
			$('.DetailEndCampaign').datepicker("update", EndCampaignDt);
			validate();
			hitungJumlahHari()
		});
	}
	
	function hitungJumlahHari(){
		var EndCampaignDt = $('#EndCampaign').datepicker('getDate');
		var StartCampaignDt = $('#StartCampaign').datepicker('getDate');
		
		if(Date.parse(EndCampaignDt) && Date.parse(StartCampaignDt)){
			var days = (EndCampaignDt- StartCampaignDt) / (1000 * 60 * 60 * 24) + 1;
			$('#txtJumlahHari').val(days);
			$('#txtJumlahHari').attr("max", days);
			$('#txtJumlahHari').prop("max", days);
			$('.JumlahHariCampaign').val(days);
			$('.JumlahHariCampaign').attr("max", days);
			$('.JumlahHariCampaign').prop("max", days);
		}
		else{
			$('#txtJumlahHari').val('');
			$('#txtJumlahHari').attr("max", 0);
			$('#txtJumlahHari').prop("max", 0);
			$('.JumlahHariCampaign').val('');
			$('.JumlahHariCampaign').attr("max", 0);
			$('.JumlahHariCampaign').prop("max", 0);
		}
	}
	
	$(document).ready(function() {		
		// document.getElementById('filterDivisi').style.display = none;
		$("#filterDivisiReal").change(function() {
			$("#filterDivisi").val($(this).val());
			RemoveAllBarang();
			addRow();
		});
		
		$("#txtJumlahHari").change(function() {
			// $('.JumlahHariCampaign').val($('#txtJumlahHari').val());
			// $('.JumlahHariCampaign').attr("max", $('#txtJumlahHari').val());
			// $('.JumlahHariCampaign').prop("max", $('#txtJumlahHari').val());
			
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
		
		$("#filterDivisi").val($("#filterDivisiReal").val());

		$("#btnEdit").prop("disabled", true);
		$("#btnEdit").attr("disabled", true);

		$("#btnSubmit").prop("disabled", true);
		$("#btnSubmit").attr("disabled", true);
		
		$("#btnView").prop("disabled", true);
		$("#btnView").attr("disabled", true);
		
		$("#btnEmail").hide();

		$("#btnAddDt").click(function() {
			addRow();
		});

		$("#btnSubmit").click(function() {
		
		
			if($('input.cboWilayah:checked').length==0){
			
				alert('Pilih minimal 1 wilayah!');
				return false;
			}
			var isDisabled = $("#btnSubmit").prop('disabled');
			if (!isDisabled) {
			
				if($("#txtIsEdit").val()=='edit'){
					$("#FormBarang").attr("action", "PersiapanBarangCampaign/EditPersiapanCampaign");
				}
				$("#checkBoxContainer").find("input").each(function(index, item) {
					$(item).removeAttr('disabled');
				});
				$("#FormBarang").submit();
			}
		});

		$("#btnEdit").click(function() {
			var isDisabled = $("#btnEdit").prop('disabled');
			if (!isDisabled) {
				// $("#FormBarang").submit();
				$("#txtIsEdit").val("edit");
				
				$("#btnEdit").prop("disabled", true);
				$("#btnEdit").attr("disabled", true);
				
				$("#btnView").prop("disabled", true);
				$("#btnView").attr("disabled", true);
			
				$("#btnSubmit").attr("disabled", false);
				$("#btnSubmit").prop("disabled", false);
				
				setDisable(false);
				$("#filterDivisiReal").attr("disabled", true);
			}
		});
		
		$("#btnEmail").click(function() {
			var isDisabled = $("#btnEmail").prop('disabled');
			if (!isDisabled) {
			
				$("#btnEmail").prop('disabled',true);
				$("#btnEmail").attr('disabled',true);
				
				$("#filterDivisiReal").prop('disabled',true);
				$("#filterDivisiReal").attr('disabled',true);
				var Divisi = $("#filterDivisiReal").val();
				var CampaignID = $("#txtKodeCampaign").val();
			
				$.post("<?php echo site_url('PersiapanBarangCampaign/EmailUlang'); ?>", {
					CampaignID: CampaignID,
					Divisi: Divisi,
					// csrf_bit: csrf_bit
				}, function(data) {

					alert(data);
					
					$("#filterDivisiReal").prop('disabled',false);
					$("#filterDivisiReal").attr('disabled',false);
					
					$("#btnEmail").prop('disabled', false);
					$("#btnEmail").attr('disabled', false);
				});
				
			}
		});

		
		$("#btnView").click(function() {
			var isDisabled = $("#btnView").prop('disabled');
			
			if (!isDisabled) {
				setDisable(false);
				
				console.log('view');
				
				$("#txtIsEdit").val("view");
				
				$("#FormBarang").attr("action", "PersiapanBarangCampaign/ViewPersiapanCampaign");
				
				$("#FormBarang").attr("target", "_blank");
				
				// // var isDisabled = $("#btnView").prop('disabled');
				// // if (!isDisabled) {
				
				$("#FormBarang").submit();
				
				// // }
				
				$("#FormBarang").removeAttr('target');
				
				setDisable(true);
			}
		});
		
		$("#btnNew").click(function() {
			var isDisabled = $("#btnNew").prop('disabled');
			if (!isDisabled) {
			Reset();
			// $("#txtKodeCampaign").val("AutoNumber");
			// $("#txtNamaCampaign").val("");
			// $("#filterDivisiReal").attr("disabled", false);

			// var strHtml = "<tr id='kolumbrg1'>"+
				// "<td>"+
				// "	<input type='button' onclick='RemoveBarang(1)' id='btnRemoveDt1' class='btnRemoveDt1' style='width:21px;padding:0px !important;background-color:red;' value='-'>"+
				// "</td>"+
				// "<td style='background-color:#F08080;'>"+
				// "	<input type='text' class='filterText' name='filterBarang[]' id='filterBarang1' readonly>"+
				// "	<button onclick='LoadBarangList(1)' data-toggle='modal' data-target='#barang_modal' type='button'>...</button>"+
				// "</td>"+
				// "<td style='background-color:#F08080;'>"+
				// "	<input type='text' style='width:150px;' class='datepicker datepickerInput DetailStartCampaign' name='DetailStartCampaign[]' id='DetailStartCampaign1' readonly>"+
				// "</td>"+
				// "<td style='background-color:#F08080;'>"+
				// "	<input type='text' style='width:150px;' class='datepicker datepickerInput DetailEndCampaign' name='DetailEndCampaign[]' id='DetailEndCampaign1' readonly>"+
				// "</td>"+
				// "</tr>";
			// $("#tbodyBarangCampaign").html(strHtml);
			// activateDatepicker();
			}
		});

		$('.datepicker').datetimepicker('destroy');
		$('.datepicker').datepicker({
			format: "dd-M-yyyy",
			autoclose: true
		});
		
		// $('#StartCampaign').datetimepicker({
			// scrollMonth: false,
			// lang: 'en',
			// timepicker: false,
			// format: 'd-M-Y',
			// formatDate: 'd-M-Y',
			// onSelectDate: function(ct, $input) {
				// $('.DetailStartCampaign').val($input.val());
			// }
		// });
		// $('#EndCampaign').datetimepicker({
			// scrollMonth: false,
			// lang: 'en',
			// timepicker: false,
			// format: 'd-M-Y',
			// formatDate: 'd-M-Y',
			// onSelectDate: function(ct, $input) {
				// $('.DetailEndCampaign').val($input.val());
			// }
		// });

		activateDatepicker();
		loadWilayahExclude();
		
		setStartCampaign();
		setEndCampaign();
		

	});
</script>

<div class="container">
	<?php if ($this->session->flashdata('success_message') != '') { ?>
		<div class="e-message success"><?php echo $this->session->flashdata('success_message'); ?></div>
	<?php
	}
	if ($this->session->flashdata('err_message') != '') {
	?>
		<div class="e-message error"><?php echo $this->session->flashdata('err_message'); ?></div>
	<?php } ?>

	<div class="title">PERSIAPAN BARANG CAMPAIGN</div>
	<div class="form">
		<?php echo form_open('PersiapanBarangCampaign/SimpanPersiapanCampaign', array("id" => "FormBarang")); ?>
		<div class="row">
			<div class="col-3 col-m-4">Kode Campaign</div>
			<div class="col-8 col-m-7">
				<input type="text" class="form-control" name="txtKodeCampaign" id="txtKodeCampaign" placeholder="AutoNumber" readonly>
			</div>
			<div class="col-1 col-m-1">
				<button onclick="LoadTrxList(1)" data-toggle='modal' data-target='#trx_modal' type="button">...</button>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4">Nama Campaign</div>
			<div class="col-9 col-m-8">
				<input type="text" class="form-control isdisabled" name="txtNamaCampaign" id="txtNamaCampaign" placeholder="Nama Campaign" onblur="validate()" autocomplete="off" required>
				<input type="hidden" class="form-control" name="txtIsEdit" id="txtIsEdit" placeholder="txtIsEdit">
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4">Periode Campaign</div>
			<div class="col-9 col-m-8">
				<?php
				$attr = array(
					'class' => 'datepicker datepickerInput isdisabled',
					'id' => 'StartCampaign',
					'style' => 'width:150px;',
					'readonly' => true,
					'required' => 'required',
					'onblur' => 'validate()'
				);
				echo BuildInput('text', 'CampaignStart', $attr);
				echo "  -  ";
				$attr = array(
					'class' => 'datepicker datepickerInput isdisabled',
					'id' => 'EndCampaign',
					'style' => 'width:150px;',
					'readonly' => true,
					'required' => 'required',
					'onblur' => 'validate()'
				);
				echo BuildInput('text', 'CampaignEnd', $attr);
				?>
				Jumlah Hari
				<input type="number" class="isdisabled" name="txtJumlahHari" id="txtJumlahHari" style="width:100px" onblur="validate()"  min="1" autocomplete="off">
				
				<br>
				<font color="blue">*** Barang yang berbeda Periode harap diubah Periodenya di baris Tabel</font>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4">Divisi</div>
			<div class="col-9 col-m-8">
				<select id="filterDivisiReal" name="filterDivisiReal" class="form-control filterDropdownReal isdisabled" required>
					<?php
					for ($i = 0; $i < count($divisions); $i++) {
						echo ("<option value='" . $divisions[$i]["DIVISI"] . "'>" . $divisions[$i]["DIVISI"] . "</option>");
					} ?>
				</select>
				<input type="hidden" id="filterDivisi" name="filterDivisi" class="form-control" />
			</div>
		</div>
		


		<div class="row">
			<div class="col-12 col-m-12">*** Data Yang Disimpan Yang Ada Di Table</div>
		</div>

		<div>
			<p>
				<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample" style="width:150px!important;font-size:16px!important;">
					Wilayah Include
				</button>

				<input type="button" class="isdisabled" onclick="toggle(true)"value="Select all">

				/

				<input type="button" class="isdisabled" onclick="toggle(false)" value="Deselect all">
			</p>
			<div class="collapse" id="collapseExample">
				<div class="card card-body" id="checkBoxContainer">

					<?php foreach ($wilayah as $wlyh) { ?>
						<div class="col-lg-3">
							<input
							type='checkbox'
							class="hidden kdlokasi-<?php echo trim(str_replace(' ', '', $wlyh->Kota)) ?>"
							id="<?php echo trim(str_replace(' ', '', $wlyh->Kota)) ?>"
							name="KdLokasi[]" value="<?php echo  trim($wlyh->Kd_Lokasi)  ?>">
							
							<input
							type='checkbox'
							class="cboWilayah isdisabled kota-<?php echo trim(str_replace(' ', '', $wlyh->Kota)) ?>"
							id="<?php echo trim(str_replace(' ', '', $wlyh->Kota)) ?>"
							name="Kota[]"
							value="<?php echo  trim($wlyh->Kota)  ?>">
							
							<?php echo trim($wlyh->Kota) ?>
						</div>
					<?php }	?>

				</div>
			</div>
			<?php foreach ($wilayah as $wlyh) { ?>
				<input type="hidden" name="Kotas[]" value="<?php echo trim($wlyh->Kota) ?>">
			<?php } ?>

			<table id="table-primary" class="table table-striped table-bordered" cellspacing="0">
				<thead id="theadBarangCampaign">
					<tr>
						<th scope="col" width="3%" rowspan="2" align="left">
							<input type='button' id="btnAddDt" class="btnAddDt isdisabled" style="width:21px;padding:0px !important" value="+">
						</th>
						<th scope="col" style="border:1px solid #ccc;">Kode Barang</th>
						<th scope="col" style="border:1px solid #ccc;">StartCampaign</th>
						<th scope="col" style="border:1px solid #ccc;">EndCampaign</th>
						<th scope="col" style="border:1px solid #ccc;">Jumlah Hari</th>
					</tr>
				</thead>
				<tbody id="tbodyBarangCampaign">
					<tr class='rowbrg'  id='kolumbrg1'>
						<td>
							<input type='button' onclick="RemoveBarang(1)" id="btnRemoveDt1" class="isdisabled btnRemoveDt1" style="width:21px;padding:0px !important;background-color:red;" value="-">
						</td>
						<td style="background-color:#F08080;">
							<input type='text' class='filterText' name='filterBarang[]' id='filterBarang1' style="pointer-events: none;" readonly><button class="isdisabled" style="margin-left: 4px" onclick="LoadBarangList(1)" data-toggle='modal' data-target='#barang_modal' type="button">...</button>
						</td>
						<td style="background-color:#F08080;">
							<input type='text' style='width:150px;' class='isdisabled datepicker datepickerInput DetailStartCampaign' name='DetailStartCampaign[]' id='DetailStartCampaign_1' readonly>
							<span name="NoteStartCampaign[]" id="NoteStartCampaign"></span>
						</td>
						<td style="background-color:#F08080;">
							<input type='text' style='width:150px;' class='isdisabled datepicker datepickerInput DetailEndCampaign' name='DetailEndCampaign[]' id='DetailEndCampaign_1' readonly>
							<span name="NoteEndCampaign[]" id="NoteEndCampaign"></span>
						</td>
						<td style="background-color:#F08080;">
							<input type='number' style='width:80px;' class='isdisabled JumlahHariCampaign' name='JumlahHariCampaign[]' id='JumlahHariCampaign_1' min="1">
						</td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>
	<div class="clearfix" style="height:60px"></div>

	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnNew" name="btnNew">New</div>
			</div>
			
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnSubmit" name="btnSubmit">Simpan</div>
			</div>
			
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnEdit" name="btnEdit">Edit</div>
			</div>
			
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnView" name="btnView">View</div>
			</div>
			
			<!-- <div style='margin:10px;float:left;'>
				<a href="<?php //echo (base_url('ExportData/KategoriInsentif')); ?>">
				<div class="btn" id="btnExport" name="btnExport">Export</div>
				</a>
			</div> -->
			
			<div style='margin:10px;float:left;color:white;font-size:120%'>
				<div id="status"></div>
			</div>
			
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnEmail" name="btnEmail">Email Ulang</div>
			</div>
			
			
		</div>


		<?php echo form_close(); ?>
	</div>

	<!-- barang modal -->
	<div class="modal fade" id="barang_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					List Barang
				</div>
				<div class="modal-body">
					<div class="table-responsive">
						<table id="tabel-data" class="display table table-stripped table-bordered" style="width:100%;">
							<thead>
								<tr>
									<td style="width:10%;"></td>
									<td style="width:15%;">Kode Barang</td>
									<td style="width:30%;">Nama barang</td>
									<td style="width:10%;">Divisi</td>
									<td style="width:10%;">Merk</td>
									<td style="width:20%;">Jenis Barang</td>
									<td style="width:5%;">Aktif</td>
								</tr>
							</thead>
						</table>
					</div>
					<input type="hidden" name="hdnJmlhModule" id="hdnJmlhModule">
					<input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
				</div>
			</div>
		</div>
	</div>
	<!--  -->
	<!-- trx modal -->
	<div class="modal fade" id="trx_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					List Transaksi
				</div>
				<div class="modal-body">
					<div class="table-responsive">
						<table id="tabel-data-trx" class="display table table-stripped table-bordered" style="width:100%;">
							<thead>
								<tr>
									<td style="width:8%;"></td>
									<td style="width:12%;">Campaign ID</td>
									<td style="width:65%;">Nama Campaign</td>
									<td style="width:15%;">Divisi</td>
								</tr>
							</thead>
						</table>
					</div>
					<input type="hidden" name="hdnJmlhModule" id="hdnJmlhModule">
					<input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
				</div>
			</div>
		</div>
	</div>
	<!--  -->