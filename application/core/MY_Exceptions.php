<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Exceptions extends CI_Exceptions 
{
	public function __construct()
	{
		parent::__construct();
	}

    function show_php_error($severity, $message, $filepath, $line)
    {
        parent::show_php_error($severity, $message, $filepath, $line);
        die();
    }
}

/* End of file masterTemplate.php */
/* Location: ./application/controllers/masterTemplate.php */