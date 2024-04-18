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
</style>
<style>		
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
				<span class="glyphicon glyphicon-bell"></span> <long>NOTIFIKASI HARI INI</long>
				
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
			
			<div class="accordion" onclick="pending_request()">
				<span <?php echo ($Pending>0) ? 'style="background-color:red;"' : '' ?> class="my-badge"> <?=$Pending?> </span> Permintaan Approval</div>
			<div class="accordion-panel" id="pending_request">
			
			</div>

			<div class="accordion" onclick="sync_view();"><span class="my-badge"><span class="glyphicon glyphicon-transfer"></span></span> Sync Data Cabang</div>

			<div class="accordion-panel" id="sync"></div>
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
		document.getElementById('pending_request').innerHTML='Loading...';
		$.ajax({
			url 	: '<?php echo site_url('Dashboard/pending_request'); ?>', 
			success : function(data) {
				document.getElementById('pending_request').innerHTML=data;
				return false
			}
		})	
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

</div>
<script>


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

function onclic_reject() {
  alert('bbbb');
}
</script>