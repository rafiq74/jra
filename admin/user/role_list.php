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

define("MAX_USERS_TO_LIST_PER_ROLE", 20);
require_once '../../../../config.php';
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php';
require_once '../../lib/sis_lookup_lib.php';
require_once '../../lib/sis_output_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once 'lib.php'; //local library
require_once($CFG->dirroot . '/local/sis/user/selector/user_selector.php');

$urlparams = $_GET;
$PAGE->set_url('/local/sis/user/account/role_list.php', $urlparams);
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
$PAGE->navbar->add(get_string('roles'), new moodle_url('index.php'));

//Any form processing code
if(isset($_POST['delete_id'])) //only allow site admin to delete
{
	$u = new stdClass();
	$u->id = $_POST['delete_id'];
	$to_remove = array($u);
	sis_user_account_remove_role($to_remove);
}


echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('roles', 'local_sis') . ' ' . get_string('management', 'local_sis'));
$currenttab = 'role'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');
sis_set_session('sis_user_account_role_page', 'role_list');

$role_list = array_merge(array('' => get_string('all_roles', 'local_sis')), sis_get_roles());
$qs = $_GET;
$role = '';
if(isset($_GET['role'])) //if there is aountry from query string, get it
{
	$role = $_GET['role'];	
	if(!isset($role_list[$role])) //validate that it is a valid country
		$role = '';
	else
		sis_set_session('user_account_role', $role);	
}
else //if not, try to get it from the session
{
	$role = sis_get_session('user_account_role');
}

//also set the session for assign role
if($role != '') //only set if it is not all roles
	sis_set_session('ajax_user_role_role', $role);

//create the master filter of program
unset($qs['role']); //have to remove existing program query string
$a_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
$role_url = "javascript:refresh_role_index('".$a_url->out(false)."')";

$add_url = new moodle_url('/local/sis/user/account/role.php', array());
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('window-maximize', '1', true) . ' ' . get_string('role_assignment_view', 'local_sis'), array('title' => get_string('role_assignment_view', 'local_sis'))) . '</div>';


$master_filter = '<span class="pull-right"><strong>' . get_string('role', 'local_sis') . '</strong>&nbsp;&nbsp;&nbsp;' . sis_ui_select('role', $role_list, $role, $role_url) . '</span>';

$sql = "select a.id, a.role, a.subrole, a.role_value, a.date_added, b.appid, b.first_name, b.father_name, b.grandfather_name, b.family_name, c.username from {si_role_user} a inner join v_si_userlogin b on a.user_id = b.user_id inner join {user} c on a.added_by = c.id";
$conditionWhere = " a.institute = '" . sis_get_institute() . "'";
if($role != '')
	$conditionWhere = $conditionWhere . " and role = '$role'";
	
//	$condition['role'] = $role;
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
	'sortable' => false, //enable clicking of heading to sort
	'default_sort_field' => 'a.role, a.subrole, b.appid',
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'appid', //default field choose for search
	'view_page' => '',
	'edit_page' => '',
	'perpage' => sis_global_var('PER_PAGE') * 3, //increase pagination
	'master_filter' => $master_filter, //primary master filter for master-child relation
	'delete_admin' => false, //only allow to delete if it is siteadmin
	'search_reference' => true,
//	'debug' => true,
);
if($role != '')
	$subroles = sis_get_subroles($role);
else
{
	$subroles = array();
	foreach($role_list as $key => $rl)
	{
		$a = sis_get_subroles($key);
		foreach($a as $b => $c)
			$subroles[$b] = $c;
	}
}

//setup the table fields
$fields = array(
	'role' => array(
		'align' => 'left',
		'size' => '10%',
		'format' => 'lookup',
		'lookup_list' => $role_list,
		'disable_search' => true,
		'group' => true,
	),
	'subrole' => array(
		'header' => get_string('operation', 'local_sis'),
		'align' => 'left',
		'size' => '10%',
		'format' => 'lookup',
		'lookup_list' => $subroles,
		'group' => true,
		'show_reference' => true,
	),
	'role_value' => array(
		'header' => get_string('parameter', 'local_sis'),
		'align' => 'left',
		'size' => '10%',
	),
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
	'date_added' => array(
		'align' => 'left',
		'size' => '10%',
		'format' => 'date',
	),
	'username' => array(
		'header' => get_string('added_by', 'local_sis'),
		'align' => 'left',
		'size' => '10%',
	),
	'*' => array(), //action
);

//output the table
echo sis_ui_dump_table('si_role_user_list', $options, $fields, 'local_sis');

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/user/account/account.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();
