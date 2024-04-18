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
</style>
<script>
	activateDatepicker = function(){
		$('.datepicker').datetimepicker({
			scrollMonth:false,
			lang:'en',
			timepicker:false,
			format:'d-M-Y',
			formatDate:'d-M-Y'
    	});
	}

	//var ListBarang = [];

	var LoadBarangList=function() {
		divisi = $("#filterDivisi").val();
		merk = $("#filterMerk").val();
		jenis = $("#filterJenis").val();
		kategori = $("#filterKategori").val();
		filter = $("#filterBarang").val();
		view = $("#OptView").val();

		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('MsBarang/GetBarangInsentifList'); ?>", {
			divisi 	: divisi,
			merk 	: merk,
			jenis 	: jenis,
			kategori: kategori,
			filter 	: ((filter=="")?"%":filter),
			view 	: view,
			csrf_bit: csrf_bit
		}, function(data){
			if (data.error != undefined) {
				$("#tbodyBarangInsentif").html("");
			} else {
				ListBarang = data;
				var x = "";
				for(var i=0; i<data.length; i++)
				{	
					x = x + "<tr>";
					x = x + "	<td style='border:1px solid #ccc;padding:2px;'>";
					x = x + "		<input type='text' name='kodebarang[]' id='kodebarang[]' value='"+data[i].KD_BRG+"' readonly>";
					x = x + "		<input type='hidden' name='productgroupid[]' id='productgroupid[]' value='"+data[i].PRODUCTGROUPID+"' readonly>";
					x = x + "		</td>";
					x = x + "	<td class='hideOnMobile' style='border:1px solid #ccc;padding:2px;'>"+data[i].NM_BRG+"</td>";
					x = x + "	<td class='hideOnMobile' style='border:1px solid #ccc;padding:2px;'>"+data[i].DIVISI+"</td>";
					x = x + "	<td style='border:1px solid #ccc;padding:2px;'>"+data[i].MERK+"</td>";
					x = x + "	<td class='hideOnMobile' style='border:1px solid #ccc;padding:2px;'>"+data[i].JNS_BRG+"</td>";
					//x = x + "	<td class='hideOnMobile' style='border:1px solid #ccc;padding:2px;'>"+data[i].STARTDATESTR+"</td>";
					x = x + "	<td style='border:1px solid #ccc;padding:2px;'><select class='product-group' name='kategoriinsentif[]' id='kategoriinsentif[]'>"; 
					x = x + "		<option value='SILVER' "+((data[i].KATEGORI_INSENTIF=="SILVER")?"selected":"")+">SILVER</option>";
					x = x + "		<option value='GOLD' "+((data[i].KATEGORI_INSENTIF=="GOLD")?"selected":"")+">GOLD</option>";
					x = x + "	</td>";
					x = x + "	<td class='hideOnMobile' style='border:1px solid #ccc;padding:2px;'>"
					x = x + "		<input type='text' class='form-control datepicker datepickerInput' id='tglawal[]' name='tglawal[]' required value='"+data[i].STARTDATESTR+"'>";
					x = x + "	</td>";
					x = x + "	<td class='hideOnMobile' style='border:1px solid #ccc;padding:2px;'>"+data[i].ENDDATESTR+"</td>";
					x = x + "</tr>";
				}
				$("#tbodyBarangInsentif").html(x);
				activateDatepicker();
			}
		},'json',errorAjax);		
		$(".loading").hide();

	}
	
	var LoadMerkList = function() {
		divisi = $("#filterDivisi").val();

		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('MsBarang/GetMerkList'); ?>", {
			divisi 	: divisi,
			csrf_bit: csrf_bit
		}, function(data){
			if (data.error != undefined) {
				$("#filterMerk").html("<option value='all'>ALL</option>");
			} else {
				var x = "<option value='all'>ALL</option>";
				for(var i=0; i<data.length; i++) {	
					x = x + "<option value='"+data[i].MERK+"'>"+data[i].MERK+"</option>";
				}
				$("#filterMerk").html(x);
			}
			return true;
		},'json',errorAjax);		
		$(".loading").hide();
	}

	var LoadJenisList = function() {
		divisi = $("#filterDivisi").val();
		merk = $("#filterMerk").val();

		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('MsBarang/GetJenisList'); ?>", {
			divisi 	: divisi,
			merk 	: merk,
			csrf_bit: csrf_bit
		}, function(data){
			if (data.error != undefined) {
				$("#filterJenis").html("<option value='all'>ALL</option>");
			} else {
				var x = "<option value='all'>ALL</option>";
				for(var i=0; i<data.length; i++) {	
					x = x + "<option value='"+data[i].JNS_BRG+"'>"+data[i].JNS_BRG+"</option>";
				}
				$("#filterJenis").html(x);
			}
			return true;
		},'json',errorAjax);		
		$(".loading").hide();
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

		$('#filterKategori').on('change', function() {
			LoadBarangList();
		});

		$('#OptView').on('change', function() {
			LoadBarangList();
		});

		$("#btnFilter").click(function(){
			LoadBarangList();
		});

		$("#btnSubmit").click(function(){
			$("#FormBarang").submit();
		});
	});
</script>

<div class="container">
	<?php echo form_open('MsBarang/SimpanKategoriInsentif', array("id"=>"FormBarang")); ?>	
	<div class="title">Master Barang Kategori Insentif</div>
	<div class="form">
		<div>	
			<table class="table table-striped table-bordered" cellspacing="0">
				<thead id="theadBarangInsentif">
					<tr>
						<th scope="col" width="15%" style="border:1px solid #ccc;">Kode Barang</th>
						<th scope="col" width="20%" class='hideOnMobile' style="border:1px solid #ccc;">Nama Barang</th>
						<th scope="col" width="10%" class='hideOnMobile' style="border:1px solid #ccc;">Divisi</th>
						<th scope="col" width="10%" style="border:1px solid #ccc;">Merk</th>
						<th scope="col" width="15%" class='hideOnMobile' style="border:1px solid #ccc;">Jenis Barang</th>
						<th scope="col" width="10%" style="border:1px solid #ccc;">Kategori Insentif</th>
						<th scope="col" width="10%" class='hideOnMobile' style="border:1px solid #ccc;">Tgl Awal</th>
						<th scope="col" width="10%" class='hideOnMobile' style="border:1px solid #ccc;">Tgl Akhir</th>
					</tr>
				</thead>
				<tfilter>
					<td style="background-color:#990033;" colspan="2"><input type='text' class='filterText' name='filterBarang' id='filterBarang'><button id="btnFilter" type="button">CARI</button></td>
					<td class='hideOnMobile' style="background-color:#990033;">
						<select id="filterDivisi" name="filterDivisi" class="filterDropdown">
							<option value='all'>ALL</option>
							<?php 
							for($i=0;$i<count($divisions);$i++) {
								echo("<option value='".str_replace("&","",$divisions[$i]["DIVISI"])."'>".$divisions[$i]["DIVISI"]."</option>");
							} ?>
						</select>
					</td>
					<td style="background-color:#990033;">
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
					<td class='hideOnMobile' style="background-color:#990033;">
						<select id="filterKategori" name="filterKategori" class="filterDropdown">
							<option value='all'>ALL</option>
							<option value='GOLD'>GOLD</option>
							<option value='SILVER'>SILVER</option>
						</select>					
					</td>
					<td colspan="2" class='hideOnMobile' style="background-color:#990033;">&nbsp;</td>
				</tfilter>
				<tbody id="tbodyBarangInsentif">
				</tbody>
			</table>
		</div>
	</div>
	<div class="clearfix" style="height:60px"></div>

<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<!--div style='margin:10px;float:left;'>
				<div class="btn" id="btnSubmit" name="btnSubmit">Simpan</div>
			</div-->
			<!--div style='margin:10px;float:left;'>
				<a href="<?php //echo(base_url('ExportData/KategoriInsentif'));?>">
				<div class="btn" id="btnExport" name="btnExport">Export</div>
				</a>
			</div-->
			<div style='margin:10px;float:left;'>
				<font color="white">VIEW: </font>
				<select id="OptView" name="OptView">
					<option value='0'>Tampilkan Hanya Status Terakhir Setiap Produk</option>
					<option value='1'>Tampilkan Semua Status Untuk Setiap Produk</option>
				</select>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>