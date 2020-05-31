<?php

$tabs = array();	

$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('city', $url, get_string('city','local_sis'));
$url = new moodle_url('state.php', array());
$tabs[] = new tabobject('state', $url, get_string('state','local_sis'));

if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');