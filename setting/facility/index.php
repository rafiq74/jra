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
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once '../../lib/sis_output_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/facility/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);
require_login(); //always require login

$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);

sis_access_control($access_rules);

//Breadcrumb
sis_set_session('sis_home_tab', 'setup');
$PAGE->navbar->add(get_string('setup', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'setup')));
$PAGE->navbar->add(get_string('facility', 'local_sis'), new moodle_url('index.php'));

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

//Any form processing code
if(sis_is_system_admin() && isset($_POST['delete_id'])) //delete
{
	$DB->delete_records('si_room', array('id' => $_POST['delete_id']));
}

$usage_list = sis_lookup_get_list('facility', 'usage', '', true, false, sis_output_select_all_text());
$qs = $_GET;
if(isset($_GET['usage'])) //if there is aountry from query string, get it
{
	$usage = $_GET['usage'];	
	if(!isset($usage_list[$usage])) //validate that it is a valid country
		$usage = '';
}
if($usage == '') //if not, try to get it from the session
{
	$usage = sis_get_session('setting_facility_room_usage');
}
sis_set_session('setting_facility_room_usage', $usage);	


echo $OUTPUT->header();
//content code starts here

sis_ui_page_title(get_string('room','local_sis'));
$currenttab = 'room'; //change this according to tab
include('tabs.php');

echo $OUTPUT->box_start('sis_tabbox');


//create the master filter for usage
unset($qs['usage']); //have to remove existing program query string
$a_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
$usage_url = "javascript:refresh_room('".$a_url->out(false)."')";

$master_filter = $program_edit_link . '<span class="pull-right"><strong>' . get_string('usage', 'local_sis') . '</strong>&nbsp;&nbsp;&nbsp;' . sis_ui_select('usage', $usage_list, $usage, $usage_url) . '</span>';

$add_url = new moodle_url('/local/sis/setting/facility/add_room.php', array('action' => '1'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' Add Room', array('title' => 'Add Room')) . '</div>';

$condition = array('institute' => sis_get_institute());
if($usage != '')
	$condition['room_usage'] = $usage;
//setup the table options
$options = array(
	'sql' => '', //incase if we need to use sql
	'condition' => $condition, //and sql condition
	'table_class' => 'generaltable', //table class is either generaltable (moodle standard table), or table for plain
	'responsive' => true, //responsive table
	'border-table' => false, //make the table bordered
	'condensed-table' => false, //compact table (not applicable under generaltable)
	'hover-table' => false, //make the table hover (not applicable under generaltable)
	'action' => true, //automatic add form and javascript for action edit and delete
	'sortable' => true, //enable clicking of heading to sort
	'default_sort_field' => 'room',
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'room', //default field choose for search
	'view_page' => '',
	'edit_page' => 'add_room.php',
	'master_filter' => $master_filter, //primary master filter for master-child relation
	'perpage' => sis_global_var('PER_PAGE'), //use large number to remove pagination
	'delete_admin' => true, //only allow to delete if it is siteadmin
//	'debug' => true,
);
//setup the table fields
$fields = array(
	'#' => array(), //# for numbering
	'room' => array(
		'header'=>get_string('code', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '10%',
//		'format' => 'date',
//		'disable_search' => true,
	),
	'room_name' => array(
		'header'=>get_string('name'), //for custom header
		'align' => 'center',
		'size' => '10%',
	),
	'room_type' => array(
		'align' => 'left',
		'size' => '15%',
		'lang' => true,
	),
	/*
	'room_usage' => array(
		'header'=>get_string('usage', 'local_sis'), //for custom header
		'align' => 'left',
		'size' => '10%',
		'lang' => true,
	),
	*/
	'capacity' => array(
		'align' => 'center',
		'size' => '5%',
	),
	'building' => array(
		'align' => 'left',
		'size' => '10%',
	),	
	'gender' => array(
		'align' => 'center',
		'size' => '5%',
	),	
	'exam_use' => array(
		'align' => 'center',
		'size' => '10%',
		'format' => 'yesno',
	),	
	'campus' => array(
		'align' => 'center',
		'size' => '10%',
	),	
	'eff_status' => array(
		'header'=>get_string('status', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '10%',
	),	
	'*' => array(), //action
);

//output the table
echo sis_ui_dump_table('si_room', $options, $fields, 'local_sis');

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/setting/facility/facility.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();