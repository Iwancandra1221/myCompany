<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phpmailerlib
{
    public function __construct(){
        log_message('Debug', 'PHPMailer class is loaded.');
    }

    public function load(){
        
        include APPPATH.'third_party/phpmailer/class.phpmailer.php';
        include APPPATH.'third_party/phpmailer/class.smtp.php';
        
        $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->SMTPSecure = 'ssl'; 
        $mail->Host = "smtp.googlemail.com"; //host masing2 provider email
        $mail->SMTPDebug = 0;
        $mail->Port = 465;
        $mail->SMTPAuth = true;
        $mail->Username = "bitnotif@gmail.com"; //user email
        $mail->Password = "Sprite12345"; //password email 
        $mail->SetFrom("noreply@bitnotif.net","Auto Email Bhakti"); //set email pengirim
        
        return $mail;
    }
}