<script>
    $(document).ready(function() {
      $('#TblUser').DataTable({
        "pageLength": 25
      }); 
    }); 
    $(document).ready(function() {
      $('#Tblwa').DataTable({
        "pageLength": 25
      }); 
    }); 

  var loadData = function(accountid,pritority,protocol,host,port,stmp,email,alias,pass,replyto){
    document.getElementById('edit_txtaccountid').value = accountid;
    document.getElementById('edit_txtpriority').value = pritority;
    document.getElementById('edit_txtprotocol').value = protocol;
    document.getElementById('edit_txthost').value = host;
    document.getElementById('edit_txtport').value = port;
    document.getElementById('edit_txtsmtpcrypto').value = stmp;
    document.getElementById('edit_txtuser').value = email;
    document.getElementById('edit_txtalias').value = alias; 
    document.getElementById('edit_txtpass').value = pass; 
    document.getElementById('edit_txtreplyto').value = replyto; 
  }
  var loadDataWa = function(Instance,Url,Token,Vendor,Note,active,Daily){
    document.getElementById('edit_txtInstance').value = Instance;
    document.getElementById('edit_txtUrl').value = Url;
    document.getElementById('edit_txtToken').value = Token;
    document.getElementById('edit_txtVendor').value = Vendor;
    document.getElementById('edit_txtNote').value = Note;
    document.getElementById('edit_txtDaily').value = Daily; 
    if (active==1)
    {
      document.getElementById("edit_chkAktif").checked = true;
    }
    else
    {
      document.getElementById("edit_chkAktif").checked = false;
    }
  }
 
$(document).on('click', '#open', function () {
    $('#break-diag').dialog({ 
        width: "40%",
        modal: true,
        title: 'Send Email',
        show: {
            effect: "scale",
            duration: 200
        },
        resizable: false,
        buttons: [{
            text: "Send",
            click: function () {
                var txt = $('#txtemail').val();
                $.ajax({ 
                  type: 'POST', 
                  url: '<?php echo site_url("accountemail/tesEmail") ?>', 
                  data: { email: txt}, 
                  dataType: 'json',
                  success: function (data) {
                    if(data.result=='SUCCESS'){ 
                      alert("Send Email Success");
                    }
                    else
                    {
                      alert("Send Email Failed");
                    }
                  }
                });
                $(this).dialog('close');
            }
        }]
    });
});


$(document).on('click', '#open2', function () {
    $('#break-diag').dialog({ 
        width: "40%",
        modal: true,
        title: 'Send WA',
        show: {
            effect: "scale",
            duration: 200
        },
        resizable: false,
        buttons: [{
            text: "Send",
            click: function () {
                var no_wa = $('#txtNoWa').val();   
                $.ajax({ 
                  type: 'POST', 
                  url: '<?php echo site_url("accountemail/tesWA") ?>', 
                  data: { nowa: no_wa}, 
                  dataType: 'json',
                  success: function (data) {
                    if(data.result=='SUCCESS'){ 
                      alert("Send WA Success");
                    }
                    else
                    {
                      alert("Send WA Failed");
                    }
                  }
                });
                $(this).dialog('close');
            }
        }]
    });
});

function delete_email(id){
                if (confirm("Apakah anda yakin ingin menghapus Account Email ini?") == true) { 
                      var data ='&account_id='+id;  
                      $.ajax({
                        type      : 'POST', 
                        url       : '<?php echo site_url('accountemail/deleteemail') ?>',
                        data      : data,
                        success   : function(data) { 
                          var data = data.trim();
                          if(data=='1'){ 
                              location.reload(); 
                          } 
                          return false;

                        }

                      })
                }
              }

function delete_whatsapp(id){
                if (confirm("Apakah anda yakin ingin menghapus Account Whatsapp ini?") == true) { 
                      var data ='&apiInstance='+id;  
                      $.ajax({
                        type      : 'POST', 
                        url       : '<?php echo site_url('accountemail/deletewhatsapp') ?>',
                        data      : data,
                        success   : function(data) { 
                          var data = data.trim();
                          if(data=='1'){ 
                              location.reload(); 
                          } 
                          return false;

                        }

                      })
                }
              }

</script>  
  
<style>  
  #break-diag{
      display:none;
  }   
  .right {
      text-align: right;
      float: right;
      margin-bottom:5px;
  }

  .modal-content { 
    margin-top: 10%;
  } 

  .modal-dialog {
    width: 70%;
  } 

  .modal-body { 
    margin-left:25px;
    margin-right:25px; 
  }  

  .glyphicon { font-size:20px;margin-left:5px;margin-right:5px; }
  .merah { color:#c91006; }
  .hijau { color:#0ead05;}

  input[type=text], select {
    width: 100%;
    padding: 12px 20px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
  }
 
</style>
<?php 
if ($Tipe == "Email")
{
  ?>
  <div class="container">
    <div class="page-title">ACCOUNT EMAIL</div>
    <?php if($_SESSION["can_create"] == 1) { ?>
      <a href="#" data-toggle='modal' data-target='#insert_new'>Insert New Account Email</a>
    <?php } ?>  

    <div class="right"> 
      <link href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" rel="stylesheet"/> 
      <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
      <button id="open">Tes Kirim Email</button>
      <div id="break-diag"> 
        <label for="fname">Email</label>
        <input type="text" id="txtemail" name="firstname" placeholder="Your email...">
      </div>
    </div>

    <table id="TblUser" class="table table-striped table-bordered" cellspacing="0" width="100%">

      <?php
      echo "<thead>";
      echo "<tr>"; 
      echo "<th >Priority</th>"; 
      echo "<th >Protocol</th>";
      echo "<th >Host/Port/Smtp</th>"; 
      echo "<th >Email User</th>";  
      echo "<th >Modified By</th>";
      echo "<th >Modified Date</th>";   
      echo "<th >Action</th>";  
      echo "</tr>";  
      echo "</thead>"; 
      echo "<tbody id='TblUserBody'>";  
        $x = 1;  
        foreach($ListEmailAccount as $data) {
          $priority = $data->priority;
          $mail_protocol = $data->mail_protocol; 
          $mail_user = $data->mail_user;    
          $modified_by = $data->modified_by; 
          $modified_date = $data->modified_date; 

          $USERNAME = "<b>".$data->mail_user."</b><br>".$data->mail_alias."<br>".$data->mail_replyto;
          $host_port_smtp_crypto = $data->mail_host."<br>".$data->mail_port."<br>".$data->smtp_crypto;

          echo "<tr>"; 
          echo "<td class='hideOnMobile'>".$priority."</td>";
          echo "<td class='hideOnMobile'>".$mail_protocol."</td>"; 
          echo "<td class='hideOnMobile'>".$host_port_smtp_crypto."</td>";  
          echo "<td class='hideOnMobile'>".$USERNAME."</td>";    
          echo "<td class='hideOnMobile'>".$modified_by."</td>";  
          echo "<td class='hideOnMobile'>".date("d-M-Y",strtotime($modified_date))."</td>";   
          $ACTION = ''; 
          if($_SESSION["can_update"] == 1)  
            $ACTION .= "<a href='#' data-href='#' data-toggle='modal' data-target='#edit_data' onclick='loadData(".'"'.$data->account_id.'"'.',"'.$data->priority.'"'.',"'.$data->mail_protocol.'"'.',"'.$data->mail_host.'"'.',"'.$data->mail_port.'"'.',"'.$data->smtp_crypto.'"'.',"'.$data->mail_user.'"'.',"'.$data->mail_alias.'"'.',"'.$data->mail_pwd.'"'.',"'.$data->mail_replyto.'"'.")'> <i class='glyphicon glyphicon-edit hijau'></i> </a>"; 
          if($_SESSION["can_delete"] == 1)
            $ACTION .= '<a onclick="delete_email('."'".$data->account_id."'".')"  ><i class="glyphicon glyphicon-trash merah"></i></a>'; 
 
          echo "<td class='hideOnMobile'>".$ACTION."</td>";
          echo "</tr>";
          $x += 1;
        }
        echo "</tbody>"; ?>
    </table>  

    <div class="modal modal-tall fade"  id="insert_new" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  Insert New Account Email
              </div>
              <div class="modal-body">
                <?php echo form_open("accountemail/insertemail"); ?> 

                <div class="row">
                  <div class="col-2">
                    <label>Email Protocol</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="txtprotocol" id="txtprotocol" placeholder="" required>
                  </div>
                  <div class="col-2">
                    <label>Email Host</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="txthost" id="txthost" placeholder="" required> 
                  </div>
                </div> 

                <div class="row">
                  <div class="col-2">
                    <label>Email Port</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="txtport" id="txtport" placeholder="" required> 
                  </div>
                  <div class="col-2">
                    <label>Email User</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="txtuser" id="txtuser" placeholder="" required> 
                  </div>
                </div>  


                <div class="row">
                  <div class="col-2">
                    <label>Email Password</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="txtpass" id="txtpass" placeholder="" required> 
                  </div>
                  <div class="col-2">
                    <label>Email Alias</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="txtalias" id="txtalias" placeholder="" required> 
                  </div>
                </div> 

                <div class="row">
                  <div class="col-2">
                    <label>Smtp Crypto</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="txtsmtpcrypto" id="txtsmtpcrypto" placeholder="" required> 
                  </div>
                  <div class="col-2">
                    <label>Email Reply To</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="txtreplyto" id="txtreplyto" placeholder="" required> 
                  </div>
                </div>  

                  <input type="submit" class="btn btn-primary" value="Submit">
                  <input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
                <?php echo form_close(); ?>
                <!-- </form> -->  
              </div>
          </div>
      </div> 
    </div>
    <div class="modal modal-tall fade"  id="edit_data" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  Edit Account Email
              </div>
              <div class="modal-body" >
                <?php echo form_open("accountemail/updateemail"); ?>
                  

                  <div class="row">
                  <div class="col-2">
                    <label>Account ID</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="edit_txtaccountid" id="edit_txtaccountid" readonly placeholder="" required>
                  </div> 
                  <div class="col-2">
                    <label>Priority</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="edit_txtpriority" id="edit_txtpriority" placeholder="" required>
                  </div> 
                </div> 

                  <div class="row">
                  <div class="col-2">
                    <label>Email Protocol</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="edit_txtprotocol" id="edit_txtprotocol" placeholder="" required>
                  </div>
                  <div class="col-2">
                    <label>Email Host</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="edit_txthost" id="edit_txthost" placeholder="" required> 
                  </div>
                </div> 

                <div class="row">
                  <div class="col-2">
                    <label>Email Port</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="edit_txtport" id="edit_txtport" placeholder="" required> 
                  </div>
                  <div class="col-2">
                    <label>Email User</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="edit_txtuser" id="edit_txtuser" readonly placeholder="" required> 
                  </div>
                </div>  


                <div class="row">
                  <div class="col-2">
                    <label>Email Password</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="edit_txtpass" id="edit_txtpass" placeholder="" required> 
                  </div>
                  <div class="col-2">
                    <label>Email Alias</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="edit_txtalias" id="edit_txtalias" placeholder="" required> 
                  </div>
                </div> 

                <div class="row">
                  <div class="col-2">
                    <label>Smtp Crypto</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="edit_txtsmtpcrypto" id="edit_txtsmtpcrypto" placeholder="" required> 
                  </div>
                  <div class="col-2">
                    <label>Email Reply To</label>
                  </div>
                  <div class="col-4">
                    <input type="text" class="form-control" name="edit_txtreplyto" id="edit_txtreplyto" placeholder="" required> 
                  </div>
                </div>   

                  <input type="submit" class="btn btn-primary" value="Update">
                  <input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
                <?php echo form_close(); ?>
                <!-- </form> -->  
              </div>
          </div>
      </div> 
    </div>
  </div>
  <?php
}
else
{ 
  ?>
  <div class="container">
    <div class="page-title">ACCOUNT WHATSAPP</div> 
    <?php if($_SESSION["can_create"] == 1) { ?>
      <a href="#" data-toggle='modal' data-target='#insert_new_whatsapp'>Insert New Account Whatsapp</a>
    <?php } ?>  
 
    <div class="right"> 
      <link href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" rel="stylesheet"/> 
      <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
      <button id="open2">Tes Kirim WA</button>
      <div id="break-diag"> 
        <label for="fname">No Wa</label>
        <input type="text" id="txtNoWa" name="firstname" placeholder="Your Number...">
        <p style="color:red;">PROSES PENGIRIMAN PESAN KE WHATSAPP AKAN DIKENAKAN BIAYA!!!</p>
      </div>
    </div>

    <!-- Form ADD -->  
    <div class="modal modal-tall fade"  id="insert_new_whatsapp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  Insert New Account Whatsapp
              </div>
              <div class="modal-body">
                <?php echo form_open("accountemail/insertwhatsapp"); ?> 
                  <div class="form-group">
                    <label>Api Instance</label>
                    <input type="text" class="form-control" name="txtInstance" id="txtInstance" placeholder="" readonly required>
                  </div>
                  <div class="form-group">
                    <label>Api Url</label>
                    <input type="text" class="form-control" name="txtUrl" id="txtUrl" placeholder="" required> 
                  </div>
                  <div class="form-group">
                    <label>Api Token</label>
                    <input type="text" class="form-control" name="txtToken" id="txtToken" placeholder="" required> 
                  </div>
                  <div class="form-group">
                    <label>Api Vendor</label>
                    <input type="text" class="form-control" name="txtVendor" id="txtVendor" placeholder="" required> 
                  </div>
                  <div class="form-group">
                    <label>Api Note</label>
                    <input type="text" class="form-control" name="txtNote" id="txtNote" placeholder="" required> 
                  </div>
                  <div class="form-group">
                    <label>Aktif</label>&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" class="" name="chkAktif" id="chkAktif" value="1">
                  </div>
                  <div class="form-group">
                    <label>Daily Quota</label>
                    <input type="number" class="form-control" name="txtDaily" id="txtDaily" placeholder="" required> 
                  </div> 
                  <input type="submit" class="btn btn-primary" value="Submit">
                  <input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
                <?php echo form_close(); ?> 
              </div>
          </div>
      </div> 
    </div>

    <!-- Form EDIT -->  
    <div class="modal modal-tall fade"  id="edit_data_whatsapp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  Edit Account Whatsapp
              </div>
              <div class="modal-body" >
                <?php echo form_open("accountemail/updatewhatsapp"); ?> 
                  <div class="form-group">
                    <label>Api Instance</label>
                    <input type="text" class="form-control" name="edit_txtInstance" id="edit_txtInstance" placeholder="" required readonly>
                  </div>
                  <div class="form-group">
                    <label>Api Url</label>
                    <input type="text" class="form-control" name="edit_txtUrl" id="edit_txtUrl" placeholder="" required> 
                  </div>
                  <div class="form-group">
                    <label>Api Token</label>
                    <input type="text" class="form-control" name="edit_txtToken" id="edit_txtToken" placeholder="" required> 
                  </div>
                  <div class="form-group">
                    <label>Api Vendor</label>
                    <input type="text" class="form-control" name="edit_txtVendor" id="edit_txtVendor" placeholder="" required> 
                  </div>
                  <div class="form-group">
                    <label>Api Note</label>
                    <input type="text" class="form-control" name="edit_txtNote" id="edit_txtNote" placeholder="" required> 
                  </div>
                  <div class="form-group">
                    <label>Aktif</label>&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" class="" name="edit_chkAktif" id="edit_chkAktif" value="1">
                  </div>
                  <div class="form-group">
                    <label>Daily Quota</label>
                    <input type="number" class="form-control" name="edit_txtDaily" id="edit_txtDaily" placeholder="" required> 
                  </div> 
                  <input type="submit" class="btn btn-primary" value="Update">
                  <input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
                <?php echo form_close(); ?> 
              </div>
          </div>
      </div> 
    </div>

    <!-- Form Table -->  
  <table id="Tblwa" class="table table-striped table-bordered" cellspacing="0" width="100%">

      <?php
      echo "<thead>";
      echo "<tr>"; 
      echo "<th >Instance</th>"; 
      echo "<th >Url/Token</th>"; 
      echo "<th >Vendor</th>";  
      echo "<th >Note</th>"; 
      echo "<th >Active</th>"; 
      echo "<th >Daily Quota</th>"; 
      echo "<th >Modified By</th>";
      echo "<th >Modified Date</th>";   
      echo "<th >Action</th>";  
      echo "</tr>";  
      echo "</thead>"; 
      echo "<tbody id='TblWaBody'>";  
        $x = 1;  
        foreach($ListWhatsappAccount as $data) {
          $apiInstance = $data->apiInstance;
          $apiUrl = $data->apiUrl; 
          $apiToken = $data->apiToken;    
          $apiVendor = $data->apiVendor; 
          $apiNote = $data->apiNote; 
          $isActive = $data->isActive; 
          $dailyQuota = $data->dailyQuota; 
          $modifiedBy = $data->modifiedBy; 
          $modifiedDate = $data->modifiedDate; 

          $UrlToken = "<b>".$apiUrl."</b><br>".$apiToken; 

          echo "<tr>"; 
          echo "<td class='hideOnMobile'>".$apiInstance."</td>";
          echo "<td class='hideOnMobile'>".$UrlToken."</td>";   
          echo "<td class='hideOnMobile'>".$apiVendor."</td>";    
          echo "<td class='hideOnMobile'>".$apiNote."</td>";  
          if ($isActive == 1)
          {
            echo "<td class='hideOnMobile'><input name='chkBox_2' type='checkbox' checked onclick='return false' /></td>"; 
          }
          else
          {
            echo "<td class='hideOnMobile'><input name='chkBox_2' type='checkbox' onclick='return false'/></td>"; 
          } 
          echo "<td class='hideOnMobile'>".$dailyQuota."</td>";   
          echo "<td class='hideOnMobile'>".$modifiedBy."</td>";  
          echo "<td class='hideOnMobile'>".date("d-M-Y",strtotime($modifiedDate))."</td>";   
          $ACTION = ''; 
          if($_SESSION["can_update"] == 1)  
            $ACTION .= "<a href='#' data-href='#' data-toggle='modal' data-target='#edit_data_whatsapp' ";
            $ACTION .= "onclick='loadDataWa(".'"'.$apiInstance.'"'.',"'.$apiUrl.'"'.',"'.$apiToken.'"'.',"'.$apiVendor.'"'.',"'.$apiNote.'"'.',"'.$isActive.'"'.',"'.$dailyQuota.'"'.")'> ";
            $ACTION .= " <i class='glyphicon glyphicon-edit hijau'></i> </a>"; 
          if($_SESSION["can_delete"] == 1) 
            $ACTION .= '<a onclick="delete_whatsapp('."'".$apiInstance."'".')"  ><i class="glyphicon glyphicon-trash merah"></i></a>'; 
          echo "<td class='hideOnMobile'>".$ACTION."</td>";
          echo "</tr>";
          $x += 1;
        }
        echo "</tbody>"; ?>
    </table> 


  </div>  
  <?php
}
?>   