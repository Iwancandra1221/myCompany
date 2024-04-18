<script>
	$(document).ready(function(){
		var gl_account = $('<select name="gl_account_dtl[]"><select>')
			.append('<option value="A">Option 1</option>')
			.append('<option value="B">Option 2</option>');
		var costcenter = $('<select name="costcenter[]"><select>')
			.append('<option value="A">Option 1</option>')
			.append('<option value="B">Option 2</option>');
		var taxcode = $('<select name="tax_code[]"><select>')
			.append('<option value="A">Option 1</option>')
			.append('<option value="B">Option 2</option>');
		$("#addDetail").click(function(){
			var ga = gl_account.clone();
			var cc = costcenter.clone();
			var tc = taxcode.clone();
			var btnDelete = $('<button type="button" title="Delete"></button>').addClass('fa').addClass('fa-times').addClass('btnRed');
			$(btnDelete).click(function(){
				$.displayConfirm("Are you sure to delete this row?",function(){
					$(btnDelete).parent().parent().remove();
				});
			});
			$("#tblDetail").find("tbody").append($("<tr></tr>")
				.append($('<td></td>').append(ga))
				.append($('<td></td>').append($('<input type="text" name="info[]" />')))
				.append($('<td></td>').append($('<input type="text" name="marks[]" />')))
				.append($('<td></td>').append(cc))
				.append($('<td></td>').append(tc))
				.append($('<td></td>').append($('<input type="checkbox" value="1" name="reclaimable[]" />')))
				.append($('<td></td>').append($('<input type="text" name="reclaimablerate[]" />')))
				.append($('<td></td>').append(btnDelete))
				);
		});
		$("#addDetail2").click(function(){
			var ga = gl_account.clone();
			var cc = costcenter.clone();
			var tc = taxcode.clone();
			var btnDelete = $('<button type="button" title="Delete"></button>').addClass('fa').addClass('fa-times').addClass('btnRed');
			$(btnDelete).click(function(){
				$.displayConfirm("Are you sure to delete this row?",function(){
					$(btnDelete).parent().parent().remove();
				});
			});
			$("#tblDetail2").find("tbody").append($("<tr></tr>")
				.append($('<td></td>').append(ga))
				.append($('<td></td>').append($('<input type="text" name="info[]" />')))
				.append($('<td></td>').append($('<input type="text" name="marks[]" />')))
				.append($('<td></td>').append(cc))
				.append($('<td></td>').append(tc))
				.append($('<td></td>').append($('<input type="checkbox" value="1" name="reclaimable[]" />')))
				.append($('<td></td>').append($('<input type="text" name="reclaimablerate[]" />')))
				.append($('<td></td>').append(btnDelete))
				);
		});
		$("#addDetail3").click(function(){
			var ga = gl_account.clone();
			var cc = costcenter.clone();
			var tc = taxcode.clone();
			var btnDelete = $('<button type="button" title="Delete"></button>').addClass('fa').addClass('fa-times').addClass('btnRed');
			$(btnDelete).click(function(){
				$.displayConfirm("Are you sure to delete this row?",function(){
					$(btnDelete).parent().parent().remove();
				});
			});
			$("#tblDetail3").find("tbody").append($("<tr></tr>")
				.append($('<td></td>').append(ga))
				.append($('<td></td>').append($('<input type="text" name="info[]" />')))
				.append($('<td></td>').append($('<input type="text" name="marks[]" />')))
				.append($('<td></td>').append(cc))
				.append($('<td></td>').append(tc))
				.append($('<td></td>').append($('<input type="checkbox" value="1" name="reclaimable[]" />')))
				.append($('<td></td>').append($('<input type="text" name="reclaimablerate[]" />')))
				.append($('<td></td>').append(btnDelete))
				);
		});
		$("#tablist li a").click(function(){
			$(".tabdesclist .tabdetail").hide()
			$('#tablist li').removeClass('active');
			$($(this).attr('href')).show();
			$(this).parent().addClass('active');
		});
	});
</script>
<h3>Template for Bank Payment Memo(A/P) > Add</h3>
<?php
	echo form_open('templateap/add');
?>
<div class="headdata">
	<div class="inputField">
		<span>
			<label for="template_no">Template No.</label>
		</span>
		<?php
			$attr = array('id'=>'template_no',
				'readonly'=>true);
			echo BuildInput('text','template_no','001',array(),$attr);
		?>
	</div>
	<div class="inputField">
		<span>
			<label for="name">Name</label>
		</span>
		<?php
			$attr = array('id'=>'name',
				'class'=>'maxtodaydtpicker');
			echo BuildInput('text','name','',array(),$attr);
		?>
	</div>
	<div class="inputField">
		<span>
			<label for="branch_code">Branch</label>
		</span>
		<?php
			$objs = array();
			$objs['001'] = '001-Cabang A';
			$objs['002'] = '002-Cabang B';
			$attr = 'id="branch_code"';
			echo BuildInput('dropdown','branch_code','',$objs,$attr);
		?>
	</div>
	<div class="inputField">
		<span>
			<label for="currency_code">Currency</label>
		</span>
		<?php
			$objs = array();
			$objs['IDR'] = 'IDR-Rupiah';
			$objs['USD'] = 'USD-US Dollar';
			$attr = 'id="currency_code"';
			echo BuildInput('dropdown','currency_code','',$objs,$attr);
		?>
	</div>
	<div class="inputField">
		<span>
			<label for="dep_code">Department</label>
		</span>
		<?php
			$objs = array();
			$objs['001'] = '001-Department A';
			$objs['002'] = '002-Department B';
			$attr = 'id="dep_code"';
			echo BuildInput('dropdown','dep_code','',$objs,$attr);
		?>
	</div>
	<div class="inputField">
		<span>
			<label for="gl_accounts">GL Account</label>
		</span>
		<?php
			$objs = array();
			$objs['001'] = '001-GA A';
			$objs['002'] = '002-GA B';
			$attr = 'id="gl_accounts"';
			echo BuildInput('dropdown','gl_accounts','',$objs,$attr);
		?>
	</div>
	<div class="inputField">
		<span>
			<label for="validation_by">Validator</label>
		</span>
		<?php
			$objs = array();
			$objs['001'] = '001-User A';
			$objs['002'] = '002-User B';
			$attr = 'id="validation_by"';
			echo BuildInput('dropdown','validation_by','',$objs,$attr);
		?>
	</div>
	<div class="inputField">
		<span>
			<label for="memo">Memo</label>
		</span>
		<?php
			$attr = array('id'=>'memo');
			echo BuildInput('textarea','memo','',array(),$attr);
		?>
	</div>
</div>
<button type="submit" name="btnSubmit">
	Save
</button>
<a href="<?php echo site_url('templateap'); ?>">
<button type="button">Back</button>
</a>
<div id="tablist" class="clearfix">
	<ul>
		<li class="active">
			<a href="#Tab1">Tab 1</a>
		</li>
		<li>
			<a href="#Tab2">Tab 2</a>
		</li>
		<li>
			<a href="#Tab3">Tab 3</a>
		</li>
	</ul>
</div>
<div class="tabdesclist clearfix">
	<div id="Tab1" class="tabdetail">
		<table id="tblDetail" width="100%" class="dataTable display" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th>GL Account
					</th>
					<th>Info
					</th>
					<th>Marks
					</th>
					<th>Cost Center
					</th>
					<th>Tax Code
					</th>
					<th>Reclaimable
					</th>
					<th>Reclaimable Rate
					</th>
					<th>
						&nbsp;
					</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<div class="buttonlist right">
			<button type="button" id="addDetail">
				Add Detail
			</button>
		</div>
	</div>
	<div id="Tab2" class="tabdetail">
		<table id="tblDetail2" width="100%" class="dataTable display" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th>GA 2
					</th>
					<th>Info 2
					</th>
					<th>Marks 2
					</th>
					<th>CC 2
					</th>
					<th>TC 2
					</th>
					<th>Reclaimable 2
					</th>
					<th>Reclaimable Rate 2
					</th>
					<th>
						&nbsp;
					</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<div class="buttonlist right">
			<button type="button" id="addDetail2">
				Add Detail
			</button>
		</div>
	</div>
	<div id="Tab3" class="tabdetail">
		<table id="tblDetail3" width="100%" class="dataTable display" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th>GA 3
					</th>
					<th>Info 3
					</th>
					<th>Marks 3
					</th>
					<th>CC 3
					</th>
					<th>TC 3
					</th>
					<th>Reclaimable 3
					</th>
					<th>Reclaimable Rate 3
					</th>
					<th>
						&nbsp;
					</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<div class="buttonlist right">
			<button type="button" id="addDetail3">
				Add Detail
			</button>
		</div>
	</div>
</div>
<?php
	echo form_close();
?>