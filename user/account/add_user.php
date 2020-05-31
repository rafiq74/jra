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
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php'; 
require_once '../../lib/sis_query_lib.php'; 
require_once 'lib.php';

require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/user/account/add_user.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
sis_access_control($access_rules);

$id = optional_param('id', false, PARAM_INT);
if($id)
{
	$qs = '?id=' . $id;
	$bc = 'update_user';
}
else
{
	$qs = '';
	$bc = 'add_user';
}
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'system');
$PAGE->navbar->add(get_string('system', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'system')));
$PAGE->navbar->add(get_string('user', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string($bc, 'local_sis'), new moodle_url('add_user.php' . $qs));

$return_params = sis_get_session('si_user_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);

//put before header so we can redirect
if($id) //update
	$mform = new user_form_edit();
else
	$mform = new user_form();
if ($mform->is_cancelled()) 
{
    redirect('index.php');
} 
else if ($data = $mform->get_data()) 
{		
	//validate that there is no duplicate
	$isDuplicate = sis_user_account_duplicate($data);
	if(!$isDuplicate) //no duplicate, update it
	{
		$now = time();
		$institute = sis_get_institute();
		if($data->id == '') //create new
		{
			if($data->appid == sis_global_var('TEMP_USER_ID'))
				$data->eff_status = 'I';
			else
				$data->eff_status = 'A';
			if($data->appid == sis_global_var('TEMP_USER_ID')) //---
				$data->deleted = 1;
			else
				$data->deleted = 0;
		
			$data->enable_login = 'Y';
			$data->date_created = $now;
			$data->date_updated = $now;
			$new_id = $DB->insert_record('si_user', $data);	
			//create the civil id
			$info = new stdClass();
			$info->user_id = $new_id;
			$info->civil_id = $data->national_id;
			$info->id_type = $data->id_type;
			$info->language_track = 'en';
			$info->date_created = $now;
			$info->date_updated = $now;
			$info->institute = $institute;
			$DB->insert_record('si_personal_data', $info);	
			if($data->email_primary != '') //has email, create it
			{
				$contact = new stdClass();
				$contact->user_id = $new_id;
				$contact->address_type = 'primary';
				$contact->email_primary = $data->email_primary;
				$contact->date_created = $now;
				$contact->date_updated = $now;
				$contact->institute = $institute;
				$DB->insert_record('si_personal_contact', $contact);	
				
			}

		}
		else
		{
			$data->date_updated = $now;
			$DB->update_record('si_user', $data);			
			sis_log_data('si_user', $data); //log the change
		}
	    redirect($return_url);
	}
}

echo $OUTPUT->header();

if($isDuplicate)
	sis_ui_alert(get_string('duplicate_user', 'local_sis'), 'danger');
//content code starts here
sis_ui_page_title(get_string('add_user', 'local_sis'));

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$toform = $DB->get_record('si_user', array('id' => $id));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();

$PAGE->requires->js('/local/sis/user/account/account.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();