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
require_once $CFG->libdir.'/authlib.php';
require_once($CFG->dirroot.'/user/lib.php');
//require_once '../lib/jra_lib.php'; //
//require_once '../lib/jra_lib.php'; //never allow jra_lib to be here because it will cause cyclic redirection for password redirect
require_once '../lib/jra_ui_lib.php'; 
require('lib.php');
require_once('form.php');

$id     = optional_param('id', SITEID, PARAM_INT); // current course
$return = optional_param('return', 0, PARAM_BOOL); // redirect after password change

$systemcontext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
//$PAGE->https_required();

$urlparams = array('id' => $id);
$PAGE->set_url('/local/jra/user/change_password.php', $urlparams);

$PAGE->set_context($systemcontext);

if ($return) {
    // this redirect prevents security warning because https can not POST to http pages
    if (empty($SESSION->wantsurl)
            or stripos(str_replace('https://', 'http://', $SESSION->wantsurl), str_replace('https://', 'http://', $CFG->wwwroot.'/login/change_password.php')) === 0) {
        $returnto = "$CFG->wwwroot/user/preferences.php?userid=$USER->id&course=$id";
    } else {
        $returnto = $SESSION->wantsurl;
    }
    unset($SESSION->wantsurl);

    redirect($returnto);
}

$strparticipants = get_string('participants');

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

require_login(); //always require login
// require proper login; guest user can not change password
$allowChange = true;
if ($USER->auth != 'db') 
{
	$a_url = new moodle_url($CFG->wwwroot.'/user/editadvanced.php', array('id' => $USER->id, 'course' => 1, 'returnto' => 'profile'));
    redirect($a_url);
}

$PAGE->set_context(context_user::instance($USER->id));
//$PAGE->set_pagelayout('jra'); //cannot use jra template. Has to use maintenance template
$PAGE->set_pagelayout('maintenance'); //set to maintenance where there is no redirect function to avoid circular redirection
$PAGE->set_course($course);

$PAGE->set_title(get_string('brand_name', 'local_jra'));
$PAGE->set_heading(get_string('brand_name', 'local_jra'));
$_SESSION['jra_home_tab'] = 'jra';
$PAGE->navbar->add(get_string('change', 'local_jra') . ' ' . get_string('password', 'local_jra'), new moodle_url('change_password.php'));


$mform = new login_change_password_form();
$mform->set_data(array('id'=>$course->id));

$navlinks = array();
$navlinks[] = array('name' => $strparticipants, 'link' => "$CFG->wwwroot/user/index.php?id=$course->id", 'type' => 'misc');

if ($mform->is_cancelled()) 
{
	if(isset($_SESSION['jra_change_password']) && $_SESSION['jra_change_password'] === true)
	{
		$sesskey = $USER->sesskey;
		$url = new moodle_url($CFG->wwwroot.'/login/logout.php', array('sesskey' => $sesskey));
	    redirect($url);
	}
	else
	    redirect($CFG->wwwroot.'/index.php');
} 
else if ($data = $mform->get_data()) 
{	
	$jra_user = $DB->get_record('jra_user', array('id' => $USER->idnumber));
	if(jra_user_update_password($jra_user, $data->newpassword1)) //if successfully change password
		$_SESSION['jra_change_password'] = false; //cancel the password change session
    if (!empty($CFG->passwordchangelogout)) {
        \core\session\manager::kill_user_sessions($USER->id, session_id());
    }
    // Reset login lockout - we want to prevent any accidental confusion here.
    login_unlock_account($USER);

    // register success changing password
    unset_user_preference('auth_forcepasswordchange', $USER);
    unset_user_preference('create_password', $USER);

    $strpasswordchanged = jra_ui_alert(get_string('passwordchanged'), 'success', '', false, true);

    $fullname = fullname($USER, true);

    $PAGE->set_title($strpasswordchanged);
    $PAGE->set_heading(fullname($USER));
    echo $OUTPUT->header();

    notice($strpasswordchanged, new moodle_url($CFG->wwwroot.'/index.php', array('return'=>1)));

    echo $OUTPUT->footer();
    exit;
}

// make sure we really are on the https page when https login required
//$PAGE->verify_https_required();

$strchangepassword = get_string('changepassword');

$fullname = fullname($USER, true);

$PAGE->set_title($strchangepassword);
$PAGE->set_heading($fullname);
echo $OUTPUT->header();

jra_ui_page_title(get_string('changepassword'));

if($allowChange)
{
	if (get_user_preferences('auth_forcepasswordchange')) {
		echo $OUTPUT->notification(get_string('forcepasswordchangenotice'));
	}
	
	if(isset($_SESSION['jra_change_password']) && $_SESSION['jra_change_password'] === true)
	{
		jra_ui_alert(get_string('need_change_password', 'local_jra'), 'info', 'Note', true, false);
	}
	$mform->display();
}
else
{
	$msg = get_string('cannot_change_password', 'local_jra');
	$url = new moodle_url($CFG->wwwroot . '/index.php');
	$msg = $msg . '<div class="pt-3 text-center">' . jra_ui_button(get_string('continue'), $url) . '</div>';	
	jra_ui_alert($msg, 'danger', 'Note', false, false);		
}
echo $OUTPUT->footer();
