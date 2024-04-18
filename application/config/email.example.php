<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['useragent'] 	= "CodeIgniter";
$config['mailpath']		= "/usr/sbin/sendmail";	// Sendmail path
$config['protocol']		= "smtp";	// mail/sendmail/smtp
$config['smtp_host']	= "ssl://smtp.googlemail.com";		// SMTP Server.  Example: mail.earthlink.net
$config['smtp_user']	= "bithrd.noreply@gmail.com";		// SMTP Username
$config['smtp_pass']	= "";		// SMTP Password
$config['smtp_port']	= "465"; 	// SMTP Port
$config['smtp_timeout']	= 60;		// SMTP Timeout in seconds
$config['wordwrap']		= TRUE;		// TRUE/FALSE  Turns word-wrap on/off
$config['wrapchars']	= "76";		// Number of characters to wrap at.
$config['mailtype']		= "html";	// text/html  Defines email formatting
$config['charset']		= "utf-8";	// Default char set: iso-8859-1 or us-ascii
$config['validate']		= FALSE;	// TRUE/FALSE.  Enables email validation
$config['priority']		= "3";		// Default priority (1 - 5)
$config['newline']		= "\r\n";		// Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)
$config['crlf']			= "\r\n";		// The RFC 2045 compliant CRLF for quoted-printable is "\r\n".  Apparently some servers,
									// even on the receiving end think they need to muck with CRLFs, so using "\n", while
									// distasteful, is the only thing that seems to work for all environments.

/* End of file Email.php */
/* Location: ./application/config/email.php */