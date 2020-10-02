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
$PAGE->set_url('/local/jra/application/applicant/contact_info.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
jra_allow_application(); //make sure it is permissable

$return_url = new moodle_url($CFG->wwwroot, $return_params);

$jra_user = $USER->jra_user;
$applicant = jra_app_get_applicant();

if(!$applicant || $applicant->status < 1)
    redirect($return_url);

//check if it is read only
if($applicant->status >= jra_app_read_only_stage())
    redirect($return_url);

if(jra_is_closed(true))
    redirect($return_url);

$contact = $DB->get_record('si_applicant_contact', array('applicant_id' => $applicant->id));

if($contact)
	$id = $contact->id;
else
	$id = false;
if($id)
{
	$bc = ['update_contact_information'];
}
else
{
	$bc = ['add', 'contact_information'];
}

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('contact_info.php', $urlparams));


//put before header so we can redirect
$mform = new applicant_contact_form();
if ($mform->is_cancelled()) 
{
    redirect($return_url);
} 
 
else if ($data = $mform->get_data()) 
{	
	$now = time();
	//get the state
	$state = jra_lookup_get_state($data->address_city);
	if($state)
		$data->address_state = jra_output_show_field_language($state, 'state');
	else
		$data->address_state = '';
	if($data->id != '') //updating
	{
		$DB->update_record('si_applicant_contact', $data);	
	}
	else //insert new
	{
		$data->user_id = $jra_user->id;
		$data->applicant_id = $applicant->id;
		$data->address_type = 'primary';
		$data->email_primary = $jra_user->email;		
		$data->date_created = $now;
		$data->date_updated = $now;
		//use transaction
		try {
			$transaction = $DB->start_delegated_transaction();
			$DB->insert_record('si_applicant_contact', $data);	
			
			//update the stage
			$obj = new stdClass();
			$obj->id = $applicant->id;
			$obj->status = 2;
			$obj->date_updated = $now;
			$DB->update_record('si_applicant', $obj);	
			
			 // Assuming the both inserts work, we get to the following line.
			$transaction->allow_commit();
		 
		} catch(Exception $e) {
			$transaction->rollback($e);
		}
	}
	$text = jra_ui_alert(get_string('applicant_information_update', 'local_jra'), 'success', '', true, true);
	jra_ui_set_flash_message($text, 'jra_information_updated');
	redirect($return_url);
}

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string($bc));

if($contact)
	$mform->set_data($contact);
	
$mform->display();

echo $OUTPUT->footer();