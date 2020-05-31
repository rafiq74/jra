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

if($operation == 'delete')
{
	$DB->delete_records('si_personal_finance', array('user_id' => $id));	
}
//get the personal data (false if not created
$toform = $DB->get_record('si_personal_finance', array('user_id' => $id));

//put before header so we can redirect
$return_url = new moodle_url('view_user.php', array('id' => $id, 'tab' => 'finance'));
$submit_url = new moodle_url('view_user.php', array('id' => $id, 'op' => 'edit'));
$mform = new finance_info_form($submit_url->out(false), array('uid' => $id, 'self_update' => false));
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
	);
	$isDuplicate = sis_query_is_duplicate('si_personal_finance', $duplicate_condition, $data->id);
	if(!$isDuplicate) //no duplicate, update it
	{
		$now = time();
		//get bank
		$bank = $DB->get_record('si_bank', array('bank_code' => $data->bank_code));
		$data->bank_name = $bank->bank_name;
		$data->bank_name_a = $bank->bank_name_a;
		$data->user_updated = $USER->id;
		$data->date_updated = $now;
		if($data->id == '') //create new
		{
			$data->date_created = $now;
			$data->institute = sis_get_institute();
			$DB->insert_record('si_personal_finance', $data);	
		}
		else
		{
			$DB->update_record('si_personal_finance', $data);			
		}
	    redirect($return_url);
	}
}

//content code starts here
sis_ui_page_title(get_string('financial_info', 'local_sis'));
	
if($toform)
	$mform->set_data($toform);

if($operation == 'edit')
{
	$mform->display();
}
else
{
	if($toform)
		sis_user_account_show_financial_info($toform);
	else
		sis_ui_alert(get_string('no_financial_info', 'local_sis'), 'info', false, false);
}