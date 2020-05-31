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

require_once 'form.php';

require_login(); //always require login

//multi roles
$roles = array();
$roles[] = array(
	'role' => 'admin',
	'subrole' => 'all',
);
$roles[] = array(
	'role' => 'student',
	'subrole' => 'all',
);
$access_rules = array(
	'role' => $roles,
);

sis_access_control($access_rules);

$post_data = $_POST;
if(isset($post_data['uid']))
	$id = $post_data['uid'];
else
	$id = required_param('id', PARAM_INT);
	
$operation = optional_param('op', '', PARAM_TEXT);

$dataid = optional_param('dataid', false, PARAM_INT);
if($dataid)
{
	$qs = '?id=' . $id.  '&dataid=' . $dataid;
	$bc = 'update_contact';
}
else
{
	$qs = '?id=' . $id;
	$bc = 'add_contact';
}

//put before header so we can redirect
$return_url = new moodle_url('view_user.php', array('id' => $id, 'tab' => 'address'));
$submit_url = new moodle_url('view_user.php', array('id' => $id, 'tab' => 'address', 'op' => 'edit'));

$mform = new personal_address_form($submit_url->out(false), array('uid' => $id));
if ($mform->is_cancelled()) 
{
    redirect($return_url->out(false));
} 
else if ($data = $mform->get_data()) 
{		
	//validate that there is no duplicate
	$data->user_id = $data->uid;
	$duplicate_condition = array(
		'user_id' => $data->user_id,
		'address_type' => $data->address_type,
	);
	$isDuplicate = sis_query_is_duplicate('si_personal_contact', $duplicate_condition, $data->id);
	if(!$isDuplicate) //no duplicate, update it
	{
		$now = time();
		if($data->id == '') //create new
		{
			$data->date_created = $now;
			$data->date_updated = $now;
			$data->institute = sis_get_institute();
			$DB->insert_record('si_personal_contact', $data);	
		}
		else
		{
			$data->date_updated = $now;
			$DB->update_record('si_personal_contact', $data);			
		}
		if($data->address_type == 'primary')
		{
			//check if we need to update the moodle user email
			$m_user = $DB->get_record('user', array('idnumber' => $data->user_id));
			if($m_user)
			{
				if($m_user->email != $data->email_primary) //email has changed
				{
					//make sure that email has no duplicate
					$temp_user = $DB->get_record('user', array('email' => $data->email_primary));
					if(!$temp_user)
					{
						$m_user->email = $data->email_primary;
						$DB->update_record('user', $m_user);
					}
				}
			}
		}
	    redirect($return_url);
	}
	else
		sis_ui_alert(get_string('duplicate_contact', 'local_sis'), 'danger');
	
}

//content code starts here
sis_ui_page_title(get_string('contacts', 'local_sis'));
	
if($operation == 'edit')
{
	if($dataid)
	{
		//get the personal data (false if not created
		$toform = $DB->get_record('si_personal_contact', array('id' => $dataid));
		$mform->set_data($toform);
	}
	$mform->display();
}
else
{
	$toform = $DB->get_record('si_personal_contact', array('user_id' => $id)); //try to get at least one record
	if($toform)
		sis_user_account_show_personal_address($id);
	else
		sis_ui_alert(get_string('no_personal_address', 'local_sis'), 'info', false, false);
}