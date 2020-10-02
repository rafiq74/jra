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

require_once '../../../config.php';
require_once '../lib/jra_lib.php'; //
require_once '../lib/jra_ui_lib.php'; 
require_once 'lib.php'; //local library

require_login(); //always require login

$urlparams = $_GET;
$PAGE->set_url('/local/jra/user/profile.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

//set up breadcrumb
$PAGE->navbar->add(jra_get_string(['user', 'profiles']), new moodle_url('profile.php'));
//end of breadcrumb

echo $OUTPUT->header();

//content code starts here

//content code starts here
jra_ui_page_title(jra_get_string(['user', 'profiles']));

$change_password = new moodle_url('change_password.php');

echo jra_ui_button(jra_get_string(['change', 'password']), $change_password);

//for now no need js yet
//$PAGE->requires->js('/local/rcyci/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/jra/user/user.js');
echo $OUTPUT->footer();