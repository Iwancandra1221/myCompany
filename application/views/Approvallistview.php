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
<script>
	$(document).ready(function() {
		// $('#dp1').datepicker({
		// 	format: "dd-M-yyyy",
		// 	autoclose: true,
		// });
	});
</script>

<div style="display:<?php if(!$this->session->flashdata('error')) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="login-alert" class="alert alert-danger col-sm-12">
  <!-- error msg here -->
  <?php 
    echo $this->session->flashdata('error'); 
    if(isset($_SESSION['error'])){
        unset($_SESSION['error']);
    }
  ?>
</div>
<div style="display:<?php if(!$this->session->flashdata('info')) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="info-alert" class="alert alert-success col-sm-12">
  <?php 
    echo $this->session->flashdata('info'); 
    if(isset($_SESSION['info'])){
        unset($_SESSION['info']);
    }
  ?>
</div> 

<?php

if ($msg != "")
{
    echo '<script>alert("' . $msg . '");</script>';
}


?>

<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
		<div class="row">
			<div class="col-12">
				<table id="tblRequests" class="table table-bordered" style="width:100%;">
					<thead>
						<tr>
							<th width="5%">NO</th>
							<th width="20%">JENIS REQUEST</th>
							<th width="30%">REQUEST</th>
							<th width="30%">INFO</th>
							<th width="15%" class="no-sort">VIEW</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>


</div> <!-- /container -->


<script type="text/javascript">
	$(document).ready(function() {
	    $('#tblRequests').dataTable( {
	        "bProcessing": true,
	        "bServerSide": true,
	         "columnDefs": [
		      { targets: 'no-sort', orderable: false },
		      { targets: 'col-hide', visible: false }
		      ],
	        "sAjaxSource": '<?php echo site_url('approvallist/list') ?>',
	        "oLanguage": {
		        "sLengthMenu": "Menampilkan _MENU_ Data per halaman",
		        "sZeroRecords": "Maaf, Data tidak ada",
		        "sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
		        "sInfoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
		        "sSearch": "",
		        "sInfoFiltered": "",
		        "oPaginate": {
			       	"sPrevious": "Sebelumnya",
			        "sNext": "Berikutnya"
		    	}
		    }
	    });
	});
</script>