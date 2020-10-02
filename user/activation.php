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

$systemcontext = context_system::instance();

$urlparams = $_GET;
$PAGE->set_url('/local/jra/user/activation.php', $urlparams);
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('login'); //set to maintenance where there is no redirect function to avoid circular redirection

$PAGE->set_heading(get_string('brand_name', 'local_jra'));

// if you are logged in then you shouldn't be here!
//if (isloggedin() and !isguestuser()) {
//    redirect($CFG->wwwroot.'/index.php', get_string('loginalready'), 5);
//}

$token = required_param('token', PARAM_TEXT);

//try to obtain the user
//try to obtain the user
$now = time();
$success = false;
$user = $DB->get_record('jra_user', array('token' => $token));
if($user) //valid user
{
	//For activation, token never expire
	$user->token = null;
	$user->token_date = null;
	$user->active_status = 'A';
	$user->active_date = $now;
	$DB->update_record('jra_user', $user);
	//retrieve back the user and update the session
	$user = $DB->get_record('jra_user', array('id' => $user->id));
	$USER->jra_user = $user;
	$success = true;
}
else
{
	echo $OUTPUT->header();
	throw new moodle_exception(get_string('invalid_activation_code', 'local_jra'));	
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

if($success)
{
	jra_ui_alert(get_string('account_activation_success', 'local_jra', $user->username), 'success', get_string('note', 'local_jra'), false, false);
}
else
{
	$error_message = get_string('account_activation_failed', 'local_jra');
	jra_ui_alert($error_message, 'danger', get_string('error'), false, false);
}
$url = new moodle_url($CFG->wwwroot.'/index.php');
$str = '<div class="text-center mt-5">' . jra_ui_button(get_string('continue'), $url). '</div>';
echo $str;

echo '</div>'; //card-body
echo '</div>'; //card-block
echo '</div>'; //end of card
echo '</div>';
echo '</div>';

echo $OUTPUT->footer();
