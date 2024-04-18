<style>
	.custom-date {
		position: relative;
		width: 158px; height: 25px;
		color: white;
	}

	.custom-date:before {
		position: absolute;
		top: 3px; left: 3px;
		content: attr(data-date);
		display: inline-block;
		color: black;
	}

	.custom-date::-webkit-datetime-edit, .custom-date::-webkit-inner-spin-button, .custom-date::-webkit-clear-button {
		display: none;
	}

	.custom-date::-webkit-calendar-picker-indicator {
		position: absolute;
		top: 3px;
		right: 0;
		color: black;
		opacity: 1;
	}
</style>
<div class="container">
	<div class="page-title"></div>
	<div class="panel panel-default">
		<div class="panel-body">
			<div class="row">
				<form id="f-fil-activity-log" action="<?=$formURL?>">
					<div class="col-xs-4">
						<div class="form-group">
							<label>Module</label>
							<select name="module">
								<option value="">ALL</option>
								<?php
								foreach($module as $value){
									echo '<option value="'.$value['Module'].'">'.$value['Module'].'</option>';
								}
								?>
							</select>
						</div>
						<div class="form-group">
							<label>Action</label>
							<select name="action">
								<option value="">ALL</option>
								<?php
								foreach($action as $value){
									echo '<option value="'.$value['Action'].'">'.$value['Action'].'</option>';
								}

								?>
							</select>
						</div>
					</div>
					<div class="col-xs-4">
						<div class="form-group">
							<label>User</label>
							<select name="user">
								<option value="">ALL</option>
								<?php
								foreach($user as $value){
									echo '<option value="'.$value['UserID'].'">'.$value['UserID'].'</option>';
								}
								?>
							</select>
						</div>
						<div class="form-group">
							<label>Trx ID</label>
							<input type="text" name="trx_id">
						</div>
					</div>
					<div class="col-xs-4">
						<div class="form-group"><label>Periode</label></div>
						<div class="form-group">
							<input class="custom-date" type="date" name="start_date" data-date="" data-date-format="DD MMM YYYY" value="<?=date('Y-m-d')?>" style="width:158px;color:#000;">
							s/d
							<input class="custom-date" type="date" name="end_date" data-date="" data-date-format="DD MMM YYYY"  value="<?=date('Y-m-d')?>" style="width:158px;color:#000;">
						</div>
					</div>
					<div class="col-xs-12">
						<input type="button" name="btn-submit" value="Filter" style="width:100%;">
					</div>
				</form>	
			</div>
		</div>
		
	</div>
	
	<table id="tb-activity-log"></table>
	<i>Belum semua module mencatat activity log</i>
	<div style='clear:both;height:20px;'></div>

</div> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script>
$(".custom-date").on("change", function() {
	this.setAttribute(
		"data-date",
		moment(this.value, "YYYY-MM-DD")
		.format( this.getAttribute("data-date-format") )
	)
}).trigger("change");
var tbActivityLogData = {};
tbActivityLog = $('#tb-activity-log').DataTable({
	"order":[[1,"desc"]],
	responsive: true,
	"columnDefs": [
		{"title":"Log ID","targets": 0},
		{"title":"Log Date","targets": 1},
		{"title":"Module","targets": 2},
		{"title":"Trx ID","targets": 3},
		{"title":"Log Activities","targets": 4},
	],
	"columns": [
		{ "data": "LogId" },
		{ "data": "LogDate" },
		{ "data": "Module" },
		{ "data": "TrxID" },
		{ "data": "Log_Activities_Remarks" },
	],
	processing: true,
	serverSide: true,
	ajax: {
		url: $("#f-fil-activity-log").attr('action'),
		type:'POST',
		data: function(d){
			return $.extend(d,tbActivityLogData);
		},
		dataSrc: function ( json ) {
			return json.data;
		}

	},
});

$('#f-fil-activity-log input[name="btn-submit"]').click(function(){

	var object = {};
	var formData = new FormData($("#f-fil-activity-log")[0]);
	formData.forEach(function(value, key){
		object[key] = value;
	});
	tbActivityLogData = object;

	$('#tb-activity-log').DataTable().ajax.reload(null, true);
});
	
</script>