<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    var unmappedBasss = <?php echo(json_encode($UnmappedBass));?>;
    var mappedBasss = <?php echo(json_encode($mappedBass));?>;
    var dealers = <?php echo(json_encode($Dealers));?>;

    $(document).ready(function(){
        $(".fieldsetTab").hide();
		$("#fieldsetTab1").show();
        $("#PopUpFormMapping").hide();

        $('#OptMapping').on('change', function() {
			var Opt = $(this).val();
			$(".tMap").addClass("hideMe");
			$(".loading").show();

			var x = document.getElementById("branch").value;

			if (Opt=="unmapped") {
				//$("#tblUnmapped").removeClass("hideMe");
				window.location.replace("<?php echo(site_url('bass')); ?>");
			} else if (Opt=="mapped") {
				//$("#tblMapped").removeClass("hideMe");
				window.location.replace("<?php echo(site_url('bass/mappedbass')); ?>");

			} 
		});

		$('#branch').on('change', function() {
			var Opt = document.getElementById("OptMapping").value;
			$(".tMap").addClass("hideMe");
			$(".loading").show();

			var x = document.getElementById("branch").value;

			if (Opt=="unmapped") {
				window.location.replace("<?php echo(site_url('bass')); ?>");
			} else if (Opt=="mapped") {
				window.location.replace("<?php echo(site_url('bass/mappedbass')); ?>");
			}
		});

        $(".btnMapBass").click(function(){
   
            $("#fieldsetTab1").hide();

			var IDNO = $(this).attr("idno");
			var BassID = $(this).attr("bassid");
			var BassName = $(this).attr("bassname");
			var csrf_bit=$("input[name=csrf_bit]").val();

			$("#MIdNO").val(IDNO);
          	$("#MBassID").val(BassID);
          	var DataBass = "Nama Bass : <b>"+BassName+"</b><br>";
          	for(var i=0;i<unmappedBasss.length;i++) {
          		if (unmappedBasss[i].KODE_BASS==BassID) {
                    DataBass += "Alamat Bass : <b>"+unmappedBasss[i].ALAMAT_BASS+"</b><br>";
                    DataBass += "Kota : <b>"+unmappedBasss[i].KOTA+"</b><br>";
                    DataBass += "Contact Person : <b>"+unmappedBasss[i].CONTACT_PERSON+"</b><br>";
                    DataBass += "Email : <b>"+unmappedBasss[i].EMAIL+"</b><br>";
                    DataBass += "No Telp : <b>"+unmappedBasss[i].NOMOR_TELP+"</b>";
          		}
			}
            for(var i=0;i<mappedBasss.length;i++) {
          		if (mappedBasss[i].KODE_BASS==BassID) {
                    DataBass += "Alamat Bass : <b>"+mappedBasss[i].ALAMAT_BASS+"</b><br>";
                    DataBass += "Kota : <b>"+mappedBasss[i].KOTA+"</b><br>";
                    DataBass += "Contact Person : <b>"+mappedBasss[i].CONTACT_PERSON+"</b><br>";
                    DataBass += "Email : <b>"+mappedBasss[i].EMAIL+"</b><br>";
                    DataBass += "No Telp : <b>"+mappedBasss[i].NOMOR_TELP+"</b><br>";
                    DataBass += "Kode Bass Mapping : <b>"+mappedBasss[i].KD_PLG+"</b><br>";
                    DataBass += "Kode Wilayah Mapping : <b>"+mappedBasss[i].KD_WIL+"</b>";
          		}
			}
          	$("#MBass").html(DataBass);

          	$("#DealerID").val("");
          	$("#MDealer").html("");
			$("#DealerID").autocomplete({
				source: dealers
			});

			$("#PopUpFormMapping").show();
			//alert(SalesmanName);
		});

		$('#DealerID').on('change', function() {
			var Dealer = $("#DealerID").val();
			var eArray = Dealer.split(" - ");

			var DataDealer = "Kode Dealer : <b>"+eArray[0]+"</b><br>";
            DataDealer += "Nama Dealer : <b>"+eArray[1]+"</b><br>";
			DataDealer += "Kode Wilayah : <b>"+eArray[2]+"</b><br>";
			DataDealer += "Wilayah : <b>"+eArray[3]+"</b><br>";
			DataDealer += "Alamat : <b>"+eArray[4]+"</b><br>";
            DataDealer += "Kode Lokasi : <b>"+eArray[5]+"</b><br>";

            $("#MDealer").html(DataDealer);
		    $(".loading").hide();
		});

		$("#btnMapping").click(function(e){
			e.preventDefault();		
			if(confirm("Simpan Data ?"))
			{
				var IdNO = $("#MIdNO").val();
				var BassID = $("#MBassID").val();
				var Dealer = $("#DealerID").val();
				var eArray = Dealer.split(" - ");
				var DealerID = eArray[0];
                var WilayahID = eArray[2];
                var KodeLokasi = eArray[5];

				var csrf_bit=$("input[name=csrf_bit]").val();
				$(".loading").show();
                //buka koneksi ke bktAPI Cabang
                //insert dan update ke bktAPI Cabang
                $.post("<?php echo(site_url('Bass/CaribktAPI_webAPI')); ?>", {
                  location_code	: KodeLokasi,
                  kode_bass : BassID,
                  kode_plg : DealerID,
                  kode_wil : WilayahID,                 
		          csrf_bit : csrf_bit
		        }, function(data){ 
                    // if (data.result == "sukses") {
					// 	$("#btn_unmapped_"+IdNO).html("<font color='#1e8a30'><b>"+"SUKSES"+"</b></font>");
					// 	$("#PopUpFormMapping").hide();
					// 	$("#fieldsetTab1").show();
					// 	$(".loading").hide();
                    // } else {
                    //     $(".loading").hide();
                    // }

                    $("#btn_unmapped_"+IdNO).html("<font color='#1e8a30'><b>"+"SUKSES"+"</b></font>");
                    $("#PopUpFormMapping").hide();
                    $("#fieldsetTab1").show();
                    $(".loading").hide();

		        },'json',errorAjax); 		
			}
		});

		$("#btnCancelMapping").click(function(){
			CloseForm();
		});

		function CloseForm(){
			$(".fieldsetTab").hide();
			$("#fieldsetTab1").show();
		};


    });
</script>

<style>
	.btn, form button { 
		background-color:#b3b6bd!important; 
		padding:2px;
		border-radius:0px;
		color:black;
		font-size:9pt;
		top:0px!important;
		height:15px;
		width:75px;
		padding-left:auto;
		padding-right:auto;
	}
	.btn:hover, form button:hover { background-color: #c5c7c9; border:1px solid #99ccff; }
	form button {
		top:0px!important;
		height:24px;
		width:100px;
	}

	.fieldsetTab {
		margin-top:20px;
	}

	.TabSelected { background-color: #34d2eb;}
	th,td {
		text-align: left;
		border:1px solid #ccc;
	}
	td {
		height:60px;
		vertical-align: top;
	}
	.td-filter {
		height:25px;
		border:0px;
		padding:0px;
	}

	select {
		font-size: 12px! important;
		background-color: white!important;
	}

	#PopUpForm, #PopUpFormMapping {
		font-size: 10pt !important;
	}

	#PopUpForm input, select, textarea {
		font-size:10pt !important;
		width:50%;
	}

	#PopUpFormMapping input, select, textarea {
		font-size:10pt !important;
		width:50%;
	}

	.popupform_title {
		font-weight: bold;
		font-size:20px;
		margin-bottom:15px;
		text-align: right;
	}

	.labelText { width:25%!important; }
	.labelInput { width:75%!important;}
	.hideMe { display:none; }

    .map-bass-box { border:1px solid #041338; padding:10px; }
    .map-bass { line-height: 20px; vertical-align: middle; font-size: 12px; padding:10px; }
    .map-bass-hd { font-size: 16px; font-weight: bold; }
    .map-bass input { background-color: white; font-size: 10px; }

    .fieldsetTabTitleContainer{
        border: 1px solid #999;
        border-radius:5px;
        overflow: hidden;
        padding: 0;
        margin-top:15px;
    }

    .fieldsetTabTitle{
        padding: 5px 18px 5px 19px;
        float:left;
        cursor: pointer;
    }
</style>

<fieldset>
<div class="container">
	<div class="fieldsetTab"  id="fieldsetTab1" style="display: none;">
		<div class="trheader">
			<select id="OptMapping" name="OptMapping" style="width:400px!important;font-size:14pt!important;font-weight:bold;background-color:#cdff57!important;margin-bottom:20px;">
					<option value='unmapped'<?php echo(($OptMapping=="UNMAPPED BASS")?" selected":"");?>>BASS BELUM DIMAPPING</option>
					<option value='mapped'<?php echo(($OptMapping=="MAPPED BASS")?" selected":"");?>>BASS SUDAH DIMAPPING</option>
			</select>

			<?php
				if(!empty($BranchIDList)){
			?>
					<select id="branch" name="branch" style="width:400px!important;font-size:14pt!important;font-weight:bold;background-color:#cdff57!important;margin-bottom:20px;">
						<?php
							foreach ($BranchIDList as $key => $b) {
						?>
								<option value='<?php echo $b->BranchID; ?>' <?php if($b->BranchID==$BranchIDGet){ echo 'selected'; } ?>><?php echo $b->BranchName; ?></option>
						<?php
							}
						?>
					</select>
			<?php
				}else{
			?>
					<input type="hidden" id="branch" name="branch" value="">
			<?php
				}
			?>

			<?php if($OptMapping=="UNMAPPED BASS") {?>
			<table class="dataTable display tMap" id="tblUnmapped" summary="table">
				<thead id="tblUnmappedHead">
					<tr>
						<th scope="col" width="5%">No</th>
						<th scope="col" width="10%">Kode Bass</th>
						<th scope="col" width="20%">Nama Bass</th>
						<th scope="col" width="30%">Alamat Bass</th>
						<th scope="col" width="20%">Kota</th>
						<th scope="col" width="15%"></th>
					</tr>
				</thead>
				<tbody id="tblUnmappedBody">
				<?php
					$NO = 0;
					for($i=0;$i<count($UnmappedBass);$i++) {
						if (isset($UnmappedBass[$i])) {
							$NO += 1;
							$data = $UnmappedBass[$i];
							echo("<tr style='padding:5px;' id='tr_unmapped_".$data["KODE_BASS"]."'>");
							echo("	<td style='text-align:center;'>".$NO."</td>");
							echo("	<td style=''>".$data["KODE_BASS"]."</td>");
							echo("	<td style=''>".$data["NAMA_BASS"]."</td>");
							echo("	<td style=''>".$data["ALAMAT_BASS"]."</td>");
							echo("	<td style=''>".$data["KOTA"]."</td>");
							$BTN = (($data["KD_PLG"]=="") ? "MAPPING" : "MAPPING ULANG");
							echo("	<td id='btn_unmapped_".$NO."' style=''><button class='btnMapBass' idno='".$NO."' bassid='".$data["KODE_BASS"]."' bassname='".$data["NAMA_BASS"]."'>".$BTN."</button></td>");
							echo("</tr>");
						}
					}
				?>				
				</tbody>
			</table>
			<?php } ?>
			<?php if($OptMapping=="MAPPED BASS") {?>
			<table class="dataTable display tMap" id="tblMapped">
				<thead id="tblMappedHead">
					<tr>
                        <th width="5%">No</th>
						<th width="10%">Kode Bass</th>
						<th width="20%">Nama Bass</th>
						<th width="30%">Alamat Bass</th>
						<th width="20%">Kota</th>
						<th width="15%"></th>
					</tr>
				</thead>
				<tbody id="tblMappedBody">
				<?php
					$NO = 0;
					for($i=0;$i<count($mappedBass);$i++) {
						if (isset($mappedBass[$i])) {
							$NO += 1;
							$data = $mappedBass[$i];
							echo("<tr style='padding:5px;' id='tr_unmapped_".$data["KODE_BASS"]."'>");
							echo("	<td style='text-align:center;'>".$NO."</td>");
							echo("	<td style=''>".$data["KODE_BASS"]."</td>");
							echo("	<td style=''>".$data["NAMA_BASS"]."</td>");
							echo("	<td style=''>".$data["ALAMAT_BASS"]."</td>");
							echo("	<td style=''>".$data["KOTA"]."</td>");
							$BTN = (($data["KD_PLG"]=="") ? "MAPPING" : "MAPPING ULANG");
							echo("	<td id='btn_unmapped_".$NO."' style=''><button class='btnMapBass' idno='".$NO."' bassid='".$data["KODE_BASS"]."' bassname='".$data["NAMA_BASS"]."'>".$BTN."</button></td>");
							echo("</tr>");
						}
					}
				?>				
				</tbody>
			</table>
			<?php } ?>
		</div>
	</div>

	<div class="fieldsetTab" id="PopUpFormMapping" style="display: block;">
        <div class="loadingItem"></div>            
		<div class="popupform_title" id="formTitle">MAPPING BASS</div>
		<div class="map-bass-box">
			<div style="float:left;width:45%;margin-left:5%;">
				<div class="" style="border-right:1px dashed #ccc;border-bottom:1px dashed #ccc;">
					<div class="map-bass map-bass-hd">DATA BASS (BHAKTI PURNA JUAL)</div>
				</div>
				<div class="" style="border-right:1px dashed #ccc;">
					<div class="map-bass">
						<?php
							$attr = array 
							(
								'placeholder' => 'Bass',
								'id' => 'MBassID',
								'maxlength' => '50',
								'style'=>'width:20%;',
								"readonly"=>true
							);
							BuildInput('text','MBassID',$attr);						
						?>
					</div>
					<div class="map-bass" id="MBass">

					</div>
				</div>
			</div>

			<div style="float:right;width:45%;margin-right:5%;">
				<div class="" style="border-bottom:1px dashed #ccc;">
					<div class="map-bass map-bass-hd">DATA DEALER (BHAKTI)</div>
				</div>
				<div class="">
					<div class="map-bass">
						<?php
							$attr = array 
							(
								'placeholder' => 'Dealer',
								'id' => 'DealerID',
								'style'=>'width:90%;'
							);
							BuildInput('text','DealerID',$attr);	
						?>
					</div>	
					<div class="map-bass" id="MDealer">
					</div>
				</div>
			</div>
			<div style="clear:both;"></div>
			<div class="formInputAjax" align="right">
				<?php
					$attr = array 
					(
						'id' => 'MIdNO',
						'style'=>'width:90%;display:none;'
					);
					BuildInput('text','MIdNO',$attr);
				?>				
				<button id="btnMapping" name="btnMapping">Simpan</button>
				<button id="btnCancelMapping" name="btnCancelMapping">Batal</button>
			</div>
		</div>
	</div>
</div>
</fieldset>

<div style="height:80px;"></div>

<?php echo form_open(); ?>
<?php echo form_close(); ?>
