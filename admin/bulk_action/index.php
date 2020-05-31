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
 * @license   http://www.gnu.org/copycenter/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php';
require_once '../../lib/sis_lookup_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once '../../user/lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/admin/setting/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'system' => ''
); //super admin role only
sis_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

sis_set_session('sis_home_tab', 'system');
$PAGE->navbar->add(get_string('system', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'system')));
$PAGE->navbar->add(get_string('bulk_action', 'local_sis'), new moodle_url('index.php'));

echo $OUTPUT->header();

//content code starts here
sis_ui_page_title(get_string('bulk_action','local_sis'));
$currenttab = 'general'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

$output = '';
$output .= '<div class="pt-3">';
//one button
$url = new moodle_url('index.php', array('action' => 1)); //1 for lookup
$output .= sis_ui_button(get_string('update_lookup_values', 'local_sis'), $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('index.php', array('action' => 2)); //1 for lookup
$output .= sis_ui_button(get_string('update') . ' ' . get_string('course_code', 'local_sis') . ' ' . get_string('separator', 'local_sis'), $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('index.php', array('action' => 3)); //1 for lookup
$output .= sis_ui_button(get_string('reset') . ' ' . get_string('active', 'local_sis') . ' ' . get_string('student', 'local_sis') . ' ' . get_string('password'), $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('index.php', array('action' => 4)); //1 for lookup
$output .= sis_ui_button('Make Active all Active Students', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('backup.php', array('action' => 4)); //1 for lookup
$output .= sis_ui_button('Backup', $url, 'primary', '', '', true);
//end of one button

$output .= '</div>';
echo $output;

$action = optional_param('action', 0, PARAM_INT);

if($action == 1)
	sis_admin_bulk_action_lookup();
else if($action == 2) //update course code separator
{
	$var_name = 'admin_course_separator';
	$separator = sis_get_config($var_name);
	$courses = $DB->get_records('si_course');
	foreach($courses as $data)
	{
		$data->course_code = $data->code . $separator . $data->course_num;
		$DB->update_record('si_course', $data);	
	}
}
else if($action == 3) //reset all active student password
{
	//get active user list
	$institute = sis_get_institute();
	$sql = "select 
		b.id, a.appid, a.program, a.program_status, a.program_action, a.eff_date, b.first_name, b.family_name, b.gender from {si_student_program} a inner join {si_user} b on a.user_id = b.id 
	where " . sis_query_eff_date('si_student_program', 'a', array('user_id'), $enforce_eff_date) . " and " . sis_query_eff_seq('si_student_program', 'a', array('user_id')) . " $student_status_where and a.institute = '$institute' and a.eff_status = 'AC'"; //effective date and seq
	
	$users = $DB->get_records_sql($sql);
	foreach($users as $u)
	{
		$success = sis_user_reset_password($u->id);
		if($success)
			print_object($u);
	}
}
else if($action == 4)
{
	//make all active student si_user account active
	//get active user list
	$institute = sis_get_institute();
	$sql = "select 
		b.id, a.appid, a.program, a.program_status, a.program_action, a.eff_date, b.first_name, b.family_name, b.gender from {si_student_program} a inner join {si_user} b on a.user_id = b.id 
	where " . sis_query_eff_date('si_student_program', 'a', array('user_id'), $enforce_eff_date) . " and " . sis_query_eff_seq('si_student_program', 'a', array('user_id')) . " $student_status_where and a.institute = '$institute'"; //effective date and seq
	
	$users = $DB->get_records_sql($sql);
	foreach($users as $u)
	{
		$a = $DB->get_record('si_user', array('id' => $u->id));
		$a->eff_status = 'A';
		$DB->update_record('si_user', $a);
		print_object($a);
	}
}

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/admin/bulk_action/bulk_action.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();