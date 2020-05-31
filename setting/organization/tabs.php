<?php

$tabs = array();	
$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('organization', $url, get_string('organization','local_sis'));
//	$url = new moodle_url('section.php', array());
//	$tabs[] = new tabobject('section', $url, get_string('section','local_sis'));
$url = new moodle_url('campus.php', array());
$tabs[] = new tabobject('campus', $url, get_string('campus','local_sis'));
$url = new moodle_url('institute.php', array());
$tabs[] = new tabobject('institute', $url, get_string('institute','local_sis'));
if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');