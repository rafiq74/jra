<?php

$tabs = array();	

$role_page = jra_get_session('jra_user_account_role_page');
if($role_page == '')
	$role_page = 'role';
$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('user', $url, get_string('users','local_jra'));
$url = new moodle_url($role_page . '.php', array());
$tabs[] = new tabobject('role', $url, get_string('roles','local_jra'));
/*
$url = new moodle_url('academic_career.php', array());
$tabs[] = new tabobject('academic_career', $url, get_string('academic_career','local_jra'));
*/

if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');