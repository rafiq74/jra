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
require_once '../../lib/jra_output_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../lib/jra_app_lib.php';
require_once '../../lib/jra_file_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once($CFG->libdir.'/csvlib.class.php');
require_once 'lib.php'; //local library
require_once 'form.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/update_id.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admission',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$PAGE->navbar->add(jra_get_string(['applicant', 'management']), new moodle_url('index.php'));
$PAGE->navbar->add('Update Trainee ID', new moodle_url('update_id.php'));

$return_params = jra_get_session('si_applicant_admitted_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('admitted.php', $return_params);
//put before header so we can redirect
$mform = new file_upload_form();
if ($mform->is_cancelled()) 
{
    redirect($return_url);
} 
 
else if ($data = $mform->get_data()) 
{	
	$now = time();
/*	
	$name = $mform->get_new_filename('userfile');
	$ext = sis_file_get_extension($name);
	$data->filepath = sis_file_admission_path();
	$data->filename = 'admission.' . $ext;

	$override = true;
	$dir = $data->filepath . $data->filename;
	if($mform->save_file('userfile', $dir, $override))
	{
		//update the applicant
		$text = sis_ui_alert(get_string('file_upload_successful', 'local_sis'), 'success', '', true, true);
		sis_ui_set_flash_message($text, 'sis_admission_updated');
		redirect($upload_url); //we redirect to kill the post session
	}
	else
	{
		$text = sis_ui_alert(get_string('file_upload_fail', 'local_sis', $a), 'danger', '', true, true);
		sis_ui_set_flash_message($text, 'sis_admission_updated');
	}
*/

	$iid = csv_import_reader::get_new_iid('uploaduser');
	$cir = new csv_import_reader($iid, 'uploaduser');

	$content = $mform->get_file_content('userfile');
	$readcount = $cir->load_csv_content($content, $data->encoding, $data->delimiter_name);

	$csvloaderror = $cir->get_error();
	unset($content);

	if (!is_null($csvloaderror)) {
		print_error('csvloaderror', '', $returnurl, $csvloaderror);
	}
	
    // init csv import helper
    $cir->init(); //very important
    $linenum = 1; //column header is first line
	
	$columns = $cir->get_columns();

	$semester = '';
	$arr = array();
    while ($line = $cir->next()) 
	{
		$obj = new stdClass();
		foreach($columns as $key => $column)
		{
			$obj->$column = $line[$key];
			if($column == 'semester' && $semester == '') //remember the semester
				$semester = $line[$key];
		}
		if($obj->student_id != '') //has student id
		{
			//try to get the applicant
			$applicant = $DB->get_record('si_applicant', array('appid' => $obj->appid));
			if($applicant)
			{
				$data = new stdClass();
				$data->id = $applicant->id;
				$data->idnumber = $obj->student_id;
				$DB->update_record('si_applicant', $data);
			}
		}
	}
    $cir->close();
    $cir->cleanup(true);
	if($semester != '') //has semester
	{
//		print_object($semester);	
//		print_object($arr);
		$text = jra_ui_alert('Trainee ID from SIS has been updated to admission system', 'success', '', true, true);
		jra_ui_set_flash_message($text, 'jra_admission_updated');
		redirect($return_url); //we redirect to kill the post session
	}
}



echo $OUTPUT->header();
//content code starts here
jra_ui_page_title(jra_get_string(['applicant', 'management']) . ' : Update Applicant Trainee ID');

$currenttab = 'admitted'; //change this according to tab
include('tabs.php');
jra_set_session('jra_applicant_tab', 'admitted');
echo $OUTPUT->box_start('jra_tabbox');

$mform->display();

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/application/applicant/applicant.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();
