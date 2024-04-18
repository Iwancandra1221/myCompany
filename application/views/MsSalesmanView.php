<style>
</style>
<script>
	var mode='';
	$(document).ready(function(){
		$("#tblSalesman").DataTable();
		/*$("#btnAdd").PopUpForm({
			target:'#PopUpForm',
			after:function()
			{
				$("#PopUpForm #formTitle").html("TAMBAH SALES MANAGER");
				$("#PopUpForm #SaveMode").val("add");
				$("#PopUpForm #DivisionID").val("");
				$("#PopUpForm #EmployeeID").val("");
				$("#PopUpForm #EmailAddress").val("");
			}
		});*/

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
	<div class="form_title">SALESMAN</div>
	<div class="clearfix">
		<table id="tblSalesman" class="table table-striped table-bordered" cellspacing="0">
			<thead>
				<tr>
					<th class='hideOnMobile'>No</th>
					<th class='hideOnMobile'>Kode Salesman</th>
					<th>Nama Salesman</th>
					<th class='hideOnMobile'>Level Salesman</th>
					<th class='hideOnMobile'>Wilayah</th>
					<th>UserID</th>
					<th>Mapping?</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$No = 1;
			for($i=0;$i<count($salesman);$i++)
			{
				echo("<tr>");
				echo("	<td class='hideOnMobile'>".$No."</td>");
				echo("	<td class='hideOnMobile' id='col_kode_".$salesman[$i]["KD_SLSMAN"]."'>".$salesman[$i]["KD_SLSMAN"]."</td>");
				echo("	<td id='col_nama_".$salesman[$i]["KD_SLSMAN"]."'>".$salesman[$i]["NM_SLSMAN"]."</td>");
				echo("	<td class='hideOnMobile' id='col_level_".$salesman[$i]["KD_SLSMAN"]."'>".$salesman[$i]["NAMA_LEVEL"]."</td>");
				echo("	<td class='hideOnMobile' id='col_wil_".$salesman[$i]["KD_SLSMAN"]."'>".$salesman[$i]["WILAYAH"]."</td>");
				echo("	<td id='col_user_".$salesman[$i]["KD_SLSMAN"]."'>".$salesman[$i]["USEREMAIL"]."</td>");
				echo("	<td><button class='btn btnMapping' data=".$salesman[$i]["KD_SLSMAN"].">MAPPING</button></td>");
				echo("</td>");
				$No += 1;
			}?>
			</tbody>
		</table>
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