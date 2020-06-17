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
require_once '../../lib/jra_lib.php'; 
require_once '../../lib/jra_ui_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/bulk_action/migrate.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'system' => ''
); //super admin role only
jra_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$PAGE->navbar->add(get_string('dashboard', 'local_jra'), new moodle_url($CFG->wwwroot . '/local/jra/dashboard/index.php', array()));
$PAGE->navbar->add(get_string('bulk_action', 'local_jra'), new moodle_url('migrate.php'));

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string(['data', 'migration']));
$currenttab = 'general'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('jra_tabbox');

$output = '';
$output .= '<div class="pt-3">';
//one button
$url = new moodle_url('migrate.php', array('action' => 2)); //import from data
$output .= jra_ui_button(get_string('execute', 'local_jra'), $url, 'primary', '', '', true);
//end of one button

$output .= '</div>';
echo $output;
set_time_limit(0);
$action = optional_param('action', 0, PARAM_INT);

$country = jra_get_country();

/* update national id in si_user
$sql = "select a.id, b.civil_id as national_id from {si_user} a inner join {si_personal_data} b on a.id = b.user_id";

$x = $DB->get_records_sql($sql);
foreach($x as $y)
{
	print_object($y);
	$DB->update_record('si_user', $y);
}
*/
$now = time();

if($action == 1) //for import of iban number. Don't delete
{
	$info = $DB->get_records('jra_data_import', array(
	));
//	print_object($info);
	$arr = array();
	$state = array();
	foreach($info as $a)
	{
		$data = new stdClass();
		$data->state = $a->field3;
		$data->state_code = $a->field4;
		$data->city = $a->field2;
		$data->postcode = $a->field1;
		$data->country = 'MY';
		$state[$a->field4] = $a->field3;
//			$DB->insert_record('si_personal_finance', $data);
		$arr[] = $data;
	}
	ksort($state);
	$brr = array();
	$count = 1;
	foreach($state as $code => $s)
	{
		$data = new stdClass();
		$data->state_code = $code;
		$data->state = $s;
		$data->sort_order = $count;
		$data->country = 'MY';
		$count++;
		$brr[] = $data;
	}
	print_object($brr);
	print_object($arr);
//	$DB->insert_records('jra_state', $brr);
//	$DB->insert_records('jra_city', $arr);
}
else if($action == 2) //refactor the moodle course
{
	$courses = $DB->get_records('course');
	foreach($courses as $course)
	{
		if($course->idnumber != '')
		{
			$arr = explode(' : ', $course->shortname);
			print_object($arr);
			print_object($course->shortname . ' ' . $course->idnumber);
			$course_code = trim($arr[0]);
			$course->shortname = $course_code;
			$course->idnumber = $course_code;
//			$DB->update_record('course', $course);
		}
	}
}
echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/admin/bulk_action/bulk_action.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();