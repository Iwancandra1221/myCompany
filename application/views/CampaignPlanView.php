
<style type="text/css">
	.scroll{
		height:500px;
		overflow-y:auto;
		width: 100%;
	}
</style>


	<div class="col-12" align="center" style="margin-top:50px;">
		<div class="page-title" align="center">ANTRIAN RENCANA PO</div>
	</div>

<div class="col-1"></div>
<div class="col-10">


	<div class="col-12" align="right">
		<?php
			if($akses_edit==1 && $mode!=='edit'){
		?>
				<a href="<?php echo site_url('CampaignPlanView/edit/'.$id.'/'.$type); ?>">
					<button class="btnEdit">Ubah</button>
				</a>
		<?php
			}
		?>
				<a href="<?php echo site_url('CampaignPlanView'); ?>">
					<button class="btnEdit">Kembali</button>
				</a>

			</div>


	<table class="table table-striped" summary="table">
		<tr>
			<td width="200px">ID Transaksi</td>
			<td width="10px">:</td>
			<td id="number"></td>
		</tr>
		<tr>
			<td>Keterangan</td>
			<td>:</td>
			<td id="keterangan"></td>
		</tr>
		<tr>
			<td>Divisi</td>
			<td>:</td>
			<td id="divisi"></td>
		</tr>
		<tr>
			<td>Status</td>
			<td>:</td>
			<td id="status"></td>
		</tr>
		<tr>
			<td>Tipe</td>
			<td>:</td>
			<td id="tipe"></td>
		</tr>
		<tr>
			<td colspan="3"></td>
		</tr>
	</table>



		<table class="table table-striped" summary="table">
			<tr>
				<td width="200px">
					<select class="form-control" id="search_periode" onchange="barang()">
						<option value="">Periode</option>
					</select>
				</td>
				<td width="200px">
					<select class="form-control" id="search_barang" onchange="wilayah()">
						<option value="">ALL Barang</option>
					</select>
				</td>
				<td width="200px">
					<select class="form-control" id="search_wilayah" onchange="list_detail()">
						<option value="">ALL Wilayah</option>
					</select>
				</td>
				<td width="200px">
					<select class="form-control" id="search_status" onchange="list_detail()">
						<option value="">ALL Status</option>
						<option value="UNPROCESSED">UNPROCESSED</option>
						<option value="PROCESSED">PROCESSED</option>
						<option value="CANCELLED">CANCELLED</option>
					</select>
				</td>
				<td>
					<button type="submit" class="btnSave" onclick="list_detail();">Cari</button>
				</td>
				<?php
					if($mode=='edit'){
				?>
					<td align="right">
						<button type="submit" class="btnSave" onclick="save_data();">Save</button>
					</td>
				<?php
					}else{
				?>
						<td></td>
				<?php
					}
				?>
			</tr>
		</table>




	<div class="scroll">
		<table class="table table-striped">
			
		   <thead style="background-color: #eaeaea">
		      <tr>
		        <td width="50px" align="center">No</td>
		        <td>Kode Barang</td>
		        <td>Periode</td>
		        <td>Wilayah</td>
		        <td>Group Gudang</td>
		        <td>Qty</td>
		        <td>PO Major / PORO</td>
		        <td>No Prepo</td>
		        <td align="center"><input type="checkbox" name="checked_all" id="checked_all"> Cancel</td>
		      </tr>
		    </thead>
		    <tbody id="datadetail">
		    	<tr>
		    		<td colspan="9" align="center" style="background-color:#FFFFFF;">
		    			<img src='<?php echo base_url("images/loading.gif") ?>' width="500px">
		    		</td>
		    	</tr>
		    </tbody>

		</table>
	</div>

</div>
<div class="col-1"></div>


<script type="text/javascript">

	const bulan_alf = ["","Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Ssep","Okt","Nov","Des"];
	$.ajax({
	    url: '<?php echo base_url('CampaignPlan')."/listhead/".$id."/".$type; ?>',
	    type: 'GET',
	    dataType: 'json',
	    success: function(data) {
	        if (data == 0) {
	            window.location.href = "<?php echo site_url('CampaignPlanView'); ?>";
	        } else {
	            document.getElementById('number').innerHTML = data[0].CampaignID;
	            document.getElementById('keterangan').innerHTML = data[0].CampaignName;
	            document.getElementById('divisi').innerHTML = data[0].Divisi;
	            document.getElementById('status').innerHTML = 'APPROVED (' + data[0].ApprovedBy + ' ' + data[0].ApprovedDate + ')';
	            document.getElementById('tipe').innerHTML = data[0].TypeTrans;
	        }
	    },
	    error: function(jqXHR, textStatus, errorThrown) {
	        console.log("Ajax request failed: " + textStatus + ", " + errorThrown);
	    }
	});


	var periode='';


	$.ajax({
	    url: '<?php echo base_url('CampaignPlan')."/Get_priod/".$id."/".$type; ?>',
	    type: 'GET',
	    dataType: 'json',
	    success: function(data) {

	        var periode = '';

	        for (var i = 0; i < data.length; i++) {
	            periode += '<option data-tahun_select="' + data[i].Tahun + '" data-bulan_select="' + data[i].Bulan + '" data-priod_select="' + data[i].Periode + '">P' + data[i].Periode + ' ' + bulan_alf[data[i].Bulan] + ' ' + data[i].Tahun + '</option>';
	        }

	        if (data == 0) {
	        	periode = '<option value="">Data periode tidak ditemukan</option>'
	        }

	        $("#search_periode").html(periode);
	        $("#search_barang").html('<option value="">Loading</option>');
	        $("#search_wilayah").html('<option value="">Loading</option>');

	        barang();
	    },
	    error: function(xhr, status, error) {
	        console.error('Error fetching data:', status, error);
	    }
	});


		

	function barang() {
	    const tahun_select = $('#search_periode option:selected').data('tahun_select');
		const result_tahun = tahun_select !== null && tahun_select !== undefined ? btoa(tahun_select).replace(/=/g, "") : "";

		const bulan_select = $('#search_periode option:selected').data('bulan_select');
		const result_bulan = bulan_select !== null && bulan_select !== undefined ? btoa(bulan_select).replace(/=/g, "") : "";

		const priod_select = $('#search_periode option:selected').data('priod_select');
		const result_priod = priod_select !== null && priod_select !== undefined ? btoa(priod_select).replace(/=/g, "") : "";

	    // const barangValue = btoa(document.getElementById('search_barang').value).replace(/=/g, "");

	    // const wilayahValue = btoa(document.getElementById('search_wilayah').value).replace(/=/g, "");

	    // const statusValue = btoa(document.getElementById('search_status').value).replace(/=/g, "");

	    const initialOption = '<option value="">ALL Barang</option>';

	    const apiUrl = '<?php echo base_url('CampaignPlan')."/Get_Barang/".$id."/".$type."/"; ?>'+result_tahun+'/'+result_bulan+'/'+result_priod;
			  
	    $.ajax({
	        url: apiUrl,
	        type: 'GET',
	        dataType: 'json',
	        success: function(data) {
	        	if(data==0){
	        		$("#search_barang").html('<option value="">Data barang tidak ditemukan</option>');
	        	}else{
		            let barangOptions = initialOption;

		            for (let i = 0; i < data.length; i++) {
		                barangOptions += '<option value="' + data[i].Kd_Brg + '">' + data[i].Kd_Brg + '</option>';
		            }

		            $("#search_barang").html(barangOptions);
		            $("#search_wilayah").html('<option value="">Loading</option>');
		        }
		            wilayah();
	        },
	        error: function(xhr, status, error) {
	            console.error('Error fetching data:', status, error);
	        }
	    });
	}



	function wilayah(){
		var wilayahOptions='<option value="">ALL Wilayah</option>';

	    const tahun_select = $('#search_periode option:selected').data('tahun_select');
		const result_tahun = tahun_select !== null && tahun_select !== undefined ? btoa(tahun_select).replace(/=/g, "") : "";

		const bulan_select = $('#search_periode option:selected').data('bulan_select');
		const result_bulan = bulan_select !== null && bulan_select !== undefined ? btoa(bulan_select).replace(/=/g, "") : "";

		const priod_select = $('#search_periode option:selected').data('priod_select');
		const result_priod = priod_select !== null && priod_select !== undefined ? btoa(priod_select).replace(/=/g, "") : "";

		var barang_select = btoa(document.getElementById('search_barang').value).replace(/=/g, "");

		var wilayah_select = btoa(document.getElementById('search_wilayah').value).replace(/=/g, "");

		var status_select = btoa(document.getElementById('search_status').value).replace(/=/g, "");
		
				
	    const apiUrl = '<?php echo base_url('CampaignPlan/Get_Wilayah?api=APITES').'&number='.$id.'&type='.$type; ?>&tahun=' + result_tahun + '&bulan=' + result_bulan + '&priod=' + result_priod + '&barang=' + barang_select + '&wilayah=' + wilayah_select + '&status=' + status_select;
		$.ajax({
		    url: apiUrl,
		    type: 'GET',
		    dataType: 'json',
		    success: function(data) {

		    	if(data==0){
	        		$("#search_wilayah").html('<option value="">Data wilayah tidak ditemukan</option>');
	        	}else{

			        for (var i = 0; i < data.length; i++) {
			        	if(data[i].Wilayah!=null){
			            	wilayahOptions += '<option value="' + data[i].Wilayah + '">' + data[i].Wilayah + '</option>';
			            }
			        }

			        $("#search_wilayah").html(wilayahOptions);

			    }
			        list_detail();
		    },
		    error: function(xhr, status, error) {
		        console.error('Error fetching data:', status, error);
		    }
		});


	}

	function list_detail(){ 

		$("#datadetail").html('<tr><td colspan="9" align="center" style="background-color:#FFFFFF;"><img src="<?php echo base_url("images/loading.gif") ?>" width="500px"></td></tr>');

	    const tahun_select = $('#search_periode option:selected').data('tahun_select');
		const result_tahun = tahun_select !== null && tahun_select !== undefined ? btoa(tahun_select).replace(/=/g, "") : "";

		const bulan_select = $('#search_periode option:selected').data('bulan_select');
		const result_bulan = bulan_select !== null && bulan_select !== undefined ? btoa(bulan_select).replace(/=/g, "") : "";

		const priod_select = $('#search_periode option:selected').data('priod_select');
		const result_priod = priod_select !== null && priod_select !== undefined ? btoa(priod_select).replace(/=/g, "") : "";

		var barang_select = btoa(document.getElementById('search_barang').value).replace(/=/g, "");

		var wilayah_select = btoa(document.getElementById('search_wilayah').value).replace(/=/g, "");

		var status_select = btoa(document.getElementById('search_status').value).replace(/=/g, "");

		if(result_tahun!=='dW5kZWZpbmVk'){
	    	
	    	const apiUrl = '<?php echo base_url('CampaignPlan/Get_list?api=APITES').'&number='.$id.'&type='.$type; ?>&tahun=' + result_tahun + '&bulan=' + result_bulan + '&priod=' + result_priod + '&barang=' + barang_select + '&wilayah=' + wilayah_select + '&status=' + status_select;

			$.ajax({
			    url: apiUrl,
			    type: 'GET',
			    dataType: 'json',
			    success: function(data) {
			        var html = '';

			        if (data == 0) {
			            html = '<tr><td colspan="9">Data Tidak Ada</td></tr>';
			        } else {
			            var no = 1;

			            for (var i = 0; i < data.length; i++) {
			                var disabled = '';
			                var No_PrePo = '';
			                var cancel = 'cancel';
			                var data_get = 'data-kdbrg="' + data[i].Kd_Brg + '" data-priod="' + data[i].Periode + '" data-bulan="' + data[i].Bulan + '" data-tahun="' + data[i].Tahun + '" data-kdgudang="' + data[i].Kd_GroupGudang + '"';
			                var trk_id = data[i].Kd_Brg + data[i].Periode + data[i].Bulan + data[i].Tahun + data[i].Kd_GroupGudang;
			                var acak_id = btoa(trk_id).replace("=", "");

			                <?php
			                if ($mode == 'view') {
			                ?>
			                    disabled = 'disabled';
			                <?php
			                }
			                ?>

			                if (data[i].No_PrePo !== null) {
			                    No_PrePo = data[i].No_PrePo;
			                    if (data[i].No_PrePo !== null || data[i].No_PrePo !== '') {
			                        disabled = 'disabled';
			                        cancel = '';
			                        data_get = '';
			                        acak_id = 'no';
			                    }
			                }

			                if(data[i].Wilayah==null){
			                	wilayah = ' - ';
			                }else{
			                	wilayah = data[i].Wilayah;
			                }

			                html += '<tr>' +
			                    '<td align="center">' + no + '.</td>' +
			                    '<td ' + data_get + '>' + data[i].Kd_Brg + '</td>' +
			                    '<td>P' + data[i].Periode + '</td>' +
			                    '<td>' + wilayah + '</td>' +
			                    '<td>' + data[i].Kd_GroupGudang + '</td>' +
			                    '<td>' + data[i].Qty + '</td>' +
			                    '<td>' + data[i].Tipe_PO + '</td>' +
			                    '<td id="' + acak_id + '">' + No_PrePo + '</td>' +
			                    '<td align="center"><input type="checkbox" name="' + cancel + '" id="' + cancel + '" ' + disabled + '></td>' +
			                    '</tr>';

			                no++;
			            }
			        }
			        $("#datadetail").html(html);
			    },
			    error: function(xhr, status, error) {
			        console.error('Error fetching data:', status, error);
			    }
			});

		}
	}

		$('#checked_all').click(function(event) {  

			var checkboxes = document.getElementsByName('cancel');
			for (var checkbox of checkboxes) {
		        checkbox.checked = this.checked;
		    }
		});
</script>

<?php
	if($mode=='edit'){
?>
		<script type="text/javascript">

			function save_data(){

				var checkbox = document.getElementsByName("cancel");

				var a = document.querySelectorAll('[data-kdbrg]');
				var b = document.querySelectorAll('[data-priod]');
				var c = document.querySelectorAll('[data-bulan]');
				var d = document.querySelectorAll('[data-tahun]');
				var e = document.querySelectorAll('[data-kdgudang]');

				for (var i in a) if (a.hasOwnProperty(i)) {

					if(checkbox[i].checked){

						checkbox[i].disabled = true;
						checkbox[i].checked = false;
						

						var aa = btoa(encodeURI(a[i].getAttribute('data-kdbrg')));
						var bb = btoa(encodeURI(b[i].getAttribute('data-priod')));
						var cc = btoa(encodeURI(c[i].getAttribute('data-bulan')));
						var dd = btoa(encodeURI(d[i].getAttribute('data-tahun')));
						var ee = btoa(encodeURI(e[i].getAttribute('data-kdgudang')));

						aa = aa.replace("=", "");
						bb = bb.replace("=", "");
						cc = cc.replace("=", "");
						dd = dd.replace("=", "");
						ee = ee.replace("=", "");

						var trk_id = aa+bb+cc+dd+ee;
						var acak_id = btoa(trk_id).replace("=", "");
						$("#"+acak_id).html('CANCELLED');

				        $.ajax({
				            type  : 'ajax',
							url   :'<?php echo base_url('CampaignPlan/GetIn?api=APITES').'&number='.$id.'&type='.$type; ?>&kdbrg='+aa+'&priod='+bb+'&bulan='+cc+'&tahun='+dd+'&kdgudang='+ee,
							async : false,
							dataType : 'json',
							success : function(data){}
				 
				        });

				    }

				}

				
			}
		</script>
<?php
	}
?>



