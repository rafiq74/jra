<?php

$tabs = array();	

$role_page = jra_get_session('jra_user_account_role_page');
if($role_page == '')
	$role_page = 'role';
$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('complete', $url, jra_get_string(['completed','application']));
$url = new moodle_url('confirmed.php', array());
$tabs[] = new tabobject('confirmed', $url, jra_get_string(['confirmed','application']));
$url = new moodle_url('admitted.php', array());
$tabs[] = new tabobject('admitted', $url, jra_get_string(['admitted','application']));
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