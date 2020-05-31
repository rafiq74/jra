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
require_once '../../lib/sis_lookup_lib.php'; 
require_once '../../lib/sis_output_lib.php'; 
require_once '../../lib/sis_query_lib.php'; 
require_once 'lib.php'; 
require_once 'form.php'; 

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/program/update_plan_course_action.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

$id = required_param('plan_id', PARAM_INT);
$record_id = required_param('id', PARAM_INT);
$display = optional_param('display', 'full', PARAM_TEXT); //full or minimal

//content starts here
$record = $DB->get_record('si_plan_course', array('id' => $record_id));
if($record)
{
	$req_course = $DB->get_record('si_course', array('id' => $record->course_id));
	$mform = new plan_course_form(null, array('id'=>$id, 'dataid' => $dataid), 'post', '', array('name' => 'mform1', 'onsubmit' => 'return save_grade_letter()'));
	
	//no use as form is submitted to grade_scheme_action
	if ($mform->is_cancelled()) 
	{
	} 
	else if ($data = $mform->get_data()) 
	{	
	}

	$mform->set_data($record);

	$form = $mform->render();
	sis_ui_box($form, get_string('update_requisite', 'local_sis') . ' : ' . sis_output_show_course_code($req_course->code, $req_course->course_num) . ' - ' . $req_course->course_name);
}
else
	sis_ui_alert(get_string('wrong_parameter', 'local_sis'), 'danger');



//content ends here