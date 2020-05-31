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

require_once '../../../../config.php';
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php';
require_once '../../lib/sis_lookup_lib.php';
require_once '../../lib/sis_output_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/user/account/index.php', $urlparams);
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
sis_set_session('sis_home_tab', 'system');
$PAGE->navbar->add(get_string('system', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'system')));
$PAGE->navbar->add(get_string('users', 'local_sis'), new moodle_url('index.php'));

//Any form processing code
if(sis_is_system_admin() && isset($_POST['delete_id'])) //only allow site admin to delete
{
	//we have to remove any cascading record (even if we don't delete the user physically)
	$cascade = array(
		'si_role_user' => 'user_id',
	);
	sis_query_delete_multiple($_POST['delete_id'], $cascade, sis_get_institute());
	//we don't delete from db, but we make the deleted field as 2
	$data->id = $_POST['delete_id'];
	$data->date_updated = time();
	$data->deleted = 2;
	$DB->update_record('si_user', $data);			
}

echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('users','local_sis'));
$currenttab = 'user'; //change this according to tab
include('tabs.php');
sis_set_session('sis_user_tab', 'user');
echo $OUTPUT->box_start('sis_tabbox');

$status_list = array_merge(array('all' => get_string('all', 'local_sis')), sis_lookup_isactive());

$user_type_list = array_merge(array('' => get_string('all', 'local_sis')), sis_lookup_user_type());
$qs = $_GET;
//for status
$status = 'A';
if(isset($_GET['status'])) //if there is aountry from query string, get it
{
	$status = $_GET['status'];	
	if(!isset($status_list[$status])) //validate that it is a valid country
		$status = 'A';
	else
		sis_set_session('user_account_status', $status);	
}
else //if not, try to get it from the session
{
	$status = sis_get_session('user_account_status');
}
if($status == '')
	$status = 'A';
	
//for user type
$user_type = '';
if(isset($_GET['user_type'])) //if there is aountry from query string, get it
{
	$user_type = $_GET['user_type'];	
	if(!isset($user_type_list[$user_type])) //validate that it is a valid country
		$user_type = '';
	else
		sis_set_session('user_account_user_type', $user_type);	
}
else //if not, try to get it from the session
{
	$user_type = sis_get_session('user_account_user_type');
}

//create the master filter of program
unset($qs['status']); //have to remove existing status query string
unset($qs['user_type']); //have to remove existing user type query string
$a_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
$user_type_url = "javascript:refresh_index('".$a_url->out(false)."')";

$add_url = new moodle_url('/local/sis/user/account/add_user.php', array());
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add_user', 'local_sis'), array('title' => get_string('add_user', 'local_sis'))) . '</div>';

$master_filter = '<span class="pull-right"><strong>';
$master_filter = $master_filter . get_string('user_type', 'local_sis') . '</strong>&nbsp;&nbsp;&nbsp;' . sis_ui_select('user_type', $user_type_list, $user_type, $user_type_url);
$master_filter = $master_filter . sis_ui_space(3);
$master_filter = $master_filter . get_string('status', 'local_sis') . '</strong>&nbsp;&nbsp;&nbsp;' . sis_ui_select('status', $status_list, $status, $user_type_url);
$master_filter = $master_filter . '</span>';

$sql = "select * from {si_user}";
$conditionWhere = " institute = '" . sis_get_institute() . "' and deleted <= 1";
if($status != 'all')
	$conditionWhere = $conditionWhere . " and eff_status = '$status'";
if($user_type != '')
	$conditionWhere = $conditionWhere . " and user_type = '$user_type'";

//	$condition['user_type'] = $user_type;
//setup the table options
$options = array(
	'sql' => $sql, //incase if we need to use sql. Skip the where part and put in under conditionText
	'condition' => array(), //and sql condition
	'conditionText' => $conditionWhere, //and sql condition in textual format
	'table_class' => 'generaltable', //table class is either generaltable (moodle standard table), or table for plain
	'responsive' => true, //responsive table
	'border-table' => false, //make the table bordered
	'condensed-table' => false, //compact table (not applicable under generaltable)
	'hover-table' => false, //make the table hover (not applicable under generaltable)
	'action' => true, //automatic add form and javascript for action edit and delete
	'sortable' => true, //enable clicking of heading to sort
	'default_sort_field' => 'appid',
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'appid', //default field choose for search
	'view_page' => 'view_user.php',
	'edit_page' => 'add_user.php',
	'perpage' => sis_global_var('PER_PAGE'), //use large number to remove pagination
	'master_filter' => $master_filter, //primary master filter for master-child relation
	'delete_admin' => true, //only allow to delete if it is siteadmin
	'search_reference' => true,
//	'debug' => true,
);
$gender_list = sis_lookup_gender();
//setup the table fields
$fields = array(
	'#' => array(), //# for numbering
	'appid' => array(
		'align' => 'left',
		'size' => '10%',
		'sort' => '', //indicates that these must be sorted together
//		'format' => 'date',
//		'disable_search' => true,
	),
	'name' => array(
		'header'=>get_string('name', 'local_sis'), //for custom header
		'align' => 'left',
		'size' => '30%',
		'format' => 'combine',
		'combine' => array('first_name', 'father_name', 'grandfather_name', 'family_name'),
		'disable_search' => true,
	),
	'first_name' => array( //for search purpose
		'visible' => false,
	),
	'father_name' => array( //for search purpose
		'visible' => false,
	),
	'grandfather_name' => array( //for search purpose
		'visible' => false,
	),
	'family_name' => array( //for search purpose
		'visible' => false,
	),
	'national_id' => array(
		'header'=>get_string('civil_id', 'local_sis'), //for custom header
		'align' => 'left',
		'size' => '10%',
	),
	'user_type' => array(
		'align' => 'left',
		'size' => '10%',
		'format' => 'lookup',
		'lookup_list' => $user_type_list,
	),
	'enable_login' => array(
		'header'=>get_string('login'), //for custom header
		'align' => 'left',
		'size' => '10%',
		'format' => 'yesno',
		'show_reference' => true,
	),
	'gender' => array(
		'header'=>get_string('gender', 'local_sis'), //for custom header
		'align' => 'left',
		'size' => '10%',
		'format' => 'lookup',
		'lookup_list' => $gender_list,
		'show_reference' => true,
	),
	'eff_status' => array(
		'header'=>get_string('status', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '5%',
		'show_reference' => true,
	),	
	'*' => array(), //action
);

//output the table
echo sis_ui_dump_table('si_user', $options, $fields, 'local_sis');

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/user/account/account.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();