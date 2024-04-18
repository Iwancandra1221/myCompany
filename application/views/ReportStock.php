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
	input{
		color: black;
	}
  
    .align-right {
        text-align: right;
    }
</style>

<div class="container">
  	<div class="page-title"><?php echo($opt);?></div>
  
  		<?php echo form_open('ReportStock/ProsesStockQuickCount', array("target"=>"_blank")); ?>
			<div class="form-container">
			    <div class="row">
			      <div class="col-3 col-m-4" align="right">Tanggal</div>
			      <div class="col-9 col-m-8">
			        <!-- <input type="text" class="form-control" id="tanggal" placeholder="yyyy-mm-dd" name="tanggal" required> -->
			        <select  class="form-control" id="tanggal" name="tanggal" required>
			        	<option value="">Pilih Tanggal</option>
			        	<?php
			        		$tamp_tgl=array();
					    	foreach ($Wilayah as $key => $w) {
					    		$tamp_tgl[]= date_format(date_create($w['tanggal']),'Y-m-d');
					    	}

					    	$hapus=array_unique($tamp_tgl);
					    	for($i=0; $i<count($Wilayah); $i++){
					    		if(!empty($hapus[$i])){
					    ?>
					    			<option value="<?php echo $hapus[$i]; ?>"><?php echo $hapus[$i]; ?></option>
					    <?php
					    		}
					    	}
					    ?>
			        </select>
			      </div>
			    </div>

			    <div class="row">
			      <div class="col-3 col-m-4" align="right">Divisi</div>
			      <div class="col-9 col-m-8">
			        <select class="form-control" name="divisi">
			        	<option value="ALL">Semua Divisi</option>
			        	<?php
					    	foreach ($Divisi as $key => $d) {
					    ?>
					    		<option value="<?php echo $d['divisi']; ?>"><?php echo $d['divisi']; ?></option>
					    <?php
					    	}
					    ?>
			        </select>
			      </div>
			    </div>

			    <div class="row">
			      <div class="col-3 col-m-4" align="right">Merk</div>
			      <div class="col-9 col-m-8">
			        <select class="form-control" name="merk">
			        	<option value="ALL">Semua Merk</option>
			        	<?php
					    	foreach ($Merk as $key => $m) {
					    ?>
					    		<option value="<?php echo $m['merk']; ?>"><?php echo $m['merk']; ?></option>
					    <?php
					    	}
					    ?>
			        </select>
			      </div>
			    </div> 
			    
			    <div class="row">
			      <div class="col-3 col-m-4" align="right">Jenis Barang</div>
			      <div class="col-9 col-m-8">
			        <select class="form-control" name="jenisbarang">
			        	<option value="ALL">Semua Jenis Barang</option>
			        	<?php
					    	foreach ($JenisBarang as $key => $jb) {
					    ?>
					    		<option value="<?php echo $jb['jns_brg']; ?>"><?php echo $jb['jns_brg']; ?></option>
					    <?php
					    	}
					    ?>
			        </select>
			      </div>
			    </div>    

			    <div class="row">
			      <div class="col-3 col-m-4" align="right">Kode Barang</div>
			      <div class="col-9 col-m-8">
			        <select class="form-control" name="kodebarang">
			        	<option value="ALL">Semua Kode Barang</option>
			        	<?php
					    	foreach ($KodeBarang as $key => $kb) {
					    ?>
					    		<option value="<?php echo $kb['kd_brg']; ?>"><?php echo $kb['kd_brg']; ?></option>
					    <?php
					    	}
					    ?>
			        </select>
			      </div>
			    </div> 

			    <div class="row">
			      <div class="col-3 col-m-4" align="right">Wilayah</div>
			      <div class="col-9 col-m-8">
			        <select class="form-control" name="wilayah">
			        	<option value="ALL">Semua Wilayah</option>
			        	<?php
					    	foreach ($Wilayah as $key => $w) {
					    ?>
					    		<option value="<?php echo $w['wilayah']; ?>"><?php echo $w['wilayah']; ?></option>
					    <?php
					    	}
					    ?>
			        </select>
			      </div>
			    </div> 

			 
			    <div class="row" align="center">
			      <div class="col-12 col-m-12">

			        <input type="submit" class="btn" name="btnExcel" value="EXCEL"/>

			      </div>
			    </div>
		 	</div>
		<?php echo form_close(); ?>
			<div class="form-container" style="background-color:#FFFFFF">
			    <div class="row">
			    	<div class="col-12" style="overflow-x:auto; height:300px">
				    	<table class="table table-hover" style="color:#303331">
				    		<thead>
					    		<tr>
					    			<td align="center" width="80px">
					    				<b>
					    					No
					    				</b>
					    			</td>
					    			<td>
					    				<b>
					    					Wilayah
					    				</b>
					    			</td>
					    			<td>
					    				<b>
						    				Tanggal
						    			</b>
					    			</td>
					    			<td>
					    				<b>
						    				Cabang
						    			</b>
					    			</td>
					    		</tr>
					    	</thead>
					    	<tbody>
							    <?php
							    $no = 1;
							    foreach ($Wilayah as $key => $w) {
							    ?>
							        <tr>
							            <td align="center">
							                <?php echo $no; ?>
							            </td>
							            <td>
							                <?php echo $w['wilayah']; ?>
							            </td>
							            <td>
							                <?php echo date_format(date_create($w['tgl_proses']), 'd/m/Y'); ?>
							            </td>
							            <td>
							                <?php echo $w['branch_code']; ?>
							            </td>
							            <td class="align-right"> 
							                <button onclick="handleButtonClick('<?php echo $w['wilayah']; ?>')">Tarik Data</button>
							            </td> 
							        </tr>
							    <?php
							        $no++;
							    }
							    ?>
							</tbody>
				    	</table>
				    </div>
			    </div>
			</div>
  	<div style='clear:both;height:20px;'></div>

</div> 

<script>
  $(document).ready(function(){
    $("#loading").hide();
    $("#disablingDiv").hide();
    <?php if(isset($error) && $error != '') echo 'alert("'.$error.'");';  ?>
  });

  function handleButtonClick(wilayah) {
    var formData = new FormData();
    formData.append('wilayah', wilayah); 
  	$(".loading").show();
    $.ajax({
        url: "<?=base_url()?>ReportStock/sync_data",
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
   					if (response === 'success') {
                alert('Tarik Data Berhasil');location.reload();
            } else {
                alert('Tarik Data Gagal');
            }
      			$(".loading").hide();
        }
    });
}
</script>