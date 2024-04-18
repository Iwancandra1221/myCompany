<div class="container">
	<div class="form_title" style="text-align: center;">REKAP MASTER CUSTOMER</div>
	<div class="text-right">
		<button onclick="exportdata();">
			Export Excel
		</button>
	</div>
    <div class="row">
        <div class="col-12">
            <table id="listcustomer" class="table table-striped">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Telp</th>
                        <th>Alamat</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $('#listcustomer').dataTable( {
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": '<?php echo site_url('Rekapmscustomer/list'); ?>',
        "oLanguage": {
            "sLengthMenu": "Menampilkan _MENU_ Data per halaman",
            "sZeroRecords": "Maaf, Data tidak ada",
            "sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
            "sInfoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
            "sSearch": "",
            "sInfoFiltered": "",
            "oPaginate": {
                "sPrevious": "Sebelumnya",
                "sNext": "Berikutnya"
            }
        }
    });

});

     function exportdata(){
     	var search ='&search='+$('input[type="search"]').val();

		window.location.href = '<?php echo site_url('Rekapmscustomer/ExportExcel') ?>' + '?search=' + search;
    }
</script>
