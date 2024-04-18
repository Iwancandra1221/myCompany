<style>
</style>
<script>
	var mode='';
	$(document).ready(function(){
		$(".btnView").click(function(){
			idGroup = $(this).attr("id");
			$("#list-group-divisi").hide();
			$("#content-group-divisi").show();
		});

		$(".btnMapping").on('click', function(e){
			var KdSlsman = $(this).attr("data");
			popupWindow(KdSlsman);
		});		

		$("#btnSubmit").click(function(){
			$("#FormSalesManager").submit();
		});

		$("#btnCancel").click(function(){
			$(".ClosePopUp").click();
		});
	});

    function popupWindow(id, kode){
       window.open('DataPicker/PickEmployee?id='+encodeURIComponent(id),
       	'popuppage','width=800,toolbar=1,resizable=1,scrollbars=yes,height=600,top=0,left=100');
    }
    
    function updateValue(id, EmployeeID, EmployeeName, Email)
    {
		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('MsSalesman/MappingSalesman'); ?>", {
			KodeSalesman 	: id,
			NamaSalesman	: $("#col_nama_"+id).text(),
			LevelSalesman	: $("#col_level_"+id).text(),
			UserEmail 		: EmployeeID,
			csrf_bit		: csrf_bit
		}, function(data){
			if (data.result == "sukses") {
				$("#col_user_"+id).text(EmployeeID);
				alert("Mapping Berhasil");
			} else {
				alert("Mapping Gagal : "+data.error);
			}
		},'json',errorAjax);		
		$(".loading").hide();     
    }	
</script>

<div class="container">
	<div class="form_title" align="right"><h4>GROUP DIVISI</h4></div>
	<div id="list-group-divisi" class="clearfix">
		<table id="tblGroupDivisi" class="table table-striped table-bordered" cellspacing="0">
			<thead>
				<tr>
					<th class='hideOnMobile'>No</th>
					<th class='hideOnMobile'>ID Group Divisi</th>
					<th>Nama Group Divisi</th>
					<th class="hideOnMobile">Kategori Insentif</th>
					<th class="hideOnMobile">Aktif</th>
					<th class="hideOnMobile">NonAktif</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$No = 1;
			for($i=0;$i<count($divisi);$i++)
			{
				$ID = $divisi[$i]["ID_GROUPDIVISI"];
				echo("<tr>");
				echo("	<td class='hideOnMobile'>".$No."</td>");
				echo("	<td id='".$ID."id' class='hideOnMobile'>".$ID."</td>");
				echo("	<td id='".$ID."nama'>".$divisi[$i]["NAMA_GROUPDIVISI"]."</td>");
				echo("	<td id='".$ID."katins' class='hideOnMobile'>".$divisi[$i]["KATEGORI_INSENTIF"]."</td>");
				echo("	<td id='".$ID."aktif'  class='hideOnMobile'>".$divisi[$i]["TAHUN_AKTIF"]."-".(($divisi[$i]["BULAN_AKTIF"]<10)?"0":"").$divisi[$i]["BULAN_AKTIF"]."</td>");
				echo("	<td id='".$ID."naktif' class='hideOnMobile'>".$divisi[$i]["TAHUN_NONAKTIF"]."-".(($divisi[$i]["BULAN_NONAKTIF"]<10)?"0":"").$divisi[$i]["BULAN_NONAKTIF"]."</td>");
				echo("	<td><button class='btn btnView' data=".$ID.">View</button></td>");
				echo("</td>");
				$No += 1;
			}?>
			</tbody>
		</table>
	</div>
	<div id="content-group-divisi">
		<div><div id="btnSubGroup">CREATE SUBGROUP</div></div>

	</div>

	<div class="PopUpForm" id="PopUpForm">
		<div class="overlay"></div>
		<div class="loadingItem">
		    <?php echo form_open('MsSalesman', array("id"=>"FormSalesman")); ?>  
			<form style="width:450px; height:420px;">
				<i class="fa fa-times ClosePopUp"></i>
				<div class="popupform_title" id="formTitle"></div>
				<div><input type="hidden" id="SaveMode" name="SaveMode" readonly></div>
				<div class="formInputAjax">
					<label>Kode Salesman</label>
					<label><input type="text" id="txtKodeSalesman" name="txtKodeSalesman" readonly></label>
				</div>
				<div class="formInputAjax">
					<label>Karyawan</label>
					<label><input type="text" class="form-control" name="TxtUserEmail" id="TxtUserEmail" placeholder="ID Karyawan" required readonly></label>
				</div>			
				<div class="formInputAjax">
					<div class="btn" id="btnSubmit" name="btnSubmit">Simpan</div>
					<div class="btn" id="btnCancel" name="btnCancel">Batal</div>
				</div>
			</form>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>
<?php if (1==0) { ?>
<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
	<div style="clear:both;">
		<div style='margin:10px;float:left;'>
		<?php if($_SESSION["can_create"]==1) { ?><div class="btn btnAdd" id="btnAdd">Tambah</div><?php } ?>
		</div>
	</div>
</div>
<?php } ?>