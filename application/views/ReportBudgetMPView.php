<div class="container">
    <div class="page-title"><?php echo(strtoupper($title));?></div>
    <?php echo form_open("ReportPenerimaanPembayaran/Proses", array("target"=>"_blank", "onsubmit"=>"return false;")) ?>
        <div class="form-container">
            <div class="row">
                <div class="col-2">Periode</div>
                <div class="col-4">
                    <input type="text" class="form-control" placeholder="dd/mm/yyyy" id="dp1" name="dp1" autocomplete="off" required  >
                </div>
                <div class="col-2" align="center">SD</div>
                <div class="col-4">
                    <input class="form-control" placeholder="dd/mm/yyyy" id="dp2" name="dp2" autocomplete="off" required  >
                </div>
            </div>
             <div class="row">
                <div class="col-2">Merk</div>
                <div class="col-10">
                    <select class="form-control" id="merk" name="merk">
                        <option value="ALL">ALL</option>
                        <option value="MIYAKO">MIYAKO</option>
                        <option value="RINNAI">RINNAI</option>
                        <option value="SHIMIZU">SHIMIZU</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-2">Report</div>
                <div class="col-2">
                    <input type="radio" id="report" name="report" value="po" checked> No PO
                </div>
                <div class="col-2">
                    <input type="radio" id="report" name="report" value="merk"> Merk Detail 
                </div>
                <div class="col-2">
                    <input type="radio" id="report" name="report" value="merksummary"> Merk Summary
                </div>
                </div>
            </div>
            <div class="row" align="center" style="padding-top:20px;">
                <button type="submit" class="btn_custom btn-primary" name="btnExcel" onclick="exportToExcel()">
                    Export Excel
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() { 
        $('#dp1').datepicker({
            format: "dd/mm/yyyy",
            autoclose: true
        }).on('changeDate', function(e) {
            var StartDt = $('#dp1').datepicker('getDate');
            $('#dp2').datepicker("setStartDate", StartDt);
        });
        
        $('#dp2').datepicker({
            format: "dd/mm/yyyy",
            autoclose: true
        }).on('changeDate', function(e) {
            var EndDt = $('#dp2').datepicker('getDate');
            $('#dp1').datepicker("setEndDate", EndDt);
        });
    });

    function exportToExcel() {

        var dariDate = $('#dp1').val();
        var sampaiDate = $('#dp2').val();

        var dariFormatted = formatDate(dariDate);
        var sampaiFormatted = formatDate(sampaiDate);

        var formData = {
            dari: dariFormatted,
            sampai: sampaiFormatted,
            merk: $('#merk').val(),
            report: $('input[name=report]:checked').val()
        };

        var queryString = $.param(formData);

        var newListTab = window.open('<?php echo base_url('ReportBudgetMP/List'); ?>?' + queryString, '_blank');
        newListTab.focus();
    }

    function formatDate(dateString) {
        var dateParts = dateString.split("/");
        var formattedDate = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
        return formattedDate;
    }

</script>
