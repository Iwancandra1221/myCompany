<?php
defined('BASEPATH') OR exit('No direct script access allowed');

	// require 'lib/phpqrcode/qrlib.php';
	
class QR extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		
		// // $this->load->library('ciqrcode'); //pemanggilan library QR CODE
 
        // // $config['cacheable']    = true; //boolean, the default is true
        // // $config['cachedir']     = 'assets/'; //string, the default is application/cache/
        // // $config['errorlog']     = 'assets/'; //string, the default is application/logs/
        // // $config['imagedir']     = 'assets/images/'; //direktori penyimpanan qr code
        // // $config['quality']      = true; //boolean, the default is true
        // // $config['size']         = '1024'; //interger, the default is 1024
        // // $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        // // $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        // // $this->ciqrcode->initialize($config);
 
        // // $image_name='tes.png'; //buat name dari qr code sesuai dengan nim
 
        // // $params['data'] = 'google.com'; //data yang akan di jadikan QR CODE
        // // $params['level'] = 'H'; //H=High
        // // $params['size'] = 10;
        // // $params['savename'] = $config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        // // $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
		
		
		 $file_QR = 'assets/QRCODE.png';
		$this->load->library('ciqrcode');
		// header("Content-Type: image/png");
		$params['data'] = 'This is a text to encode become QR Code';
        $params['level'] = 'H'; //H=High
        $params['size'] = 10;
        $params['savename'] = $file_QR; //simpan image QR CODE ke folder assets/images/
		$this->ciqrcode->generate($params);
		
		return $file_QR;
		
	

		 // $this->load->library('ciqrcode');
		 // header("Content-Type: image/png");
		 // $qr['data'] = 'http://h4nk.blogspot.com';
		 // $this->ciqrcode->generate($qr);
		 
		 
			



	}  


}