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
require_once '../../lib/jra_output_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/university/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admission',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['university']), new moodle_url('major.php'));

//Any form processing code
if(isset($_POST['delete_id'])) //only allow site admin to delete
{
	$cascade = array(
		'si_university' => 'id',
	);
	jra_query_delete_cascade('si_university', $_POST['delete_id'], $cascade);
}

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(get_string('university','local_jra'));
$currenttab = 'university'; //change this according to tab
include('tabs.php');

echo $OUTPUT->box_start('jra_tabbox');

$action_item = array();
$action_item[] = array(
	'title' => jra_get_string(['add', 'university']), // - for divider
	'url' => 'add_univerity.php',
	'target' => '', //_blank
	'icon' => 'plus-circle',
);
$action_menu = '<div class="row pull-right pr-3">' . jra_ui_dropdown_menu($action_item, get_string('action', 'local_jra')) . '</div><br /><br />';
//echo $action_menu;

//$condition = array('institute' => jra_get_institute());
//setup the table options
$options = array(
	'sql' => '', //incase if we need to use sql
	//'condition' => $condition, //and sql condition
	'table_class' => 'generaltable', //table class is either generaltable (moodle standard table), or table for plain
	'responsive' => false, //responsive table
	'border-table' => false, //make the table bordered
	'condensed-table' => false, //compact table (not applicable under generaltable)
	'hover-table' => false, //make the table hover (not applicable under generaltable)
	'action' => true, //automatic add form and javascript for action edit and delete
	'sortable' => true, //enable clicking of heading to sort
	'default_sort_field' => 'name',
	'desc' => true, //indicates that the initial sort state is descending
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'name', //default field choose for search
	'view_page' => '',
	//'edit_page' => 'add_university.php',
	'perpage' => jra_global_var('PER_PAGE'), //use large number to remove pagination
	'delete_admin' => true, //only allow to delete if it is siteadmin
//	'debug' => true,
);

//setup the table fields
$fields = array(
	'#' => array(), //# for numbering
	'name' => array(
		'align' => 'center',
		'size' => '10%',
//		'sort' => 'code,course_num', //indicates that these must be sorted together
//		'format' => 'date',
//		'disable_search' => true,
	),
	'*' => array(), //action
);

//output the table
echo jra_ui_dump_table('si_university', $options, $fields, 'local_jra');

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/admin/university/university.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();
