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
 * @license   http://www.gnu.org/copycenter/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../lib/jra_lib.php'; 
require_once '../../lib/jra_ui_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/admit_modal_action.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admission',
	'subrole' => 'all',
);
jra_access_control($access_rules);

//content starts here
//put before header so we can redirect
$mform = new admit_form();
if ($mform->is_cancelled()) 
{
    redirect($return_url);
} 
 
else if ($data = $mform->get_data()) 
{		
}

$id = $_GET['id'];
$applicant = $DB->get_record('si_applicant', array('id' => $id));
if($applicant)
	$mform->set_data($applicant);
$mform->display();




//jra_ui_alert(get_string('grade_not_found', 'local_jra'), 'danger', '', false);	

