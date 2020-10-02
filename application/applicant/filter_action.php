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
require_once '../../lib/jra_app_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/filter_action.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admission',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$id = $_POST['id'];
$secondary = $_POST['secondary'];
$tahseli = $_POST['tahseli'];
$qudorat = $_POST['qudorat'];
$aggregate = $_POST['aggregate'];
$applicant = $_POST['applicant'];
$city_filter = $_POST['city_filter'];

$need_recompute = false;

$sem = $DB->get_record('si_semester', array('id' => $id));
if($sem->secondary_weight != $secondary)
	$need_recompute = true;
if($sem->tahseli_weight != $tahseli)
	$need_recompute = true;
if($sem->qudorat_weight != $qudorat)
	$need_recompute = true;
$data = new stdClass();
$data->id = $id;
$data->secondary_weight = $secondary;
$data->tahseli_weight = $tahseli;
$data->qudorat_weight = $qudorat;
$data->city_filter = $city_filter;
if($aggregate != '')
	$data->min_aggregate = $aggregate;
else
	$data->min_aggregate = null;
if($applicant != '')
	$data->num_applicant = $applicant;
else
	$data->num_applicant = null;

$DB->update_record('si_semester', $data);

if($need_recompute)
	jra_app_recompute_aggregate();

/*
$return_params = jra_get_session('si_applicant_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);
*/
?>

