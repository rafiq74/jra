<?php

$tabs = array();	
$tab_access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
if(jra_access_control($tab_access_rules, false))
{
	$url = new moodle_url('index.php', array());
	$tabs[] = new tabobject('general', $url, get_string('general','local_jra'));
	$url = new moodle_url('selfservice.php', array());
	$tabs[] = new tabobject('selfservice', $url, get_string('self_services','local_jra'));
	if(jra_is_system_admin())
	{
		$url = new moodle_url('system.php', array());
		$tabs[] = new tabobject('system', $url, get_string('system','local_jra'));
	}
}
if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');