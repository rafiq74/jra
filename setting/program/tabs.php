<?php

$tabs = array();	

$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('program', $url, get_string('academic_program','local_sis'));
if(isset($page) && $page == 'view_plan')
{
	$url = new moodle_url('plan.php', array());
	$tabs[] = new tabobject('plan', $url, get_string('academic_plan','local_sis'));
}
$url = new moodle_url('academic_career.php', array());
$tabs[] = new tabobject('academic_career', $url, get_string('academic_career','local_sis'));

if(sis_is_system_admin())
{
	$url = new moodle_url('program_status.php', array());
	$tabs[] = new tabobject('program_status', $url, get_string('program_status','local_sis'));
}

if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');