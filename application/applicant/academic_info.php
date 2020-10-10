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
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/academic_info.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
jra_allow_application(); //make sure it is permissable

$return_url = new moodle_url($CFG->wwwroot, $return_params);

$jra_user = $USER->jra_user;
$applicant = jra_app_get_applicant();

if(!$applicant || $applicant->status < 2)
    redirect($return_url);

//check if it is read only
if($applicant->status >= jra_app_read_only_stage())
    redirect($return_url);

if(jra_is_closed(true))
    redirect($return_url);

$id = $applicant->id;

if($applicant->tahseli != '')
{
	$bc = ['update_academic_information'];
}
else
{
	$bc = ['add', 'academic_information'];
}

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('academic_info.php', $urlparams));

$semester = $DB->get_record('si_semester', array('semester' => $applicant->semester));

//put before header so we can redirect

$mform = new applicant_academic_form(null, array('semester' => $semester));

if ($mform->is_cancelled())
{
    redirect($return_url);
}

else if ($data = $mform->get_data())
{
	$now = time();
	if($semester->admission_type == 'regular')
	{
		$data->secondary_weight = $semester->secondary_weight;
		$data->tahseli_weight = $semester->tahseli_weight;
		$data->qudorat_weight = $semester->qudorat_weight;
		$data->aggregation = jra_app_compute_aggregate($data, $semester);
	}
	$data->date_updated = $now;
	if($applicant->status < 3) //only update if the status is less
		$data->status = 3;
  
	$DB->update_record('si_applicant', $data);

	$text = jra_ui_alert(get_string('applicant_information_update', 'local_jra'), 'success', '', true, true);
	jra_ui_set_flash_message($text, 'jra_information_updated');
	redirect($return_url);
}

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string($bc));

$mform->set_data($applicant);

$mform->display();

echo $OUTPUT->footer();
