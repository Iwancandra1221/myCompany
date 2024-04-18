<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<style>
	.filterDropdown {
		width:100%;
		background-color: #ffffcc;
	}
	.filterText {
		width:75%;
		background-color: #ffffcc;
	}
	.title {
		font-size : 15pt;
		font-weight: bold;
		text-align: center;
	}

	td, th, .datepicker {
		font-size:9pt!important;
	}
	
	.ui-autocomplete {
		overflow-x: hidden;
		max-height: 264px;
	}

</style>
<script>
	
	var no = 0;

	var LoadBarangList=function() {
	
		divisi = $("#filterDivisi").val();
		merk = $("#filterMerk").val();
		jenis = $("#filterJenis").val();

		$(".loading").show();
		 $.ajax({
            url: "<?php echo site_url('TargetFokus/GetBarangList'); ?>",
            type: 'POST',
			data:{
				divisi 	: divisi,
				merk 	: merk,
				jenis 	: jenis
			},
            dataType: 'JSON',
            success: function (data) {
				if (data.error != undefined) {
					$("#tbodyBarangInsentif").html("");
				} else {
					 $("#filterBarang").autocomplete({
						  source: data,
						  select: function(event, ui) {
							$("#KdBrg").val(ui.item.id);
							$("#NmBrg").val(ui.item.value) ;
						}
					});
				}
				$(".loading").hide();
            },
        });
	}
	
	var LoadMerkList = function() {
		let divisi = $("#filterDivisi").val();
		$(".loading").show();
		 $.ajax({
            url: "<?php echo site_url('TargetFokus/GetMerkList'); ?>",
            type: 'POST',
			data:{
				divisi 	: divisi
			},
            dataType: 'JSON',
            success: function (data) {
				if (data.error != undefined) {
					$("#filterMerk").html("<option value='all'>ALL</option>");
				} else {
					var x = "<option value='all'>ALL</option>";
					for(var i=0; i<data.length; i++) {	
						x = x + "<option value='"+data[i].MERK+"'>"+data[i].MERK+"</option>";
					}
					$("#filterMerk").html(x);
				}	
				$(".loading").hide();
            },
        });
	}

	var LoadJenisList = function() {
		let divisi = $("#filterDivisi").val();
		let merk = $("#filterMerk").val();
		$(".loading").show();
		 $.ajax({
            url: "<?php echo site_url('TargetFokus/GetJenisList'); ?>",
            type: 'POST',
			data:{
				divisi 	: divisi,
				merk 	: merk,
			},
            dataType: 'JSON',
            success: function (data) {
				if (data.error != undefined) {
					$("#filterJenis").html("<option value='all'>ALL</option>");
				} else {
					var x = "<option value='all'>ALL</option>";
					for(var i=0; i<data.length; i++) {	
						x = x + "<option value='"+data[i].JNS_BRG+"'>"+data[i].JNS_BRG+"</option>";
					}
					$("#filterJenis").html(x);
				}
				$(".loading").hide();
				return true;
            },
        });
	}

	var AddBarangList = function() {
	
		var filterDivisi = $('#filterDivisi').val();
		var filterMerk = $('#filterMerk').val();
		var filterJenis = $('#filterJenis').val();
		var filterKategori = $('#filterKategori').val();
		var dp1 = $('#dp1').val();
		var dp2 = $('#dp2').val();
		
		if(filterDivisi=='all'){
			alert('Pilih Divisi!');
			return false;
		}
		
		if(dp1==''){
			alert('Pilih Tanggal Awal!');
			return false;
		}
		if(dp2==''){
			alert('Pilih Tanggal Akhir!');
			return false;
		}
		
		
		var KdBrg = $('#KdBrg').val();
		var NmBrg = $('#NmBrg').val();
		
		var filterBarang = $('#filterBarang').val();
		if(KdBrg==''){
			return false;
		}
		
		if(checkKdBrg(KdBrg)){
			alert('Kode barang sudah ada!');
			$('#KdBrg').val('');
			$('#NmBrg').val('');
			$('#filterBarang').val('');
			$('#filterBarang').focus();
			return false;
		}
		
		no += 1;
		
		var x = '';
		x +='<tr id="baris_'+no+'">';
		x +='	<td style="border:1px solid #ccc;padding:2px;">'+filterDivisi+'</td>';
		x +='	<td class="hideOnMobile" style="border:1px solid #ccc;padding:2px;">'+filterMerk+'</td>';
		x +='	<td class="hideOnMobile" style="border:1px solid #ccc;padding:2px;">'+filterJenis+'</td>';
		x +='	<td style="border:1px solid #ccc;padding:2px;">'+filterKategori+'</td>';
		x +='	<td style="border:1px solid #ccc;padding:2px;">'+dp1+'</td>';
		x +='	<td style="border:1px solid #ccc;padding:2px;">'+dp2+'</td>';
		x +='	<td colspan="2" style="border:1px solid #ccc;padding:2px;">';
		x +='		<input type="hidden" name="kodebarang[]" id="kodebarang[]" class="kodebarang" value="'+KdBrg+'" readonly>';
		x +='		<input type="hidden" name="kategoriinsentif[]" id="kategoriinsentif[]" value="'+filterKategori+'" readonly>';
		x +='		<input type="hidden" name="tglawal[]" id="tglawal[]" value="'+dp1+'" readonly>';
		x +='		<input type="hidden" name="tglakhir[]" id="tglakhir[]" value="'+dp2+'" readonly>';
		// x +='		<input type="hidden" name="productgroupid[]" id="productgroupid[]" value="'+PRODUCTGROUPID+'" readonly>';
		x +='		'+NmBrg;
		x +='		</td>';
		x +='	<td style="border:1px solid #ccc;padding:2px;text-align:center">';
		x +='		<button id="btnFilter" type="button" onclick="javascript:DelRow('+no+')"><b>&#10005;</b></button>';
		x +='		</td>';
		x +='</tr>';
		
		$("#tbodyBarangInsentif").append(x);
		
		$('#KdBrg').val('');
		$('#NmBrg').val('');
		$('#filterBarang').val('');
		$('#filterBarang').focus(); 
		
		//disable untuk isi baris pertama
		$('#filterDivisi').prop('disabled', true);
		$('#filterKategori').prop('disabled', true);
		$('#dp1').prop('disabled', true);
		$('#dp2').prop('disabled', true);
		
		
		
	}

	$(document).ready(function(){

		$('#filterDivisi').on('change', function() {
			$("#filterJenis").val("all");
			LoadMerkList();
			LoadBarangList();
		});

		$('#filterMerk').on('change', function() {
			LoadJenisList();
			LoadBarangList();
		});

		$('#filterJenis').on('change', function() {
			LoadBarangList();
		});

		$("#btnFilter").click(function(){
			AddBarangList();
		});

		$("#btnSubmit").click(function(){
			//enable untuk ambil value
			$('#filterDivisi').prop('disabled', false);
			$('#filterKategori').prop('disabled', false);
			$('#dp1').prop('disabled', false);
			$('#dp2').prop('disabled', false);
			
			$("#FormBarang").submit();
		});

		$("#btnExport").click(function(){
			//enable untuk ambil value
			$('#filterDivisi').prop('disabled', false);
			$('#filterKategori').prop('disabled', false);
			$('#dp1').prop('disabled', false);
			$('#dp2').prop('disabled', false);
			
			$("#FormBarang").submit();
		});
	});
    $(document).ready(function() {
		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		$('#dp2').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
	});
	
	function DelRow(no){
		let text = "Ingin hapus baris ini?";
		if (confirm(text) == true) {
			$('#baris_'+no).remove();
		}
	}
	
	function checkKdBrg(kdbrg){
		let bAda = false;
		$('.kodebarang').each(function(i, obj){
			if($(this).val()==kdbrg){
				bAda = true;
			}
		});
		return bAda;
	}

</script>

<div class="container">
	<?php echo form_open('TargetFokus/SimpanTargetFokus', array("id"=>"FormBarang", "onkeydown"=>"return event.key != 'Enter';")); ?>	
	<div class="title">Item Fokus</div>
	<div class="form">
		<div>	
			<table class="table table-striped table-bordered" id="tableBarangInsentif" cellspacing="0">
				<thead id="theadBarangInsentif">
					<tr>
						<th width="10%" style="border:1px solid #ccc;">Divisi</th>
						<th width="10%" class='hideOnMobile' style="border:1px solid #ccc;">Merk</th>
						<th width="15%" class='hideOnMobile' style="border:1px solid #ccc;">Jenis Barang</th>
						<th width="10%" style="border:1px solid #ccc;">Item Fokus</th>
						<th width="10%" style="border:1px solid #ccc;">Tgl Awal</th>
						<th width="10%" style="border:1px solid #ccc;">Tgl Akhir</th>
						<th width="15%" style="border:1px solid #ccc;">Kode Barang</th>
						<th width="*" style="border:1px solid #ccc;">Nama Barang</th>
						<th width="5%" style="border:1px solid #ccc;">#</th>
					</tr>
				</thead>
				<tfilter>
					<td style="background-color:#990033;">
						<select id="filterDivisi" name="filterDivisi" class="filterDropdown">
							<option value='all'>ALL</option>
							<?php 
							for($i=0;$i<count($divisions);$i++) {
								// echo("<option value='".str_replace("&","",$divisions[$i]["DIVISI"])."'>".$divisions[$i]["DIVISI"]."</option>");
								echo("<option value='".$divisions[$i]["DIVISI"]."'>".$divisions[$i]["DIVISI"]."</option>");
							}
							?>
						</select>
					</td>
					<td class='hideOnMobile' style="background-color:#990033;">
						<select id="filterMerk" name="filterMerk" class="filterDropdown">
							<option value='all'>ALL</option>
							<?php 
							for($i=0;$i<count($merks);$i++) {
								echo("<option value='".$merks[$i]["MERK"]."'>".$merks[$i]["MERK"]."</option>");
							} ?>
						</select>
					</td>
					<td class='hideOnMobile' style="background-color:#990033;">
						<select id="filterJenis" name="filterJenis" class="filterDropdown">
							<option value='all'>ALL</option>
						</select>
					</td>
					<td style="background-color:#990033;">
						<select id="filterKategori" name="filterKategori" class="filterDropdown">
							<?php 
							for($i=0;$i<count($kategoris);$i++) {
								echo("<option value='".$kategoris[$i]["KATEGORI"]."'>".$kategoris[$i]["KATEGORI"]."</option>");
							}
							?>
						</select>					
					</td>
					<td style="background-color:#990033;"><input type='text' name='dp1' id='dp1' style="width:100px" autocomplete="off"></td>
					<td style="background-color:#990033;"><input type='text' name='dp2' id='dp2' style="width:100px" autocomplete="off"></td>
					<td style="background-color:#990033;" colspan="2">
					<input type='hidden' id='KdBrg'>
					<input type='hidden' id='NmBrg'>
					<input type='text' class='filterText' style="width:100%" name='filterBarang' id='filterBarang' onblur="javascript:AddBarangList()">
					</td>
					<td class='hideOnMobile' style="background-color:#990033;">
						<button id="btnFilter" type="button">ADD</button>
					</td>
				</tfilter>
				<tbody id="tbodyBarangInsentif">
				</tbody>
			</table>
		</div>
	</div>
	<div class="clearfix" style="height:60px"></div>

	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<div style='margin:10px;float:left;'>
				<input type="submit" class="btn" id="btnSubmit" name="save" value="Simpan">
			</div>
			<div style='margin:10px;float:left;'>
				<input type="submit" class="btn" id="btnExport" name="export" value="Export">
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>