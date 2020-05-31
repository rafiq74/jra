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

//get the personal data (false if not created
$toform = $DB->get_record('si_user', array('id' => $id));

//put before header so we can redirect
$return_url = new moodle_url('view_user.php', array('id' => $id, 'tab' => 'account'));
$submit_url = new moodle_url('view_user.php', array('id' => $id, 'tab' => 'account', 'op' => 'edit'));
$mform = new account_info_form($submit_url->out(false), array('uid' => $id));
if ($mform->is_cancelled()) 
{
    redirect($return_url->out(false));
} 
else if ($data = $mform->get_data()) 
{		
	$now = time();
	$data->date_updated = $now;
	$DB->update_record('si_user', $data);			
	redirect($return_url);
}

//content code starts here
sis_ui_page_title(sis_get_string(['student', 'account', 'information']));
	
if($toform)
{
	$mform->set_data($toform);
	if($operation == 'edit')
	{
		$mform->display();
	}
	else
	{
		if($operation == 'reset')
		{			
			$success = sis_user_reset_password($toform->id);
			if($success)
				sis_ui_alert(get_string('password_reset_successful', 'local_sis'), 'success', false, false);
			else
				sis_ui_alert(get_string('password_reset_failed', 'local_sis'), 'danger', false, false);
		}
		else if($operation == 'eula')
		{
			$toform->eula = '';
			$toform->eula_date = null;
			$DB->update_record('si_user', $toform);
		}
		sis_user_account_show_account_info($toform);
	}
}