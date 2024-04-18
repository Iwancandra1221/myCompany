<div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
	<div style="padding: 5px;">
		<?php
			if (isset($_GET['success'])) {
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Updated <strong>Successfully !</strong></div>";
			}
		?>
	</div>
</div>

<div class="container">
	<div class="page-title">EXPORT PAJAK MASUKAN</div>
	<form action="" method="POST">
		<div class="form-container">
			<div class="row">
				<div class="col-12" align="center">
	        		<table border="0" width="500px">
			            <tr>
			            	<td valign="top">
			            		Dari
			            	</td>
			            	<td>
			            		<input type="text" name="dari" id="dari" class="form-control"><br>
			            	</td>
			            </tr>
			            <tr>
			            	<td valign="top">
			            		Sampai
			            	</td>
			            	<td>
			            		<input type="text" name="sampai" id="sampai" class="form-control">
			            	</td>
			            </tr>
			            <tr>
			            	<td colspan="2">
			            		<div class="col-6 text-center">
			            			<input type="checkbox" name="product" value="1" checked> Produk
			            		</div>
			            		<div class="col-6 text-center">
			            			<input type="checkbox" name="sparepart" value="1" checked> Sparepart
			            		</div>
			            	</td>
			            </tr>
			            <tr>
			            	<td colspan="2">
			            		<button class="btn btn-default" name="export" style="width:100%">
			            			EXPORT
			            		</button>
			            	</td>
			            </tr>
			        </table>
			    </div>
			</div>
		</div>
	</form>
</div>

<script>
  $(document).ready(function() {
    $('#dari').datepicker({
      format: "dd-mm-yyyy",
      autoclose: true
    });

    $('#sampai').datepicker({
      format: "dd-mm-yyyy",
      autoclose: true
    });

  });
</script>
