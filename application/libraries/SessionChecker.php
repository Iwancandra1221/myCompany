<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SessionChecker
{
    public function __construct(){
        log_message('Debug', 'PHPMailer class is loaded.');
    }

    public function checkSession($uri){
        $ctrname = "";

        if($uri->segment(2) != '')
            $ctrname = $uri->segment(1)."/".$uri->segment(2);
        else
            $ctrname = $uri->segment(1);
        
        if ($ctrname!="") {
            if (array_key_exists("logged_in", $_SESSION)) {
                //die("EXISTS");
            } else {
                if(session_status() === PHP_SESSION_ACTIVE) {
                    session_unset();
                    session_destroy();
                }
                redirect('MainController','refresh');
            }
        }        
    }
}