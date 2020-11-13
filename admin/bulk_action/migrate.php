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

$institute = jra_get_institute();

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
	foreach($info as $a)
	{
		$data = new stdClass();
		$data->username = $a->field10;
		$data->email = $a->field10;
		$data->user_type = 'public';
		$data->national_id = $a->field9;
		$data->nationality = 'SA';
		$data->first_name = $a->field1;	
		$data->father_name = $a->field2;	
		$data->grandfather_name = $a->field3;	
		$data->family_name = $a->field4;	
		$data->first_name_a = $a->field5;	
		$data->father_name_a = $a->field16;	
		$data->grandfather_name_a = $a->field7;	
		$data->family_name_a = $a->field8;	
		$data->gender = 'M';	
		$data->password =  '2097c69fbd54c190cd87c5eb3d1e7caa';
		$data->enable_login = 'Y';	
		$data->deleted = '0';	
		$data->password_change = 'N';
		$data->active_status = 'A';
		$data->active_date = '1595489849';
		$data->date_created = '1595489849';
		$data->date_updated = '1595489849';
		$data->institute = 'HIEI';
		$arr[] = $data;
	}
	print_object($arr);
//	$DB->insert_records('jra_user', $arr);

}
else if($action == 2) //refactor the moodle course
{
	$info = $DB->get_records('jra_user', array(
		'user_type' => 'public',
	));
	$arr = array();
	$dob = strtotime('1-Jan-2000');	
	foreach($info as $a)
	{
		$data = new stdClass();
		$data->user_id = $a->id;
		$data->national_id = $a->national_id;
		$data->id_type = 'iqamah';
		$data->first_name = $a->first_name;	
		$data->father_name = $a->father_name;	
		$data->grandfather_name = $a->grandfather_name;	
		$data->family_name = $a->family_name;	
		$data->first_name_a = $a->first_name_a;	
		$data->father_name_a = $a->father_name_a;	
		$data->grandfather_name_a = $a->grandfather_name_a;	
		$data->family_name_a = $a->family_name_a;	
		$data->gender = 'M';	
		$data->dob = $dob;	
		$data->marital_status = 'S';
		$data->nationality = $a->nationality;
		$data->nationality_at_birth = $a->nationality;
		$data->religion = 'Islam';
		$data->blood_type = 'O';
		$data->city = '';
		$data->tahseli = rand(60,100);
		$data->qudorat = rand(65,100);
		$data->secondary = rand(75,100);
		$data->national_id_file = '';	
		$data->secondary_file = '';	
		$data->tahseli_file = '';	
		$data->qudorat_file = '';
		$data->status = 5;
		$data->status_date = '1595607507';
		$data->date_created = '1595489849';
		$data->date_updated = '1595489849';
		$data->deleted = 0;
		$data->institute = 'HIEI';
		$arr[] = $data;
	}
	print_object($arr);
	
//	$DB->insert_records('si_applicant', $arr);
}
else if($action == 3) //refactor the moodle course
{
	$contact = $DB->get_record('si_applicant_contact', array('id' => 4));
	$contact->id = '';
	print_object($contact);
	$info = $DB->get_records('si_applicant', array(
	));
	$arr = array();
	foreach($info as $a)
	{
		if($a->id != 1)
		{
			$x = $DB->get_record('jra_user', array('id' => $a->user_id));
			$obj = clone $contact;
			$obj->user_id = $a->user_id;
			$obj->applicant_id = $a->id;
			$obj->email_primary = $x->email;
			$arr[] = $obj;
		}
	}
	print_object($arr);
	
//	$DB->insert_records('si_applicant_contact', $arr);
}
else if($action == 4)
{
	$info = $DB->get_records('jra_data_import', array(
	));
//	print_object($info);
	$arr = array();
	foreach($info as $a)
	{
		$data = new stdClass();
		$data->state = $a->field1;
		$data->state_a = $a->field2;
		$data->city = $a->field3;
		$data->city_a = $a->field4;
		$data->institute = 'HIEI';
		$arr[] = $data;
	}
	print_object($arr);
//	$DB->insert_records('jra_city', $arr);
}
echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/admin/bulk_action/bulk_action.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();