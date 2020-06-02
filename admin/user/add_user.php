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
require_once '../../lib/jra_query_lib.php'; 
require_once '../../user/lib.php'; //user library
require_once 'lib.php';

require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/user/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$id = optional_param('id', false, PARAM_INT);
if($id)
{
	$qs = '?id=' . $id;
	$bc = ['update', 'user'];
}
else
{
	$qs = '';
	$bc = ['add', 'user'];
}
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add('JRA ' . strtolower(get_string('administration')), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['user', 'management']), new moodle_url('index.php'));
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('add_user.php'));

$return_params = jra_get_session('jra_user_return_params');
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
	$isDuplicate = jra_username_duplicate($data);
	if(!$isDuplicate) //no duplicate, update it
	{
		$now = time();
		if($data->id == '') //create new
		{
			//for manual creation, the account is automatically active
			$data->active_status = 'A';
			$data->deleted = 0;
			$data->enable_login = 'Y';
			$data->email = $data->username;
			$var_name = 'system_default_password_custom';
			$pswd = jra_get_config($var_name);
			$data->password = jra_user_password_hash($pswd);
			$data->password_change = 'Y'; //force password to change in the first time login
			
			$data->active_date = $now;
			$data->date_created = $now;
			$data->date_updated = $now;
			$DB->insert_record('jra_user', $data);	
		}
		else
		{
			$data->date_updated = $now;
			$DB->update_record('jra_user', $data);			
			jra_log_data('jra_user', $data); //log the change
		}
	    redirect($return_url);
	}
}

echo $OUTPUT->header();

if($isDuplicate)
	jra_ui_alert(get_string('duplicate_user', 'local_jra'), 'danger');
//content code starts here
jra_ui_page_title(jra_get_string($bc));

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$toform = $DB->get_record('si_user', array('id' => $id));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();

$PAGE->requires->js('/local/jra/user/account/account.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();