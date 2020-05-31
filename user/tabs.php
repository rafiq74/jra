<?php

$tabs = array();	
$url = new moodle_url('index.php', array());
$tabs[] = new tabobject('user', $url, get_string('user'));
if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');