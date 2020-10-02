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
require_once '../lib/jra_lib.php'; //use the jra_public_lib instead. It has the same replica of functions in jra_lib
require_once '../lib/jra_ui_lib.php'; 
require_once '../lib/jra_lookup_lib.php'; 
require_once '../lib/jra_mail_lib.php'; 
require_once($CFG->dirroot.'/user/lib.php');
require('lib.php');

require('form_public.php'); //for public features (no login), use this

$systemcontext = context_system::instance();

$urlparams = $_GET;
$PAGE->set_url('/local/jra/user/resend_email.php', $urlparams);
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('login'); //set to maintenance where there is no redirect function to avoid circular redirection

$PAGE->set_heading(get_string('brand_name', 'local_jra'));

require_login(); //always require login

//try to obtain the user

$jra_user = $DB->get_record('jra_user', array('id' => $USER->jra_user->id));
if($jra_user)
{
	jra_mail_send_activation_email($jra_user);
	jra_set_session('jra_resend_email', 'sent');
    redirect($CFG->wwwroot.'/index.php');
}

echo $OUTPUT->header();


echo $OUTPUT->footer();
