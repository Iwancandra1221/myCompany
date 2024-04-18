<style>
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
					var opt;
					if($('#rad_wilayah').is(':checked')){
						opt ='wilayah';
					}
					else{
						opt ='toko';
					}
					
					$(".loading").show();
					$.post("<?php echo site_url('MsPengaliLimit/ExportKeCabang'); ?>", {
						db 		: db,
						opt 	: opt
					}, function(data){
						if (data.result=="gagal") {
							result = "GAGAL : "+data.error;
						} else {
							result = "SUKSES";
						}
						$("#res"+db).text(data.result);
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
	<div class="title">Export Pengali Limit <?php echo $opt; ?></div>
	<div class="form">
		<?php echo form_open('MsPengaliLimit/Export', array("id"=>"FormExport")); ?>	
		
		
		<input type="radio" name="opt" id="rad_wilayah" value="WILAYAH" checked> <label for="rad_wilayah" style="cursor:pointer">Pengali Limit By Wilayah</label>
		<input type="radio" name="opt" id="rad_toko" value="TOKO"> <label for="rad_toko" style="cursor:pointer">Pengali Limit By Toko</label>
		<br>
		<br>
		
		<input type="hidden" name="opt" id="opt" value="<?php echo $opt; ?>">
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