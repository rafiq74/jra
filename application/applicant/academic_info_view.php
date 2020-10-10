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
$PAGE->set_url('/local/jra/application/applicant/academic_info_view.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
jra_allow_application(); //make sure it is permissable

$return_url = new moodle_url($CFG->wwwroot, $return_params);
$jra_user = $USER->jra_user;
$applicant = jra_app_get_applicant();
if(!$applicant || $applicant->status < 3) //if no record, redirect to main page
    redirect($return_url);

    $semester = $DB->get_record('si_semester', array('semester' => $applicant->semester));

$bc = ['view', 'academic_information'];

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('academic_info_view.php', $urlparams));

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string($bc));

$detail_data = array();
//one row of data

if($semester->admission_type == 'regular'){
  $obj = new stdClass();
  $obj->title = get_string('secondary_school_result', 'local_jra');
  $obj->content = $applicant->secondary;
  $detail_data[] = $obj;
  //end of data row
  //one row of data
  $obj = new stdClass();
  $obj->title = get_string('tahseli', 'local_jra');
  $obj->content = $applicant->tahseli;
  $detail_data[] = $obj;
  //end of data row
  //one row of data
  $obj = new stdClass();
  $obj->title = get_string('qudorat', 'local_jra');
  $obj->content = $applicant->qudorat;
  $detail_data[] = $obj;
}
else {
  $university = $DB->get_record('si_university', array('id' => $applicant->graduated_from));
  $university = jra_output_show_field_language($university,'name');

  $major = $DB->get_record('si_major', array('id' => $applicant->graduated_major));
  $major = jra_output_show_field_language($major,'name');

  $obj = new stdClass();
  $obj->title = get_string('graduate_from', 'local_jra');
  $obj->content = $university;
  $detail_data[] = $obj;
  //end of data row
  //one row of data
  $obj = new stdClass();
  $obj->title = get_string('major', 'local_jra');
  $obj->content = $major;
  $detail_data[] = $obj;
  //end of data row
  //one row of data
  $obj = new stdClass();
  $obj->title = get_string('year_graduation', 'local_jra');
  $obj->content = $applicant->graduated_year;
  $detail_data[] = $obj;

  $obj = new stdClass();
  $obj->title = get_string('cgpa', 'local_jra');
  $obj->content = $applicant->graduated_gpa;
  $detail_data[] = $obj;
}

//end of data row
/* for now don't show
//one row of data
$obj = new stdClass();
$obj->title = jra_get_string(['aggregation', 'points']);
$obj->content = $applicant->aggregation;
$detail_data[] = $obj;
//end of data row
*/
$str = jra_ui_data_detail($detail_data, 2);

$url = new moodle_url($CFG->wwwroot);
$btn = '<div class="text-center">
		<a href="' . $url . '"><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['back']) . '</button></a>
</div>		';

echo jra_ui_box($str, '', $btn, true);


echo $OUTPUT->footer();
