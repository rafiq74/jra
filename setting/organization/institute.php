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
require_once '../../lib/sis_output_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/organization/institute.php', $urlparams);
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

//Any form processing code
if(sis_is_system_admin() && isset($_POST['delete_id'])) //delete
{
//	sis_query_delete_institute($_POST['delete_id']); //delete all the data related to the institute
	$DB->delete_records('si_institute', array('id' => $_POST['delete_id']));	
}

sis_set_session('sis_home_tab', 'setup');
$PAGE->navbar->add(get_string('setup', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'setup')));
$PAGE->navbar->add(get_string('organization', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('institute', 'local_sis'), new moodle_url('institute.php'));
echo $OUTPUT->header();//content code starts here

sis_ui_page_title(get_string('institute','local_sis'));
$currenttab = 'institute'; //change this according to tab
include('tabs.php');

$add_url = new moodle_url('/local/sis/setting/organization/add_institute.php', array('action' => '1'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add_institute', 'local_sis'), array('title' => get_string('add_institute', 'local_sis'))) . '</div>';

echo $OUTPUT->box_start('sis_tabbox');

if(sis_is_system_admin())
{
	$condition = array();
	$edit_page = 'add_institute.php';
}
else
{
	$condition = array('institute' => sis_get_institute());
	$edit_page = '';
}
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
	'default_sort_field' => 'institute',
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'institute', //default field choose for search
	'view_page' => 'view_institute.php',
	'edit_page' => $edit_page,
	'perpage' => sis_global_var('PER_PAGE'), //use large number to remove pagination
	'delete_admin' => true, //only allow to delete if it is siteadmin
//	'debug' => true,
);

//setup the table fields
$fields = array(
	'#' => array(), //# for numbering
	'institute' => array(
		'align' => 'left',
		'size' => '10%',
//		'format' => 'date',
//		'disable_search' => true,
	),
	'institute_name' => array(
		'header'=>get_string('name', 'local_sis') . ' (' . get_string('english', 'local_sis') . ')', //for custom header
		'align' => 'left',
		'size' => '20%',
	),
	'institute_name_a' => array(
		'header' => get_string('name', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')',
		'align' => 'left',
		'size' => '20%',
	),
	'prefix' => array(
		'align' => 'center',
		'size' => '5%',
	),
	'country' => array(
		'align' => 'center',
		'size' => '10%',
		'format' => 'country',
	),	
	'iban_code' => array(
		'align' => 'center',
		'size' => '10%',
	),	
	'currency' => array(
		'align' => 'center',
		'size' => '10%',
	),	
	'*' => array(), //action
);

//output the table
echo sis_ui_dump_table('si_institute', $options, $fields, 'local_sis');

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/setting/organization/organization.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();