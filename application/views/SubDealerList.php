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
		$.post("<?php echo site_url('PlanPO/GetPlanPOList'); ?>", {
			ChkCancelled			: (($("#ChkCancelled").prop("checked")==true)?1:0),
			ChkDraft				: (($("#ChkDraft").prop("checked")==true)?1:0),
			ChkSaved				: (($("#ChkSaved").prop("checked")==true)?1:0),
			ChkWaiting				: (($("#ChkWaiting").prop("checked")==true)?1:0),
			ChkApproved				: (($("#ChkApproved").prop("checked")==true)?1:0),
			ChkRejected				: (($("#ChkRejected").prop("checked")==true)?1:0),
			csrf_bit				: csrf_bit
		}, function(data){
			//alert(data.result + " : " + data.campaignId);
			if (data.result=="SUCCESS") {
				//alert("SUCCESS");
				list = data.list;

				var CanRead = "<?php echo($_SESSION['can_read']);?>";
				var CanUpdate = "<?php echo($_SESSION['can_update']);?>";
				var CanDelete = "<?php echo($_SESSION['can_delete']);?>";
				//alert(list.length);

				for(var p=0; p<list.length; p++)
				//for(var p=0; p<1; p++)
				{
					// list[p].idx = idx;
					idx = list[p].PlanId;

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
						enableEdit=false;
						enableDelete=false;
					}
					planList+="<tr>";
					planList+="	<td>"+list[p].PlanNo+"<br><b>"+list[p].Division+"</b></td>";
					planList+="	<td>"+list[p].Period1+" s/d "+list[p].Period2+"</td>";
					planList+="	<td id='planStatus"+idx+"' class='"+pclass+"'>"+list[p].PlanStatus+"</td>";
					
					var createdBy = "";
					if (list[p].ModifiedDate==null) {
						createdBy = list[p].CreatedBy+"<br>"+list[p].CreatedDate;
					} else {
						if (list[p].CreatedBy==list[p].ModifiedBy) {
							createdBy = list[p].CreatedBy+"<br>"+list[p].ModifiedDate;
						} else {
							createdBy = list[p].CreatedBy+"<br>"+list[p].CreatedDate;
							createdBy += "<br><b>MODIFIED BY</b><br>";
							createdBy += list[p].ModifiedBy+"<br>"+list[p].ModifiedDate;
						}
					}
					planList+="	<td class='hideOnMobile'>"+createdBy+"</td>";

					if (list[p].IsApproved==0) {
						planList+="	<td class='hideOnMobile'></td>";				
					} else {
						planList+="	<td class='hideOnMobile'>"+list[p].ApprovedBy+"<br>"+list[p].ApprovedDate+"<br>"+((list[p].ApprovalNote==null)?"":list[p].ApprovalNote)+"</td>";
					}

					planList+="	<td>";
		            if(CanRead == "1") {
		            	planList+="	<a href='PlanPO/view?trxid="+encodeURI(list[p].PlanId)+"', target='_blank'><button class='btnView' planid='"+list[p].PlanId+"'><i class='glyphicon glyphicon-eye-open'></i></button></a>";
		            }
		            if(CanUpdate == "1" && enableEdit==true) {
		            	planList+="	<a href='PlanPO/edit?trxid="+encodeURI(list[p].PlanId)+"'><button class='btnEdit' id='btnEdit"+idx+"' idx='"+idx+"' planid='"+list[p].PlanId+"'><i class='glyphicon glyphicon-pencil' style='color:green;'></i></button></a>";
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
				$("#TblPlanPOBody").html(planList);
				//alert(planList);
				//alert("Helloooo");
			} else {
				$("#TblPlanPOBody").html(planList);
				//alert("TIDAK ADA DATA");
			}
		},'json',errorAjax);		
		$(".loading").hide();
	}

	function cancelPlan(i) {
		// alert("btnDelete is Clicked");
		for(var p in list) {

			if (list[p].PlanId==i) {

				var Idx = i;
				var PlanNo  = list[p].PlanNo;
				var Division= list[p].Division;
				// var CurrentStatus = $("#planStatus"+Idx).text();

				if (confirm("Batalkan Rencana PO "+PlanNo+" ?")) {
					var alasan = prompt("Input Alasan Batal");
					alasan = alasan.toUpperCase();

					$(".loading").show();
					var csrf_bit = $("input[name=csrf_bit]").val();
					$.post("<?php echo site_url('PlanPO/CancelPlan'); ?>", {
						PlanNo 		: PlanNo,
						Alasan 		: alasan,
						csrf_bit	: csrf_bit
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

							refreshTable();
						} else {
						}

					},'json',errorAjax);		
					$(".loading").hide();
				}

			}
		}
	}

	$(document).ready(function(){
		refreshTable();

		$(".ChkStatus").click(function(){
			refreshTable();
		});
	});
</script>

<div class="container">
<div class="form_title">SUBDEALER</div>
<div class="clearfix" style="margin: 10px;">
	<div class="row">
		<div class="col-1 col-m-1"><div style="border:1px solid #fff; border-radius:10px; padding:5px;">STATUS :</div></div>
		<div class="col-1 col-m-1"><div class="planDraft" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkDraft" id="ChkDraft" checked="checked">&nbsp;&nbsp;DRAFT</div></div>
		<div class="col-1 col-m-1"><div class="planSaved" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkSaved" id="ChkSaved" checked="checked">&nbsp;&nbsp;SAVED</div></div>
		<div class="col-3 col-m-3"><div class="planWaiting" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkWaiting" id="ChkWaiting" checked="checked">&nbsp;&nbsp;WAITING FOR APPROVAL</div></div>
		<div class="col-2 col-m-2"><div class="planApproved" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkApproved" id="ChkApproved" checked="checked">&nbsp;&nbsp;APPROVED</div></div>
		<div class="col-2 col-m-2"><div class="planRejected" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkRejected" id="ChkRejected">&nbsp;&nbsp;REJECTED</div></div>
		<div class="col-2 col-m-2"><div class="planCancelled" style="border:1px solid #ccc; border-radius:10px; padding:5px;background-color:#ccc;"><input type="checkbox" class="ChkStatus" name="ChkCancelled" id="ChkCancelled">&nbsp;&nbsp;CANCELLED</div></div>
	</div>
</div>
<div class="clearfix">
	<table id="TblPlanPO" class="table table-striped table-bordered" cellspacing="0">
		<thead id="TblPlanPOHead">
			<tr>
				<th width="15%">Plan</th>
				<th width="20%">Periode</th>
				<th width="15%">Status</th>
				<th width="20%" class='hideOnMobile'>CreationInfo</th>
				<th width="20%" class='hideOnMobile'>ApprovalInfo</th>
		        <th width="10%"></th>
			</tr>
		</thead>
		<tbody id="TblPlanPOBody">
		</tbody>
	</table>
</div>


</div>
	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<div style='margin:10px;float:left;'>
			<?php if($_SESSION["can_create"]==1) { ?>
			<a href="PlanPO/add"><div class="btn btnAdd" id="btnAdd">NEW PO PLAN</div></a>
			<?php } ?>
			</div>
		</div>
	</div>
</div>