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
require_once '../../lib/jra_theme_lib.php';
require_once '../../lib/jra_app_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/accept_offer_action.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login

$applicant = jra_app_get_applicant();
if($applicant)
{
	$option = $_POST['option'];
	$data = new stdClass();
	$data->id = $applicant->id;
	$data->acceptance = $option;
	$data->acceptance_date = time();
	
	$DB->update_record('si_applicant', $data);
	
	$complete_status = jra_theme_acceptance_status($data);
	echo $complete_status;	
}
