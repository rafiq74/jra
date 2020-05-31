<?php

$tabs = array();	

$role_page = sis_get_session('sis_user_account_role_page');
if($role_page == '')
	$role_page = 'role';
$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('user', $url, get_string('users','local_sis'));
$url = new moodle_url('login_list.php', array());
$tabs[] = new tabobject('login', $url, get_string('login_accounts','local_sis'));
$url = new moodle_url($role_page . '.php', array());
$tabs[] = new tabobject('role', $url, get_string('roles','local_sis'));
/*
$url = new moodle_url('academic_career.php', array());
$tabs[] = new tabobject('academic_career', $url, get_string('academic_career','local_sis'));
*/

if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');