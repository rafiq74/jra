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
require_once($CFG->dirroot.'/user/lib.php');
require('lib.php');
require('form_public.php');

$systemcontext = context_system::instance();

$urlparams = $_GET;
$PAGE->set_url('/local/jra/user/reset_password.php', $urlparams);

$PAGE->set_context($systemcontext);

$PAGE->set_pagelayout('login'); //set to maintenance where there is no redirect function to avoid circular redirection

$PAGE->set_title(get_string('brand_name', 'local_jra'));
//$PAGE->set_heading(get_string('brand_name', 'local_jra'));

// if you are logged in then you shouldn't be here!
if (isloggedin() and !isguestuser()) {
    redirect($CFG->wwwroot.'/index.php', get_string('loginalready'), 5);
}

$token = required_param('token', PARAM_TEXT);
//try to obtain the user
$now = time();
$token_expired = false;
$user = $DB->get_record('jra_user', array('token' => $token));
if($user) //valid user
{
	//make sure the token not expired
	if($user->token_date < $now) //token expired
		$token_expired = true;
}
else
{
	echo $OUTPUT->header();
	throw new moodle_exception(get_string('invalid_token', 'local_jra'));	
}
if($token_expired)
{
	echo $OUTPUT->header();
	throw new moodle_exception(get_string('token_expired', 'local_jra'));	
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
	jra_user_update_password($user, $data->newpassword1);
	$success = true;
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

jra_ui_page_title(get_string('reset_password','local_jra'));

if(!$form_submit)
{
	$mform->display();
}
else
{
	if($success)
	{
		jra_ui_alert(get_string('password_change_success', 'local_jra'), 'success', get_string('note', 'local_jra'), false, false);
	}
	else
	{
		jra_ui_alert(get_string('password_change_failed', 'local_jra'), 'danger', get_string('error'), false, false);
	}
	$url = new moodle_url($CFG->wwwroot.'/index.php');
	$str = '<div class="text-center mt-5">' . jra_ui_button(get_string('continue'), $url). '</div>';
	echo $str;
}

echo '</div>'; //card-body
echo '</div>'; //card-block
echo '</div>'; //end of card
echo '</div>';
echo '</div>';

echo $OUTPUT->footer();
