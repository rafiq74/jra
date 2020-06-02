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
//require_once '../lib/jra_lib.php'; //
//require_once '../lib/jra_lib.php'; //never allow jra_lib to be here because it will cause cyclic redirection for password redirect
require_once '../lib/jra_ui_lib.php'; 
require('lib.php');
require('form.php');

$systemcontext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
//$PAGE->https_required();

$urlparams = array('id' => $id);
$PAGE->set_url('/local/jra/user/forget_password.php', $urlparams);

$PAGE->set_context($systemcontext);

$PAGE->set_pagelayout('maintenance'); //set to maintenance where there is no redirect function to avoid circular redirection
$course = $DB->get_record('course', array('id' => 1));
$PAGE->set_course($course);

$PAGE->set_title(get_string('brand_name', 'local_jra'));
//$PAGE->set_heading(get_string('brand_name', 'local_jra'));

// if you are logged in then you shouldn't be here!
if (isloggedin() and !isguestuser()) {
    redirect($CFG->wwwroot.'/index.php', get_string('loginalready'), 5);
}

$mform = new forget_password_form();
$form_submit = false;
$success = false;
if ($mform->is_cancelled()) 
{
    redirect($CFG->wwwroot.'/index.php');
} 
else if ($data = $mform->get_data()) 
{	
	//try to get the user
	$data->username = strtolower($data->username);
	$m_user = $DB->get_record('user', array('username' => $data->username, 'email' => $data->email, 'deleted' => 0, 'suspended' => 0));
	if($m_user) //found the user, get the jra user
	{
		//check if user allow to change account			
		if($m_user->auth == 'db') //only if it is from external user
		{
			$user = $DB->get_record('jra_user', array('id' => $m_user->idnumber));
			$allow_change = true;
		}
	}
	$form_submit = true;
}

echo $OUTPUT->header();

echo '<h2>' . get_string('brand_name', 'local_jra') . '</h2>';
echo '<hr />';

if(!$form_submit)
{
	jra_ui_alert(get_string('forget_password_instruction', 'local_jra'), 'info', get_string('note', 'local_jra'), false, false);
	echo '<br />';
	$mform->display();
}
else
{
	if($success)
	{
		jra_user_send_password_change_info($m_user, $user);
		jra_ui_alert(get_string('forget_password_reset_sent', 'local_jra'), 'success', get_string('note', 'local_jra'), false, false);
	}
	else
	{
		jra_ui_alert(get_string('forget_password_failed', 'local_jra'), 'danger', get_string('error'), false, false);
	}
	$url = new moodle_url($CFG->wwwroot.'/index.php');
	$str = '<div class="text-center mt-5">' . jra_ui_button(get_string('continue'), $url). '</div>';
	echo $str;
}


echo $OUTPUT->footer();
