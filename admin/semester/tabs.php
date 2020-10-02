<?php

$tabs = array();	
$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('semester', $url, get_string('semester','local_sis'));

if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');