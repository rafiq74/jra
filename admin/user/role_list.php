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
require_once '../../lib/jra_lib.php'; 
require_once '../../lib/jra_ui_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../lib/jra_output_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once 'lib.php'; //local library
require_once($CFG->dirroot . '/local/jra/user/selector/user_selector.php');

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/user/role_list.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
jra_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['user', 'management']), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('roles'), new moodle_url('role_list.php'));

//Any form processing code
if(isset($_POST['delete_id'])) //only allow site admin to delete
{
	$u = new stdClass();
	$u->id = $_POST['delete_id'];
	$to_remove = array($u);
	jra_admin_user_remove_role($to_remove);
	redirect($PAGE->url->out(false));
}


echo $OUTPUT->header();
//content code starts here
jra_ui_page_title(get_string('roles', 'local_jra') . ' ' . get_string('management', 'local_jra'));
$currenttab = 'role'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('jra_tabbox');
jra_set_session('jra_admin_user_role_page', 'role_list');

$role_list = array_merge(array('' => jra_get_string(['all', 'roles'])), jra_get_roles());
$qs = $_GET;
$role = '';
if(isset($_GET['role'])) //if there is aountry from query string, get it
{
	$role = $_GET['role'];	
	if(!isset($role_list[$role])) //validate that it is a valid country
		$role = '';
	else
		jra_set_session('user_account_role', $role);	
}
else //if not, try to get it from the session
{
	$role = jra_get_session('user_account_role');
}

//also set the session for assign role
if($role != '') //only set if it is not all roles
	jra_set_session('ajax_user_role_role', $role);

//create the master filter of program
unset($qs['role']); //have to remove existing program query string
$a_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
$role_url = "javascript:refresh_role_index('".$a_url->out(false)."')";

$add_url = new moodle_url('role.php', array());
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, jra_ui_icon('window-maximize', '1', true) . ' ' . get_string('role_assignment_view', 'local_jra'), array('title' => get_string('role_assignment_view', 'local_jra'))) . '</div>';


$master_filter = '<span class="pull-right"><strong>' . get_string('role', 'local_jra') . '</strong>&nbsp;&nbsp;&nbsp;' . jra_ui_select('role', $role_list, $role, $role_url) . '</span>';

$sql = "select a.id, a.role, a.subrole, a.role_value, a.added_date, b.username, b.fullname, c.username as added_by from {jra_user_role} a inner join v_jra_userlogin b on a.user_id = b.id inner join {user} c on a.added_user = c.id";
$conditionWhere = " a.country = '" . jra_get_country() . "'";
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
	'default_sort_field' => 'a.role, a.subrole, b.fullname',
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'fullname', //default field choose for search
	'view_page' => '',
	'edit_page' => '',
	'perpage' => jra_global_var('PER_PAGE') * 3, //increase pagination
	'master_filter' => $master_filter, //primary master filter for master-child relation
	'delete_admin' => false, //only allow to delete if it is siteadmin
	'search_reference' => true,
//	'debug' => true,
);
if($role != '')
	$subroles = jra_get_subroles($role);
else
{
	$subroles = array();
	foreach($role_list as $key => $rl)
	{
		$a = jra_get_subroles($key);
		foreach($a as $b => $c)
			$subroles[$b] = $c;
	}
}

//setup the table fields
$fields = array(
	'role' => array(
		'align' => 'left',
		'size' => '15%',
		'format' => 'lookup',
		'lookup_list' => $role_list,
		'disable_search' => true,
		'group' => true,
	),
	'subrole' => array(
		'header' => get_string('operation', 'local_jra'),
		'align' => 'left',
		'size' => '15%',
		'format' => 'lookup',
		'lookup_list' => $subroles,
		'group' => true,
		'show_reference' => true,
	),
	'role_value' => array(
		'header' => get_string('parameter', 'local_jra'),
		'align' => 'left',
		'size' => '10%',
	),
	'username' => array(
		'header' => get_string('username'),
		'align' => 'left',
		'size' => '15%',
		'sort' => '', //indicates that these must be sorted together
//		'format' => 'date',
//		'disable_search' => true,
	),
	'fullname' => array(
		'header'=>get_string('name', 'local_jra'), //for custom header
		'align' => 'left',
		'size' => '20%',
	),
	'added_date' => array(
		'header' => jra_get_string(['added', 'date']),
		'align' => 'left',
		'size' => '10%',
		'format' => 'date',
	),
	'added_by' => array(
		'header' => jra_get_string(['added', 'user']),
		'align' => 'left',
		'size' => '10%',
	),
	'*' => array(), //action
);

//output the table
echo jra_ui_dump_table('jra_user_role_list', $options, $fields, 'local_jra');

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/admin/user/user.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();
