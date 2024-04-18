<style type="text/css" media="print">
    @page 
    {
        size: auto;   /* auto is the initial value */
        margin: 10mm;  /* this affects the margin in the printer settings */
        margin-top: 15mm;  /* this affects the margin in the printer settings */
        font-size:-1pt;
    }
}
</style>
<style type="text/css">
    body { 
    	color: black;
    }

    .form-container {
      width: 400px;
      height: 250px;
      /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#deefff+0,98bede+100;Blue+3D+%2310 */
      background: #deefff; /* Old browsers */
      background: -moz-linear-gradient(top,  #deefff 0%, #98bede 100%); /* FF3.6-15 */
      background: -webkit-linear-gradient(top,  #deefff 0%,#98bede 100%); /* Chrome10-25,Safari5.1-6 */
      background: linear-gradient(to bottom,  #deefff 0%,#98bede 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
      filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#deefff', endColorstr='#98bede',GradientType=0 ); /* IE6-9 */

      border:1px solid blue;
      border-radius:15px;
      padding:15px;
    }

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

    /*.disablingDiv{
      z-index:1;
       
      position: fixed; 
      top: 0%; 
      left: 0%; 
      width: 100%; 
      height: 100%; 
      overflow: hidden;
      margin:0;
      
      background-color: white; 
      opacity:0.5;  
    }
    .loader {
        position: absolute;
        left: 50%;
        top: 50%;
        z-index: 1;
        width: 150px;
        height: 150px;
        margin: -75px 0 0 -75px;
        border: 16px solid #f3f3f3;
        border-radius: 50%;
        border-top: 16px solid #3498db;
        width: 120px;
        height: 120px;
        -webkit-animation: spin 2s linear infinite;
        animation: spin 2s linear infinite;
    }*/

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<?php
  echo($content_html);
?>

<script>
  $(document).ready(function(){
    $("#loading").hide();
    $("#disablingDiv").hide();
    <?php if(isset($error) && $error != '') echo 'alert("'.$error.'");';  ?>
  });
  
  $(document).ajaxStart(function() {
    $("#disablingDiv").show();
    $("#loading").show();
  });

  $(document).ajaxStop(function() {
    $("#loading").hide();
    $("#disablingDiv").hide();
  });

</script>