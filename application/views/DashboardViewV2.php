<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.js"></script>
<link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/magnific-popup/magnific-popup.css">
<script src="<?=base_url()?>assets/magnific-popup/jquery.magnific-popup.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
	$( document ).ready(function(){
		var tabs = $(".accordion").length
		$('.image-popup').magnificPopup({
			type: 'image',
			closeOnContentClick: true,
			closeBtnInside: false,
			fixedContentPos: true,
			mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
			image: {
				verticalFit: true
			},
			zoom: {
				enabled: true,
				duration: 300 // don't foget to change the duration also in CSS
			}
		});
		$(".accordion").click(function(e){
			var index = $(".accordion").index(this);
			for(var i=0;i<tabs;i++){
				if($(".accordion-panel").eq(i).css('display')!='none'){
					$(".accordion-panel").eq(i).slideToggle(200,'swing');
				}
			}

			if($(".accordion-panel").eq(index).css('display')=='none'){
				$(".accordion-panel").eq(index).slideToggle(200,'swing');
			}
		});
		
	});
</script>

<style>
	
	.dataTables_processing{
		font-size:150%;
		z-index:9999;
	}
	.modal-lg{
	width:90%;
	margin: 30px auto;
	}
	table{
	font-size:12px;
	}
	
	th { background-color: #95c9de;}

	.table tr{
		color:black;
		font-style: normal;
		text-align:left;
	}
	
	.table tr:nth-child(even) {background-color: #f8f8f8;}
	
	.table tr td{
		padding:2px;
	}
	
	.table-edit td input{
		border:1px solid #555;
	}
	.table-edit td input[type=text]{
		width:100%;
		text-align:right;
	}
	
	.table-edit td input:disabled{
		background: #fff;
		border: 1px solid #fff;	
	}
	.table-edit td input.justify{
	text-align: left;
	}
	
	#margin_approval{
	margin:20px;
	}	
	.glyphicon { font-size:20px;margin-left:5px;margin-right:5px; }
	.merah { color:#c91006; }
	.hijau { color:#0ead05;}
</style>


<div style="background-color:white;padding:15px;width:100%;height:100%;">
	<div class="row">
		<div class="col-4 col-md-4">
			
		</div>
		<div class="col-5 col-m-5">
			
		</div>

		<div class="col-3 col-m-3">
		</div>
	</div>
	<style>
		.accordion {
			background-color:  #4d499a;
			cursor: pointer;
			color: white;
			font-weight: bold;
			padding: 5px 0px;
			padding-left: 10px;
			border: solid #2e2b5d 0.01px;
			-webkit-user-select: none;  
			-moz-user-select: none;    
			-ms-user-select: none;      
			user-select: none;
		}
		.accordion-panel{
			display: none;
			font-family: sans-serif;
			padding: 10px;
			border: solid black 1px;
   			width: 100%;
		}
		.accordion-panel ul, .accordion-panel li{
			background-color: white !important;
			color: black !important;
		}
		.accordion-panel .glyphicon{
			font-size: 14px;
			vertical-align: middle;
			color: #ff9038;
		}
		.my-badge{
			padding: 2px;
			background-color:#020b5d;
			border-radius: 6px;
			min-width: 28px;
			display: inline-block;
			text-align: center;
		}
		.my-badge .glyphicon{
			font-size: small;
		}

	</style>
	<div class="row">
		<div class="col-12">
			<div class="accordion"> 
				<span class="my-badge" <?=($Pengumuman!=null ? 'style="background-color:red;"' : '')?> ><span class="glyphicon glyphicon-bullhorn"></span></span> Pengumuman
			</div>
			<div class="accordion-panel">
				<!-- Panel Pengumuman -->
				<span class="glyphicon glyphicon-bell"></span> <b>NOTIFIKASI HARI INI</b>
				
				<?php
				if($Pengumuman==null){
					echo '<ul><li>Tidak Ada Notifikasi Untuk Hari ini</li></ul>';
				}
				else{
					$html = '<br><br>';
					foreach($Pengumuman as $key => $value){
						//echo '<li>'.$value['announcement'].' '.date('d M Y', strtotime($value['start_published_date'])).'-'.date('d M Y', strtotime($value['end_published_date'])).'</li>';
						$no = ($key+1);
						$tglAwal = date('d M Y', strtotime($value['start_published_date']));
						$tglAkhir = date('d M Y', strtotime($value['end_published_date']));
						$file1 = $value['attachment_1'];
						$file2 = $value['attachment_2'];
						$file3 = $value['attachment_3'];
						$announcement = html_entity_decode (html_entity_decode( $value['announcement']));

						$html .= <<<HTML
						{$announcement}
						{$file1} {$file2} {$file3} {$file1}

HTML;
						
					}
					echo $html;
				}	
				?>
			
			</div>
			

			<!-- <div class="accordion" onclick="pending_request()"> -->
			<a href="approvallist">
				<div class="accordion">
					<span <?php echo ($Pending>0) ? 'style="background-color:red;"' : '' ?> class="my-badge"> <?=$Pending?> </span> Permintaan Approval
				</div>
			</a>
			<div class="accordion-panel" id="pending_request">Loading...</div>

			<div class="accordion" onclick="sync_view();"><span class="my-badge"><span class="glyphicon glyphicon-transfer"></span></span> Sync Data Cabang</div>

			<div class="accordion-panel" id="sync"></div>
			<?php if(($_SESSION['logged_in']['isSalesman']==1) && ($_SESSION['logged_in']['userLevel']=='ASS. MANAGER' || $_SESSION['logged_in']['userLevel']=='KABAG')) { ?>
			<div class="accordion" onclick="openShopboardApproval()"><span <?php echo ($shopboardapproval>0) ? 'style="background-color:red;"' : '' ?> class="my-badge"> <?=$shopboardapproval?> </span> Shopboard Approval</div>
			<div class="accordion-panel"></div>
			<?php } ?>
			
			<?php if($_SESSION['logged_in']['isSalesman']==1) { ?>
			<div class="accordion" onclick="load_acv_kpi();"><span class="my-badge"><span class="glyphicon glyphicon-list"></span></span> Achievement KPI </div>
			<div class="accordion-panel" id="div_acv_kpi">
				<table id="table_achievement" class="table table-bordered" summary="table">
					<thead>
						<tr>
							<th scope="col" width="2%" class="no-sort">NO</th>
							<th scope="col" style="*" >PERIODE</th>
							<th scope="col" width="20%" >ACHIEVEMENT (%)</th>
							<th scope="col" width="2%" class="no-sort">View</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<?php } ?>
			
		</div>
	</div>
</div>

<script type="text/javascript">
	function sync_view(){
		document.getElementById('sync').innerHTML='Loading...';
		$.ajax({
			url 	: '<?php echo site_url('Dashboard/sync_data'); ?>', 
			success : function(data) {
				document.getElementById('sync').innerHTML=data;
				return false
			}
		})	
	}
	function pending_request(){
		// document.getElementById('pending_request').innerHTML='Loading...';
		// $.ajax({
		// 	url 	: '<?php echo site_url('Dashboard/pending_request'); ?>', 
		// 	success : function(data) {
		// 		document.getElementById('pending_request').innerHTML=data;
		// 		return false
		// 	}
		// })	
	}

	function oper_link(e,number){
		// document.getElementById('object').data=e;
		// document.getElementById('iframe').src=e;
		// $("#number").empty(); 
		// document.getElementById('number').append(number);
		// var h = window.innerHeight-150;
		// // document.getElementById('object').style.height = h+'px';
		// document.getElementById('iframe').style.height = h+'px';
		document.getElementById('view').innerHTML='Loading...';
		$.ajax({
			url 	: e, 
			success : function(data) {
				document.getElementById('view').innerHTML=data;
				return false
			}
		})	

		$('#largeShoes').modal('show');
	}
</script>

<div class="modal fade" id="largeShoes" tabindex="-1" role="dialog" aria-labelledby="modalLabelLarge" aria-hidden="true">
	<div class="modal-dialog" style="width:50%">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #333;">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="modalLabelLarge">Permintaan Approval - <span id="number"></span></h4>
			</div>

			<div class="modal-body" style="background-color:#eaeaea" align="center">
				<div style="width:85%; margin: auto; background-color: #ffffff; padding:10px 30px 30px 30px" id="view"></div>
				<!-- <iframe src="" id="iframe" style="width: 80%; padding:0px; margin: 0px; background-color: #ffffff; padding:30px; border: none;"></iframe>
				<object data="" id="object" style="width: 80%; padding:0px; margin: 0px; background-color: #ffffff; padding:30px">
			    <embed src="" id="embed" style="width: 80%; padding:0px; margin: 0px;"></embed>
			    Error: Embedded data could not be displayed.
				</object> -->

			</div>

		</div>
	</div>
</div>

<div class="modal fade" id="modal_edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form id="form_save" class="form-horizontal" action="<?php echo site_url('Achievementkpisalesman/save') ?>" method="POST" onkeydown="return event.keyCode != 13">
				<div class="modal-header form_title">
					<div style="text-align:center;">
						ACHIEVEMENT KPI SALESMAN
					</div>
				</div>
				<div class="modal-body p20">
					<input type="hidden" name="atasan" id="atasan">
					<input type="hidden" name="kodesalesman" id="kodesalesman">
					<input type="hidden" name="namasalesman" id="namasalesman">
					<input type="hidden" name="levelsalesman" id="levelsalesman">
					<input type="hidden" name="namalevel" id="namalevel">
					<input type="hidden" name="wilayahsalesman" id="wilayahsalesman">
					<input type="hidden" name="kategori" id="kategori">
					<input type="hidden" name="th" id="th">
					<input type="hidden" name="bl" id="bl">
					<input type="hidden" name="kodetarget" id="kodetarget">
					<input type="hidden" name="withtargetkpi" id="withtargetkpi">
					<input type="hidden" name="norequestkpi" id="norequestkpi">
					<input type="hidden" name="norequestacv" id="norequestacv">
					<input type="hidden" name="acvkpistatus" id="acvkpistatus">
					
					<div class="row">
						<div class="col-6">
							<table class="" style="width:100%" summary="table">
								<tr><td scope="col">Kode Salesman </td>		<td scope="col"><strong><span id="span_kodesalesman"></span></strong></td></tr>
								<tr><td scope="col">Nama </td>			<td scope="col"><strong><span id="span_namasalesman"></span></strong></td></tr>
								<tr><td scope="col">Wilayah </td>		<td scope="col"><strong><span id="span_wilayahsalesman"></span></strong></td></tr>
								<tr><td scope="col">Kategori </td>		<td scope="col"><strong><span id="span_namalevel"></span></strong></td></tr>
								<tr><td scope="col">USERID </td>		<td scope="col"><strong><span id="span_userid"></span></strong></td></tr>
								<tr><td scope="col">Catatan </td>		<td scope="col"><strong><span id="catatan"></span></strong></td></tr>
								<tr><td scope="col">Status </td>		<td scope="col"><strong><span id="span_acvkpistatus"></span> <span id="span_norequestacv"></span></strong></td></tr>
								<!--tr><th scope="col">Template </th>		<th scope="col"><strong><span id="template_name"></span></strong></th></tr-->
							</table>
						</div>
						
						<div class="col-6">
							<table id="table_history" class="table table-bordered table-sm" summary="table">
								<thead>
									<tr>
										<th scope="col" width="20%">History Date</th>
										<th scope="col" width="20%">History Name</th>
										<th scope="col" width="20%">UserName</th>
										<th scope="col" width="20%">Email Approval</th>
										<th scope="col" width="20%">History Note</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
					
					<div class="row">
						<div class="col-12">
							<input type="hidden" name="kode_target" id="kode_target" value="">
							<input type="hidden" name="no_request" id="no_request" value="">
							<div style="overflow-x:scroll">
								<table id="table_kpi" class="table table-bordered table-sm table-edit" style="margin-bottom:0">
									<thead>
										<tr>
											<th style="min-width:280px">KEY PERFORMANCE INDICATOR</th>
											<th style="min-width:200px">DESKRIPSI</th>
											<th style="min-width:180px">TARGET</th>
											<th style="min-width:80px">BOBOT (%)</th>
											
											<th style="min-width:180px" class="text-center target_1">WEEK 1</th>
											<th style="min-width:180px" class="text-center target_2">WEEK 2</th>
											<th style="min-width:180px" class="text-center target_3">WEEK 3</th>
											<th style="min-width:180px" class="text-center target_4">WEEK 4</th>
											<th style="min-width:180px" class="text-center target_5">WEEK 5</th>
											<th style="min-width:180px" class="text-center target_6">WEEK 6</th>
											
											
											<th style="min-width:180px">TOTAL</th>
											<th style="min-width:80px">% ACV</th>
											<th style="min-width:80px">BOBOT ACV</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
							<span style="float:right">
								TOTAL ACHIEVEMENT <input type="text" name="total_achievement" id="total_achievement" style="width:60px;text-align:center" readonly>
							</span>
						</div>
					
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-12">
						<small id="LastModified" style="float:left;text-align:left"></small>
						<button type="submit" class="btn-success form-edit">SAVE</button>
						<button type="button" class="btn-danger" data-dismiss="modal">CLOSE</button>
						<div id="result"></div>
					</div>
				</div>
				
			</form>
		</div>
	</div>
</div>

<script>

	var w;

function onlick_approve() {
	var e = $('#APPROVE').attr("data-href");
	document.getElementById("APPROVE").style.display = 'none';
	document.getElementById("REJECT").style.display = 'none';
	Swal.fire({
		icon: 'info',
		title: 'Loading...',
		showConfirmButton: false
	})

	$.ajax({
		url 	: e, 
		success : function(data) {
			cek=isJson(data);
			if(cek==true){
				pending_request();
				hasil = JSON.parse(data);
				Swal.fire(
					'',
					hasil.pesan,
					'success'
				)
			}else{
				Swal.fire(
					'',
					'Server Error, Silahkan Tunggu beberapa menit atau hubungi IT Suport!!!',
					'danger'
				)
				document.getElementById("APPROVE").style.display = 'block';
				document.getElementById("REJECT").style.display = 'block';
			}
		

			return false
		}
	})
}

function isJson(str) {
  try {
      const obj = JSON.parse(str);
      if (obj && typeof obj === `object`) {
        return true;
      }
    } catch (err) {
      return false;
    }
   return false;
}

function openShopboardApproval(){
	window.location.href = '<?php echo base_url() ?>shopboardapproval';
}

	// ACHIEVEMENT KPI SALESMAN
	function load_acv_kpi(){
		var table_achievement = $('#table_achievement').DataTable({
			"pageLength"    : 10,
			"searching"     : false,
			"lengthChange"  : false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"order": [1, 'desc'],
			"autoWidth": false,
			"processing": true,
			// "ordering": false,
			"serverSide": true,
			"retrieve": true,
			"ajax": {
				"url": '<?php echo base_url('dashboard/achievement_kpi') ?>',
				"type": "GET",
				"datatype": "json",
				"data": function (data) {
					data.userid = '<?php echo $_SESSION['logged_in']['userid'] ?>';
				}
			},
		});
	}
	
	function view_acv(tahun,bulan){
	
		$('.loading').show();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Dashboard/achievement_kpi_dashboard') ?>',  
			data: {
				'userid': '<?php echo $_SESSION['logged_in']['userid'] ?>',
				'tahun': tahun,
				'bulan': bulan
			}, 
			dataType: 'json',
			success: function (res) {
				if(res.result=='success'){
					console.log(JSON.stringify(res));
							
					var data = res.data;
					var kodesalesman = data.kodesalesman;
					var namasalesman = data.namasalesman;
					var levelsalesman = data.levelsalesman;
					var namalevel = data.namalevel;
					var wilayahsalesman = data.wilayahsalesman;
					var kategori = data.kategori;
					var kodetarget = data.kodetarget;
					var userid = data.userid;
					var withtargetkpi = data.withtargetkpi;
					var norequestkpi = data.norequestkpi;
					var catatan = data.catatan;
					var norequestacv = data.norequestacv;
					var acvkpistatus = data.acvkpistatus;
					var templateid = data.templateid;
					var templatename = data.templatename;
					
					var span_norequestacv = (norequestacv!='') ? ' ['+norequestacv+']' : '';
					$('#atasan').val(atasan);
					$('#kodesalesman').val(kodesalesman);
					$('#namasalesman').val(namasalesman);
					$('#levelsalesman').val(levelsalesman);
					$('#namalevel').val(namalevel);
					$('#wilayahsalesman').val(wilayahsalesman);
					$('#kategori').val(kategori);
					
					$('#th').val(th);
					$('#bl').val(bl);
					
					$('#kodetarget').val(kodetarget);
					$('#withtargetkpi').val(withtargetkpi);
					$('#norequestkpi').val(norequestkpi);
					$('#norequestacv').val(norequestacv);
					$('#acvkpistatus').val(acvkpistatus);
					$('#span_kodesalesman').html(kodesalesman);
					$('#span_namasalesman').html(namasalesman);
					$('#span_wilayahsalesman').html(wilayahsalesman);
					$('#span_namalevel').html(namalevel);
					$('#span_userid').html(userid);
					$('#template_name').html(templatename);
					$('#span_acvkpistatus').html(acvkpistatus+span_norequestacv);
					$('#catatan').html(catatan);
					
					idx = 0;
					$('#table_kpi tbody').html('');
					$('#table_history tbody').html('<tr><td colspan="5" align="center">Tidak ada history</td></tr>');
					$.ajax({ 
						type: 'POST', 
						url: '<?php echo site_url('Achievementkpisalesman/TargetSalesman_KPI_AmbilAchievementDetail') ?>',  
						data: {
							'KodeTarget': kodetarget,
							'NoRequestKPI': norequestkpi,
							'NoRequestAcv': norequestacv
						}, 
						dataType: 'json',
						success: function (result) {
							console.log(JSON.stringify(result));
							// console.log(result);
							
							$('.loading').hide();
							for(i=0;i<result.detail.length;i++){
								idx++;
								var data = result.detail[i];
								var tbody = '';
								tbody+='<tr id="baris_'+idx+'">'+
								'<td><input type="hidden" name="kpicode[]" value="'+data.KPICode+'"><input type="hidden" name="kpiname[]" value="'+data.KPIName+'">'+data.KPIName+'</td>'+
								'<td><input type="hidden" name="kpinote[]" value="'+data.KPINote+'"><input type="hidden" name="kpiunit[]" value="'+data.KPIUnit+'">'+data.KPINote+'</td>'+
								'<td><input type="text" name="target[]" value="'+format_currency(data.KPITarget)+'" id="target_'+idx+'" class="form-input target" disabled></td>'+
								'<td><input type="text" name="bobot[]" value="'+format_currency(data.KPIBobot)+'" id="bobot_'+idx+'" class="form-input bobot" disabled></td>'+
							
								'<td class="text-center target_1"><input type="text" name="acv1[]" id="acv1_'+idx+'" value="'+format_currency(data.AcvWeek1)+'" class="acv-input numeric"></td>'+
								
								'<td class="text-center target_2"><input type="text" name="acv2[]" id="acv2_'+idx+'" value="'+format_currency(data.AcvWeek2)+'" class="acv-input numeric"></td>'+
								
								'<td class="text-center target_3"><input type="text" name="acv3[]" id="acv3_'+idx+'" value="'+format_currency(data.AcvWeek3)+'" class="acv-input numeric"></td>'+
								
								'<td class="text-center target_4"><input type="text" name="acv4[]" id="acv4_'+idx+'" value="'+format_currency(data.AcvWeek4)+'" class="acv-input numeric"></td>'+
								
								'<td class="text-center target_5"><input type="text" name="acv5[]" id="acv5_'+idx+'" value="'+format_currency(data.AcvWeek5)+'" class="acv-input numeric"></td>'+
								
								'<td class="text-center target_6"><input type="text" name="acv6[]" id="acv6_'+idx+'" value="'+format_currency(data.AcvWeek6)+'" class="acv-input numeric"></td>'+
								
								'<td><input type="text" name="acvtotal[]" value="'+format_currency(data.AcvTotal)+'" id="acvtotal_'+idx+'" class="form-input" disabled></td>'+
								'<td><input type="text" name="acvpersen[]" value="'+format_currency(data.AcvPersen)+'" id="acvpersen_'+idx+'" class="form-input" disabled></td>'+
								'<td><input type="text" name="acvbobot[]" value="'+format_currency(data.AcvBobot)+'" id="acvbobot_'+idx+'" class="form-input acvbobot" disabled></td>'+
								'</tr>';
								$('#table_kpi tbody').append(tbody);
							}
							
							if(result.history.length>0){
								$('#table_history tbody').html('');
								for(i=0;i<result.history.length;i++){
									idx++;
									var data = result.history[i];
									var tbody = '';
									tbody+='<tr>'+
									'<td>'+data.HistoryDate+'</td>'+
									'<td>'+data.HistoryName+'</td>'+
									'<td>'+data.UserName+'</td>'+
									'<td>'+data.ApproverEmail+'</td>'+
									'<td>'+data.HistoryNote+'</td>'+
									'</tr>';
									$('#table_history tbody').append(tbody);
								}
							}
							
							hitung_achievement();	
							w = weekCount(tahun,bulan);
							set_kolom_minggu();
							
							$('.form-edit').hide();
							$('.acv-input').prop('disabled', true);
							$('#modal_edit').modal('show');
						}
					});	
				}
				else{
					alert('Data tidak ditemukan!');
				}
			}
		});
	}
	
	function weekCount(year, month_number) {
		// month_number is in the range 1..12
		var firstOfMonth = new Date(year, month_number-1, 1);
		var lastOfMonth = new Date(year, month_number, 0);
		var used = firstOfMonth.getDay() + lastOfMonth.getDate();
		return Math.ceil( used / 7);
	}
	
	function set_kolom_minggu(){
		if(w<5){
			$('.target_5').hide();
			$('.target_6').hide();
			}else if(w<6){
			$('.target_6').hide();
		}
		else{
			$('.target_5').show();
			$('.target_6').show();
		}	
	}
	
	function hitung_achievement(){
		var acvbobot = 0;
		$('.acvbobot').each(function(i, obj) {
			acvbobot+= parseFloat($(this).val().replace(/\,/g,'')) || 0;
		});
		
		$('#total_achievement').val(parseFloat(acvbobot).toFixed(0));
	}
	
	function format_currency(x){
		var str = x.toString().replace(/[^0-9.]/g,'');
		x = str.split('.'); 
		x1 = x[0].length > 0 ? x[0] : '0';
		x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/; 
		while (rgx.test(x1)) { 
			x1 = x1.replace(rgx, '$1' + ',' + '$2'); 
		} 
		return x1 + x2;
	}
	
</script>