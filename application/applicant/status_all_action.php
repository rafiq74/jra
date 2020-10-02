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
require_once '../../lib/jra_lookup_lib.php'; 
require_once '../../lib/jra_app_lib.php'; 
require_once '../../lib/jra_system_lib.php'; 
require_once '../../lib/jra_mail_lib.php'; 
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/status_all_action.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admission',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$approval_status = $_POST['status'];

$semester = $DB->get_record('si_semester', array('semester' => jra_get_semester()));

$status_list = jra_lookup_admission_status(get_string('all', 'local_jra'));
$status = jra_ui_filter_value('status', '5', 'applicantlist', $status_list, true);
$status_filter = '';
if($status != '')
	$status_filter = " and status = '$status'";
else
	$status_filter = " and status >= " . jra_app_read_only_stage(); //if all, take those that is completed

$aggregate_filter = '';
if($semester->min_aggregate != '')
	$aggregate_filter = " and aggregation >= '$semester->min_aggregate'";

if($semester->num_applicant != '' && $semester->num_applicant != 0)
	$limit = 'limit 0, ' . $semester->num_applicant;
else
	$limit = '';

$city_filter = '';
if($semester->city_filter != '')
	$city_filter = " and address_city >= '$semester->city_filter'";

$sql = "select * from v_si_applicant where institute = '" . jra_get_institute() . "' and semester = '" . $semester->semester . "' and deleted = 0 and acceptance is null $status_filter $aggregate_filter $city_filter order by aggregation desc $limit";

$rec = $DB->get_records_sql($sql);

if($approval_status != 20) //updating status
{
	$arr = array();
	foreach($rec as $r)
	{
		$arr[] = $r->id;
	}
	$inStr = jra_system_implode_instr($arr);
	
	$now = time();
	
	$sql = "update {si_applicant} set status = '$approval_status', status_date = '$now', status_user = '$USER->id' where id in ($inStr)";
	
	$DB->execute($sql);
}
else //sending email
{
	foreach($rec as $r)
	{
		jra_mail_send_notification_email($r);
	}
}
//print_object($inStr);

/*
$return_params = jra_get_session('si_applicant_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);
*/
?>

