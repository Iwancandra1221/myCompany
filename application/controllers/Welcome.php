<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Welcome extends MY_Controller {
 
    public function index()
    {
    	require_once __DIR__ . '\vendor\autoload.php';
        $mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 0,
					'default_font' => '',
					'margin_left' => 15,
					'margin_right' => 15,
					'margin_top' => 16,
					'margin_bottom' => 16,
					'margin_header' => 9,
					'margin_footer' => 9,
					'orientation' => 'P'
				));
        $html = $this->load->view('html_to_pdf',[],true);
        $mpdf->WriteHTML($html);
        $mpdf->Output(); 
    }
}