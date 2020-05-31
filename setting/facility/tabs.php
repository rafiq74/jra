<?php

$tabs = array();	

$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('room', $url, get_string('room','local_sis'));
$url = new moodle_url('building.php', array());
$tabs[] = new tabobject('building', $url, get_string('building','local_sis'));
//$url = new moodle_url('usage.php', array());
//$tabs[] = new tabobject('usage', $url, get_string('usage','local_sis'));
$url = new moodle_url('room_type.php', array());
$tabs[] = new tabobject('room_type', $url, get_string('room_type','local_sis'));

if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');