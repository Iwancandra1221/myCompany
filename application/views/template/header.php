<style>
  .dropdown-submenu {
      position: relative;
  }

  .dropdown-submenu .dropdown-menu {
      top: 0;
      left: 100%;
      margin-top: -1px;
  }

  nav, ul, li {
    background-color: #303331!important;
    color: #ffffff!important;
  }
  nav a {
    color: white!important;
  }
  nav a:hover {
    color: #8cc8de!important;
  }
  .navbar-default .navbar-nav > li > a:focus, .navbar-default .navbar-nav > li > a:hover {
      background-color: #02124f!important;
  }
  .dropdown-menu > li > a:focus, .dropdown-menu > li > a:hover {
    /*color: #262626;*/
    /*text-decoration: none;*/
    background-color:  #02124f!important;
  }
</style>

<div style="position:absolute; top:100px; left:0; width: 100%; z-index: 999 !important;">
    <div style="padding:5px;">
      <?php
        if (isset($alert)) {
          if ($alert!="") {
            echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>".$alert."</strong></div>";
          }
        }
      ?>
    </div>
</div>
<?php
  // die(json_encode($_SESSION['module']));
?>
<?php if (isset($_SESSION["logged_in"])) {?>
<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-header">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
	
      <ul class="nav navbar-nav" style="padding:10px 0">
        <li>
          <a href="<?php echo base_url('Dashboard'); ?>" style="font-size:25px!important;">PT.BHAKTI IDOLA TAMA</a>
        </li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
          <li class='dropdown' style='text-align:right;'>
            <a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>
              <?php 
                echo(strtoupper($_SESSION['logged_in']["username"]));
                if (isset($_SESSION['logged_in']["userPosition"])){
                  if ($_SESSION['logged_in']["userPosition"]!="") echo(" | ".strtoupper($_SESSION['logged_in']["userPosition"]));
                  if ($_SESSION['logged_in']["userGroup"]!="") echo("<br>".strtoupper($_SESSION['logged_in']["userGroup"]));
                }
                if (isset($_SESSION['conn'])) {
                  echo (" | ".$_SESSION['conn']->Server." (".$_SESSION['conn']->Database.") ");
                }
              ?><span class='caret'></span>
            </a>
            <ul class='dropdown-menu'>
              <li class='dropdown-submenu'>
                <a href="<?php echo base_url('UserControllers/ChangeProfile'); ?>">Edit Profile</a>
              </li>
              <li class='dropdown-submenu'>
                <a href="<?php echo base_url('UserControllers/ChangePassword'); ?>">Ganti Password</a>
              </li>
              <?php 
                $isUserPabrik = 0;
                if (isset($_SESSION["logged_in"]["isUserPabrik"])==1) {
                  $isUserPabrik = $_SESSION["logged_in"]["isUserPabrik"];
                } 

                if ($isUserPabrik==0) { 
                  if ($_SESSION["logged_in"]["branch_id"]=="JKT" && $_SESSION["logged_in"]["city"]=="JAKARTA") {
                    if(isset($_SESSION['conn'])){?>
                      <li class='dropdown-submenu'>
                        <a href="<?php echo base_url('ConnectDB'); ?>">Ganti DB</a>
                      </li>
              <?php } else { ?>
                      <li class='dropdown-submenu'>
                        <a href="<?php echo base_url('ConnectDB'); ?>">Connect DB</a>
                      </li>
              <?php }
                  } else {
                    if(isset($_SESSION['conn'])){?>
                      <li class='dropdown-submenu' style="padding: 15px;">
                        <?php echo $_SESSION['conn']->Server; ?> (<?php echo $_SESSION['conn']->Database; ?>)
                      </li>
              <?php } else { ?>
                      <li class='dropdown-submenu'>
                        <a href="<?php echo base_url('ConnectDB'); ?>">Connect DB</a>
                      </li>
              <?php }
                  }
                }
              ?>
              <li class='dropdown-submenu'>
                <a href="<?php echo base_url()."home/logout"?>">Sign out</a>
              </li>
            </ul>
          </li>
      </ul>
      <?php if (isset($_SESSION["module"])) {?>
      <ul class="nav navbar-nav" style="padding:10px 0">
        <?php
          foreach($_SESSION['module'] as $r) {
            if($r->module_type == 'PARENT' && $r->can_read == 1 && $r->is_active==1 && 
              ((WEBTITLE=="REPORT BHAKTI" && substr($r->module_id,0,1)=="R") 
                || (WEBTITLE!="REPORT BHAKTI" && substr($r->module_id,0,1)!="R")
                || ($this->session->userdata('user_pabrik')==true)
                || (strtoupper(BUGSNAG_RELEASE_STAGE)=="DEVELOPMENT")
                || (WEBTITLE=="PT.BHAKTI IDOLA TAMA"))){
              if($r->controller == '' && $r->can_read == 1){
                echo "<li class='dropdown'><a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>".$r->module_name."<span class='caret'></span></a>";
                echo "<ul class='dropdown-menu'>";
                foreach($_SESSION['module'] as $c){
                  if($c->module_type == 'CHILD' && $c->parent_module_id == $r->module_id){
                    if($c->controller == '' && $c->can_read == 1 && $c->is_active==1){
                      echo "<li class='dropdown-submenu'><a class='test' tabindex='-1' href='#'>".$c->module_id.".".$c->module_name."&nbsp;<span class='glyphicon glyphicon-triangle-right' style='font-size:10px;'></span></a>";
                      echo "<ul class='dropdown-menu grandchildren'>";
                      foreach($_SESSION['module'] as $g){
                        if($g->module_type == 'GRANDCHILD' and $g->parent_module_id == $c->module_id){
                          if($g->controller == '' and $g->can_read == 1 && $g->is_active==1){

                            echo "<li class='dropdown-submenu'><a class='menugreat' tabindex='-2' href='#'>".$g->module_id.".".$g->module_name."&nbsp;<span class='glyphicon glyphicon-triangle-right' style='font-size:10px;'></span></a>";
                            echo "<ul class='dropdown-menu greatgrandchildren'>";
                            foreach($_SESSION['module'] as $f){
                              if($f->parent_module_id == $g->module_id and $f->can_read == 1 && $f->is_active==1){
                                echo "<li><a tabindex='-2' href='".base_url().$f->controller."'>".$f->module_id.".".$f->module_name."</a></li>";
                              }
                            }
                            echo "</ul>";
                            echo "</li>";
                          }
                          elseif($g->can_read == 1 && $g->is_active==1){
                            echo "<li><a tabindex='-1' href='".base_url().$g->controller."'>".$g->module_id.".".$g->module_name."</a></li>";
                          }
                        }
                      }
                      echo "</ul>";
                      echo "</li>";
                    }
                    elseif($c->can_read == 1 && $c->is_active == 1){
                      echo "<li><a href='".base_url().$c->controller."'>".$c->module_id.".".$c->module_name."</a></li>";
                    }
                  }
                }
                echo "</ul>";
                echo "</li>";
              }
              elseif ($r->can_read == 1 && $r->is_active==1){
                echo "<li><a href='".base_url().$r->controller."'>".$r->module_name."</a></li>";
              }  
            }
          }
        ?>
      </ul>
      <?php } ?>    
  	</div>
  </div>
</nav>
<?php } ?>
<!-- <div style="background-color:black;height:30px;color:white;font-size:20px;top:-10px!important;">
<a class="navbar-brand hideOnMobile" href="<?php //echo(site_url("Home"))?>">Hi, Welcome <?php //echo $_SESSION['logged_in']['username'];?></a><br/>
<a class="navbar-brand colMobile" style="height:35px!important;" href="<?php //echo(site_url("Home"))?>">HOME</a><br/>
</div> -->

<?php 
  // echo(json_encode($_SESSION["logged_in"]));
?>

<script>
  $('.dropdown-submenu a.test').on("click", function(e){
    $(".grandchildren").hide();
    $(this).next('ul').toggle();
    e.stopPropagation();
    e.preventDefault();
  });


  $('.dropdown-submenu a.menugreat').on("click", function(e){
    $(".greatgrandchildren").hide();
    $(this).next('ul').toggle();
    e.stopPropagation();
    e.preventDefault();
  });

  $(document).ready(function () {
      $("#flash-msg").delay(1200).fadeOut("slow");
  });  
</script>