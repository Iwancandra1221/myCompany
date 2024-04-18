<style>			
	.table{
	font-size:10px;
	}
	
	.table tr:nth-child(even) {background-color: #f8f8f8;}
	
	table tr{
	font-size: 12px;
	}
	
	table tr td{
	padding:3px;
	}
</style>
<div class="container">
	<h3><center>FOTO DISPLAY PRODUK</center></h3>
	<div class="row">
		<div class="col-sm-6" id="left-filter-box" style="display:none;">	
			<?php echo form_open_multipart('spreadsheet/export', array('target'=>'_blank')); ?>
			<table align="center" cellpadding="5" width="100%">
				<tr>
					<td valign="top" width="25%">Cabang :</td>
					<td valign="top" width="*">
						<select name="cabang" id="filter_cabang">
							<?php
								if($_SESSION["logged_in"]['branch_id']=='JKT'){
									// if(count($cabang)>0){
									echo "<option value=''>ALL</option>";										
								}
							?>
							<?php
								foreach($cabang as $cbg){
									echo "<option value='".$cbg->Cabang."'>".$cbg->Cabang."</option>";
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td valign="top">Nama MD: </td>
					<td valign="top">
						<select name="namamd" id="filter_namamd">
							<option value="">ALL</option>
						</select>
					</td>
				</tr>
				<tr style="display:none;">
					<td valign="top"></td>
					<td valign="top">
						<input type="submit" name="exportSubDealer" id="exportSubDealer" value="Export SubDealer">
						<input type="submit" name="exportMarketSurvey" id="exportMarketSurvey" value="Export Market Survey">
					</td>
				</tr>
			</table>
			<?php echo form_close();?>
		</div>
		<div class="col-sm-6" id="right-filter-box">
			<?php //echo form_open_multipart('spreadsheet/import',array('name' => 'spreadsheet','target' => '_blank')); ?>
			
			<table align="center" cellpadding = "5" width="100%" >
				<tr style="display:none;">
					<td valign="top" >Filter By : </td>
					<td valign="top" colspan="3">
						<input type="radio" name="filter" id="f_kotamadya" value="kotamadya" onclick="javascript:filter_per_kotamadya()" checked> <label for="f_kotamadya">Per Kotamadya/Kabupaten</label>
						<br>
						<input type="radio" name="filter" id="f_gabungan" value="gabungan" onclick="javascript:filter_gabungan()"> <label for="f_gabungan">Gabungan Per MD</label>
						<br>
						<br>
					</td>
				</tr>
				<tr>
					<td valign="top" width="25%">Tgl. Awal : </td>
					<td valign="top" width="25%"><input type="input" id="dp1" placeholder="mm/dd/yyyy" name="dp1" autocomplete="off" /></td>
					<td valign="top" width="25%">Tgl. Akhir : </td>
					<td valign="top" width="25%"><input type="input" id="dp2" placeholder="mm/dd/yyyy" name="dp2" autocomplete="off" /></td>
				</tr>
				<tr>
					<td valign="top"></td>
					<td valign="top" colspan="3">
						<input type="button" onclick="javascript:filter_data()" value="Filter Tanggal">
						<input type="button" onclick="javascript:reset_tanggal()" value="Reset">
					</td>
				</tr>
			</table>
			<?php //echo form_close();?>
		</div>
	</div>
	<table id="table" class="table table-bordered">
		<thead>
			<tr>
				<th width="5px">NO</th>
				<th width="15px">CABANG</th>
				<th width="25px">NAMA MD</th>
				<th width="15px">JUMLAH FOTO DISPLAY</th>
				<th width="15px">LASTUPDATE</th>
				<th width="15px">VIEW</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>



<script type="text/javascript">
	$(document).ready(function(){
		
		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		
		$('#dp2').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		
		filter_data();
	});
	
	
	function filter_data(){		
		var dp1  = $('#dp1').val();
		var dp2  = $('#dp2').val();
		
		if(dp1!='' || dp2!=''){
			if(dp1=='' || dp2==''){	
				alert('Tanggal wajib diisi semua!');
				return;
			}
		}
		
		var postForm = {
			'dp1' : dp1,
			'dp2' : dp2,
		};
		
		$('#table tbody').html('<tr><td colspan="9" align="center">Loading...</td></tr>');
		$.ajax({
			url:'GetListFotoDisplay',
			type:'POST',
			dataType  : 'json',
			data      : postForm, //Forms name
			success:function(msg){
				if(msg.length>0){
					$('#table tbody').html('');
					var no = 0;					
					var fd = 0;
					
					for(var i =0;i < msg.length;i++)
					{
						var item = msg[i];
						
						var row = '<tr>';
						no++;
						row+='<td>'+no+'</td>';
						row+='<td>'+item.CabangMD+'</td>';
						row+='<td>'+item.NamaMD+'</td>';
						row+='<td align="center">'+item.JumlahFotoDisplay+'</td>';
						row+='<td>'+item.LastUpdate+'</td>';
						row+='<td><a href="ViewFotoDisplay?Cabang='+encodeURIComponent(item.CabangMD)+'&NamaMD='+encodeURIComponent(item.NamaMD)+'&fd='+encodeURIComponent(item.JumlahFotoDisplay)+'&awal='+encodeURIComponent(item.TglAwal)+'&akhir='+encodeURIComponent(item.TglAkhir)+'" target="_blank">VIEW</a></td>';
						$('#table').append(row);
						fd = fd + item.JumlahFotoDisplay;
					}
					$('#table tbody').append('<tr><td colspan="3" align="center">Jumlah</td><td align="center">'+fd+'</td><td colspan="2"></td></tr>');
				}
				else{
					$('#table tbody').html('<tr><td colspan="9" align="center">Tidak ada data.</td></tr>');
				}
			}
		});
		
	}
	
	function reset_tanggal(){
		$('#dp1').val('');
		$('#dp2').val('');
	}
</script>
