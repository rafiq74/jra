<?php

$tabs = array();	
$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('university', $url, get_string('university','local_jra'));
$url = new moodle_url('major.php', array());
$tabs[] = new tabobject('major', $url, get_string('major','local_jra'));

if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');