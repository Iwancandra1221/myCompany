<style type="text/css">
	.row {
    line-height:30px; 
    vertical-align:middle;
    clear:both;
	}
	.row-label, .row-input {
    float:left;
	}
	.row-label {
    padding-left: 15px;
    width:180px;
	}
	.row-input {
    width:420px;
	}
</style>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        echo form_open_multipart("ReportStock/Preview", array("target"=>"_blank"));
	?>
	<div class="form-container">
		<div class="row">
			<div class="col-3 col-m-3">KANTOR<br><small>Report Stock Total</small></div>
			<div class="col-9 col-m-8">
				<input type="file" class="form-control" name="kantor" accept=".xlsx" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-3">GUDANG<br><small>Laporan Berdasarkan Tgl Faktur</small></div>
			<div class="col-9 col-m-8">
				<input type="file" class="form-control" name="gudang" accept=".xlsx" required>
				<em>Hanya mendukung excel format .xlsx</em>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-3"></div>
			<div class="col-9 col-m-8">
			<input type="submit" name="btnPreview" value="PREVIEW"/>
			<input type="submit" name="btnExcel" value="EXCEL"/>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->
