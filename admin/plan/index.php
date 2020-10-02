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
require_once '../../lib/jra_output_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/plan/index.php', $urlparams);
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
$PAGE->navbar->add(jra_get_string(['subscription', 'plan']), new moodle_url('index.php'));

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string(['subscription','plan']));

$action_item = array();
$action_item[] = array(
	'title' => jra_get_string(['add', 'new', 'plan']), // - for divider
	'url' => 'add_plan.php',
	'target' => '', //_blank
	'icon' => 'plus-circle',
);
$action_menu = '<div class="row pull-right pr-3">' . jra_ui_dropdown_menu($action_item, get_string('action', 'local_jra')) . '</div><br /><br />';
echo $action_menu;

$country = jra_get_country();
$sql = "select * from {jra_plan} a";
$conditionText = jra_query_eff_date('jra_plan', 'a', array('plan_code'), false) . " and a.country = '$country'"; //effective date

$condition = array();
//setup the table options
$options = array(
	'sql' => $sql, //incase if we need to use sql
	'condition' => $condition, //and sql condition
	'conditionText' => $conditionText, //and sql textual condition
	'table_class' => 'generaltable', //table class is either generaltable (moodle standard table), or table for plain
	'responsive' => true, //responsive table
	'border-table' => false, //make the table bordered
	'condensed-table' => false, //compact table (not applicable under generaltable)
	'hover-table' => false, //make the table hover (not applicable under generaltable)
	'action' => true, //automatic add form and javascript for action edit and delete
	'sortable' => true, //enable clicking of heading to sort
	'default_sort_field' => 'plan_code',
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'plan_code', //default field choose for search
	'edit_page' => 'edit_plan.php',
	'view_page' => 'plan_user.php',
	'perpage' => jra_global_var('PER_PAGE'), //use large number to remove pagination
	'delete_admin' => true, //only allow to delete if it is siteadmin
//	'debug' => true,
);

//setup the table fields
$fields = array(
	'id' => array(
		'header'=>jra_get_string(['plan', 'id']), //for custom header
		'align' => 'left',
		'size' => '5%',
		'sort' => '', //indicates that these must be sorted together
//		'format' => 'date',
//		'disable_search' => true,
	),
	'plan_code' => array(
		'header'=>jra_get_string(['plan', 'code']), //for custom header
		'align' => 'left',
		'size' => '10%',
	),
	'title' => array(
		'align' => 'left',
		'size' => '20%',
	),
	'eff_status' => array(
		'header'=>get_string('status', 'local_jra'), //for custom header
		'align' => 'center',
		'size' => '10%',
	),	
	'eff_date' => array(
		'header'=>get_string('eff_date', 'local_jra'), //for custom header
		'align' => 'center',
		'size' => '10%',
		'format' => 'date',
	),	
	'*' => array(
		'view' => array(
				'icon' => 'user',
				'title' => 'plan_user',
			),
	), //action
);

//output the table
echo jra_ui_dump_table('jra_plan', $options, $fields, 'local_jra');


$PAGE->requires->js('/local/jra/admin/plan/plan.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();