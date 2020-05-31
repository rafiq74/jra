<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This page is provided for compatability and redirects the user to the default grade report
 *
 * @package   core_grades
 * @copyright 2005 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once '../lib/sis_lib.php'; 
require_once '../lib/sis_ui_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);

sis_access_control($access_rules);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);

sis_access_control($access_rules);
	
//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

$PAGE->navbar->add(get_string('setting', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'setup')));

echo $OUTPUT->header();
//content code starts here

sis_ui_page_title(get_string('student','local_sis'));




$PAGE->requires->js('/local/sis/setting/setting.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();