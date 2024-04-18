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
		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		// $('#ex_cash').prop('checked', true);
		// $('#ex_bass').prop('checked', true);
		// $("#grup_subkategori").prop("checked", false);
		// $(".opt_produk").hide();
		// $(".opt_wilayah").hide();
		
		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		
		
		
	});
</script>
<div class="">
  <?php echo form_open($formDest, array("target"=>"_blank")) ?>
	 <div class="form-container" style="height:500px!important;">
      <div class="row">
        <div class="col-3">Grouping</div>
        <div class="col-9 col-m-8">
            <input type="radio" name="grouping" id="groupingDG" value="gudang" onclick="javascript:group('gudang')" checked> <label for="groupingDG">Per Gudang</label> &nbsp;&nbsp;&nbsp;
            <input type="radio" name="grouping" id="groupingGD" value="cabang" onclick="javascript:group('cabang')"> <label for="groupingGD">Per Cabang</label>
        </div>
      </div>
      <div class="row">
        <div class="col-3">Tanggal</div>
		
		<div class="input-group col-4 col-md-4">
          <input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="tanggal" value="<?php echo date('m/d/Y') ?>" required autocomplete="off">
			<span class="input-group-addon" onclick="javascript:showDP()"><span class="glyphicon glyphicon-calendar"></span></span>
		</div>
		
      </div>
      <div class="row">
         <div class="col-3">
            Kategori Barang
         </div>
         <div class="col-9 col-md-8">
            <input type="radio" name="kategori" id="kategoriP" value="P" checked> <label for="kategoriP">Produk</label> &nbsp;&nbsp;&nbsp;
            <input type="radio" name="kategori" id="kategoriS" value="S"> <label for="kategoriS">Sparepart</label>
         </div>
      </div>
      <div class="row" id="div_cabang">
         <div class="col-3">Cabang</div>
         <div class="col-9 col-md-8">
            <select name="cabang" id="cabang" class="form-control">
              <option value='ALL'>ALL</option>
              <?php 
				  for($i=0;$i<count($cabang);$i++)
				  {
					echo("<option value='".$cabang[$i]["Kd_Lokasi"]."'>".$cabang[$i]["Nm_Lokasi"]."</option>");
				  }
              ?>
            </select>
         </div>
      </div>
      <div class="row" id="div_gudang">
         <div class="col-3">Gudang</div>
         <div class="col-9 col-md-8">
            <select name="gudang" id="gudang" class="form-control">
              <option value='ALL'>ALL</option>
              <?php 
				  for($i=0;$i<count($gudang);$i++)
				  {
					echo("<option value='".$gudang[$i]["Kd_Gudang"]."'>".$gudang[$i]["Kd_Gudang"]." - ".$gudang[$i]["Nm_Gudang"]."</option>");
				  }
              ?>
            </select>
         </div>
      </div>
      <div class="row" id="rowDivisi">
         <div class="col-3">
            Divisi
         </div>
         <div class="col-9 col-md-8">
            <select name="divisi" id="divisi" class="form-control">
              <option value='ALL'>ALL</option>
              <?php 
              for($i=0;$i<count($divisi);$i++)
              {
                echo("<option value='".$divisi[$i]["DIVISI"]."'>".$divisi[$i]["DIVISI"]."</option>");
              }
              ?>
            </select>
         </div>
      </div>
      <div class="row" align="center" style="padding-top:50px;">
         <input type = "submit" name="btnPreview" value="PREVIEW"/>
          <input type = "submit" name="btnExcel" value="EXCEL"/>
      </div>
    </div>
	<?php echo form_close(); ?>
</div> <!-- /container -->

<script type="text/javascript">
	
	function showDP(){
		$('#dp1').datepicker('show');
	}
	
	function group(g){
		if(g=='gudang'){
			$('#div_gudang').show();
			$('#div_cabang').hide();
		}
		else{
			$('#div_cabang').show();
			$('#div_gudang').hide();
		}
	}
	$('#div_cabang').hide();
	 
	  
	  
	  
 
</script>


