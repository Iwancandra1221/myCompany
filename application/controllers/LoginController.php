<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class LoginController extends CI_Controller {
 
 function __construct()
 {
   parent::__construct();
   $this->load->model('UserModel');
 }

 function index()
 {
    //600 seconds = 10 menit
    $streamContext = stream_context_create(array('http'=>array('timeout' => 60)));
  
    $username = $this->input->post('username');
    $password = $this->input->post('password');

    $ports = explode(":", $_SERVER['HTTP_HOST']);
    $port = "80";
    if (count($ports)>1) {
      $port = $ports[1];
    }

    if (strtoupper(trim($username))=="USER@PABRIK.KG" || strtoupper(trim($username))=="QR@PABRIK.KG" || 
        strtoupper(trim($username))=="USER@PABRIK.PTRI" || strtoupper(trim($username))=="QR@PABRIK.PTRI" || 
        strtoupper(trim($username))=="USER@PABRIK.TIN" || strtoupper(trim($username))=="QR@PABRIK.TIN") {

        if (strtoupper(BUGSNAG_RELEASE_STAGE)=="PRODUCTION") {
          if ($port!="81" && $port!="84" && $port!="85" && $port!="86") {
            $this->session->set_flashdata('error', 'LOGIN BHAKTI KE http://www.bhakti.co.id:81/ atau URL BACKUP');
            redirect('MainController');
          } 
        }

        $array = explode(".", $username);
        $GroupId = "PABRIK";
        if (count($array)>0) {
          $last = count($array)-1;
          $GroupId = $array[$last];
        }

        $result = $this->UserModel->login($username, $password);
        if($result["result"]=="success") { 
          // 30 Des 2022 : di LoginControllers tidak perlu define terlalu banyak variable session
          // variable2 session didefine di controller Home
          $_SESSION["user_pabrik"] = true;
          $_SESSION["logged_in"] = array();

          $goto1 = site_url("Home/callHome/".urlencode($username)."/".urlencode("0"));
          redirect($goto1);
        } else {
          $this->session->set_flashdata('error','Wrong email and password combination.');
          redirect('MainController');
        } 
    } 

    //query the database
    $result = $this->UserModel->login($username, $password);
    if($result["result"]=="success") {

      $sess_array = array();
      $employee = $result["data"];
      $userid = $employee->UserID;

      $SalesmanID = "";

      if ($employee->needSync==1 && $userid>0) {
        //die($userid);

        //Jika userid / $employee->AlternateID==0, maka jangan Sync ke ZEN
        $zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($userid);
        // $GetEmployee = json_decode(file_get_contents($zen, false, $streamContext));
		
		    $curl = curl_init();
    		curl_setopt_array($curl, array(
    			CURLOPT_URL => $zen,
    			CURLOPT_RETURNTRANSFER => true,
    			CURLOPT_TIMEOUT => 120, //detik
    		));

    		$result = curl_exec($curl);
    		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    		$err = curl_error($curl);
    		curl_close($curl);
		
    		if($httpcode==200){
    			$GetEmployee = json_decode($result);
    			// die(json_encode($GetEmployee));
    			if ($GetEmployee->result=="sukses") {
    				$Employee = $GetEmployee->data;
    				//die("perlu update user : ".json_encode($Employee));
    				$this->UserModel->UpdateUser($Employee, $userid);
    				// die('selesai');
    			}
            }
    		else{
    			// die('zen offline');
    		}
      }            

      if ($userid>0) {
        $employee = $this->UserModel->Get($userid);
        if (strtoupper($employee->Email)!=strtoupper($username) && strtoupper($employee->USERID)!=strtoupper($username)) {
          $this->session->set_flashdata('info','Email Login Anda diubah ke '.$employee->Email);
        }
      } else {
        $employee = $this->UserModel->getUserDataByEmail($username);
      }

      if (strtoupper(BUGSNAG_RELEASE_STAGE)=="PRODUCTION") {
        if ($employee->BranchID != "JKT") {
          if ($port!="83" && $port!="84" && $port!="85" && $port!="86") {
            $this->session->set_flashdata('error', 'LOGIN BHAKTI KE http://www.bhakti.co.id:83/ atau URL BACKUP');
            redirect('MainController');
          } 
        } else if (substr($employee->DivisionID,0,3)=="2.2") {
          if ($port!="82" && $port!="84" && $port!="85" && $port!="86") {
            $this->session->set_flashdata('error', 'LOGIN BHAKTI KE http://www.bhakti.co.id:82/ atau URL BACKUP');
            redirect('MainController');
          } 
        } else if ($port == "81") {
            $this->session->set_flashdata('error', 'LOGIN KE http://www.bhakti.co.id:81/ HANYA UNTUK USER PABRIK');
            redirect('MainController');
        }
      }
      $username = $employee->UserName;
      $useremail = $employee->UserEmail;
      $_SESSION['logged_in'] = array();
      $_SESSION["user_pabrik"] = false;
      //die(json_encode($_SESSION));

      if (WEBTITLE=="REPORT BHAKTI") {
        $goto1 = site_url()."Home/callHome/".urlencode($username)."/".urlencode($userid);
        // die($goto1);
        redirect($goto1);
      } else {
        $goto1 = site_url()."HomeController/setSession/".urlencode($useremail)."/".urlencode($userid)."/".rawurlencode($username);
        // die($goto1);
        redirect($goto1);
      }

    } else if ($result["result"]=="authentication failed") {
      // die("Wrong Username and Password");
      $this->session->set_flashdata('error','Wrong email and password combination.');
      redirect('MainController');
    } else {
      // die("Check Login ke ZenHRS");
      $this->authZEN2($username, $password);
    }
  }

 function authZEN2($username, $password)
 {
    if ((strtoupper(trim($username))=="USER@PABRIK.KG" || 
        strtoupper(trim($username))=="USER@PABRIK.PTRI" ||
        strtoupper(trim($username))=="USER@PABRIK.TIN") &&
        trim($password)=="12345") {

        $array = explode(".", $username);
        $GroupId = "PABRIK";
        if (count($array)>0) {
          $last = count($array)-1;
          $GroupId = $array[$last];
        }
      
        $sess_array = array(
         'username'   => $username,
         'useremail'  => $username,
         'userid'     => 0,
         'email'      => $username,
         'whatsapp'   => "",
         'branch_id'  => "JKT",
         'loginPayroll' => 0,
         'isSalesman' => 0,
         'salesmanid' => "",
         'userPosition' => "",
         'userDivision' => "",
         'userLevel'  => "PABRIK",
         'userGroupId'=> $GroupId,
         'userGroup'  => ""
        );

        $arrayrole = array();
        array_push($arrayrole, "ROLE11"); //ROLE11 : PABRIK

        $this->session->set_userdata('logged_in', $sess_array);
        $this->session->set_userdata('user_role', $arrayrole);

        $_SESSION['logged_in'] = $sess_array;
        $_SESSION['user_role'] = $arrayrole;
        $_SESSION["user_pabrik"] = true;
        //redirect('HomeController');
        $goto1 = "http://".$_SERVER['HTTP_HOST']."/Home/callHome/".urlencode($_SESSION['logged_in']['useremail'])."/".urlencode($_SESSION["logged_in"]["userid"]);
        redirect($goto1);

    } else {
		
      $zen = API_ZEN."/ZenAPI/CheckLogin?user=".urlencode($username)."&pwd=".urlencode(md5($password));
      // $res = json_decode(file_get_contents($zen, true));

  		$curl = curl_init();
  		curl_setopt_array($curl, array(
  			CURLOPT_URL => $zen,
  			CURLOPT_RETURNTRANSFER => true,
  			CURLOPT_TIMEOUT => 120, //detik
  		));

  		$result = curl_exec($curl);
  		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  		$err = curl_error($curl);
  		curl_close($curl);
		
		  // echo $result;die;
		
		if($httpcode==200){
			$res = json_decode($result);

		  if ($res->result == "SUKSES")
		  {

			$SalesmanID = "";
			$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($res->userid);
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $zen,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 120, //detik
			));

			$result = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			
			// echo $result;die;
			
			if($httpcode==200){
				$GetEmployee = json_decode($result, true);
				if ($GetEmployee["result"]=="sukses") {
					$Employee = $GetEmployee["data"];
					$this->UserModel->AddUser($Employee, $password, $username);
					$newUser = $this->UserModel->Get($username);

					$SalesmanID = $Employee["SALESMANID"];
					$EmployeeID = $Employee["USERID"];

				$sess_array = array(
				 'username'   => $res->nama,
				 'useremail'  => $username,
				 'userid'     => $EmployeeID,
				 'email'      => $Employee['EMAIL'],
				 'whatsapp'   => "",
				 'branch_id'  => $res->cabang,
				 'loginPayroll' => 0,
				 'isSalesman' => (($SalesmanID!="" && $SalesmanID!=null)? 1:0),
				 'userPosition' => $Employee['EMPPOSITIONNAME'],
				 'userDivision' => $Employee['DIVISIONNAME'],
				 'userLevel'  => $Employee['EMPLEVEL'],
				 'userGroupId'=> $Employee['GROUPID'],
				 'userGroup'  => $Employee['GROUPNAME']
				);
				//die(json_encode($sess_array));

				$role = $this->UserModel->getRoleUser($username);

				$arrayrole = array();
				for($i=0;$i<count($role);$i++){
				  array_push($arrayrole, $role[$i]->role_id);
				}
				$this->session->set_userdata('logged_in', $sess_array);
				$this->session->set_userdata('user_role', $arrayrole);

				$_SESSION['logged_in'] = $sess_array;
				$_SESSION['user_role'] = $arrayrole;
				$_SESSION["user_pabrik"] = false;

				redirect('HomeController');

				}           
			  else {
				$this->session->set_flashdata('error',$GetEmployee["error"]);
				redirect('MainController');
			  } 
			}
			else {
				$this->session->set_flashdata('error','ZEN sedang OFFLINE/ERROR, silahkan coba kembali nanti.');
				redirect('MainController');
			  }
		  }
		  else{
				$this->session->set_flashdata('error', $res->error);
				redirect('MainController');
		  }
      }
	  else {
        $this->session->set_flashdata('error','ZEN sedang OFFLINE/ERROR, silahkan coba kembali nanti.');
        redirect('MainController');
      }
    }
 } 

 function authZEN()
 {
    $username = $this->input->post('username');
    $password = $this->input->post('password');

    if ((strtoupper(trim($username))=="USER@PABRIK.KG" || 
        strtoupper(trim($username))=="USER@PABRIK.PTRI" ||
        strtoupper(trim($username))=="USER@PABRIK.TIN") &&
        trim($password)=="12345") {

        $array = explode(".", $username);
        $GroupId = "PABRIK";
        if (count($array)>0) {
          $last = count($array)-1;
          $GroupId = $array[$last];
        }
      
        $sess_array = array(
         'username'   => $username,
         'useremail'  => $username,
         'userid'     => 0,
         'email'      => $username,
         'whatsapp'   => '',
         'branch_id'  => "JKT",
         'loginPayroll' => 0,
         'isSalesman' => 0,
         'salesmanid' => "",
         'userPosition' => "",
         'userDivision' => "",
         'userLevel'  => "PABRIK",
         'userGroupId'=> $GroupId,
         'userGroup'  => ""
        );

        $arrayrole = array();
        array_push($arrayrole, "ROLE11"); //ROLE11 : PABRIK

        $this->session->set_userdata('logged_in', $sess_array);
        $this->session->set_userdata('user_role', $arrayrole);

        $_SESSION['logged_in'] = $sess_array;
        $_SESSION['user_role'] = $arrayrole;
        redirect('HomeController');

    } else {
      $zen = API_ZEN."/ZenAPI/CheckLogin?user=".urlencode($username)."&pwd=".urlencode(md5($password));
      $res = json_decode(file_get_contents($zen, true));
      /*$res = json_decode(file_get_contents($url."/Helper/HelpLoginZen?user=".urlencode($userid).
               "&pwd=".urlencode($encrypt_pwd)."", true));*/

      if ($res->result == "SUKSES")
      {
        $GetUser = $this->UserModel->GetLocalUserID($res->userid);
        if ($GetUser!="") {
          $username = $GetUser;
        }
        $employee = $res->data;

        $SalesmanID = "";
        $zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($res->userid);
        $GetEmployee = json_decode(file_get_contents($zen), true);
        if ($GetEmployee["result"]=="sukses") {
            $Employee = $GetEmployee["data"];
            $SalesmanID = $Employee["SALESMANID"];
            $EmployeeID = $Employee["USERID"];
        }            


        $sess_array = array(
         'username'   => $res->nama,
         'useremail'  => $username,
         'userid'     => $EmployeeID,
         'email'      => $Employee->EMAIL,
         'whatsapp'   => '',
         'branch_id'  => $res->cabang,
         'loginPayroll' => 0,
         'isSalesman' => (($SalesmanID!="" && $SalesmanID!=null)? 1:0),
         'userPosition' => $Employee->EMPPOSITIONNAME,
         'userDivision' => $Employee->DIVISIONNAME,
         'userLevel'  => $Employee->EMPLEVEL,
         'userGroupId'=> $Employee->GROUPID,
         'userGroup' => $Employee->GROUPNAME
        );
        //die(json_encode($sess_array));

        $role = $this->UserModel->getRoleUser($username);

        $arrayrole = array();
        for($i=0;$i<count($role);$i++){
          array_push($arrayrole, $role[$i]->role_id);
        }
        $this->session->set_userdata('logged_in', $sess_array);
        $this->session->set_userdata('user_role', $arrayrole);

        $_SESSION['logged_in'] = $sess_array;
        $_SESSION['user_role'] = $arrayrole;

        redirect('HomeController');
      } else {
        $this->session->set_flashdata('error',$res->error);
        redirect('MainController');
      }
    }
 } 
}
?>