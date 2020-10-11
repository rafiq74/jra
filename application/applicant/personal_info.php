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
require_once '../../lib/jra_lib.php';
require_once '../../lib/jra_ui_lib.php';
require_once '../../lib/jra_app_lib.php';
require_once '../../lib/jra_hdate.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/personal_info.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
jra_allow_application(); //make sure it is permissable

$jra_user = $USER->jra_user;
$semester = jra_get_semester();
$applicant = jra_app_get_applicant();
if($applicant)
	$id = $applicant->id;
else
	$id = false;
if($id)
{
	$qs = '?id=' . $id;
	$bc = ['update_personal_information'];
}
else
{
	$qs = '';
	$bc = ['add', 'personal_information'];
}


$semester = $DB->get_record('si_semester', array('semester' => $semester));

$return_url = new moodle_url($CFG->wwwroot, $return_params);

//check if it is read only
if($applicant && $applicant->status >= jra_app_read_only_stage())
    redirect($return_url);

if(jra_is_closed(true))
    redirect($return_url);
//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('personal_info.php', $urlparams));


//put before header so we can redirect
$mform = new applicant_form(null, array('admission_type' => $semester->admission_type));
if ($mform->is_cancelled())
{
    redirect($return_url);
}


else if ($data = $mform->get_data())
{


	$now = time();
	$data->dob_hijri = $data->h_y . '/' . $data->h_m . '/' . $data->h_d;

	if($data->id != '') //updating
	{
		
		$DB->update_record('si_applicant', $data);
	}
	else //insert new
	{
		$data->user_id = $jra_user->id;
		$data->semester = $semester->semester;
		$data->religion = 'Islam';
		$data->status = '1'; //1 indicates completed step 1
		$data->status_date = $now;
		$data->date_created = $now;
		$data->date_updated = $now;
		$data->deleted = 0;



		$appid = $DB->insert_record('si_applicant', $data);
		$obj = new stdClass();
		$obj->id = $appid;
		$obj->appid = jra_app_ref_number($obj);
		$DB->update_record('si_applicant', $obj);



	}
	$text = jra_ui_alert(get_string('applicant_information_update', 'local_jra'), 'success', '', true, true);
	jra_ui_set_flash_message($text, 'jra_information_updated');
	redirect($return_url);
}

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string($bc));

if($applicant)
{
	if($applicant->dob_hijri != '')
	{
		$x = explode('/', $applicant->dob_hijri);
		$applicant->h_y = isset($x[0]) ? $x[0] : '';
		$applicant->h_m = isset($x[1]) ? $x[1] : '';
		$applicant->h_d = isset($x[2]) ? $x[2] : '';
	}
	$mform->set_data($applicant);
}
$mform->display();

echo $OUTPUT->footer();
