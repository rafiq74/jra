<?php

$tabs = array();	
$access_rules = array();
$isAdmin = sis_access_control($access_rules);
if($isAdmin)
{
	$url = new moodle_url('index.php', array());
	$tabs[] = new tabobject('general', $url, get_string('general','local_sis'));
	$url = new moodle_url('view.php', array());
	$tabs[] = new tabobject('view', $url, get_string('view','local_sis'));
	/* Not as simple as we thought
	$url = new moodle_url('delete_institute.php', array());
	$tabs[] = new tabobject('delete', $url, 'Delete Institute Data');
	$url = new moodle_url('replication.php', array());
	$tabs[] = new tabobject('replication', $url, 'Replicate Institute Data');
	*/
}
//if($isAdmin || sis_has_access(array('suspend'), $institute))
{
	//$url = new moodle_url('suspend.php', array());
	//$tabs[] = new tabobject('suspend', $url, 'Suspend User');
}
if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');