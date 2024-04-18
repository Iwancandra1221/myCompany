<?php
$config = array();
$UID = $this->session->userdata("db_user");
$PWD = $this->session->userdata("db_pwd");

//include_once('/../includes/connection.php');

$config['hostname'] = "localhost";
$config['database'] = "hrd";
$config['username'] = $UID;
$config['password'] = $PWD;
$config['dbdriver'] = 'sqlsrv';
$config['dbprefix'] = '';
$config['pconnect'] = FALSE;
$config['db_debug'] = FALSE;
$config['cache_on'] = FALSE;
$config['cachedir'] = '';
$config['char_set'] = 'utf8';
$config['dbcollat'] = 'utf8_general_ci';
$config['swap_pre'] = '';
$config['autoinit'] = TRUE;
$config['stricton'] = FALSE;
?>