<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<style>
  .header-row, .header-row-label {
    padding:4px!important; font-size:12px!important;
  }
  .header-row-label { font-size:14px!important;}
  .header-row input, .header-row select {
    font-size:12px!important;
    text-transform: uppercase;
  }
  .ui-front {
    z-index: 9999999 !important;
  }
  .draft {
    font-size:10px;margin-left:10px;padding:1px;width:25px;border:0px;background-color: transparent;
    /*color:white;font-size:12px;margin-left:10px;padding:1px;background-color:red;width:75px;*/
  }
  .smalldraft {
    color:white;font-size:10px;margin-left:10px;padding:1px;background-color:#070a91;width:75px;
  }
  .hideMe {
    display:none;
  }
  .campaign-plan-card {
    padding-left:50px;padding-right:50px;margin-top:20px; margin-bottom:20px;
  }

  .dt-cell {
    background-color: #fdff8f;
  }
  .filterDropdown {
    width: 100%;
    background-color: #ffffcc;
  }

  .filterText {
    width: 75%;
    background-color: #ffffcc;
    text-transform: uppercase;
  }

  .title {
    font-size: 15pt;
    font-weight: bold;
    text-align: center;
  }

  .btn {
    font-size:12px!important;
  }

  .e-message {
    color: #fff;
    font-size: 13px;
    font-style: italic;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
  }

  .e-message.error {
    background-color: #dc3545;
  }

  .e-message.success {
    background-color: #218838;
  }

  .hidden {
    display: none;
  }

  
  /* Important part */
  .modal-dialog{
      width: 750px;
      overflow-y: initial !important
  }  

  .btndeleteclass {
    background-color: transparent;
    border: none;
    color: white; 
    font-size: 16px;
    cursor: pointer;
  }

</style>
<script>

  var listuseridselect="";
  var ListUser;

  var ListUserAdd = "";

  var ThisUser;
  var idxuser = 0; // untuk simpan nilai id unik terakhir supaya tidak ada yg double id jika ada row yg terhapus di baris tengah table
  var brs = 0;
 
 
  $(document).ready(function() {

      $('.dropdownuser').select2({  dropdownParent: $("#add_user_role"),
            ajax: {
           url: "<?=base_url()?>Role/getRoleAllUser2",
           data: function (params) {
              var query = {
                q: params.term 
              }
              return query;
           },
           processResults: function (data) {
              // Transforms the top-level key of the response object from 'items' to 'results'
              var json = JSON.parse(data);
              return {
                results : json.data
              };
           }
          }
        }); 
    $('#example').DataTable({
      "pageLength": 25
    });  
    $("#flash-msg").delay(1200).fadeOut("slow");
  } );

  var RemoveUser2 = function(idx,kduser) {  
      var newlistuser = "";
      const myArray = ListUserAdd.split(",");
      for (var i = 0; i < myArray.length; i++) {
         if (myArray[i]!=kduser)
         {
           if (newlistuser=="")
            {
              newlistuser = myArray[i];
            }
            else
            {
              newlistuser = newlistuser + "," + myArray[i];
            }
         }
       }   
       ListUserAdd = newlistuser;  
      
      document.getElementById('listuserid').value = ListUserAdd; 

     $("#kolumuser" + idx).remove();
  }
 
  var addRowUser = function() {   
    var e = document.getElementById("dropdownusers");
    var value = e.value;

    if (value=="")
    {
      alert("User Belum Dipilih !!!");
    }
    else
    {
      var text = e.options[e.selectedIndex].text;

      var tr = "<tr class='rowbrg'  id='kolumuser" + idxuser + "'>";
      var td = "<td> <button onclick='RemoveUser2("+'"' + idxuser + '","' + value + '"' +")' class='btndeleteclass' ><i class='glyphicon glyphicon-trash' aria-hidden='true'></i></button> " + 
        "</td>";
      var td2 = " <td> "+text+" </td> </tr>"; 


      var duplicates = false; 
      const myArray = ListUserAdd.split(",");
      for (var i = 0; i < myArray.length; i++) {
         if (myArray[i]==value)
         {
           duplicates = true;
         }
       }   

      if (duplicates)
      {
        alert("User Sudah Ada Di List !!!");
      }
      else { 
        if (ListUserAdd=="")
        {
          ListUserAdd = value;
        }
        else
        {
          ListUserAdd = ListUserAdd + "," + value;
        } 

        document.getElementById('listuserid').value = ListUserAdd; 

        $("#tbodyUser").append(tr+td+td2); 
        idxuser++;
      } 

    }

    

  }

  $('#confirm-delete').on('show.bs.modal', function(e) {
      $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
      $('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
  });
  function selectall(i) {  
        var checkbox = document.getElementById('chkAll'+i);
        var status = checkbox.checked; 

        $('#chkread'+i).prop('checked', status); 
        $('#chkcreate'+i).prop('checked', status); 
        $('#chkupdate'+i).prop('checked', status); 
        $('#chkdelete'+i).prop('checked', status); 
        $('#chkprint'+i).prop('checked', status);    
      }  


    function checkAllorNot(i) {  
        var Readcheckbox = document.getElementById('chkread'+i).checked;
        var Insertcheckbox = document.getElementById('chkcreate'+i).checked;
        var Updatecheckbox = document.getElementById('chkupdate'+i).checked;
        var Deletecheckbox = document.getElementById('chkdelete'+i).checked;
        var Printcheckbox = document.getElementById('chkprint'+i).checked;
  
        if (Readcheckbox && Insertcheckbox&& Updatecheckbox&& Deletecheckbox&& Printcheckbox)
        {
          $('#chkAll'+i).prop('checked', true);    
        }
        else
        {
          $('#chkAll'+i).prop('checked', false); 
        }
      }  

    function popupWindow(id){
      if(id=='userpick' || id=='userpickforupd'){
        window.open('UserPicker?id=' + encodeURIComponent(id),'popuppage',
        'width=800,toolbar=1,resizable=1,scrollbars=yes,height=600,top=0,left=100');
      }
      else if(id=='dbpick' || id=='dbpickforupd'){
        window.open('DatabasePicker?id=' + encodeURIComponent(id),'popuppage',
        'width=800,toolbar=1,resizable=1,scrollbars=yes,height=600,top=0,left=100');
      }
    }
    function loadMapData (kdrole, nmrole, ishrd, ismainbranch, importance){

      document.getElementById('utxtKodeRole').value = kdrole;
      document.getElementById('utxtNamaRole').value = nmrole; 
      if(ismainbranch == "1"){
        document.getElementById('uchkMainBranch').checked = true;
      }
      else{
        document.getElementById('uchkMainBranch').checked = false;
      }

      //document.getElementById('uselImpor').value = importance;
    }
    function loadduplicaterole (roleid ,nmrole, ismainbranch){ 
  
      document.getElementById('txtroleidhidden').value = roleid;
      document.getElementById('txtDupKodeRole').value = '<?php echo 'ROLE'.$rolecode;?>'
      document.getElementById('txtDupNamaRole').value = nmrole; 

      if(ismainbranch == "1"){
        document.getElementById('chDupMainBranch').checked = true;
      }
      else{
        document.getElementById('chDupMainBranch').checked = false;
      }
    }
    function loadaddnewrole (){     
      document.getElementById('txtKodeRole').value = '<?php echo 'ROLE'.$rolecode;?>'
    }
    function loadModuleData(kdrole,nmrole){

      document.getElementById('mtxtKodeRole').value = kdrole;
      document.getElementById('mtxtKodeRoleName').value = nmrole;
      $(".loading").show();
      var csrf_bit = $("input[name=csrf_bit]").val();
      $.post("<?php echo site_url('Role/getRoleModule'); ?>", {
        akdrole :kdrole,
        csrf_bit:csrf_bit
      }, 
      function(data){
          
          $("#tableModule tbody tr").remove();
          for(var i=0;i<data.length;i++)
          {
              var tr="<tr>";
              var td1="<td style='width:10%'>"+data[i]["module_id"]+" <input type='hidden' name='hdnKdModule"+i+"' id='hdnKdModule"+i+"' value='"+data[i]["module_id"]+"'></td>";
              var td2="<td style='width:30%'>"+data[i]["module_name"]+"</td>";


              if(data[i]["can_read"] == "1" && data[i]["can_create"] == "1" && data[i]["can_update"] == "1" && data[i]["can_delete"] == "1" && data[i]["can_print"] == "1"){
                var tdall="<td style='width:10%'><input type='checkbox' onClick='selectall("+i+")' name='chkAll"+i+"' id='chkAll"+i+"' checked></td>";
              }
              else
              { 
                var tdall="<td style='width:10%'><input type='checkbox' onClick='selectall("+i+")' name='chkAll"+i+"' id='chkAll"+i+"' ></td>";
              } 

              if(data[i]["can_read"] == "1"){
                var td3= "<td style='width:10%'><input type='checkbox' onClick='checkAllorNot("+i+")'  name='chkread"+i+"' id='chkread"+i+"' checked></td>";
              }
              else{
                var td3= "<td style='width:10%'><input type='checkbox' onClick='checkAllorNot("+i+")'  name='chkread"+i+"' id='chkread"+i+"'></td>";
              }
              if(data[i]["can_create"] == "1"){ 
                var td4="<td style='width:10%'><input type='checkbox' onClick='checkAllorNot("+i+")'  name='chkcreate"+i+"' id='chkcreate"+i+"' checked></td>";
              }
              else{
                var td4="<td style='width:10%'><input type='checkbox' onClick='checkAllorNot("+i+")'  name='chkcreate"+i+"' id='chkcreate"+i+"'></td>";
              }
              if(data[i]["can_update"] == "1"){
                var td5="<td style='width:10%'><input type='checkbox' onClick='checkAllorNot("+i+")'  name='chkupdate"+i+"' id='chkupdate"+i+"' checked></td>";
              }
              else{
                var td5="<td style='width:10%'><input type='checkbox' onClick='checkAllorNot("+i+")'  name='chkupdate"+i+"' id='chkupdate"+i+"'></td>";
              }
              if(data[i]["can_delete"] == "1"){
                var td6="<td style='width:10%'><input type='checkbox' onClick='checkAllorNot("+i+")'  name='chkdelete"+i+"' id='chkdelete"+i+"' checked></td>";
              }
              else{
                var td6="<td style='width:10%'><input type='checkbox' onClick='checkAllorNot("+i+")'  name='chkdelete"+i+"' id='chkdelete"+i+"'></td>";
              }
              if(data[i]["can_print"] == "1"){
                var td7="<td style='width:10%'><input type='checkbox' onClick='checkAllorNot("+i+")'  name='chkprint"+i+"' id='chkprint"+i+"' checked></td>";
              }
              else{
                var td7="<td style='width:10%'><input type='checkbox' onClick='checkAllorNot("+i+")'  name='chkprint"+i+"' id='chkprint"+i+"'></td>";
              }  
              var tr2="</tr>";
              $("#tableModule tbody").append(tr+td1+td2+tdall+td3+td4+td5+td6+td7+tr2);
          }
          $("#hdnJmlhModule").val(data.length);
          $(".loading").hide();
      },'json',"Error get data from ajax");
    }
    function loaduserrole(kdrole,nmrole){

      document.getElementById('txtKodeUserByRole').value = kdrole;
      document.getElementById('txtKodeUserByRoleName').value = nmrole; 
      $(".loading").show(); 
      $.post("<?php echo site_url('Role/getRoleUser'); ?>", {
        akdrole :kdrole 
      }, 
      function(data){
          
          $("#tableuser tbody tr").remove();
          for(var i=0;i<data.length;i++)
          {
              var tr="<tr>";
              var td1="<td style='width:20%'>"+data[i]["user_id"]+"</td>";
              var td2="<td style='width:40%'>"+data[i]["user_name"]+"</td>";
              var td3="<td style='width:20%'>"+data[i]["group_name"]+"</td>";     
              var td4="<td style='width:20%'><input type='checkbox' name='chkdelete"+i+"' id='chkdelete"+i+"'></td>";
              var kduser = "<input type='hidden' name='hdnkduser"+i+"' id='hdnkduser"+i+"' value='"+data[i]["user_id"]+"'>";
              $("#tableuser tbody").append(tr+td1+td2+td3+td4+kduser);
          } 
          $("#hdnJmlhUser").val(data.length);
          $(".loading").hide();
      },'json',"Error get data from ajax");
    } 
    function LoadFormAddUserKeRole(kdrole){ 
      ListUserAdd = ""; 
      idxuser = 0;
      document.getElementById('txtKodeAdduser2').value = kdrole;
      document.getElementById('txtroleidhidden2').value = kdrole;  
      idx = 0; 
      $("#tbodyUser").empty();
    } 
    $('#confirm-delete').on('show.bs.modal', function(e) {
        $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
        
        $('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
    });
    $('#confirm-delete').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.title', this).text(data.recordTitle);
    }); 
</script>

  <div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
    <div style="padding: 5px;">
    <?php
        if (isset($_GET['insertsuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Inserted <strong>Successfully !</strong></div>";
        }
        if (isset($_GET['duplicatesuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Duplicated <strong>Successfully !</strong></div>";
        }
        if (isset($_GET['updatesuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Updated <strong>Successfully !</strong></div>";
        }

        if (isset($_GET['deletesuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Removed <strong>Successfully !</strong></div>";
        }
        if (isset($_GET['deleteusersuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Delete User <strong>Successfully !</strong></div>";
        }
        if (isset($_GET['addusersuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Add User <strong>Successfully !</strong></div>";
        }

    ?>

    <?php
        if (isset($_GET['inserterror'])) {
          echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Insert Data Error ! ".$_GET['inserterror']."</strong></div>";
        }
        if (isset($_GET['duplicateerror'])) {
          echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Duplicate Data Error ! ".$_GET['duplicateerror']."</strong></div>";
        }
        if (isset($_GET['updateerror'])) {
          echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Update Data Error ! ".$_GET['updateerror']."</strong></div>";
        }

        if (isset($_GET['deleteerror'])) {
          echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Delete Data Error !</strong></div>";
        }
        if (isset($_GET['deleteusererror'])) {
          echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Delete User Data Error !</strong></div>";
        }
        if (isset($_GET['addusererror'])) {
          echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Add User Data Error !</strong></div>";
        }
    ?>
    </div>
  </div>


  <div class="container">
    <?php if($_SESSION["can_create"] == 1){ ?>
    <div style="font-size:15pt;"><a href="#" data-toggle='modal' data-target='#insert_new'
      onclick="loadaddnewrole()" >Insert New Role</a></div> 
    <?php } ?>
    <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>"; 
        echo "<th>Kode Role</th>";
        echo "<th>Nama Role</th>"; 
        echo "<th></th>";
        echo "<th></th>";
        echo "<th></th>";
        if($_SESSION["can_create"] == 1)
          echo "<th></th>";
        if($_SESSION["can_update"] == 1)
          echo "<th></th>";
        if($_SESSION["can_delete"] == 1)
          echo "<th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $i = 1;
        foreach($result as $r) {
           echo "<tr>"; 
           echo "<td>".$i."</td>";
           echo "<td>".$r->role_id."</td>";
           echo "<td>".$r->role_name."</td>"; 
           ?>

           <?php 
           if ($user_role == "ROLE01")
           {
              ?>
                <td>
                    <a href = '#' data-toggle='modal' data-target='#add_user_role' onclick="LoadFormAddUserKeRole('<?php echo $r->role_id ?>')"><i class='glyphicon glyphicon-plus' aria-hidden='true'>
                </td> 
              <?php
           }
           else
           {
            if ($r->role_id == "ROLE01")
            {

              ?>
                <td>  
                </td> 
              <?php
            }
            else
            {
              ?>
                <td>
                    <a  readonly href = '#' data-toggle='modal' data-target='#add_user_role' onclick="LoadFormAddUserKeRole('<?php echo $r->role_id ?>')"><i class='glyphicon glyphicon-plus' aria-hidden='true'>
                </td> 
              <?php
            } 
           }
           ?> 
           <td>
              <a href = '#'  data-href='Role/updateuserbyrole?id=".$r->role_id."' data-toggle='modal' data-target='#view_user_role' onclick="loaduserrole('<?php echo $r->role_id ?>','<?php echo $r->role_name ?>')"><i class='glyphicon glyphicon-user' aria-hidden='true'>
           </td> 

           <td>
              <a href = '#' data-href='Role/updateModule?id=".$r->role_id."' data-toggle='modal' data-target='#module_modal' onclick="loadModuleData('<?php echo $r->role_id ?>','<?php echo $r->role_name ?>')"><i class='glyphicon glyphicon-th-list' aria-hidden='true'>
           </td>

           <?php 
           if($_SESSION["can_create"] == 1) { ?>
           <td>
              <a href = '#'  data-toggle='modal' data-target='#duplicate_role' onclick="loadduplicaterole('<?php echo $r->role_id ?>','<?php echo $r->role_name ?>',
                '<?php echo $r->mainbranch_role ?>')"><i class='glyphicon glyphicon-duplicate' aria-hidden='true'>
           </td>
           <?php } ?>

           <?php if($_SESSION["can_update"] == 1) { ?>
           <td>
              <a href = '#' data-href='Role/update?id=".$r->role_id."' data-toggle='modal' 
                data-target='#update_modal' onclick="loadMapData('<?php echo $r->role_id ?>',
                '<?php echo $r->role_name ?>','<?php echo $r->is_hrd ?>','<?php echo $r->mainbranch_role ?>',
                '<?php echo $r->role_importance ?>')"><i class='glyphicon glyphicon-pencil' aria-hidden='true'>
           </td>
           <?php } ?>
           <?php
           if($_SESSION["can_delete"] == 1)
              echo "<td><a href = '#' data-href='Role/delete?id=".$r->role_id."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r->role_id."'><i class='glyphicon glyphicon-trash' aria-hidden='true'></a></td>"; 
           echo "</tr>";
           $i += 1;
        }
        echo "</tbody>"; ?>
    </table>
  </div> 
  <!-- /container -->

  <!-- model delete confirm -->
  <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Delete Confirmation
            </div>
            <div class="modal-body">
              <p>You are about to delete <b><i class="title"></i></b> record, this procedure is irreversible.</p>
              <p>Do you want to proceed?</p>
              <!-- <p class="debug-url"></p> -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
  </div> 
  <!--  -->

  <!-- model insert new -->
  <div class="modal fade" id="insert_new" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Insert New Role
            </div>
            <div class="modal-body">
            <?php echo form_open('Role/insert'); ?>
                <div class="form-group">
                  <label>Kode Role</label>
                  <input type="text" class="form-control" name="txtKodeRole" id="txtKodeRole" placeholder="" maxlength="6" required readonly>
                </div>
                <div class="form-group">
                  <label>Nama Role</label>
                  <input type="text" class="form-control" name="txtNamaRole" id="txtNamaRole" placeholder="" required>
                  <!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
                </div>

                <!-- <div class="form-group">
                  <label>Role Default</label>&nbsp;&nbsp;&nbsp;
                  <input type="checkbox" class="" name="chkDefault" id="chkDefault" value="1">
                </div> -->

                <!-- <div class="form-group">
                  <label>HRD</label>&nbsp;&nbsp;&nbsp;
                  <input type="checkbox" class="" name="chkHRD" id="chkHRD" value="1">
                </div> -->

                <!-- <div class="form-group">
                  <label>Report Cuti Notification</label>&nbsp;&nbsp;&nbsp;
                  <input type="checkbox" class="" name="chkNotif" id="chkNotif" value="1">
                </div> -->

                <div class="form-group">
                  <label>Main Branch Only</label>&nbsp;&nbsp;&nbsp;
                  <input type="checkbox" class="" name="chkMainBranch" id="chMainBranch" value="1">
                </div>

                <!--  <div class="form-group">
                  <label>Role Importance</label>
                  <select class="form-control" name="selImpor" id="selImpor">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                  </select>
                </div> -->

                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
            <?php echo form_close(); ?>
            </div>
        </div>
    </div>
  </div>
  <!--  -->
 
  <!-- model duplicate role -->
  <div class="modal fade" id="duplicate_role" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">  
                   Duplicate Role 
              </div>
              <div class="modal-body">
              <?php echo form_open('Role/duplicaterole'); ?>
                  <div class="form-group">
                    <label>Kode Role</label>
                    <input type="text" class="form-control" name="txtDupKodeRole" id="txtDupKodeRole" placeholder="" maxlength="6" required readonly>
                  </div>
                  <div class="form-group">
                    <label>Nama Role</label>
                    <input type="text" class="form-control" name="txtDupNamaRole" id="txtDupNamaRole" placeholder="" required> 
                  </div> 
                  <div class="form-group">
                    <label>Main Branch Only</label>&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" class="" name="chkDupMainBranch" id="chDupMainBranch" value="1">
                  </div>  
                  <input type="hidden" class="form-control" name="txtroleidhidden" id="txtroleidhidden"> 
                  <input type="submit" class="btn btn-primary" value="Submit">
                  <input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
              <?php echo form_close(); ?>
              </div>
          </div>
      </div>
    </div>
  <!--  -->
 
  <!-- model update data -->
  
  <div class="modal fade" id="update_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Update Data Role
            </div>
            <div class="modal-body">
            <?php echo form_open('Role/update'); ?>
                <div class="form-group">
                  <label>Kode Role</label>
                  <input type="text" class="form-control" name="utxtKodeRole" id="utxtKodeRole" placeholder="" required readonly>
                </div>
                <div class="form-group">
                  <label>Nama Role</label>
                  <input type="text" class="form-control" name="utxtNamaRole" id="utxtNamaRole" placeholder="" required> 
                </div> 

                <div class="form-group">
                  <label>Main Branch Only</label>&nbsp;&nbsp;&nbsp;
                  <input type="checkbox" class="" name="uchkMainBranch" id="uchkMainBranch" value="1">
                </div> 
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
            <?php echo form_close(); ?>
            </div>
        </div>
    </div>
  </div> 
  <!--  -->
 
  <!-- model view user role -->
  <div class="modal fade " id="add_user_role" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                Add User To Role  
            </div>
            <div class="modal-body">

            <div class="row" >
            <div class="col-2"> <label>Kode Role</label></div>
            <div class="col-8"> <input style="width: 100px;" type="text"name="txtKodeAdduser2" id="txtKodeAdduser2" readonly> </div>
            </div>

            <div class="row" >
            <div class="col-2"> <label>PILIH USER</label></div>
            <div class="col-8"> 
              <select class="dropdownuser" id="dropdownusers"style="width: 400px;"> </select>
              <button onclick="addRowUser()" name="btnadduser" style="width: 50px;"><i class="glyphicon glyphicon-plus"></i></button> 
            </div> 
            </div>

            <div class="row" style="margin:0px">
            <div class="col-12">               
              <table id="table-primary" class="table table-striped table-bordered" cellspacing="0">
                    <thead id="theadUser">
                      <tr>
                        <th id="table-primary-no" width="5%" ></th> 
                        <th id="table-primary-user-name" width="95%" >User Name</th>  
                      </tr>
                    </thead>
                    <tbody id="tbodyUser">
                    </tbody>
                  </table>
                   </div> 
            </div> 
                <br>
            <?php echo form_open('Role/adduser'); ?>
                <div class="form-group"> 
                <input type="hidden" class="form-control" name="txtroleidhidden2" id="txtroleidhidden2"> 
                <input type="hidden" class="form-control" name="listuserid" id="listuserid"> 
                <?php if($_SESSION["can_update"] == 1) { ?>
                <input type="submit" class="btn btn-primary" value="Add">
                <?php } ?>
                <input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
              </div>
            <?php echo form_close(); ?>
            </div>
        </div>
    </div>
  </div>
  <!--  -->

  <!-- model view all user -->
  <div class="modal fade" id="view_user_role" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open('Role/updateuserbyrole'); ?>
            <div class="modal-header">
              <label>List User</label> 
            </div>
            <div class="modal-body">
               <table style="width:100%">
                <tr>
                  <th id="view_user_role-kode-role" style="width:15%">Kode Role</th>
                  <th id="view_user_role-nama-role" style="width:50%">Nama Role</th> 
                  <th id="view_user_role-kode-" style="width:30%"></th>
                </tr>
                <tr>
                  <td ><input type="text" size="5" name="txtKodeUserByRole" id="txtKodeUserByRole" placeholder="" required readonly></td> 
                  <td ><input type="text" size="30"  name="txtKodeUserByRoleName" id="txtKodeUserByRoleName" placeholder="" required readonly></td> 
                  <td> 
                    <input type="hidden" name="hdnJmlhUser" id="hdnJmlhUser">
                    <?php if($_SESSION["can_update"] == 1) { ?>
                    <input type="submit" class="btn btn-primary" value="Delete">
                    <?php } ?>
                    <input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
                  </td> 
                </tr>
              </table> 
              <br> 
              <table class="table">
                  <thead>
                    <tr> 
                      <td style="width:20%"><b>User Id</b></td>
                      <td style="width:40%"><b>User Name</b></td>
                      <td style="width:20%"><b>Group Kerja</b></td>
                      <td style="width:20%"><b>Delete</b></td> 
                    </tr>
                  </thead>  
                </table> 
              <div style="overflow-y: scroll; height:450px;"> 
                <table id="tableuser" class="table table-stripped table-bordered"> 
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
  </div>
  <!--  -->

<!-- model detail module -->
  <div class="modal fade" id="module_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open('Role/updateModule'); ?>
            <div class="modal-header"> 
              <label>Role to Module Access</label>  
            </div> 
            <div class="modal-body">  
              <table style="width:100%">
                <tr>
                  <th id="module_modal-kode-role" style="width:15%">Kode Role</th>
                  <th id="module_modal-nama-role" style="width:50%">Nama Role</th> 
                  <th id="module_modal-" style="width:30%"></th>
                </tr>
                <tr>
                  <td ><input type="text" size="5" name="mtxtKodeRole" id="mtxtKodeRole" placeholder="" required readonly></td> 
                  <td ><input type="text" size="30" name="mtxtKodeRoleName" id="mtxtKodeRoleName" placeholder="" required readonly></td> 
                  <td> 
                      <input type="hidden" name="hdnJmlhModule" id="hdnJmlhModule">
                      <?php if($_SESSION["can_update"] == 1) { ?>
                      <input type="submit" class="btn btn-primary" value="Update">
                      <?php } ?>
                      <input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
                  </td> 
                </tr>
              </table>  
              <br>
              <table class="table">
                  <thead>
                    <tr>
                      <td style="width:10%"><b>ID</b></td>
                      <td style="width:30%"><b>Module Name</b></td>
                      <td style="width:10%"><b>ALL</b></td>
                      <td style="width:10%"><b>Read</b></td>
                      <td style="width:10%"><b>Insert</b></td>
                      <td style="width:10%"><b>Update</b></td>
                      <td style="width:10%"><b>Delete</b></td>
                      <td style="width:10%"><b>Print</b></td>
                    </tr>
                  </thead>  
                </table> 
              <div style="overflow-y: scroll; height:450px;">
                <table id="tableModule" class="table table-stripped table-bordered"> 
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div> 
            <?php echo form_close(); ?>
        </div>
    </div>
  </div>
  <!--  -->
 
