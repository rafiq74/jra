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
 * Change password page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
//require_once '../lib/sis_lib.php'; //
//require_once '../lib/sis_lib.php'; //never allow sis_lib to be here because it will cause cyclic redirection for password redirect
require_once '../lib/sis_ui_lib.php'; 
require_once($CFG->dirroot.'/user/lib.php');
require('lib.php');
require('change_password_form.php');

$systemcontext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$urlparams = $_GET;
$PAGE->set_url('/local/sis/user/reset_password.php', $urlparams);

$PAGE->set_context($systemcontext);

$PAGE->set_pagelayout('maintenance'); //set to maintenance where there is no redirect function to avoid circular redirection
$course = $DB->get_record('si_course', array('id' => 1));
$PAGE->set_course($course);

$PAGE->set_title(get_string('brand_name', 'local_sis'));
//$PAGE->set_heading(get_string('brand_name', 'local_sis'));

// if you are logged in then you shouldn't be here!
if (isloggedin() and !isguestuser()) {
    redirect($CFG->wwwroot.'/index.php', get_string('loginalready'), 5);
}

$token = required_param('token', PARAM_TEXT);
//try to obtain the user
$now = time();
$token_expired = false;
$user = $DB->get_record('si_user', array('token' => $token));
if($user) //valid user
{
	//make sure the token not expired
	if($user->token_date < $now) //token expired
		$token_expired = true;
}
else
{
	echo $OUTPUT->header();
	throw new moodle_exception(get_string('invalid_token', 'local_sis'));	
}
if($token_expired)
{
	echo $OUTPUT->header();
	throw new moodle_exception(get_string('token_expired', 'local_sis'));	
}

$mform = new login_reset_password_form(null, array('id' => $user->id, 'token' => $token));
$form_submit = false;
$success = false;
if ($mform->is_cancelled()) 
{
    redirect($CFG->wwwroot.'/index.php');
} 
else if ($data = $mform->get_data()) 
{
	$m_user = $DB->get_record('user', array('idnumber' => $user->id));
	if($m_user)
	{
		sis_user_update_password($m_user, $data->newpassword1);
		$success = true;
	}
	$form_submit = true;
}

echo $OUTPUT->header();

echo '<h2>' . get_string('brand_name', 'local_sis') . '</h2>';
echo '<hr />';

sis_ui_page_title(get_string('reset_password','local_sis'));

if(!$form_submit)
{
	$mform->display();
}
else
{
	if($success)
	{
		sis_ui_alert(get_string('password_change_success', 'local_sis'), 'success', get_string('note', 'local_sis'), false, false);
	}
	else
	{
		sis_ui_alert(get_string('password_change_failed', 'local_sis'), 'danger', get_string('error'), false, false);
	}
	$url = new moodle_url($CFG->wwwroot.'/index.php');
	$str = '<div class="text-center mt-5">' . sis_ui_button(get_string('continue'), $url). '</div>';
	echo $str;
}


echo $OUTPUT->footer();
