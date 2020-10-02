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
require_once '../lib/jra_public_lib.php'; //use the jra_public_lib instead. It has the same replica of functions in jra_lib
require_once '../lib/jra_ui_lib.php'; 
require_once '../lib/jra_lookup_lib.php'; 
require_once '../lib/jra_mail_lib.php'; 
require_once($CFG->dirroot.'/user/lib.php');
require('lib.php');

require('form_public.php'); //for public features (no login), use this

$systemcontext = context_system::instance();

$urlparams = $_GET;
$PAGE->set_url('/local/jra/user/self_registration.php', $urlparams);
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('login'); //set to maintenance where there is no redirect function to avoid circular redirection

$PAGE->set_heading(get_string('brand_name', 'local_jra'));

// if you are logged in then you shouldn't be here!
if (isloggedin() and !isguestuser()) {
    redirect($CFG->wwwroot.'/index.php', get_string('loginalready'), 5);
}

$is_closed = jra_is_closed();
if($is_closed != '')
{
	redirect($CFG->wwwroot);
}
//try to obtain the user
$now = time();
$mform = new self_registration_form(null, array('id' => $user->id, 'token' => $token));
$form_submit = false;
$success = false;
if ($mform->is_cancelled()) 
{
    redirect($CFG->wwwroot.'/index.php');
} 
else if ($data = $mform->get_data()) 
{
	//first check if user already has an account
	$user = $DB->get_record('jra_user', array('username' => $data->username, 'deleted' => '0'));
	if(!$user)
	{
		//for manual creation, the account is automatically active
		$data->active_status = 'P'; //status is pending
		$data->deleted = 0;
		$data->enable_login = 'Y';
		$data->email = $data->username;
		$data->password = jra_user_password_hash($data->newpassword1);
		$data->password_change = 'N'; //force password to change in the first time login
		
		$data->date_created = $now;
		$data->date_updated = $now;
		$id = $DB->insert_record('jra_user', $data);	
		//send activation email
		$jra_user = $DB->get_record('jra_user', array('id' => $id));
		if($jra_user)
		{
			jra_mail_send_activation_email($jra_user);
			$success = true;
		}
		$error_type = 'database';
	}
	else
		$error_type = 'duplicate';
	$form_submit = true;
}

echo $OUTPUT->header();

echo '<div class="row justify-content-center">';
echo '<div class="col-xl-8 col-sm-10 ">';

echo '<div class="card">';
echo '    <div class="card-block">';

echo '<h3 class="card-header text-center">
		<img src="' . $CFG->wwwroot . '/local/jra/images/logo/main_logo.jpg" />
	</h3>';

echo '<div class="card-body">';

jra_ui_page_title(get_string('create_new_account','local_jra'));
if(!$form_submit)
{
	$mform->display();
}
else
{
	if($success)
	{
		jra_ui_alert(get_string('new_account_creation_success', 'local_jra'), 'success', get_string('note', 'local_jra'), false, false);
		$url = new moodle_url($CFG->wwwroot.'/index.php');
		$str = '<div class="text-center mt-5">' . jra_ui_button(get_string('continue'), $url). '</div>';
		echo $str;
	}
	else
	{
		$error_message = get_string('new_account_creation_failed', 'local_jra');
		if($error_type == 'duplicate')
			$error_message = $error_message . '<br >' . get_string('duplicate_email', 'local_jra');
		else
			$error_message = $error_message . '<br >' . get_string('db_error', 'local_jra');
		jra_ui_alert($error_message, 'danger', get_string('error'), false, false);
		$mform->display();
	}
}

echo '</div>'; //card-body
echo '</div>'; //card-block
echo '</div>'; //end of card
echo '</div>';
echo '</div>';

echo $OUTPUT->footer();
