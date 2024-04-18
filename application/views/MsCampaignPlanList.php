<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
	.btnEdit, .btnDelete, .btnView {
		cursor: pointer;
	}
	#formTitle {
		font-weight: bold;
		font-size:20pt;
	}

	.planCancelled { color:#4e5450;} 
	.planDraft { color:red; }
	.planSaved { color:#000d9c;}
	.planWaiting { color:#cf8104;}
	.planApproved { color:#17542b;}
	.planRejected { color:#a10261;}
	.hideMe { display:none; }

</style>
<script>
	var mode='';
	var list = [];

	function refreshTable() {
		var planList="";
		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('CampaignPlan/GetCampaignPlanList'); ?>", {
			ChkCancelled			: (($("#ChkCancelled").prop("checked")==true)?1:0),
			ChkDraft				: (($("#ChkDraft").prop("checked")==true)?1:0),
			ChkSaved				: (($("#ChkSaved").prop("checked")==true)?1:0),
			ChkWaiting				: (($("#ChkWaiting").prop("checked")==true)?1:0),
			ChkApproved				: (($("#ChkApproved").prop("checked")==true)?1:0),
			ChkRejected				: (($("#ChkRejected").prop("checked")==true)?1:0),
			ChkActive				: (($("#ChkActive").prop("checked")==true)?1:0),
			ChkInActive				: (($("#ChkInActive").prop("checked")==true)?1:0),
			csrf_bit				: csrf_bit
		}, function(data){
			//alert(data.result + " : " + data.campaignId);
			if (data.result=="SUCCESS") {
				//alert("SUCCESS");
				var idx = 1; 
				list = data.list;
				var CanRead = "<?php echo($_SESSION['can_read']);?>";
				var CanUpdate = "<?php echo($_SESSION['can_update']);?>";
				var CanDelete = "<?php echo($_SESSION['can_delete']);?>";
				//alert(list.length);

				for(var p=0; p<list.length; p++)
				//for(var p=0; p<1; p++)
				{
					list[p].idx = idx;

					enableEdit = true;
					enableDelete=true;
					statusColor = "black";
					pclass = "";

					if (list[p].CampaignStatus=="CANCELLED") {
						pclass="planCancelled";
						enableEdit=false;
						enableDelete=false;
					} else if (list[p].CampaignStatus=="DRAFT") {
						pclass="planDraft";
					} else if (list[p].CampaignStatus=="SAVED") {
						pclass="planSaved";
					} else if (list[p].CampaignStatus=="WAITING FOR APPROVAL") {
						pclass="planWaiting";
					} else if (list[p].CampaignStatus=="APPROVED") {
						pclass="planApproved";
						enableEdit=false;
						penableDelete=false;
					} else if (list[p].CampaignStatus=="REJECTED") {
						pclass="planRejected";
						enableEdit=true;
						enableDelete=false;
					}
					planList+="<tr>";
					planList+="	<td><b>"+list[p].CampaignName+"</b><br>"+list[p].Division+"<br>"+list[p].CampaignID+"</td>";
					planList+="	<td>"+date("d-M-Y", strtotime(list[p].CampaignStart))+" s/d<br>"+date("d-M-Y", strtotime(list[p].CampaignEnd))+"<br>Jumlah Hari Kerja: "+list[p].JumlahHari+"</td>";
					planList+="	<td id='planStatus"+idx+"' class='"+pclass+"'>"+list[p].CampaignStatus+"</td>";
					
					if (list[p].UpdatedDate==null) {
						planList+="	<td class='hideOnMobile'>"+list[p].CreatedBy+"<br>"+list[p].CreatedDate+"</td>";
					} else {
						planList+="	<td class='hideOnMobile'>"+list[p].CreatedBy+"<br>"+list[p].UpdatedDate+"</td>";
					}
					if (list[p].IsApproved==0) {
						planList+="	<td class='hideOnMobile'></td>";				
					} else {
						planList+="	<td class='hideOnMobile'>"+list[p].ApprovedByName+"<br>"+list[p].ApprovedDate+"</td>";				
					}

					planList+="	<td>";
		            if(CanRead == "1") {
		            	planList+="	<a href='CampaignPlan/view?trxid="+encodeURI(list[p].CampaignID)+"', target='_blank'><button class='btnView' planid='"+list[p].CampaignID+"'><i class='glyphicon glyphicon-eye-open'></i></button></a>";
		            }
		            if(CanUpdate == "1" && enableEdit==true) {
		            	planList+="	<a href='CampaignPlan/edit?trxid="+encodeURI(list[p].CampaignID)+"'><button class='btnEdit' id='btnEdit"+idx+"' idx='"+idx+"' planid='"+list[p].CampaignID+"'><i class='glyphicon glyphicon-pencil' style='color:green;'></i></button></a>";
		            }
		            if (CanDelete == "1" && enableDelete==true) {
		             	planList+="	<button class='btnDelete' id='btnDelete"+idx+"' idx='"+idx+"' onclick='cancelPlan("+idx+")'><i class='glyphicon glyphicon-trash' style='color:red;'></i></button>"; 
		             	// planid='"+list[p].CampaignID+"'  planname='"+list[p].CampaignName+"'
		            }
		            planList+="	</td>";
					planList+="</tr>";
					idx = idx+1;
					//alert(planList);
				}
				$("#TblCampaignPlanBody").html(planList);
				//alert(planList);
				//alert("Helloooo");
			} else {
				$("#TblCampaignPlanBody").html(planList);
				//alert("TIDAK ADA DATA");
			}
		},'json',errorAjax);		
		$(".loading").hide();
	}

	function cancelPlan(i) {
		for(var p in list) {
			if (list[p].idx==i) {

				var Idx = i;
				var CampaignId = list[p].CampaignID;
				var CampaignName=list[p].CampaignName;
				var CurrentStatus=$("#planStatus"+Idx).text();

				if (confirm("Batalkan Rencana Campaign "+CampaignName+" ?")) {
					var alasan = prompt("Input Alasan Batal");
					alasan = alasan.toUpperCase();

					$(".loading").show();
					var csrf_bit = $("input[name=csrf_bit]").val();
					$.post("<?php echo site_url('CampaignPlan/CancelPlan'); ?>", {
						kode_plan 		: CampaignId,
						alasan 			: alasan,
						csrf_bit		: csrf_bit
					}, function(data){
						//alert(data.result + " : " + data.campaignId);
						if (data.result=="SUCCESS") {
							$("#planStatus"+Idx).text("CANCELLED");
							if (CurrentStatus=="DRAFT") {
								$("#planStatus"+Idx).removeClass("planDraft");
							} else if (CurrentStatus=="SAVED") {
								$("#planStatus"+Idx).removeClass("planSaved");
							} else if (CurrentStatus=="WAITING FOR APPROVAL") {
								$("#planStatus"+Idx).removeClass("planWaiting");
							}
							$("#planStatus"+Idx).addClass("planCancelled");
							$("#btnEdit"+Idx).addClass("hideMe");
							$("#btnDelete"+Idx).addClass("hideMe");
						} else {
						}

					},'json',errorAjax);		
					$(".loading").hide();
				}

			}
		}
	}

	$(document).ready(function(){
		/*$('#tblCampaignPlan').DataTable({
          "pageLength": 50,
          "destroy": true
        });*/
		refreshTable();


		$(".btnDelete").click(function(){
			alert("delete");
		})

		$(".ChkStatus").click(function(){
			refreshTable();
		});
	});
</script>

<div class="container">
<div class="form_title">PERENCANAAN CAMPAIGN</div>
<div class="clearfix" style="margin: 10px;">
	<div class="row">
		<div class="col-1 col-m-1"><div style="border:1px solid #fff; border-radius:10px; padding:5px;">STATUS :</div></div>
		<div class="col-1 col-m-1"><div class="planDraft" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkDraft" id="ChkDraft" checked="checked">&nbsp;&nbsp;DRAFT</div></div>
		<div class="col-1 col-m-1"><div class="planSaved" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkSaved" id="ChkSaved" checked="checked">&nbsp;&nbsp;SAVED</div></div>
		<div class="col-3 col-m-3"><div class="planWaiting" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkWaiting" id="ChkWaiting" checked="checked">&nbsp;&nbsp;WAITING FOR APPROVAL</div></div>
		<div class="col-2 col-m-2"><div class="planApproved" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkApproved" id="ChkApproved" checked="checked">&nbsp;&nbsp;APPROVED</div></div>
		<div class="col-2 col-m-2"><div class="planRejected" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkRejected" id="ChkRejected" checked="checked">&nbsp;&nbsp;REJECTED</div></div>
		<div class="col-2 col-m-2"><div class="planCancelled" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkCancelled" id="ChkCancelled">&nbsp;&nbsp;CANCELLED</div></div>
	</div>
	<div class="row">
		<div class="col-1 col-m-1"><div style="border:1px solid #fff; border-radius:10px; padding:5px;">PERIODE:</div></div>
		<div class="col-3 col-m-3"><div class="planActive" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkActive" id="ChkActive" checked="checked">&nbsp;&nbsp;AKTIF [SEDANG/AKAN BERLANGSUNG]</div></div>
		<div class="col-3 col-m-3"><div class="planInActive" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkInActive" id="ChkInActive">&nbsp;&nbsp;NONAKTIF [SUDAH LEWAT]</div></div>
	</div>
</div>
<div class="clearfix">
	<table id="tblCampaignPlan" class="table table-striped table-bordered" cellspacing="0">
		<thead>
			<tr>
				<th scope="col" width="25%">Plan</th>
				<th scope="col" width="20%">Periode</th>
				<th scope="col" width="10%">Status</th>
				<th scope="col" width="15%" class='hideOnMobile'>CreationInfo</th>
				<th scope="col" width="15%" class='hideOnMobile'>ApprovalInfo</th>
		        <th scope="col" width="15%"></th>
			</tr>
		</thead>
		<tbody id="TblCampaignPlanBody">
		<?php
		/*
		$idx = 1; 
		foreach($CampaignPlans as $p)
		{
			$enableEdit = true;
			$enableDelete=true;
			$statusColor = "black";

			if ($p->CampaignStatus=="CANCELLED") {
				$class="planCancelled";
				$enableEdit=false;
				$enableDelete=false;
			} else if ($p->CampaignStatus=="DRAFT") {
				$class="planDraft";
			} else if ($p->CampaignStatus=="SAVED") {
				$class="planSaved";
			} else if ($p->CampaignStatus=="WAITING FOR APPROVAL") {
				$class="planWaiting";
			} else if ($p->CampaignStatus=="APPROVED") {
				$class="planApproved";
				$enableEdit=false;
				$enableDelete=false;
			} else if ($p->CampaignStatus=="REJECTED") {
				$class="planRejected";
				$enableEdit=false;
				$enableDelete=false;
			}
			echo("<tr>");
			echo("	<td><b>".$p->CampaignName."</b><br>".$p->Division."<br>".$p->CampaignID."</td>");
			echo("	<td>".date("d-M-Y", strtotime($p->CampaignStart))." s/d<br>".date("d-M-Y", strtotime($p->CampaignEnd))."<br>Jumlah Hari Kerja: ".$p->JumlahHari."</td>");
			echo("	<td id='planStatus".$idx."' class='".$class."'>".$p->CampaignStatus."</td>");
			if ($p->UpdatedDate==null) {
			echo("	<td class='hideOnMobile'>".$p->CreatedBy."<br>".date("d-M-Y H:i:s",strtotime($p->CreatedDate))."</td>");
			} else {
			echo("	<td class='hideOnMobile'>".$p->CreatedBy."<br>".date("d-M-Y H:i:s",strtotime($p->UpdatedDate))."</td>");
			}
			if ($p->IsApproved==0) {
				echo("	<td class='hideOnMobile'></td>");				
			} else {
				echo("	<td class='hideOnMobile'>".$p->ApprovedByName."<br>".date("d-M-Y H:i:s",strtotime($p->ApprovedDate))."</td>");				
			}

			echo("	<td>");
            if($_SESSION["can_read"] == 1) {
            	echo("	<a href='CampaignPlan/view?trxid=".urlencode($p->CampaignID)."', target='_blank'><button class='btnView' planid='".$p->CampaignID."'><i class='glyphicon glyphicon-eye-open'></i></button></a>");
            }
            if($_SESSION["can_update"] == 1 && $enableEdit==true) {
            	echo("	<a href='CampaignPlan/edit?trxid=".urlencode($p->CampaignID)."'><button class='btnEdit' id='btnEdit".$idx."' idx='".$idx."' planid='".$p->CampaignID."'><i class='glyphicon glyphicon-pencil' style='color:green;'></i></button></a>");
            }
            if ($_SESSION["can_delete"]==1 && $enableDelete==true) {
             	echo "	<button class='btnDelete' id='btnDelete".$idx."' idx='".$idx."' planid='".$p->CampaignID."' planname='".$p->CampaignName."'><i class='glyphicon glyphicon-trash' style='color:red;'></i></button>"; 
            }
            echo("	</td>");
			echo("</tr>");
			$idx = $idx+1;
		}*/
		?>
		</tbody>
	</table>
</div>
<fieldset>
	<div align="left">
		<u>Perubahan 29 Sep 2021:</u><br>
		<li>User Tidak Lagi Memilih Mau Ikut Campaign Lama/Tidak (Radiobutton Hilang)<br>
		Jika User akan ikut Qty Campaign Lama, maka ketikan Qty yg Sama di textbox yang Tersedia</li>
		<li>Tambahan Status Save In Background di STEP ke-2, Namun User bisa langsung SAVE tanpa menunggu Proses Save In Background Selesai.</li>
		<li>Campaign Plan yang Direject muncul di List Default</li>
	</div>
</fieldset>

</div>
	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<div style='margin:10px;float:left;'>
			<?php if($_SESSION["can_create"]==1) { ?>
			<a href="CampaignPlan/add"><div class="btn btnAdd" id="btnAdd">NEW PLAN</div></a>
			<?php } ?>
			</div>
		</div>
	</div>
</div>