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
//require_once '../lib/sis_lib.php'; //
//require_once '../lib/sis_lib.php'; //never allow sis_lib to be here because it will cause cyclic redirection for password redirect
require_once '../lib/sis_ui_lib.php'; 
require('lib.php');

$id     = optional_param('id', SITEID, PARAM_INT); // current course
$return = optional_param('return', 0, PARAM_BOOL); // redirect after password change

$systemcontext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$urlparams = array('id' => $id);
$PAGE->set_url('/local/sis/user/eula_reject.php', $urlparams);

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
if ($USER->auth != 'db') 
{
	$a_url = new moodle_url($CFG->httpswwwroot.'/index.php', array());
    redirect($a_url);
}
else if($USER->auth == 'db') //check if user allow to change password 
{
	$user = $DB->get_record('si_user', array('id' => $USER->idnumber));
	if($user->eula == 'R')
	{
		$fullname = fullname($USER, true);
	
		$PAGE->set_pagelayout('maintenance'); //set to maintenance where there is no redirect function to avoid circular redirection
		$PAGE->set_title($strpasswordchanged);
		$PAGE->set_heading(fullname($USER));
		echo $OUTPUT->header();
	
		$sesskey = $USER->sesskey;
		$url = new moodle_url($CFG->wwwroot.'/login/logout.php', array('sesskey' => $sesskey));
		notice(get_string('rejected_eula', 'local_sis'), $url);
	
		echo $OUTPUT->footer();
		exit;
	}
	else
	{
		$a_url = new moodle_url($CFG->httpswwwroot.'/index.php', array());
		redirect($a_url);
	}
}