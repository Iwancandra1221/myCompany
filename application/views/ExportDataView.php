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
</style>
<script>
	<?php if(isset($err)) {
		echo("alert('".$err."');");
	} ?>

	$(document).ready(function(){
		$("#btnExport").click(function(){
			$(".res").text("");

			$('.chkBranch').each(function () {
				var result = "";
	           if (this.checked) {
	           		var db = $(this).val();
					$(".loading").show();
					var csrf_bit = $("input[name=csrf_bit]").val();
					$.post("<?php echo site_url('ExportData/ExportKategoriInsentif'); ?>", {
						db 		: db,
						csrf_bit: csrf_bit
					}, function(data){
						if (data.result=="gagal") {
							result = "GAGAL : "+data.error;
						} else {
							result = "SUKSES";
						}
						$("#res"+db).text(result);
						$(".loading").hide();	
					},'json',errorAjax);		
	           }
			});		
		});

		$("#chkAll").change(function(){
			if (this.checked) {
				$('.chkBranch').each(function(){
	                this.checked = true;
	            });
			} else {
				$('.chkBranch').each(function(){
	                this.checked = false;
	            })
			}
		});		
	});
</script>

<div class="container">
	<div class="title">Export Barang Kategori Insentif</div>
	<div class="form">
		<?php echo form_open('ExportData/ExportKategoriInsentif', array("id"=>"FormExport")); ?>	
		<div>	
			<table class="table table-striped table-bordered" cellspacing="0">
				<thead id="theadDB">
					<tr>
						<th><input type='checkbox' id='chkAll'></th>
						<th>Cabang</th>
						<th>Server</th>
						<th class='hideOnMobile'>Database</th>
						<th class='hideOnMobile'>URL API</th>
						<th>Result</th>
					</tr>
				</thead>
				<tbody id="tbodyDB">
				<?php 
				foreach($databases as $db) {
					echo("<tr>");
					echo("	<td><input type='checkbox' class='chkBranch' name='chkBranch' value='".$db->DatabaseId."'></td>");
					echo("	<td>".$db->BranchId."</td>");
					echo("	<td>".$db->Server."</td>");
					echo("	<td class='hideOnMobile'>".$db->Database."</td>");
					echo("	<td class='hideOnMobile'>".$db->AlamatWebService."</td>");
					echo("	<td class='res' id='res".$db->DatabaseId."'></td>");
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="clearfix" style="height:60px"></div>

	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<div style='margin:10px;float:left;'>
				<div class="btn" id="btnExport" name="btnExport">Export</div>
			</div>
	</div>
	<?php echo form_close(); ?>
</div>