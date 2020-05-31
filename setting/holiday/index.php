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
require_once '../../lib/sis_lookup_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/holiday/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);

sis_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

sis_set_session('sis_home_tab', 'setup');
$PAGE->navbar->add(get_string('setup', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'setup')));
$PAGE->navbar->add(get_string('public_holiday', 'local_sis'), new moodle_url('index.php'));

//Any form processing code
if(isset($_POST['delete_id'])) //delete
{
	$DB->delete_records('si_holiday', array('id' => $_POST['delete_id']));
}

$display_option = optional_param('display_option', '', PARAM_TEXT);

if($display_option == '')
	$display_option = sis_get_session('sis_public_holiday_filter'); //try to get from session

if($display_option == '')
	$display_option = 'current';

sis_set_session('sis_public_holiday_filter', $display_option);

if(isset($_GET['semester'])) //if there is aountry from query string, get it
{
	$semester = $_GET['semester'];	
	if(!isset($semester_list[$semester])) //validate that it is a valid country
		$semester = '';
	else
		sis_set_session('student_term_activate_semester', $semester);	
}
else //if not, try to get it from the session
{
	$semester = sis_get_session('student_term_activate_semester');
}
if($semester == '') //no semester, initialize it
{
	$semester = sis_user_preference('user_default_semester');
	if($semester == '')
	{
		foreach($semester_list as $semester => $v)
			break;
	}
	sis_set_session('student_term_activate_semester', $semester);	
}
if(!$semester)
{
	sis_ui_alert(get_string('must_define_semester', 'local_sis'), 'info', $title = 'Note', $close = false);
}


echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('public_holiday','local_sis'));
$currenttab = 'holiday'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

$display_list = array(
	'current' => get_string('current_date', 'local_sis'),				  
	'all' => get_string('all', 'local_sis'),			  
);
$display_url = "javascript:refresh_holiday('".$PAGE->url->out(false)."')";
$master_filter = '<span class="pull-right"><strong>';
$master_filter = $master_filter . get_string('display', 'local_sis') . '</strong>&nbsp;&nbsp;&nbsp;' . sis_ui_select('display_option', $display_list, $display_option, $display_url);
$master_filter = $master_filter . '</span>';


$add_url = new moodle_url('/local/sis/setting/holiday/add_holiday.php', array('action' => '1'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add_holiday', 'local_sis'), array('title' => get_string('add_holiday', 'local_sis'))) . '</div>';

$institute = sis_get_institute();

$sql = "select * from {si_holiday}";
$conditionText = "institute = '$institute'";

$current_time = strtotime(date('d-M-Y', time()));
if($display_option == 'current')
{
	$conditionText = $conditionText . " and holiday_date >= '$current_time'";
	$sort = "holiday_date, holiday";
}
else
{
	$sort = "holiday_date desc, holiday"; //for all, we sort in descending
}
//setup the table options
$options = array(
	'sql' => $sql, //incase if we need to use sql
	'condition' => array(), //and sql condition
	'conditionText' => $conditionText, //and sql textual condition
	'responsive' => true, //responsive table
	'border-table' => false, //make the table bordered
	'condensed-table' => false, //compact table (not applicable under generaltable)
	'hover-table' => false, //make the table hover (not applicable under generaltable)
	'action' => true, //automatic add form and javascript for action edit and delete
	'sortable' => false, //enable clicking of heading to sort
	'default_sort_field' => $sort,
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'holiday', //default field choose for search
	'view_page' => '',
	'edit_page' => 'add_holiday.php',
	'perpage' => sis_global_var('PER_PAGE'), //use large number to remove pagination
	'master_filter' => $master_filter, //primary master filter for master-child relation
	'delete_admin' => false, //only allow to delete if it is siteadmin
//	'debug' => true,
);

//setup the table fields
$fields = array(
	'#' => array(), //# for numbering
	'holiday' => array(
		'header'=>get_string('public_holiday', 'local_sis'), //for custom header
		'align' => 'left',
		'size' => '65%',
//		'disable_search' => true,
	),
	'holiday_date' => array(
		'header'=>get_string('date', 'local_sis'), //for custom header
		'align' => 'left',
		'size' => '20%',
		'format' => 'date',
		'disable_search' => true,		
	),
	'holiday_date_raw' => array(
		'header'=>get_string('date', 'local_sis'), //for custom header
		'visible' => false,
	),
	'*' => array(), //action
);

//output the table
echo sis_ui_dump_table('si_holiday', $options, $fields, 'local_sis');

echo $OUTPUT->box_end();
$PAGE->requires->js('/local/sis/setting/holiday/holiday.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();