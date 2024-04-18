<style type="text/css">
    .row {
      line-height:30px; 
      vertical-align:middle;
      clear:both;
    }
    .row-label, .row-input {
      float:left;
    }
    .row-label {
      width:40%;
    }
    .row-input {
      width:60%;
    }
    .noBorder {
        border:none !important;
    }
    .disablingDiv{
      z-index:1;
       
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
        z-index: 1;
/*        width: 150px;
        height: 150px;*/
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
<div style="height:25px;"></div>
<?php
  echo($content_html);
?>

<script>
  $(document).ready(function(){
    <?php if(isset($error) && $error != '') echo 'alert("'.$error.'");';  ?>
    $("#loading").hide();
    $("#disablingDiv").hide();
  });
  
  $(document).ajaxStart(function() {
    $("#disablingDiv").show();
    $("#loading").show();
  });

  $(document).ajaxStop(function() {
    $("#loading").hide();
    $("#disablingDiv").hide();
  });

  var prevVal = 0;

  function recalcStock(kolom,baris){
    for(var i = kolom; i < 5 ; i++){

      var jual = document.getElementById("Jual"+i+baris).value;
      var camp = document.getElementById("Camp"+i+baris).innerHTML;
      var stokakhir = document.getElementById("Stok"+i+baris).innerHTML;
      var beli = document.getElementById("Beli"+i+baris).innerHTML;

      if(i == 1){
        var stockAwal = document.getElementById("StokAwal"+baris).innerHTML;
      }
      else{
        var stockAwal = document.getElementById("Stok"+(i-1)+baris).innerHTML;
      }

      jual = parseInt(jual);
      camp = parseInt(camp);
      stokakhir = parseInt(stokakhir);
      stockAwal = parseInt(stockAwal);
      beli = parseInt(beli);

      var prevStokAwal = stockAwal;
      var prevJual = prevVal;
      var prevStokAkhir = stokakhir;
      var prevBeli = beli;

      var totaljual = document.getElementById("totaljual"+i).innerHTML;
      var totalstok = document.getElementById("totalstok"+i).innerHTML;
      var totalbeli = document.getElementById("totalbeli"+i).innerHTML;

      totaljual = parseInt(totaljual);
      totalstok = parseInt(totalstok);
      totalbeli = parseInt(totalbeli);

      var out = 0;

      if (jual!=prevJual) {

        //1. Hitung Stok Akhir
        stokakhir = Math.ceil(0.5*jual);
        
        out = out+camp;
        out = out+jual;
        out = out+stokakhir;
        //alert(out);

        //2. Hitung Beli
        if (stockAwal >= out) {
          beli = 0;
        } else {
          beli = out - stockAwal;
        }

        //3. Hitung Stok Akhir
        //stokakhir = ((stockAwal+beli)-(camp+jual));
        stokakhir = stockAwal+beli;
        stokakhir = stokakhir-camp;
        stokakhir = stokakhir-jual;

        document.getElementById("Beli"+i+baris).innerHTML = beli.toString();
        document.getElementById("Stok"+i+baris).innerHTML = stokakhir.toString();


        totaljual = totaljual+jual;
        totaljual = totaljual-prevJual;

        totalstok = totalstok+stokakhir;
        totalstok = totalstok-prevStokAkhir;

        totalbeli = totalbeli+beli;
        totalbeli = totalbeli-prevBeli;

        document.getElementById("totaljual"+i).innerHTML = totaljual.toString();
        document.getElementById("totalstok"+i).innerHTML = totalstok.toString();
        document.getElementById("totalbeli"+i).innerHTML = totalbeli.toString();
      }
    }
  }

  function setPrevValue(val){
    prevVal = val;
  }

  function SimpanData(jumlahdata,div,bln,thn,username){
    var arr = [];
    for (var i = 0; i < jumlahdata; i++) {
      arr.push({
          divisi: div,
          bulan: bln,
          tahun: thn,
          jenisbarang: document.getElementById("JenisBarang"+i).innerHTML,
          kodebarang: document.getElementById("KodeBarang"+i).innerHTML,
          stokawal: document.getElementById("StokAwal"+i).innerHTML,
          beli1: document.getElementById("Beli1"+i).innerHTML,
          jual1: document.getElementById("Jual1"+i).value,
          stok1: document.getElementById("Stok1"+i).innerHTML,
          beli2: document.getElementById("Beli2"+i).innerHTML,
          jual2: document.getElementById("Jual2"+i).value,
          stok2: document.getElementById("Stok2"+i).innerHTML,
          beli3: document.getElementById("Beli3"+i).innerHTML,
          jual3: document.getElementById("Jual3"+i).value,
          stok3: document.getElementById("Stok3"+i).innerHTML,
          beli4: document.getElementById("Beli4"+i).innerHTML,
          jual4: document.getElementById("Jual4"+i).value,
          stok4: document.getElementById("Stok4"+i).innerHTML,
          subkategori: document.getElementById("subkategori"+i).value,
          merk: document.getElementById("merk"+i).value,
          username: username
      });
    }
    $.ajax({
        url : '<?php echo API_URL ?>LaporanPreOrderPembelianBulanan/Simpan',
        type: "POST",
        data: {api:'APITES', data:JSON.stringify(arr)},
        dataType: "JSON",
        success: function(data)
        {
          alert('Data berhasil disimpan!');

        },
        error: function (jqXHR, textStatus, errorThrown)
        {
          alert('Error get data from ajax');
        }
    });

  }
</script>