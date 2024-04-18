<body onload="focusOnInput()">
	<div class="container">
		<div class="form_title">
			<div style="text-align: center;">
				INVALIDATE SCAN QR<hr>
			</div>
		</div>	
		<div class="row">
			<div class="col-12"><b>QR CODE</b></div>
			<div class="col-sm-6 col-md-6 col-lg-6">
				<!-- <input type="text" id="qr_code" class="form-control form-control-dark" oninput="checkForScan()"> -->
				<input type="text" id="qr_code" class="form-control form-control-dark">
			</div>
		</div>
		<div class="row">
			<div class="col-6">
				<b>
					INVALIDATE HISTORY
				</b>
			</div>
			<div class="col-6 text-right">
				<button class="btn btn-sm btn-dark" onclick="clearList()">
					CLEAR LIST
				</button>
			</div>
		</div>
		
		<div class="row">
			<table class="table table-striped">
				<thead>
					<tr>
						<td width="50px" align="center">No</td>
						<td>Kode Barang</td>
						<td>No Seri</td>
						<td width="500px">Status Scan</td>
					</tr>
				</thead>
				<tbody id="detail">
					<tr id="awal">
						<td colspan="4">
							Silahkan Scan atau Masukan QR Code
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<script>

		function focusOnInput() {
			document.getElementById("qr_code").focus();
		}

		var no = 1;
		let isScanned = true;
		var qr = document.getElementById('qr_code');
		var qr_code = '';
		var method= 'MANUAL';
		var action = 0;
		var scan = 0;

		document.getElementById('qr_code').addEventListener('keydown', function (event) {
		    var isAlphanumeric = /^[a-zA-Z0-9]$/;

		    if(action==0){
			    if (event.key === 'Enter' || event.key === 'Tab') {
			    	action++;
			    	method= 'MANUAL';

			    	var characterCount = qr.value.length;
			        isScanned = true;

			    	if(characterCount>1){
			        	submitForm();
				    	event.preventDefault();
			    	}

			    } else if (isAlphanumeric.test(event.key)) {
			        if (qr.value !== '') {
			            isScanned = false;
			        } else {
			            isScanned = true;
			        }
			    }else if(event.key === 'Shift'){
			    	setTimeout(function () {
			    		checkForScan();
			    	}, 2000);
			    }else{
			    	scan=0;
			    }
			}

		});

		function checkForScan(){
			if(scan==0){
				method= 'SCAN';
				submitForm();
				scan++;
			}
		}

		function submitForm() {

		    var awal = document.getElementById('awal');
		    if (awal) {
		        awal.remove();
		    }
	        
	        var data_status = 'NO';

		    var tamp = '<tr id="baris_data"><td colspan="4">Loading...</td></tr>';

		    qr.placeholder = qr.value;

		    const qr_code = qr.value;

		    const url = '<?php echo base_url('Invalidateqr/prosesSend'); ?>';

		    const postData = {
		        qr_code: qr_code,
		        method: method
		    };

	        var qrCodeValue = qr_code;
	        const hasPipe = qrCodeValue.includes('|');
	        let qrCodeArray;

	        if (hasPipe) {
	            qrCodeArray = qrCodeValue.split('|');
	        } else {
	            qrCodeValue = qrCodeValue + '|';
	            qrCodeArray = qrCodeValue.split('|');
	        }

	        var noseri = '';
	        var kode_brg = '';

	        if (qrCodeArray[0] !== '') {
	            kode_brg = qrCodeArray[0].trim();
	        }

	        if (qrCodeArray[1] !== '') {
	            noseri = qrCodeArray[1].trim();
	        }

		    document.getElementById('detail').innerHTML += tamp;

	        const xhr = new XMLHttpRequest();

	        xhr.open('POST', url, true);
	        xhr.setRequestHeader('Content-Type', 'application/json');

	        xhr.onload = function () {
	           

	            if (xhr.status >= 200 && xhr.status < 300) {
	                var responseData = xhr.responseText;

	                if (isJSON(responseData)) {
	                    try {
	                        const responseDataParsed = JSON.parse(responseData);
	                        const data = responseDataParsed.data;

	                        if (data != null) {
	                            const message = responseDataParsed.message;

	                            if (data && data.status_scan_campaign) {
	                                const utcTimestamp = data.scan_date_campaign;

	                                const utcDate = new Date(utcTimestamp);
	                                const wibString = utcDate.toLocaleString('en-US', {
	                                    timeZone: 'Asia/Jakarta',
	                                    year: 'numeric',
	                                    month: 'short',
	                                    day: 'numeric',
	                                    hour: 'numeric',
	                                    minute: 'numeric',
	                                    second: 'numeric',
	                                    hour12: true
	                                });

	                                data_status = `YES - ${data.value} - ${data.wallet_id} - ${wibString}`;
	                            }

	                            var detail = `<td align="center">${no}</td><td>${kode_brg}</td><td>${noseri}</td><td>${data_status}</td>`;
	                            document.getElementById('baris_data').innerHTML = detail;
	                            var baris_data = document.getElementById('baris_data');
	                            if (baris_data) {
	                                baris_data.removeAttribute('id');
	                            }

	                            no++;
	                            action = 0;
	                            


	                        } else {
	                            alert(responseDataParsed.message);
	                            document.getElementById('baris_data').remove();
	                        }
	                    } catch (error) {
	                        alert('Error parsing JSON');
	                        document.getElementById('baris_data').remove();
	                    }
	                } else {
	                    document.getElementById('baris_data').remove();
	                }
	            } else {
	                alert(`Error: ${xhr.status} - ${xhr.statusText}`);
	                document.getElementById('baris_data').remove();
	            }

	            action = 0;
	            

	        };

	        xhr.onerror = function () {
	            document.getElementById('baris_data').remove();

	            action = 0;
	            
	        };

	        xhr.send(JSON.stringify(postData));

	        qr.value = '';
	    }

		function isJSON(str) {
		    try {
		        JSON.parse(str);
		        return true;
		    } catch (e) {
		        return false;
		    }
		}

		function clearList() {
		    document.getElementById('detail').innerHTML = '<tr id="awal"><td colspan="4">Silahkan Scan atau Masukan QR Code</td></tr>';
		    no=1;
		    action = 0;
		    
		}
	</script>
</body>