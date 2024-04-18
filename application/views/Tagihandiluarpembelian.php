<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style type="text/css">
	fieldset {
		background-color: #eeeeee;
	}

	legend {
		background-color: gray;
		color: white;
		padding: 5px lengthValuepx;
		font-size: 15px;
		padding: 10px;
	}
	.disablingDiv{
		z-index:99999;
	
		/* make it cover the whole screen */
		position: fixed; 
        top: 0%; 
        left: 0%; 
        width: 100%; 
        height: 100%; 
        overflow: hidden;
        margin:0;
        /* make it white but fully transparent */
        background-color: white; 
        opacity:0.5;  
	}
	.loader {
		position: absolute;
		left: 50%;
		top: 50%;
		z-index: 9999999;
		margin: -75px 0 0 -75px;
		border: 16px solid #f3f3f3;
		border-radius: 50%;
		border-top: 16px solid #3498db;
		width: 120px;
		height: 120px;
		-webkit-animation: spin 2s linear infinite;
		animation: spin 2s linear infinite;
	}
	@keyframes spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}
</style>

<div id="disablingDiv" class="disablingDiv">
</div>
<div id="loading" class="loader"></div>
<form method="POST" id="prosestransaksi">
	<div class="container">
		<div class="row">
			<div class="page-title">TAGIHAN DILUAR PEMBELIAN</div>

			<fieldset class="form-group border p-3">
				<legend class="w-auto px-2">CETAK LAPORAN BERDASARKAN MASA PELAPORAN</legend>
				<table class="table table-striped">
					<tr>
						<td width="100px" style="padding-top: 25px;">
							Bulan :
						</td>
						<td style="padding-top: 18px;">
							<select class="form-control" name="reportbulan" id="reportbulan"></select>
						</td>
						<td width="100px" style="padding-top: 25px;">
							Tahun :
						</td>
						<td style="padding-top: 18px;">
							<select class="form-control" name="reporttahun" id="reporttahun"></select>
						</td>
						<td width="100px">
							<button type="button" class="btn btn-primary-dark" onclick="export_data('buku_ppn');">
								Report Buku <br>PPN <br>(EXCEL)
							</button>
						</td>
						<td width="100px">
							<button type="button" class="btn btn-primary-dark" onclick="export_data('import_pajak');">
								CSV Import Pajak <br>Masukan<br>(EFaktur)
							</button>
						</td>
						<td width="100px">
							<button type="button" class="btn btn-primary-dark" onclick="export_data('unifikasi');">
								Report Buku <br>PPh<br>(Unifikasi)
							</button>
						</td>
						<td width="100px">
							<button type="button" class="btn btn-primary-dark" onclick="export_data('pph21');">
								Report Buku <br>PPh<br>(PPh 21)
							</button>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="form-group border p-3">
				<legend class="w-auto px-2">TAGIHAN</legend>
				<table class="table table-striped">
					<tr>
						<td colspan="6">
							<b>DETAIL TAGIHAN</b>
						</td>
					</tr>
					<tr>
						<td width="220px">
							Nomor Transaksi
						</td>
						<td colspan="2">
							<input type="text" name="nomor_transaksi" class="form-control" id="nomor_transaksi" value="AUTONUMBER" readonly>
						</td>
						<td colspan="3">
							<input type="text" name="tanggal_transaksi" class="form-control" id="tanggal_transaksi" value="" readonly required>
						</td>
					</tr>
					<tr>
						<td width="220px">
							Nomor Tagihan
						</td>
						<td colspan="1">
							<input type="text" name="nomor_tagihan" class="form-control" id="nomor_tagihan" required>
						</td>
						<td>
							<button type="button" class="btn btn-light-dark" onclick="nomortagihan()">
								...
							</button>
						</td>
						<td colspan="3" style="padding-top: 15px;">
							<input type="checkbox" name="ckb_tidak_potong_pph" id="ckb_tidak_potong_pph" value="1" onchange="potongpph();"> Tidak Pot. PPH
						</td>
					</tr>
					<tr>
						<td>
							Tanggal Tagihan
							<input type="hidden" name="pemberi_jasa" id="pemberi_jasa" value="Supplier">
						</td>
						<td>
							<input type="text" name="tanggal_tagihan" id="tanggal_tagihan" class="form-control" placeholder="DD-MM-YYYY" onchange="gettanggal_tagihan();" required>
						</td>
						<td colspan="2" style="padding-top: 15px;">
							<input type="radio" name="rd_pemberi_jasa" id="rd_pemberi_jasa_supplier" value="1" onclick="rdpemberijasa('Supplier');" checked> Supplier
						</td>
						<td colspan="2" style="padding-top: 15px;">
							<input type="radio" name="rd_pemberi_jasa" id="rd_pemberi_jasa_customer" value="0" onclick="rdpemberijasa('Customer');" value="1"> Customer
						</td>
					</tr>
					<tr>
						<td>
							Pemberi Jasa
						</td>
						<td>
							<input type="text" name="nama_pemberi_jasa" id="nama_pemberi_jasa" class="form-control" placeholder="Nama Pemberi Jasa" onchange="nama_pemberi_jasa_action()">
							<input type="hidden" name="kd_pemberi_jasa" id="kd_pemberi_jasa" class="form-control">
						</td>
						<td width="50px">
							<button type="button" class="btn btn-light-dark" onclick="namapemberijasa()">
								...
							</button>
						</td>
						<td colspan="3" rowspan="4">
							<!-- <textarea class="form-control" name="detail_tagihan_textarea" id="detail_tagihan_textarea" style="height: 185px;" onchange="nama_pemberi_jasa_textarea()"></textarea> -->
							<textarea class="form-control" name="detail_tagihan_textarea" id="detail_tagihan_textarea" style="height: 185px;" readonly></textarea>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="text" name="npwp" id="npwp" class="form-control" placeholder="NPWP Pemberi Jasa" value="" onchange="nama_pemberi_jasa_action()">
						</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="text" name="nik_pemberi_jasa" id="nik_pemberi_jasa" class="form-control" placeholder="NIK Pemberi Jasa" value="" onchange="nama_pemberi_jasa_action()">
						</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="text" name="alamat_pemberi_jasa" id="alamat_pemberi_jasa" class="form-control" placeholder="Alamat Pemberi Jasa" value="" onchange="nama_pemberi_jasa_action()">
						</td>
					</tr>
					<tr>
						<td>
							Cabang
						</td>
						<td>
							<input type="text" name="cabang" id="cabang" class="form-control">
						</td>
						<td colspan="3"></td>
					</tr>
					<tr>
						<td>
							Nama Jasa
						</td>
						<td colspan="5">
							<input type="text" name="nama_jasa" id="nama_jasa" class="form-control" oninput="nama_jasa_onkeypress(this.value);" required>
						</td>
					</tr>
				</table>

				<table class="table table-striped">
					<tr>
						<td colspan="6">
							<b>
								MASA PEMOTONGAN PPH
							</b>
						</td>
					</tr>
					<tr>
						<td></td>
						<td width="50px" style="padding-top: 18px;">
							Bulan
						</td>
						<td width="150px">
							<select name="bulan_pemotongan_pph" id="bulan_pemotongan_pph" class="form-control" onchange="actionbulantahun();">
							</select>
						</td>
						<td width="50px" style="padding-top: 18px;">
							Tahun
						</td>
						<td width="150px">
							<select name="tahun_pemotongan_pph" id="tahun_pemotongan_pph" class="form-control" onchange="actionbulantahun();">
							</select>
						</td>
						<td></td>
					</tr>
				</table>

				<table class="table table-striped">
					<tr>
						<td colspan="6">
							<b>
								DETAIL PEMOTONGAN PPH
							</b>
						</td>
					</tr>
					<tr>
						<td width="220px">
							Kode Objek Pajak
						</td>
						<td colspan="2">
							<input type="text" name="kode_objek_pajak" id="kode_objek_pajak" class="form-control" readonly>
						</td>
						<td width="50px">
							<button type="button" id="btnobjekpajak" class="btn btn-light-dark" onclick="objekpajak();">
								...
							</button>
						</td>
						<td colspan="3">
							<input type="text" name="nama_objek_pajak" id="nama_objek_pajak" class="form-control" readonly>
						</td>
					</tr>
					<tr>
						<td>
							DPP Pemotongan (Rp)
						</td>
						<td colspan="2">
							<input type="text" name="dpp_pemotongan" id="dpp_pemotongan" class="form-control text-right" value="0">
						</td>
						<td colspan="3"></td>
					</tr>
					<tr>
						<td>
							Jenis PPH
						</td>
						<td style="border: thin solid #dadada;" width="150px">
							<input type="radio" name="rd_pph" id="rd_pph23" value="PPH23" disabled checked> PPH 23
						</td>
						<td style="border: thin solid #dadada;" width="150px">
							<input type="radio" name="rd_pph" id="rd_pph4" value="PPH4" disabled> PPH 4(2)
						</td>
						<td style="border: thin solid #dadada;" width="150px">
							<input type="radio" name="rd_pph" id="rd_pph21" value="PPH21" disabled> PPH 21
						</td>
						<td style="border: thin solid #dadada;" width="150px">
							<input type="radio" name="rd_pph" id="rd_pph15" value="PPH15" disabled> PPH 15
						</td>
						<td></td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="ckb_tarif_manual" id="ckb_tarif_manual" value="1" onchange="manual();"> Tarif (%) Manual
						</td>
						<td style="border: thin solid #dadada;">
							<input type="text" name="manual_pph23" id="manual_pph23" class="form-control text-right" readonly value="0%">
						</td>
						<td style="border: thin solid #dadada;">
							<input type="text" name="manual_pph4" id="manual_pph4" class="form-control text-right" readonly value="0%">
						</td>
						<td style="border: thin solid #dadada;">
							<input type="text" name="manual_pph21" id="manual_pph21" class="form-control text-right" value="0%" style="width:50%; float: left;" readonly>
							<input type="text" name="manual_pph212" id="manual_pph212" class="form-control text-right" value="0%" style="width:50%; float: left;" readonly>
						</td>
						<td style="border: thin solid #dadada;">
							<input type="text" name="manual_pph15" id="manual_pph15" class="form-control text-right" readonly value="0%">
						</td>
						<td>Keterangan</td>
					</tr>
					<tr>
						<td>
							Jumlah Dipotong (Rp)
						</td>
						<td style="border: thin solid #dadada;">
							<input type="text" name="jumlah_pph23" id="jumlah_pph23" class="form-control text-right" readonly value="0">
						</td>
						<td style="border: thin solid #dadada;">
							<input type="text" name="jumlah_pph4" id="jumlah_pph4" class="form-control text-right" readonly value="0">
						</td>
						<td style="border: thin solid #dadada;">
							<input type="text" name="jumlah_pph21" id="jumlah_pph21" class="form-control text-right" readonly value="0">
						</td>
						<td style="border: thin solid #dadada;">
							<input type="text" name="jumlah_pph15" id="jumlah_pph15" class="form-control text-right" readonly value="0">
						</td>
						<td>
							<select name="keterangan" id="keterangan" class="form-control"></select>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="ckb_nomor_skb" id="ckb_nomor_skb" value="1" onchange="f_nomor_skb();"> Nomor SKB / Suket
						</td>
						<td colspan="3">
							<input type="text" name="nomor_skb" id="nomor_skb" class="form-control" readonly>
						</td>
						<td colspan="2"></td>
					</tr>
					<tr>
						<td style="padding-top: 18px;">
							Kategori Biaya
						</td>
						<td colspan="2">
							<select name="kategori_biaya" id="kategori_biaya" class="form-control">
								<option value="LAIN_LAIN">LAIN_LAIN</option>
							</select>
						</td>
						<td style="padding-top: 18px;">
							Kategori
						</td>
						<td colspan="3">
							<select name="kategori" id="kategori" class="form-control">
								<option value=""></option>
							</select>
						</td>
					</tr>
				</table>
			</fieldset>

			<fieldset class="form-group border p-3">
				<legend class="w-auto px-2">FAKTUR PAJAK</legend>
				<table class="table table-striped">
					<tr>
						<td colspan="6">
							<b>
								DETAIL FAKTUR PAJAK
							</b>
						</td>
					</tr>
					<tr>
						<td colspan="6">
							<input type="checkbox" name="chk_tagihan_ada" id="chk_tagihan_ada" value="1" onchange="tagihan_ada();"> Tagihan Ada Faktur Pajak
						</td>
					</tr>
					<tr>
						<td width="220px">
							No FP Masukan
						</td>
						<td colspan="2">
							<input type="text" name="no_fp_masukan" class="form-control" id="no_fp_masukan" readonly>
						</td>
						<td colspan="3">
							<!-- <button type="button" class="btn btn-light-dark" id="btn_brws_no_fp_masukan" onclick="fpmasukan();">
								...
							</button> -->
						</td>
					</tr>
					<tr>
						<td>
							Tanggal FP
						</td>
						<td>
							<input type="text" name="tanggal_fp" id="tanggal_fp" class="form-control" placeholder="DD-MM-YYYY" readonly>
						</td>
						<td colspan="2" style="padding-top: 15px;">
							<input type="radio" name="rd_pemberi_jasa_fp" id="rd_pemberi_jasa_fp_supplier" value="1" checked> Supplier
						</td>
						<td colspan="2" style="padding-top: 15px;">
							<input type="radio" name="rd_pemberi_jasa_fp" id="rd_pemberi_jasa_fp_customer" value="1"> Customer
						</td>
					</tr>
					<tr>
						<td>
							Nama Pemberi Jasa
						</td>
						<td>
							<input type="text" name="nama_pemberi_jasa_fp" id="nama_pemberi_jasa_fp" class="form-control" readonly>
						</td>
						<td width="50px">
							<button type="button" class="btn btn-light-dark" id="btn_brws_nama_pemberi_jasa">
								...
							</button>
						</td>
						<td colspan="3">
							<textarea class="form-control" name="detail_pemberi_jasa_fp" id="detail_pemberi_jasa_fp" style="height: 90px;" readonly></textarea>
						</td>
					</tr>
				</table>
				<table class="table table-striped">
					<tr>
						<td colspan="6">
							<b>
								MASA PENGKREDITAN FAKTUR PAJAK
							</b>
						</td>
					</tr>
					<tr>
						<td></td>
						<td width="50px" style="padding-top: 18px;">
							Bulan
						</td>
						<td width="150px">
							<select name="bulan_masa_pengkreditan_faktur_pajak" id="bulan_masa_pengkreditan_faktur_pajak" class="form-control"></select>
						</td>
						<td width="50px" style="padding-top: 18px;">
							Tahun
						</td>
						<td width="150px">
							<select name="tahun_masa_pengkreditan_faktur_pajak" id="tahun_masa_pengkreditan_faktur_pajak" class="form-control"></select>
						</td>
						<td></td>
					</tr>
				</table>
				<table class="table table-striped">
					<tr>
						<td colspan="5">
							<b>
								DETAIL
							</b>
						</td>
					</tr>
					<tr>
						<td width="220px">
							Kategori
						</td>
						<td colspan="4">
							<input type="checkbox" name="kategori_fk" id="kategori_fk" value="1"> Tidak dikredit
						</td>
					</tr>
					<tr>
						<td>
							Nama Jasa
						</td>
						<td colspan="4">
							<textarea class="form-control" name="nama_jasa_fk" id="nama_jasa_fk" readonly></textarea>
						</td>
					</tr>
					<tr>
						<td></td>
						<td style="border: thin solid #dadada;">
							DPP
						</td>
						<td style="border: thin solid #dadada;">
							PPN
						</td>
						<td style="border: thin solid #dadada;">
							PPN
						</td>
						<td style="border: thin solid #dadada;">TOTAL</td>
					</tr>
					<tr>
						<td></td>
						<td class="text-right" style="border: thin solid #dadada;">
							<input type="text" name="ddp_fp" id="ddp_fp" value="0" class="form-control text-right">
						</td style="border: thin solid #dadada;">
						<td class="text-right" style="border: thin solid #dadada;">
							<input type="text" name="ppn_persen_fp" id="ppn_persen_fp" value="0" class="form-control text-right" readonly>
						</td>
						<td class="text-right" style="border: thin solid #dadada;">
							<input type="text" name="ppn_fp" id="ppn_fp" value="0" class="form-control text-right" readonly>
						</td>
						<td class="text-right" style="border: thin solid #dadada;">
							<input type="text" name="total_fp" id="total_fp" value="0" class="form-control text-right" readonly>
						</td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td colspan="3">
							Ubah Nilai PPN Manual <input type="checkbox" name="ckb_ubah_nilai_ppn_manual" id="ckb_ubah_nilai_ppn_manual" value="1" onchange="nilai_ppn_manual();">
						</td>
					</tr>
				</table>
			</fieldset>

			<fieldset class="form-group border p-3">
				<legend class="w-auto px-2">OTOMATIS CREATE NK &nbsp;&nbsp; <input type="checkbox" name="chk_otomatis_create_nk" id="chk_otomatis_create_nk" value="1" onclick="create_nk();"></legend>
				<table class="table table-striped">
					<tr>
						<td width="220px">
							Tanggal NK
						</td>
						<td width="300px">
							<input type="text" name="tanggal_nk" id="tanggal_nk" class="form-control" placeholder="DD-MM-YYYY">
						</td>
						<td></td>
					</tr>
					<tr>
						<td>
							Divisi
						</td>
						<td>
							<select name="divisi" id="divisi" class="form-control">
								<option value=""></option>
							</select>
						</td>
						<td></td>
					</tr>
					<tr>
						<td>
							No NK
						</td>
						<td>
							<input type="text" name="no_nk" id="no_nk" class="form-control">
						</td>
						<td></td>
					</tr>
					<tr>
						<td>
							Total NK (Rp)
						</td>
						<td>
							<input type="text" name="total_nk" id="total_nk" value="0" class="form-control text-right">
						</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3" align="right">
							
							<button type="button" id="btnreset" class="btn btn-danger-dark" onclick="resetform();">
								RESET
							</button>

							<button type="submit" id="btntambah" class="btn btn-primary-dark">
								TAMBAH
							</button>

							<?php
								if($role==true || $_SESSION["can_delete"]==true){
							?>
									<button type="button" id="btnhapus" class="btn btn-danger-dark" onclick="deleteTagihan();">
										HAPUS
									</button>
							<?php
								}
							?>

							<button type="submit" id="btnubah" class="btn btn-warning-dark">
								UBAH
							</button>
						</td>
					</tr>
				</table>
			</fieldset>
		</div>
	</div>
</form>
	<div class="modal fade" id="tagihan" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="ModalLabel"></h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          	<span aria-hidden="true">&times;</span>
			        </button>
	      		</div>
		      	<div class="modal-body">
		        	<table class="table table-striped" id="datatagihan">
		        		<thead>
			        		<tr>
			        			<td>Nomor Transaksi</td>
			                    <td>Tanggal</td>
			                    <td>Nomor Tagihan</td>
			                    <td>Nomor Faktu Pajak</td>
			                    <td>Nama Pemberi Jasa</td>
			                    <td>DPP</td>
			                    <td>PPN</td>
			                    <td>Total</td>
			                </tr>
			            </thead>
		                <tbody>
			        		<tr>
			        			<td colspan="8">Loading...</td>
			        		</tr>
			        	</tbody>
		        	</table>
		      	</div>
		      	<div class="modal-footer">
			        <button type="button" class="btn btn-danger-dark" data-dismiss="modal">Close</button>
		      	</div>
	    	</div>
	  	</div>
	</div>

	<div class="modal fade" id="pemberijasa" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="ModalLabel"></h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          	<span aria-hidden="true">&times;</span>
			        </button>
	      		</div>
		      	<div class="modal-body">
		        	<table class="table table-striped" id="datapemberijasa">
		        		<thead>
			        		<tr>
			        			<td>Kode <span id="kodepj"></span></td>
			                    <td>Nama <span id="namapj"></span></td>
			                </tr>
			            </thead>
		                <tbody>
			        		<tr>
			        			<td colspan="2">Loading...</td>
			        		</tr>
			        	</tbody>
		        	</table>
		      	</div>
		      	<div class="modal-footer">
			        <button type="button" class="btn btn-danger-dark" data-dismiss="modal">Close</button>
		      	</div>
	    	</div>
	  	</div>
	</div>

	<div class="modal fade" id="objekpajak" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="ModalLabel">
						Objek Pajak
					</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          	<span aria-hidden="true">&times;</span>
			        </button>
	      		</div>
		      	<div class="modal-body">
		        	<table class="table table-striped" id="dataobjekpajak">
		        		<thead>
			        		<tr>
			        			<td>Kode Objek Pajak</td>
			                    <td>Nama Objek Pajak</td>
			                    <td>Pasal PPH</td>
			                </tr>
			            </thead>
		                <tbody>
			        		<tr>
			        			<td colspan="3">Loading...</td>
			                </tr>
			        	</tbody>
		        	</table>
		      	</div>
		      	<div class="modal-footer">
			        <button type="button" class="btn btn-danger-dark" data-dismiss="modal">Close</button>
		      	</div>
	    	</div>
	  	</div>
	</div>

	<div class="modal fade" id="fpmasukan" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="ModalLabel">
						Faktur Pajak Masukan
					</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          	<span aria-hidden="true">&times;</span>
			        </button>
	      		</div>
		      	<div class="modal-body">
		      		<table class="table table-striped">
		      			<tr>
		      				<td width="100px">Bulan</td>
		      				<td width="150px">
		      					<select id="bulanfp" class="form-control" onchange="getDataFakturPajak();"></select>
		      				</td>
		      				<td width="100px">Tahun</td>
		      				<td width="150px">
		      					<select id="tahunfp" class="form-control" onchange="getDataFakturPajak();"></select>
		      				</td>
		      				<td></td>
		      			</tr>
		      		</table>
		        	<table class="table table-striped" id="datafakturpajakmasukan">
		        		<thead>
			        		<tr>
			        			<td>Nomor Faktur Pajak</td>
			                    <td>Tanggal</td>
			                    <td>Nama</td>
			                    <td>DPP</td>
			                    <td>PPN</td>
			                    <td>Total</td>
			                </tr>
			            </thead>
		                <tbody>
			        		<tr>
			        			<td colspan="5">Loading...</td>
			                </tr>
			        	</tbody>
		        	</table>
		      	</div>
		      	<div class="modal-footer">
			        <button type="button" class="btn btn-danger-dark" data-dismiss="modal">Close</button>
		      	</div>
	    	</div>
	  	</div>
	</div>

<script>
	$(document).ready(function() {

		<?php
			if($_SESSION["can_create"]==false){
		?>
				document.getElementById('btntambah').style.display = 'none';
				document.getElementById('btnreset').style.display = 'none';
		<?php
			}
			if($_SESSION["can_update"]==false){
		?>
				document.getElementById('btnubah').style.display = 'none';
				document.getElementById('btnreset').style.display = 'none';
		<?php
			}
		?>


		document.getElementById('chk_otomatis_create_nk').disabled=true;
		document.getElementById('no_nk').readOnly=true;


		var detailawal = 'NAMA  	 :\nNPWP      :\nNIK  	 :      \nALAMAT  :';
		document.getElementById('detail_tagihan_textarea').innerHTML = detailawal;
		document.getElementById('detail_pemberi_jasa_fp').innerHTML = detailawal;

		var noinput = document.getElementById('nomor_transaksi').value;
		if(noinput=='AUTONUMBER'){
			document.getElementById('btnubah').style.display = 'none';
			<?php
				if($role==true || $_SESSION["can_delete"]==true){
			?>
					document.getElementById('btnhapus').style.display = 'none';
			<?php
				}
			?>
			document.getElementById('btnreset').style.display = 'block';
			document.getElementById('btnreset').style.float = 'right';
		}

		$('#tanggal_tagihan,#tanggal_fp,#tanggal_nk').datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		});


	    $('#prosestransaksi').submit(function (e) {
	    	Swal.fire({
				title: 'Loading...',
				// didOpen: () => {
				// 	Swal.showLoading()
				// 	const b = Swal.getHtmlContainer().querySelector('b')
				// 	timerInterval = setInterval(() => {
				// 		b.textContent = Swal.getTimerLeft();
				// 	}, 100)
				// },
				showConfirmButton: false
			})

	        e.preventDefault();

	        var formData = $(this).serialize();

	        $.ajax({
	            type: 'POST',
	            url: '<?php echo site_url('Tagihandiluarpembelian/proses'); ?>',
	            data: formData,
	            success: function (data) {
	            	const jsonObject = JSON.parse(data);
					if (jsonObject.result == 'sukses') {

						Swal.fire({
							title: 'Nomor transaksi : '+jsonObject.data,
							text: jsonObject.msg,
							icon: 'success',
							showCancelButton: false,
							confirmButtonColor: '#3085d6',
							cancelButtonColor: '#d33',
							confirmButtonText: 'Close'
						}).then((result) => {
							resetform();
    						window.location.href = '<?php echo site_url("Tagihandiluarpembelian"); ?>';
						})
					}else{
						Swal.fire({
							icon: 'error',
							title: 'Oops...',
							text: jsonObject.error
						})
					}
	            }
	        });
	    });
	});

	var editdpp = 0;

	document.getElementById('total_nk').addEventListener('input', function (e) {
		var inputElement = e.target;
		var inputValue = inputElement.value;
		
		var numericValue = inputValue.replace(/[^0-9.]/g, '');
		
		if (isNaN(numericValue)) {
			inputElement.value = 0;
		} else {
			inputElement.value = formatAngka(numericValue);
		}

	});

	document.getElementById('dpp_pemotongan').addEventListener('input', function (e) {
		var inputElement = e.target;

		var inputValue = inputElement.value;
		
		if(inputElement===""){
			inputValue = 0;
		}

		var numericValue = inputValue.replace(/[^0-9.]/g, '');
		
		if (isNaN(numericValue)) {
			inputElement.value = '';
		} else {
			inputElement.value = formatAngka(numericValue);
			dpp_potong();
		}
	});

	document.getElementById('ddp_fp').addEventListener('input', function (e) {
		editdpp++;
		var inputElement = e.target;
		var inputValue = inputElement.value;
		var numericValue = inputValue.replace(/[^0-9.]/g, '');
		
		if (isNaN(numericValue)) {
			inputElement.value = '';
		} else {
			inputElement.value = formatAngka(numericValue);

			var dpp = document.getElementById('ddp_fp').value;
				dpp = dpp.replace(/,/g, '');

			var hasil_total = parseFloat(dpp) + parseFloat(numericValue);



		    var dpp = parseFloat(numericValue);
			var ppnInput = document.getElementById('ppn_persen_fp');
			var ppn = parseFloat(ppnInput.value.replace('%', ''));

		    var ppn_persen = parseFloat(dpp * ppn);
		    var hasil_total = parseFloat(dpp + ppn_persen);


		    if(isNaN(ppn_persen)){
		   		document.getElementById('ppn_fp').value = 0;
		   	}else{
		   		var formattedPpnPersen = formatAngka(ppn_persen.toString());
		   		document.getElementById('ppn_fp').value = formattedPpnPersen;
		   	}

			if(isNaN(hasil_total)){
				document.getElementById('total_fp').value = 0;
			}else{
				var formattedHasilTotal = formatAngka(hasil_total.toString());
				document.getElementById('total_fp').value = formattedHasilTotal;
			}
		}
	});

	document.getElementById('ppn_fp').addEventListener('input', function (e) {
		var inputElement = e.target;
		var inputValue = inputElement.value;
		var numericValue = inputValue.replace(/[^0-9.]/g, '');
		

		var ppn_persentase = document.getElementById('ppn_persen_fp');

		if (isNaN(numericValue)) {
			inputElement.value = 0;
		} else {
			inputElement.value = formatAngka(numericValue);

			var dpp = document.getElementById('ddp_fp').value;
				dpp = dpp.replace(/,/g, '');

			var hasil_total = parseFloat(dpp) + parseFloat(numericValue);

			var hitung_persentase = (parseFloat(numericValue)/parseFloat(dpp))*100;

			if (isNaN(hitung_persentase)) {
			    hitung_persentase = 0;
			}

			ppn_persentase.value = hitung_persentase+'%';

			if(isNaN(hasil_total)){
				document.getElementById('total_fp').value = 0;
			}else{
				var formattedHasilTotal = formatAngka(hasil_total.toString());
				document.getElementById('total_fp').value = formattedHasilTotal;
			}
		}
	});

	document.getElementById('ppn_persen_fp').addEventListener('input', function (e) {
		var inputElement = e.target;
		var inputValue = inputElement.value;
		var numericValue = inputValue.replace(/[^0-9.]/g, '');
		
		var ppn = document.getElementById('ddp_fp');
		var nominal_ppn = document.getElementById('ppn_fp');

		if (isNaN(numericValue)) {
			inputElement.value = 0;
		} else {

			inputElement.value = formatAngka(numericValue);

			var dpp = document.getElementById('ddp_fp').value;
				dpp = dpp.replace(/,/g, '');


			var hasil_ppn =  (parseFloat(numericValue) * parseFloat(dpp))/100;

			if(isNaN(hasil_ppn)){
				hasil_ppn = 0;
			}

			var hasil_total = parseFloat(dpp) + parseFloat(hasil_ppn);

			nominal_ppn.value = formatAngka(hasil_ppn.toString());


			if(isNaN(hasil_total)){
				document.getElementById('total_fp').value = 0;
			}else{
				var formattedHasilTotal = formatAngka(hasil_total.toString());
				document.getElementById('total_fp').value = formattedHasilTotal;
			}
		}
	});


	function gettanggal_tagihan(){
		var inputValue = document.getElementById('tanggal_tagihan').value
		document.getElementById('tanggal_fp').value = inputValue;
	};

	document.getElementById('manual_pph21').addEventListener('input', function (e) {
		dpp_potong();
	});
	document.getElementById('manual_pph212').addEventListener('input', function (e) {
		dpp_potong();
	});

	var tanggalSekarang = new Date();

	var tanggal = tanggalSekarang.getDate();
	var bulan = tanggalSekarang.getMonth() + 1;
	if (bulan < 10) {
		bulan = "0" + bulan;
	}

	var tahun = tanggalSekarang.getFullYear();

	document.getElementById('tanggal_transaksi').value = tanggal+'-'+bulan+'-'+tahun;
	
	var namapemberijasatamp = 'Supplier';
	var openpemberijasa = 0;

	var namabulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

	bulantahunpemotonganpph(bulan,tahun,'reportbulan','reporttahun');
	bulantahunpemotonganpph(bulan,tahun,'bulan_pemotongan_pph','tahun_pemotongan_pph');
	bulantahunpemotonganpph(bulan,tahun,'bulan_masa_pengkreditan_faktur_pajak','tahun_masa_pengkreditan_faktur_pajak');
	bulantahunpemotonganpph(bulan,tahun,'bulanfp','tahunfp');

	keterangan();
	function keterangan(e=''){
		var htmlketerangan = '';
		if(e==''){
			htmlketerangan += '<option value="" selected></option>';
			htmlketerangan += '<option value="Refund PPH">Refund PPH</option>';
			htmlketerangan += '<option value="Tak Tertagih">Tak Tertagih</option>';
		}else if(e=='Refund PPH'){
			htmlketerangan += '<option value=""></option>';
			htmlketerangan += '<option value="Refund PPH" selected>Refund PPH</option>';
			htmlketerangan += '<option value="Tak Tertagih">Tak Tertagih</option>';
		}else if(e=='Tak Tertagih'){
			htmlketerangan += '<option value=""></option>';
			htmlketerangan += '<option value="Refund PPH">Refund PPH</option>';
			htmlketerangan += '<option value="Tak Tertagih" selected>Tak Tertagih</option>';
		}else{
			htmlketerangan += '<option value=""></option>';
			htmlketerangan += '<option value="Refund PPH">Refund PPH</option>';
			htmlketerangan += '<option value="Tak Tertagih">Tak Tertagih</option>';
		}



		document.getElementById('keterangan').innerHTML=htmlketerangan;
	}

	function nomortagihan(){

		if ($.fn.DataTable.isDataTable('#datatagihan')) {
			$('#datatagihan').DataTable().destroy();
		}

		$('#datatagihan').dataTable( {
			"bProcessing": true,
			"bServerSide": true,
			"columnDefs": [
				{ targets: 'no-sort', orderable: false },
				{ targets: 'col-hide', visible: false }
			],
			"sAjaxSource": '<?php echo site_url('Tagihandiluarpembelian/getTagihan'); ?>',
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
			},
			"scrollX": true,
			"scrollX": true
		});

		$('#tagihan').modal('show');
	}

	function getTagihan(no_transaksi,no_tagihan,tanggal_transaksi,tanggal_tagihan,npwp,nik,alamat){
		document.getElementById('nomor_transaksi').value=no_transaksi;
		document.getElementById('nomor_tagihan').value=no_tagihan;
		document.getElementById('tanggal_transaksi').value=tanggal_transaksi;
		document.getElementById('tanggal_tagihan').value=tanggal_tagihan;
		document.getElementById('npwp').value=npwp;
		document.getElementById('nik_pemberi_jasa').value=nik;
		document.getElementById('alamat_pemberi_jasa').value=alamat;


		var data = '&no_transaksi=' + no_transaksi;

		$.ajax({
			type: 'POST',
			url: '<?php echo site_url('Tagihandiluarpembelian/getTagihanView'); ?>',
			data:data,
			success: function (data) {
				const jsonObject = JSON.parse(data);
				if (jsonObject.result == 'sukses') {

					if(jsonObject.data[0].KodeSupplier!==''){
						var Kode = jsonObject.data[0].KodeSupplier;
						document.getElementById('nama_pemberi_jasa').removeAttribute("readonly");
					}else{
						var Kode = jsonObject.data[0].KodeCustomer;
						document.getElementById('nama_pemberi_jasa').setAttribute("readonly", "readonly");
					}

					if(jsonObject.data[0].IsPotongPPH==true){
						document.getElementById('ckb_tidak_potong_pph').checked = true;
					}

					document.getElementById('nama_pemberi_jasa').value=jsonObject.data[0].nama_pemberi_jasa;
					document.getElementById('nama_pemberi_jasa_fp').value=jsonObject.data[0].nama_pemberi_jasa;

					document.getElementById('kd_pemberi_jasa').value=Kode;
					document.getElementById('detail_tagihan_textarea').innerHTML=jsonObject.data[0].detail_suplcust;
					document.getElementById('cabang').value=jsonObject.data[0].cabang;
					document.getElementById('nama_jasa').value=jsonObject.data[0].NamaJasaKenaPajak;

					bulantahunpemotonganpph(jsonObject.data[0].bulanpotongpph,jsonObject.data[0].tahunpotongpph,'bulan_pemotongan_pph','tahun_pemotongan_pph');
					bulantahunpemotonganpph(jsonObject.data[0].BulanMasaPajak,jsonObject.data[0].TahunMasaPajak,'bulan_masa_pengkreditan_faktur_pajak','tahun_masa_pengkreditan_faktur_pajak');

					document.getElementById('kode_objek_pajak').value=jsonObject.data[0].kode_objek_pajak;
					document.getElementById('nama_objek_pajak').value=jsonObject.data[0].nama_objek_pajak;

					var dpppemotongan = jsonObject.data[0].DPPPemotongan;
					document.getElementById('dpp_pemotongan').value=formatAngka(dpppemotongan.toString());

					readyonly_manualpph();
					
					if(jsonObject.data[0].TarifManual==true){
						document.getElementById('ckb_tarif_manual').checked = true;
						if(jsonObject.data[0].IsPPH23==true){
							document.getElementById('manual_pph23').removeAttribute("readonly");
						}else if(jsonObject.data[0].IsPPH4==true){
							document.getElementById('manual_pph4').removeAttribute("readonly");
						}else if(jsonObject.data[0].IsPPH21==true){
							document.getElementById('manual_pph4').removeAttribute("readonly");
						}else if(jsonObject.data[0].IsPPH15==true){
							document.getElementById('manual_pph15').removeAttribute("readonly");
						}
					}

					var jumlahpotong = jsonObject.data[0].JumlahPotong;
					if(jsonObject.data[0].IsPPH23==true){
						document.getElementById('rd_pph23').checked = true;
						document.getElementById('manual_pph23').value = jsonObject.data[0].TarifPPH+'%';
						document.getElementById('jumlah_pph23').value = formatAngka(jumlahpotong.toString());
					}else if(jsonObject.data[0].IsPPH4==true){
						document.getElementById('rd_pph4').checked = true;
						document.getElementById('manual_pph4').value = jsonObject.data[0].TarifPPH+'%';
						document.getElementById('jumlah_pph4').value = formatAngka(jumlahpotong.toString());
					}else if(jsonObject.data[0].IsPPH21==true){
						document.getElementById('rd_pph21').checked = true;
						document.getElementById('manual_pph21').value = jsonObject.data[0].TarifPPH+'%';
						document.getElementById('manual_pph212').value = jsonObject.data[0].TarifPPH2+'%';
						document.getElementById('jumlah_pph21').value = formatAngka(jumlahpotong.toString());
					}else if(jsonObject.data[0].IsPPH15==true){
						document.getElementById('rd_pph15').checked = true;
						document.getElementById('manual_pph15').value = jsonObject.data[0].TarifPPH+'%';
						document.getElementById('jumlah_pph15').value = formatAngka(jumlahpotong.toString());
					}

					keterangan(jsonObject.data[0].keterangan);

					if(jsonObject.data[0].IsSupplier==true){
						var supcust = 'Supplier';
						document.getElementById('rd_pemberi_jasa_supplier').checked = true;
						document.getElementById('rd_pemberi_jasa_customer').checked = false;
						document.getElementById('rd_pemberi_jasa_fp_supplier').checked = true;
						document.getElementById('rd_pemberi_jasa_fp_customer').checked = false;
					}else{
						var supcust = 'Customer';
						document.getElementById('rd_pemberi_jasa_supplier').checked = false;
						document.getElementById('rd_pemberi_jasa_customer').checked = true;
						document.getElementById('rd_pemberi_jasa_fp_supplier').checked = false;
						document.getElementById('rd_pemberi_jasa_fp_customer').checked = true;
					}

					katagori(supcust,jsonObject.data[0].kategori)

					
					if(jsonObject.data[0].no_skb!=='' && jsonObject.data[0].no_skb!=='NULL'){
						document.getElementById('nomor_skb').value=jsonObject.data[0].no_skb;
						document.getElementById('ckb_nomor_skb').checked = true;
						document.getElementById('nomor_skb').removeAttribute("readonly");
					}
					
					if(jsonObject.data[0].IsFakturPajak==true){
						document.getElementById('chk_tagihan_ada').checked = true;
						document.getElementById('no_fp_masukan').value=jsonObject.data[0].NoFP;

						formatdate(jsonObject.data[0].Tanggal,'tanggal_fp');

						// document.getElementById('btn_brws_no_fp_masukan').removeAttribute("disabled");
						document.getElementById('no_fp_masukan').removeAttribute("readonly");
						document.getElementById('tanggal_fp').removeAttribute("readonly");
					}

					document.getElementById('nama_jasa_fk').value=jsonObject.data[0].NamaJasaKenaPajak_fp;
					document.getElementById('detail_pemberi_jasa_fp').innerHTML=jsonObject.data[0].detail_suplcust;

					var dpp = jsonObject.data[0].DPP;
					document.getElementById('ddp_fp').value = formatAngka(dpp.toString());
					document.getElementById('ppn_persen_fp').value=jsonObject.data[0].PPN_Persen+'%';

					var ppn = jsonObject.data[0].PPN;
					document.getElementById('ppn_fp').value = formatAngka(ppn.toString());

					var total = jsonObject.data[0].Total;
					document.getElementById('total_fp').value =formatAngka(total.toString());
					
					if(jsonObject.data[0].statusdikreditkan==true){
						document.getElementById('kategori_fk').checked = true;
					}

					if(jsonObject.data[0].OtomatisCreateNK==true){
						document.getElementById('chk_otomatis_create_nk').checked = true;
					}

					formatdate(jsonObject.data[0].TglNK,'tanggal_nk');

					divisi(jsonObject.data[0].Divisi);

					document.getElementById('no_nk').value = jsonObject.data[0].NoNK;

					var totalnk = jsonObject.data[0].TotalNK;
					document.getElementById('total_nk').value = formatAngka(totalnk.toString());

					document.getElementById('btntambah').style.display = 'none';
					document.getElementById('btnubah').style.display = 'block';
					document.getElementById('btnubah').style.float = 'right';
					<?php
						if($role==true || $_SESSION["can_delete"]==true){
					?>
							document.getElementById('btnhapus').style.display = 'block';
							document.getElementById('btnhapus').style.float = 'right';
					<?php
						}
					?>
					document.getElementById('btnreset').style.display = 'block';
					document.getElementById('btnreset').style.float = 'right';

				}else{
					
				}
			}
		});

		<?php
			if($_SESSION["can_create"]==false){
		?>
				document.getElementById('btntambah').style.display = 'none';
				document.getElementById('btnreset').style.display = 'none';
		<?php
			}
			if($_SESSION["can_update"]==false){
		?>
				document.getElementById('btnubah').style.display = 'none';
				document.getElementById('btnreset').style.display = 'none';
		<?php
			}
		?>
		
		$('#tagihan').modal('hide');
	}

	function fpmasukan(){
		getDataFakturPajak();
		$('#fpmasukan').modal('show');
	}
	function getDataFakturPajak(){

		var bulanfp = document.getElementById('bulanfp').value;
		var tahunfp = document.getElementById('tahunfp').value;

		if ($.fn.DataTable.isDataTable('#datafakturpajakmasukan')) {
			$('#datafakturpajakmasukan').DataTable().destroy();
		}

		$('#datafakturpajakmasukan').dataTable( {
			"bProcessing": true,
			"bServerSide": true,
			"columnDefs": [
				{ targets: 'no-sort', orderable: false },
				{ targets: 'col-hide', visible: false }
			],
			"sAjaxSource": '<?php echo site_url('Tagihandiluarpembelian/getFakturPajakMasukan'); ?>/'+bulanfp+'/'+tahunfp,
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
			},
			"scrollX": true
		});
	}

	function resetform(){
		Swal.fire({
		  text: "Apakah Anda ingin menyetel ulang formulir ini?",
		  showDenyButton: true,
		  showCancelButton: false,
		  icon: "warning",
		  confirmButtonText: "Reset",
		  denyButtonText: `Close`
		}).then((result) => {
			if (result.isConfirmed) {
				action_reset();
			}
		});
	}

	function action_reset(){
		document.getElementById('chk_otomatis_create_nk').disabled=true;
		document.getElementById('no_nk').readOnly=true;

		var form = document.getElementById('prosestransaksi');
		form.reset();

		var detailawal = 'NAMA  	 :\nNPWP      :\nNIK  	 :      \nALAMAT  :';
		document.getElementById('detail_tagihan_textarea').innerHTML = detailawal;
		document.getElementById('detail_pemberi_jasa_fp').innerHTML = detailawal;
		document.getElementById('nama_jasa_fk').innerHTML = detailawal;

		document.getElementById('btntambah').style.display = 'block';
		document.getElementById('btntambah').style.float = 'right';

		document.getElementById('btnubah').style.display = 'none';
		<?php
			if($role==true || $_SESSION["can_delete"]==true){
		?>
				document.getElementById('btnhapus').style.display = 'none';
		<?php
			}
		?>

		document.getElementById('btnreset').style.display = 'block';
		document.getElementById('btnreset').style.float = 'right';
	}
	<?php
		if($role==true || $_SESSION["can_delete"]==true){
	?>
			function deleteTagihan() {
			    var number = document.getElementById('nomor_transaksi').value;

			    Swal.fire({
			        title: 'Delete Tagihan',
			        input: "text",
			        showCancelButton: true,
			        confirmButtonText: 'Delete',
			        cancelButtonText: 'Cancel',
			        icon: 'warning'
			    }).then((result) => {
			        if (result.isConfirmed) {
			            var ket = result.value;
			            var data = 'number=' + number + '&keterangan=' + result.value;
			            if(result.value!=''){
				            $.ajax({
				                type: 'POST',
				                url: '<?php echo site_url('Tagihandiluarpembelian/delete'); ?>',
				                data: data,
				                success: function (data) {
				                    if (data === 'success') {
				                        Swal.fire({
				                            text: "Transaksi dengan nomor tagihan " + number + " berhasil dihapus",
				                            icon: "success",
				                            confirmButtonText: "Close"
				                        }).then((result) => {
				                            action_reset();
				                        });
				                    } else {
				                        Swal.fire({
				                            text: "Transaksi dengan nomor tagihan " + number + " gagal dihapus",
				                            icon: "error",
				                            confirmButtonText: "Close"
				                        });
				                    }
				                }
				            });
			            }else{
			            	Swal.fire({
							  text: "Keterangan harus di isi",
							  icon: "error"
							});
			            }
			        }
			    });
			}

			function keterangan_delete(){
				var keterangan_delete = document.getElementById('keterangan_delete').value;
				document.getElementById('ket').value = keterangan_delete;
			}

	<?php
		}
	?>

	function getNoFP(no,tgl,bln,thn,blnmasapajak,thnmasapajak,dpp,ppb,total){
		document.getElementById('no_fp_masukan').value = no;
		document.getElementById('tanggal_fp').value = tgl;

		bulantahunpemotonganpph(blnmasapajak,thnmasapajak,'bulan_masa_pengkreditan_faktur_pajak','tahun_masa_pengkreditan_faktur_pajak');
		
		document.getElementById('ddp_fp').value = dpp;
		document.getElementById('ppn_fp').value = ppb;
		document.getElementById('total_fp').value = total;

		$('#fpmasukan').modal('hide');
	}

	tarifppn();
	function tarifppn(e=''){
		if(e!==''){
			var data = '&TglTransaksi=' + e;
		}else{
			var data = '&TglTransaksi=' + tahun+'-'+bulan+'-'+tanggal;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo site_url('Tagihandiluarpembelian/getTarifPPN'); ?>',
			data:data,
			success: function (data) {
				const jsonObject = JSON.parse(data);
				if (jsonObject.result == 'sukses') {
					document.getElementById('ppn_persen_fp').value = 11+'%';
				}else{
					document.getElementById('ppn_persen_fp').value = 11+'%';
				}
			}
		});
	}

	function dpp_potong() {
		var e = document.getElementById('dpp_pemotongan').value;

		if(e===""){
			e = 0;
		}

	    var formattedValue = e.replace(/,/g, '');

	    var dpp = parseFloat(formattedValue);
	    var ppnInput = document.getElementById('ppn_persen_fp');
		var ppn = parseFloat(ppnInput.value.replace('%', ''));

	    var ppn_persen = parseFloat(dpp * (ppn/100));
	    var hasil_total = parseFloat(dpp + ppn_persen);

	    if(editdpp==0){
	    	var formattedDpp = formatAngka(dpp.toString());

		    var formattedPpnPersen = formatAngka(ppn_persen.toString());
		    var formattedHasilTotal = formatAngka(hasil_total.toString());

	    	document.getElementById('ddp_fp').value = formattedDpp;
		    document.getElementById('ppn_fp').value = formattedPpnPersen;
		    document.getElementById('total_fp').value = formattedHasilTotal;
		}

	    var pasalpph = '';
		var radioButtons = document.getElementsByName('rd_pph');
		var checkedValue;

		for (var i = 0; i < radioButtons.length; i++) {
		    if (radioButtons[i].checked) {
		        pasalpph = radioButtons[i].value;
		        break;
		    }
		}

		var persen = 0;
		var persen2 = 0;
		var posisi = '';
		if(pasalpph=='PPH23'){
			persen = document.getElementById('manual_pph23').value;
			posisi = 'jumlah_pph23';
		}else if(pasalpph=='PPH4'){
			persen = document.getElementById('manual_pph4').value;
			posisi = 'jumlah_pph4';
		}else if(pasalpph=='PPH21'){
			persen = document.getElementById('manual_pph21').value;
			persen2 = document.getElementById('manual_pph212').value;
			posisi = 'jumlah_pph21';
		}else if(pasalpph=='PPH15'){
			persen = document.getElementById('manual_pph15').value;
			posisi = 'jumlah_pph15';
		}

		hitungpotonganpph(persen,persen2,posisi);

	}

	function formatdate(date,id){
		var dateObject = new Date(date);
		var formattgl 	= dateObject.getDate();
		var formatbln 	= dateObject.getMonth() + 1;
		var formatthn 	= dateObject.getFullYear();
		
		if (formatbln < 10) {
			formatbln = "0" + formatbln;
	    }

	    if (formattgl < 10) {
			formattgl = "0" + formattgl;
	    }

		document.getElementById(id).value=formattgl+'-'+formatbln+'-'+formatthn;
	}

	function formatAngka(angka) {
		var parts = angka.split('.');
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
		return parts.join('.');
	}

	function nama_jasa_onkeypress(e) {
	    document.getElementById('nama_jasa_fk').textContent = e;
	}

	function bulantahunpemotonganpph(xbulan='',xtahun='',id1='',id2=''){

		var bulanhtml = '';
		var tahunhtml = '';




		for(var i=1; i<=12; i++){
			var xselected = '';
			if(xbulan==i){
				xselected = 'selected';
			}
			bulanhtml += '<option value="'+i+'" '+xselected+'>'+namabulan[i]+'</option>';
		}

		for(var i = xtahun; i >= 2005; i--){
			var xselected = '';
			if(xtahun==i){
				xselected = 'selected';
			}
			tahunhtml += '<option value="'+i+'" '+xselected+'>'+i+'</option>';
		}

		document.getElementById(id1).innerHTML = bulanhtml;
		document.getElementById(id2).innerHTML = tahunhtml;

	}

	function actionbulantahun(){
		var bulanhtml = '';
		var tahunhtml = '';

		var bulanselected = document.getElementById('bulan_pemotongan_pph').value;
		var tahunselected = document.getElementById('tahun_pemotongan_pph').value;

		for(var i=1; i<=12; i++){
			var selected = '';
			if(bulanselected==i){
				selected = 'selected';
			}
			bulanhtml += '<option value="'+i+'" '+selected+'>'+namabulan[i]+'</option>';
		}

		for(var i = tahun; i >= 2005; i--){
			var selected = '';
			if(tahunselected==i){
				selected = 'selected';
			}
			tahunhtml += '<option value="'+i+'" '+selected+'>'+i+'</option>';
		}

		document.getElementById('bulan_masa_pengkreditan_faktur_pajak').innerHTML = bulanhtml;
		document.getElementById('tahun_masa_pengkreditan_faktur_pajak').innerHTML = tahunhtml;
	}

	function rdpemberijasa(pemberijasa){

		katagori(pemberijasa);

		if(namapemberijasa!==pemberijasa){
			openpemberijasa = 0;
			namapemberijasatamp = pemberijasa;
		}

		document.getElementById('pemberi_jasa').value=pemberijasa;

		document.getElementById('kd_pemberi_jasa').value='';
		document.getElementById('nama_pemberi_jasa').value='';
		document.getElementById('cabang').value='';
		document.getElementById('nama_pemberi_jasa_fp').value='';

		if(pemberijasa=='Supplier'){
			document.getElementById('nama_pemberi_jasa').removeAttribute("readonly");
			document.getElementById('rd_pemberi_jasa_fp_supplier').checked=true;
			document.getElementById('chk_otomatis_create_nk').checked=false;
			document.getElementById('chk_otomatis_create_nk').disabled=true;
			document.getElementById('no_nk').readOnly=true;
		}else{
			document.getElementById('nama_pemberi_jasa').setAttribute("readonly", "readonly");
			document.getElementById('rd_pemberi_jasa_fp_customer').checked=true;
			document.getElementById('chk_otomatis_create_nk').disabled=false;
			document.getElementById('no_nk').readOnly=false;
		}

		var detail 	= 'NAMA  	 : \nNPWP      : \nNIK 	        : 	\nALAMAT  : ';

		document.getElementById('detail_tagihan_textarea').innerHTML = detail;
		document.getElementById('detail_pemberi_jasa_fp').innerHTML = detail;

	}

	tagihan_ada();

	function tagihan_ada(){
		var chk_tagihan_ada = document.getElementById("chk_tagihan_ada");

		if (chk_tagihan_ada.checked) {
			document.getElementById('no_fp_masukan').removeAttribute("readonly");
			// document.getElementById('btn_brws_no_fp_masukan').removeAttribute("disabled");
			document.getElementById('tanggal_fp').removeAttribute("readonly");
			document.getElementById('rd_pemberi_jasa_fp_supplier').setAttribute("disabled", "disabled");
			document.getElementById('rd_pemberi_jasa_fp_customer').setAttribute("disabled", "disabled");
			document.getElementById('btn_brws_nama_pemberi_jasa').setAttribute("disabled", "disabled");
		}else{
			document.getElementById('no_fp_masukan').setAttribute("readonly", "readonly");
			// document.getElementById('btn_brws_no_fp_masukan').setAttribute("disabled", "disabled");
			document.getElementById('tanggal_fp').setAttribute("readonly", "readonly");
			document.getElementById('rd_pemberi_jasa_fp_supplier').setAttribute("disabled", "disabled");
			document.getElementById('rd_pemberi_jasa_fp_customer').setAttribute("disabled", "disabled");
			document.getElementById('btn_brws_nama_pemberi_jasa').setAttribute("disabled", "disabled");
		}

		check_tagihan_faktur();
	}

	function potongpph(){

		var ckb_tidak_potong_pph = document.getElementById("ckb_tidak_potong_pph");

		if (ckb_tidak_potong_pph.checked) {
			document.getElementById('dpp_pemotongan').value=0;
			document.getElementById('btnobjekpajak').setAttribute("disabled", "disabled");
			document.getElementById('ckb_tarif_manual').setAttribute("disabled", "disabled");
			document.getElementById('ckb_nomor_skb').setAttribute("disabled", "disabled");
			document.getElementById('keterangan').setAttribute("disabled", "disabled");
			document.getElementById('dpp_pemotongan').setAttribute("readonly", "readonly");
			document.getElementById('kategori_biaya').setAttribute("disabled", "disabled");	
			document.getElementById('kategori').setAttribute("disabled", "disabled");	
			document.getElementById('ckb_tarif_manual').checked = false;
			document.getElementById('ckb_nomor_skb').checked = false;
		}else{
			document.getElementById('btnobjekpajak').removeAttribute("disabled");
			document.getElementById('ckb_tarif_manual').removeAttribute("disabled");
			document.getElementById('ckb_nomor_skb').removeAttribute("disabled");
			document.getElementById('keterangan').removeAttribute("disabled");
			document.getElementById('dpp_pemotongan').removeAttribute("readonly");
			document.getElementById('kategori_biaya').removeAttribute("disabled");	
			document.getElementById('kategori').removeAttribute("disabled");	
		}

		manual();
	}

	function readyonly_manualpph(){
		var manualInputs = [
			document.getElementById("manual_pph23"),
			document.getElementById("manual_pph4"),
			document.getElementById("manual_pph21"),
			document.getElementById("manual_pph212"),
			document.getElementById("manual_pph15")
		];

		for (var i = 0; i < manualInputs.length; i++) {
			manualInputs[i].setAttribute("readonly", "readonly");
		}
	}
	function manual() {
		var tarifManualCheckbox = document.getElementById("ckb_tarif_manual");

		readyonly_manualpph();

		if (tarifManualCheckbox.checked) {
			if(document.getElementById('rd_pph23').checked == true){
				document.getElementById("manual_pph23").removeAttribute("readonly");
			}else if(document.getElementById('rd_pph4').checked == true){
				document.getElementById("manual_pph4").removeAttribute("readonly");
			}else if(document.getElementById('rd_pph21').checked == true){
				document.getElementById("manual_pph21").removeAttribute("readonly");
				document.getElementById("manual_pph212").removeAttribute("readonly");
			}else if(document.getElementById('rd_pph15').checked == true){
				document.getElementById("manual_pph15").removeAttribute("readonly");
			}
		}
	}

	function f_nomor_skb() {
		var ckb_nomor_skb = document.getElementById('ckb_nomor_skb');
		var nomor_skb = document.getElementById('nomor_skb');

		if (ckb_nomor_skb.checked) {
			nomor_skb.removeAttribute("readonly");
		} else {
			nomor_skb.setAttribute("readonly", "readonly");
			nomor_skb.value = "";
		}
	}

	function nilai_ppn_manual() {
		var ppn_persen_fp = document.getElementById('ppn_persen_fp');
		var ckb_ubah_nilai_ppn_manual = document.getElementById('ckb_ubah_nilai_ppn_manual');
		var ppn_nilai_fp = document.getElementById('ppn_fp');

		if (ckb_ubah_nilai_ppn_manual.checked) {
			ppn_persen_fp.removeAttribute("readonly");
			ppn_nilai_fp.removeAttribute("readonly");
		} else {
			ppn_nilai_fp.setAttribute("readonly", "readonly");
			ppn_persen_fp.setAttribute("readonly", "readonly");
		}
	}

	function namapemberijasa() {

		var checksupplier = document.getElementById('rd_pemberi_jasa_supplier').checked;
	    var pemberijasa = checksupplier ? 'Supplier' : 'Customer';

		if ($.fn.DataTable.isDataTable('#datapemberijasa')) {
			$('#datapemberijasa').DataTable().destroy();
		}

		document.getElementById('kodepj').innerHTML=pemberijasa;
		document.getElementById('namapj').innerHTML=pemberijasa;

		$('#datapemberijasa').dataTable( {
			"bProcessing": true,
			"bServerSide": true,
			"columnDefs": [
				{ targets: 'no-sort', orderable: false },
				{ targets: 'col-hide', visible: false }
			],
			"sAjaxSource": '<?php echo site_url('Tagihandiluarpembelian/getPemberijasa'); ?>/'+pemberijasa,
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
			},
			"scrollX": true
		});

		$('#pemberijasa').modal('show');
	}
	function selectpemberijasa(xkd='',xnama='',xNPWP='',xalm='',xCabang='',xaction){
		var kd 		= atob(xkd).trim();
		var nama 	= atob(xnama).trim();
		var NPWP 	= atob(xNPWP).trim();
		var alm 	= atob(xalm).trim();
		var Cabang 	= atob(xCabang).trim();

		// if(action=='supplier'){
		// 	var detail = 'NAMA  	 : ' + nama + '    \nNPWP      : ' + NPWP + '\nALAMAT  : ' + alm;
		// }else{
			var detail 	= 'NAMA  	 : '+nama+'    \nNPWP      : '+NPWP+'          \nNIK 	        : 	\nALAMAT  : '+alm;
		// }
		document.getElementById('kd_pemberi_jasa').value = kd;

		document.getElementById('nama_pemberi_jasa').value = nama;
		document.getElementById('npwp').value = NPWP;
		document.getElementById('alamat_pemberi_jasa').value = alm;

		document.getElementById('detail_tagihan_textarea').innerHTML = detail;
		document.getElementById('cabang').innerHTML = Cabang;

		document.getElementById('nama_pemberi_jasa_fp').value = nama;

		document.getElementById('detail_pemberi_jasa_fp').innerHTML = detail;


		check_tagihan_faktur();
		$('#pemberijasa').modal('hide');
	}

	var openobjekpajak = 0;
	function objekpajak() {

		if ($.fn.DataTable.isDataTable('#dataobjekpajak')) {
			$('#dataobjekpajak').DataTable().destroy();
		}

		$('#dataobjekpajak').dataTable( {
			"bProcessing": true,
			"bServerSide": true,
			"columnDefs": [
				{ targets: 'no-sort', orderable: false },
				{ targets: 'col-hide', visible: false }
			],
			"sAjaxSource": '<?php echo site_url('Tagihandiluarpembelian/getObjekpajak'); ?>',
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
			},
			"scrollX": true
		});

	    $('#objekpajak').modal('show');
	}

	function selectkodepajak(kdobjekpajak,namaobjekpajak,pasalpph){
		document.getElementById('kode_objek_pajak').value = kdobjekpajak;
		document.getElementById('nama_objek_pajak').value = namaobjekpajak;

		document.getElementById('manual_pph23').value = '0%';	
		document.getElementById('manual_pph4').value = '0%';	
		document.getElementById('manual_pph21').value = '0%';	
		document.getElementById('manual_pph212').value = '0%';	
		document.getElementById('manual_pph15').value = '0%';	

		document.getElementById('jumlah_pph23').value = '0';	
		document.getElementById('jumlah_pph4').value = '0';	
		document.getElementById('jumlah_pph21').value = '0';	
		document.getElementById('jumlah_pph15').value = '0';

		var persen = 0;
		var persen2 = 0;
		var posisi = '';
		if(pasalpph=='PPH23'){
			document.getElementById('rd_pph23').checked = true;
			document.getElementById('manual_pph23').value = '2%';
			persen = document.getElementById('manual_pph23').value;
			posisi = 'jumlah_pph23';
		}else if(pasalpph=='PPH4-2'){
			document.getElementById('rd_pph4').checked = true;
			document.getElementById('manual_pph4').value = '10%';	
			persen = document.getElementById('manual_pph4').value;
			posisi = 'jumlah_pph4';
		}else if(pasalpph=='PPH21'){
			document.getElementById('rd_pph21').checked = true;
			document.getElementById('manual_pph21').value = '50%';	
			document.getElementById('manual_pph212').value = '5%';	
			persen = document.getElementById('manual_pph21').value;
			persen2 = document.getElementById('manual_pph212').value;
			posisi = 'jumlah_pph21';
		}else if(pasalpph=='PPH15'){
			document.getElementById('rd_pph15').checked = true;
			document.getElementById('manual_pph15').value = '1.5%';	
			persen = document.getElementById('manual_pph15').value;
			posisi = 'jumlah_pph15';
		}

		manual();
		hitungpotonganpph(persen,persen2,posisi);

		$('#objekpajak').modal('hide');

	}

	function hitungpotonganpph(persen=0,persen2=0,posisi){

		var potongan = persen.replace('%', '');

		var dpp = document.getElementById('dpp_pemotongan').value;
			dpp = dpp.replace(/,/g, '');

		var hasil_total = parseFloat(dpp) * parseFloat(persen)/100;

		if(persen2!==0 && persen2!=='' && persen2!=='%' && persen2!=='0%'){
			var potongan2 = persen2.replace('%', '');
			hasil_total = parseFloat(hasil_total) * parseFloat(potongan2)/100;			
		}

		document.getElementById(posisi).value = formatAngka(hasil_total.toString());
	}

	katagori();
	function katagori(pemberijasa='Supplier',selected=''){

		document.getElementById('kategori').innerHTML = '<option value="">Loading...</option>';

		var data = '&pemberijasa=' + pemberijasa;

		$.ajax({
			type: 'POST',
			url: '<?php echo site_url('Tagihandiluarpembelian/getKategori'); ?>',
			data:data,
			success: function (data) {
				const jsonObject = JSON.parse(data);
				if (jsonObject.result == 'sukses') {
					var kategorihtml = '';
					for(var i=0; i<jsonObject.data.length; i++){
						var xselected = '';
						if(selected==jsonObject.data[i].Kategori){
							xselected = 'selected';
						}
						kategorihtml += '<option value="'+jsonObject.data[i].Kategori+'" '+xselected+'>'+jsonObject.data[i].Kategori+'</option>';
					}
					document.getElementById('kategori').innerHTML = kategorihtml;
				}
			}

		});
	}

	divisi();
	function divisi(selected=''){

		document.getElementById('divisi').innerHTML = '<option value="">Loading...</option>';

		$.ajax({
			type: 'POST',
			url: '<?php echo site_url('Tagihandiluarpembelian/getDivisi'); ?>',
			success: function (data) {
				const jsonObject = JSON.parse(data);
				if (jsonObject.result == 'sukses') {
					var divisihtml = '';
					for(var i=0; i<jsonObject.data.length; i++){
						var xselected = '';
						if(selected==jsonObject.data[i].Divisi){
							xselected = 'selected';
						}
						divisihtml += '<option value="'+jsonObject.data[i].Divisi+'" '+xselected+'>'+jsonObject.data[i].Divisi+'</option>';
					}
					document.getElementById('divisi').innerHTML = divisihtml;
				}
			}

		});
	}

	function create_nk(){
		var tanggalnk = '';
		var nonk = '';
		var totalnk = '';
		if(document.getElementById('chk_otomatis_create_nk').checked==true){
			tanggalnk = document.getElementById('tanggal_fp').value;
			nonk = document.getElementById('no_fp_masukan').value;
			totalnk = document.getElementById('total_fp').value;
		}else{
			tanggalnk = date('d-m-Y');
			nonk = '';
			totalnk = 0;
		}

		document.getElementById('tanggal_nk').value = tanggalnk;
		document.getElementById('no_nk').value = nonk;
		document.getElementById('total_nk').value = totalnk;
	}

	$("#loading").hide();
	$("#disablingDiv").hide();


	function export_data(e) {
	    var bulanx = document.getElementById('reportbulan').value;
	   	var tahunx = document.getElementById('reporttahun').value;

	    window.open("<?php echo site_url('Tagihandiluarpembelian/getDataExcel'); ?>/"+e+"?bulan="+bulanx+"&tahun="+tahunx, "_blank");
	}

	function nama_pemberi_jasa_action(){
		var nama 	= document.getElementById('nama_pemberi_jasa').value;
		var npwp 	= document.getElementById('npwp').value;
		var nik 	= document.getElementById('nik_pemberi_jasa').value;
		var alamat 	= document.getElementById('alamat_pemberi_jasa').value;

		var detail 	= 'NAMA  	 : ' + nama + '\nNPWP      : ' + npwp + ' \nNIK 	        : ' + nik + '\nALAMAT  : ' + alamat;

		document.getElementById('detail_tagihan_textarea').innerHTML = detail;

		nama_pemberi_jasa_textarea();

		check_tagihan_faktur();
	}

	function nama_pemberi_jasa_textarea(){
		var nama = document.getElementById('nama_pemberi_jasa').value;
		document.getElementById('nama_pemberi_jasa_fp').value = nama;

		var detail = document.getElementById('detail_tagihan_textarea').value;
		document.getElementById('detail_pemberi_jasa_fp').innerHTML = detail;
		check_tagihan_faktur();
	}

	function check_tagihan_faktur(){
		var tgh = document.getElementById('chk_tagihan_ada').checked;
		if(tgh==true){
			document.getElementById('tanggal_fp').value = document.getElementById('tanggal_tagihan').value;
			document.getElementById('nama_pemberi_jasa_fp').value = document.getElementById('nama_pemberi_jasa').value;
			document.getElementById('detail_pemberi_jasa_fp').innerHTML = document.getElementById('detail_tagihan_textarea').value;
		}else{
			document.getElementById('tanggal_fp').value = '';
			document.getElementById('nama_pemberi_jasa_fp').value = '';
			document.getElementById('detail_pemberi_jasa_fp').innerHTML = '';
		}
		
	}
</script>