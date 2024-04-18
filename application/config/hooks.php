<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/
require FCPATH.'application/controllers/vendor/autoload.php';
$GLOBALS['bugsnag'] = Bugsnag\Client::make("d61734763a917c1a52ff2ecd73b17cbb");
$GLOBALS['bugsnag']->setReleaseStage(BUGSNAG_RELEASE_STAGE);
/*
$GLOBALS['bugsnag']->registerCallback(function ($report) {
    $report->setMetaData([
        'account' => [
            'name' => 'Acme Co.',
            'paying_customer' => true,
        ]
    ]);
    $report->setUser([
        'id' => '123456',
        'name' => 'Leeroy Jenkins',
        'email' => 'leeeeroy@jenkins.com',
    ]);
});
*/
$hook['pre_system'] = array(
	'bugsnag' => Bugsnag\Handler::register($GLOBALS['bugsnag']),
);
/*
try{

}
catch(Exception $e){
    $GLOBALS['bugsnag']->notifyError('ErrorType', 'A wild error appeared!');
}
*/



/* End of file hooks.php */
/* Location: ./application/config/hooks.php */