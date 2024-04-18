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

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div class="container">
<?php
  echo($content_html);
?>
</div>
<script>
  $(document).ready(function(){
    $("#loading").hide();
    $("#disablingDiv").hide();
    <?php if(isset($error) && $error != '') echo 'alert("'.$error.'");';  ?>
  });
</script>