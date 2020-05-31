<?php

$tabs = array();	
$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('course_catalogue', $url, get_string('course_catalogue','local_sis'));

$url = new moodle_url('class_type.php', array());
$tabs[] = new tabobject('class_type', $url, get_string('class_type','local_sis'));

$url = new moodle_url('course_type.php', array());
$tabs[] = new tabobject('course_type', $url, get_string('course_type','local_sis'));

if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');