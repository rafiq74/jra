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
require_once '../../lib/jra_file_lib.php'; 
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/upload_document.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
jra_allow_application(); //make sure it is permissable

$return_url = new moodle_url($CFG->wwwroot, $return_params);
$upload_url = new moodle_url('/local/jra/application/applicant/upload_document.php'); //for upload, we redirect back to the upload page

$jra_user = $USER->jra_user;
$applicant = jra_app_get_applicant();
$semester = jra_get_semester();

if(!$applicant || $applicant->status < 3)
    redirect($return_url);

$read_only = false;
//check if it is read only
if($applicant->status >= jra_app_read_only_stage())
	$read_only = true;

if(jra_is_closed(true))
	$read_only = true;
	
$id = $applicant->id;

$bc = ['upload', 'supporting_documents'];

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('document_upload.php', $urlparams));

if(isset($_POST['delete_id']))
{
	$delete_module = $_POST['delete_module'];	
	$delete_file = $_POST['delete_file'];	
	
	//update the applicant
	$obj = new stdClass();
	$obj->id = $applicant->id;
	if($delete_module == 'national')
	{
		$obj->national_id_file = '';
		$a = jra_get_string(['national_id']);
	}
	else if($delete_module == 'secondary')
	{
		$obj->secondary_file = '';
		$a = get_string('secondary_school_document', 'local_jra');
	}
	else if($delete_module == 'tahseli')
	{
		$obj->tahseli_file = '';
		$a = get_string('tahseli', 'local_jra');
	}
	else if($delete_module == 'qudorat')
	{
		$obj->qudorat_file = '';
		$a = get_string('qudorat', 'local_jra');
	}
	$obj->status = 3; //reduce the status to 3
	$obj->date_updated = time();
	$DB->update_record('si_applicant', $obj);
	$applicant = jra_app_get_applicant(); //after update we need to retrieve the data again
	//now delete the file
	$file_path = jra_file_supporting_document_path($semester) . $delete_file;
	jra_file_delete_file($file_path);
	$text = jra_ui_alert(get_string('file_deleted_successful', 'local_jra', $a), 'success', '', true, true);
	jra_ui_set_flash_message($text, 'jra_information_updated');
	redirect($upload_url); //we redirect to kill the post session
	
//	redirect($upload_url); //we redirect to kill the post session
}

//put before header so we can redirect
$mform = new document_upload_form();
if ($mform->is_cancelled()) 
{
    redirect($return_url);
} 
 
else if ($data = $mform->get_data()) 
{	
	$now = time();
	
	//make sure the option is default to the submitted module
	jra_set_session('jra_document_upload_module', $data->module);
	
	$name = $mform->get_new_filename('userfile');
	$ext = jra_file_get_extension($name);
	$data->filepath = jra_file_supporting_document_path($semester);
	$data->filename = $data->module . '_' . $applicant->id . '.' . $ext;

	if($data->module == 'national')
		$a = jra_get_string(['national_id']);
	else if($data->module == 'secondary')
		$a = get_string('secondary_school_document', 'local_jra');
	else
		$a = get_string($data->module, 'local_jra');
	$override = true;
	$dir = $data->filepath . $data->filename;
	if($mform->save_file('userfile', $dir, $override))
	{
		//update the applicant
		$obj = new stdClass();
		$obj->id = $applicant->id;
		if($data->module == 'national')
			$obj->national_id_file = $data->filename;
		else if($data->module == 'secondary')
			$obj->secondary_file = $data->filename;
		else if($data->module == 'tahseli')
			$obj->tahseli_file = $data->filename;
		else if($data->module == 'qudorat')
			$obj->qudorat_file = $data->filename;
		$obj->date_updated = $now;
		$DB->update_record('si_applicant', $obj);
		$applicant = jra_app_get_applicant(); //after update we need to retrieve the data again
		if(jra_application_completed_document($applicant)) //completed all, we update the stage
		{
			$obj = new stdClass();
			$obj->id = $applicant->id;
			$obj->status = 4;
			$DB->update_record('si_applicant', $obj);
			$applicant->status = 4; //update the applicant object, just in case
		}
		$text = jra_ui_alert(get_string('file_upload_successful', 'local_jra', $a), 'success', '', true, true);
		jra_ui_set_flash_message($text, 'jra_information_updated');
		redirect($upload_url); //we redirect to kill the post session
	}
	else
	{
		$text = jra_ui_alert(get_string('file_upload_fail', 'local_jra', $a), 'danger', '', true, true);
		jra_ui_set_flash_message($text, 'jra_information_updated');
	}
}

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string($bc));
	
if(!$read_only)
	$mform->display();

jra_ui_show_flash_message('jra_information_updated');

$str = jra_application_list_supporting_document($applicant, $read_only);
echo $str;

$url = new moodle_url($CFG->wwwroot);
$btn = '
<div class="card-header mt-5">
	<div class="text-center">
		<a href="' . $url . '"><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['back']) . '</button></a>
	</div>		
</div>
';

echo $btn;
$PAGE->requires->js('/local/jra/application/applicant/applicant.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript
echo $OUTPUT->footer();
