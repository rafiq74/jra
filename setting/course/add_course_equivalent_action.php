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

require_once '../../../../config.php';
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php'; 
require_once '../../classes/course.php'; 

require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/course/add_course_equivalent_action.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

$id = required_param('id', PARAM_INT);
$dataid = optional_param('dataid', '', PARAM_TEXT);
//put before header so we can redirect
$save_url = "javascript:save_course_component()";

$sort_url = new moodle_url($save_url);

$mform = new equivalent_form(null, array('id'=>$id, 'dataid' => $dataid), 'post', '', array('name' => 'mform1', 'onsubmit' => 'return save_course_equivalent()'));
if ($mform->is_cancelled()) 
{
} 
else if ($data = $mform->get_data()) 
{		
}
$mform->display();

