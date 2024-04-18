<div class="container">
    <div class="page-title"><?php echo(strtoupper($title));?></div>
    <?php echo form_open("ReportPenerimaanPembayaran/Proses", array("target"=>"_blank", "onsubmit"=>"return false;")) ?>
        <div class="form-container">
            <div class="row">
                <div class="col-2">Tanggal Bayar</div>
                <div class="col-4">
                    <select class="form-control" id="tanggal" name="tanggal" onchange="GetSupplier()">
                        <option value="ALL">Loading...</option>
                    </select>
                </div>
                <div class="col-2">Supplier</div>
                <div class="col-4">
                    <select class="form-control" id="supplier" name="supplier">
                        <option value="ALL">Loading...</option>
                    </select>
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
    GetTanggal();
    
    function setLoadingState(element) {
        element.innerHTML = '<option value="ALL">Loading...</option>';
        document.querySelector('[name="btnExcel"]').disabled = true;
    }

    function resetLoadingState(element) {
        document.querySelector('[name="btnExcel"]').disabled = false;
    }

    function GetTanggal() {
        setLoadingState(document.getElementById('tanggal'));

        const url = '<?php echo site_url('LaporanPembayaran/ListTanggalPembayaran'); ?>';
        const supplierValue = document.getElementById('supplier').value;

        $.ajax({
            url: url,
            type: 'POST',
            data: { supplier: supplierValue },
            success: function (response) {
                try {
                    const parsedData = JSON.parse(response);

                    if (parsedData && parsedData.result === 'sukses') {
                        const result = document.getElementById('tanggal');
                        result.innerHTML = '<option value="ALL">ALL</option>';

                        parsedData.data.forEach(item => {
                            if (item !== null) {
                                const option = document.createElement('option');
                                option.value = item.Tgl_Pembayaran;
                                option.text = item.Tgl_Pembayaran;
                                result.appendChild(option);
                            }
                        });

                        GetSupplier();
                    } else {
                        console.error('Error: Unexpected response format or result is not "success".');
                    }
                } catch (error) {
                    console.error('Error parsing JSON response:', error.message);
                }
            },
            error: function (error) {
                console.error('Error fetching tanggal:', error.statusText);
            },
            complete: function () {
                resetLoadingState(document.getElementById('tanggal'));
            }
        });
    }



    function GetSupplier() {
        setLoadingState(document.getElementById('supplier'));

        const url = '<?php echo site_url('LaporanPembayaran/ListSupplierPembayaran'); ?>';
        const tanggalValue = document.getElementById('tanggal').value;

        $.ajax({
            url: url,
            type: 'POST',
            data: { tanggal: tanggalValue },
            success: function (response) {
                try {
                    const parsedData = JSON.parse(response);

                    if (parsedData && parsedData.result === 'sukses') {
                        const result = document.getElementById('supplier');
                        result.innerHTML = '<option value="ALL">ALL</option>';

                        parsedData.data.forEach(item => {
                            if (item !== null) {
                                const option = document.createElement('option');
                                option.value = item.Kd_Supl;
                                option.text = item.Nm_Supl;
                                result.appendChild(option);
                            }
                        });

                    } else {
                        console.error('Error: Unexpected response format or result is not "success".');
                    }
                } catch (error) {
                    console.error('Error parsing JSON response:', error.message);
                }
            },
            error: function (error) {
                console.error('Error fetching tanggal:', error.statusText);
            },
            complete: function () {
                resetLoadingState(document.getElementById('supplier'));
            }
        });
    }

    function exportToExcel() {
        var tanggal = document.getElementById('tanggal').value;
        var supplier = document.getElementById('supplier').value;

        var encodedTanggal = btoa(tanggal);
        var encodedSupplier = btoa(supplier);

        encodedTanggal = encodedTanggal.replace(/[^a-zA-Z0-9]/g, '');
        encodedSupplier = encodedSupplier.replace(/[^a-zA-Z0-9]/g, '');

        var url = "<?php echo site_url('LaporanPembayaran/export'); ?>/" + encodedTanggal + '/' + encodedSupplier;

        window.open(url, '_blank');
    }


</script>
