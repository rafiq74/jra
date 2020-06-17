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
require_once '../../lib/jra_lib.php'; 
require_once '../../lib/jra_ui_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../lib/jra_output_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/user/index.php', $urlparams);
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

$PAGE->navbar->add('JRA ' . strtolower(get_string('administration')), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['user', 'management']), new moodle_url('index.php'));

//Any form processing code
if(jra_is_system_admin() && isset($_POST['delete_id'])) //only allow site admin to delete
{
	//we have to remove any cascading record (even if we don't delete the user physically)
	$cascade = array(
//		'jra_role_user' => 'user_id',
	);
	jra_query_delete_multiple($_POST['delete_id'], $cascade, jra_get_country());
	//we don't delete from db, but we make the deleted field as 2
	$data->id = $_POST['delete_id'];
	$data->date_updated = time();
	$data->deleted = 2;
	$DB->update_record('jra_user', $data);			
    redirect('index.php'); //we redirect to indext again to kill the submit post data
}

echo $OUTPUT->header();
//content code starts here
jra_ui_page_title(get_string('users','local_jra'));
$currenttab = 'user'; //change this according to tab
include('tabs.php');
jra_set_session('jra_user_tab', 'user');
echo $OUTPUT->box_start('jra_tabbox');

$status_list = array_merge(array('all' => get_string('all', 'local_jra')), jra_lookup_is_active());

$user_type_list = array_merge(array('' => get_string('all', 'local_jra')), jra_lookup_user_type());
$qs = $_GET;
//for status
$status = 'A';
if(isset($_GET['status'])) //if there is aountry from query string, get it
{
	$status = $_GET['status'];	
	if(!isset($status_list[$status])) //validate that it is a valid country
		$status = 'A';
	else
		jra_set_session('user_account_status', $status);	
}
else //if not, try to get it from the session
{
	$status = jra_get_session('user_account_status');
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
		jra_set_session('user_account_user_type', $user_type);	
}
else //if not, try to get it from the session
{
	$user_type = jra_get_session('user_account_user_type');
}

//create the master filter of program
unset($qs['status']); //have to remove existing status query string
unset($qs['user_type']); //have to remove existing user type query string
$a_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
$user_type_url = "javascript:refresh_index('".$a_url->out(false)."')";

$action_item = array();
$action_item[] = array(
	'title' => jra_get_string(['add', 'new', 'user']), // - for divider
	'url' => 'add_user.php',
	'target' => '', //_blank
	'icon' => 'user-plus',
);
$action_menu = '<div class="row pull-right pr-3">' . jra_ui_dropdown_menu($action_item, get_string('action', 'local_jra')) . '</div><br /><br />';
echo $action_menu;


$master_filter = '<span class="pull-right"><strong>';
$master_filter = $master_filter . jra_get_string(['user', 'type']) . '</strong>&nbsp;&nbsp;&nbsp;' . jra_ui_select('user_type', $user_type_list, $user_type, $user_type_url);
$master_filter = $master_filter . jra_ui_space(3);
$master_filter = $master_filter . get_string('status', 'local_jra') . '</strong>&nbsp;&nbsp;&nbsp;' . jra_ui_select('status', $status_list, $status, $user_type_url);
$master_filter = $master_filter . '</span>';

$sql = "select * from v_jra_user";
$conditionWhere = " country = '" . jra_get_country() . "' and deleted = 0";

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
	'default_sort_field' => 'fullname',
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'fullname', //default field choose for search
	'view_page' => 'view_user.php',
	'edit_page' => 'add_user.php',
	'perpage' => jra_global_var('PER_PAGE'), //use large number to remove pagination
	'master_filter' => $master_filter, //primary master filter for master-child relation
	'delete_admin' => true, //only allow to delete if it is siteadmin
	'search_reference' => true,
//	'debug' => true,
);
$gender_list = jra_lookup_gender();
//setup the table fields
$fields = array(
	'#' => array(), //# for numbering
	'username' => array(
		'header'=>get_string('username'), //for custom header
		'align' => 'left',
		'size' => '25%',
		'sort' => '', //indicates that these must be sorted together
//		'format' => 'date',
//		'disable_search' => true,
	),
	'fullname' => array(
		'header'=>get_string('name', 'local_jra'), //for custom header
		'align' => 'left',
		'size' => '25%',
	),
	'user_type' => array(
		'header'=>jra_get_string(['user', 'type']), //for custom header
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
		'header'=>get_string('gender', 'local_jra'), //for custom header
		'align' => 'left',
		'size' => '10%',
		'format' => 'lookup',
		'lookup_list' => $gender_list,
		'show_reference' => true,
	),
	'active_status' => array(
		'header'=>get_string('status', 'local_jra'), //for custom header
		'align' => 'center',
		'size' => '5%',
		'show_reference' => true,
	),	
	'*' => array(), //action
);

//output the table
echo jra_ui_dump_table('jra_user', $options, $fields, 'local_jra');

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/admin/user/user.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();