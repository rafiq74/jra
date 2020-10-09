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
require_once '../../lib/jra_output_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/personal_info_view.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
jra_allow_application(); //make sure it is permissable

$return_url = new moodle_url($CFG->wwwroot, $return_params);
$jra_user = $USER->jra_user;
$semester = jra_get_semester();
$applicant = jra_app_get_applicant();
if(!$applicant) //if no record, redirect to main page
    redirect($return_url);


$bc = ['view', 'personal_information'];

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('personal_info_view.php', $urlparams));

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string($bc));

$id_type = jra_lookup_get_list('personal_info', 'id_type', '', true);
$countries = jra_lookup_countries();

$detail_data = array();
//one row of data
$obj = new stdClass();
$obj->title = get_string('name', 'local_jra') . ' (' . get_string('english', 'local_jra') . ')';
$obj->content = jra_output_show_user_name($applicant, true, false, false);
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')';
$obj->content = jra_output_show_user_name($applicant, true, false, true);
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = jra_get_string(['national_id']);
$obj->content = $applicant->national_id == '' ? '-' : $applicant->national_id . ' (' . $id_type[$applicant->id_type] . ')';
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('nationality', 'local_jra');
$obj->content = $applicant->nationality == '' ? '-' : $countries[$applicant->nationality];
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('date_of_birth', 'local_jra');
//$obj->content = $applicant->dob == 0 ? '-' : jra_to_hijrah(date('d-M-Y', $applicant->dob));
$obj->content = $applicant->dob_hijri == '' ? '-' : $applicant->dob_hijri;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('age', 'local_jra');
$obj->content = $applicant->dob_hijri == '' ? '-' : jra_app_get_age_hijri($applicant->dob_hijri);
$detail_data[] = $obj;
//end of data row
//one row of data
$gender = jra_lookup_gender();
$obj = new stdClass();
$obj->title = get_string('gender', 'local_jra');
$obj->content = $applicant->gender == '' ? '-' : $gender[$applicant->gender];
$detail_data[] = $obj;
//end of data row
//one row of data
$marital_status = jra_lookup_marital_status();
$obj = new stdClass();
$obj->title = get_string('marital_status', 'local_jra');
$obj->content = $applicant->marital_status == '' ? '-' : $marital_status[$applicant->marital_status];
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('blood_group', 'local_jra');
$obj->content = $applicant->blood_type == '' ? '-' : $applicant->blood_type;
$detail_data[] = $obj;
//end of data row

$str = jra_ui_data_detail($detail_data, 2);

$url = new moodle_url($CFG->wwwroot);
$btn = '<div class="text-center">
		<a href="' . $url . '"><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['back']) . '</button></a>
</div>		';

echo jra_ui_box($str, '', $btn, true);


echo $OUTPUT->footer();
