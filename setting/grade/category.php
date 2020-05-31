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
require_once '../../lib/sis_lookup_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/grade/category.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);
require_login(); //always require login

$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

//Breadcrumb
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('grade_categories', 'local_sis'), new moodle_url('category.php'));

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

//Any form processing code
if(isset($_POST['delete_id'])) //only allow site admin to delete
{
	//we have to remove any cascading record (even if we don't delete the user physically)
	$cascade = array(
	);
	sis_query_delete_cascade('si_grade_template', $_POST['delete_id'], $cascade);
}


echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('grade_categories','local_sis'));

$add_url = new moodle_url('/local/sis/setting/grade/add_category.php', array());
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add', 'local_sis') . ' ' . get_string('grade_category', 'local_sis'), array('title' => get_string('add', 'local_sis') . ' ' . get_string('grade_category', 'local_sis'))) . '</div>';

$institute = sis_get_institute();
$condition = array(	
	'institute' => $institute,
);

//setup the table options
$options = array(
	'sql' => '', //incase if we need to use sql. Skip the where part and put in under conditionText
	'condition' => $condition, //and sql condition
	'conditionText' => '', //and sql condition in textual format
	'table_class' => 'generaltable', //table class is either generaltable (moodle standard table), or table for plain
	'responsive' => true, //responsive table
	'border-table' => false, //make the table bordered
	'condensed-table' => false, //compact table (not applicable under generaltable)
	'hover-table' => false, //make the table hover (not applicable under generaltable)
	'action' => true, //automatic add form and javascript for action edit and delete
	'sortable' => false, //enable clicking of heading to sort
	'default_sort_field' => 'class_type, sort_order',
	'desc' => false, //if we want to start with descending sort
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'category', //default field choose for search
	'view_page' => '',
	'edit_page' => 'add_category.php',
	'perpage' => '10000', //use large number to remove pagination
	'master_filter' => '', //primary master filter for master-child relation
	'delete_admin' => false, //only allow to delete if it is siteadmin
	'search_reference' => true,
//	'debug' => true,
);
//setup the table fields
$fields = array(
	'class_type' => array(
		'align' => 'left',
		'size' => '15%',
		'sort' => '', //indicates that these must be sorted together
		'group' => true,
		'lang' => true,
//		'disable_search' => true,
	),
	'category' => array(
		'align' => 'left',
		'size' => '15%',
		'disable_search' => true,
	),
	'description' => array(
		'align' => 'left',
		'size' => '15%',
		'disable_search' => true,
	),
	'student_display' => array(
		'header'=>get_string('visible_student', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '15%',
		'format' => 'yesno',
		'show_reference' => true,
	),	
	'is_final_exam' => array(
		'header'=>get_string('final_exam', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '10%',
		'format' => 'yesno',
		'show_reference' => true,
	),
	'eff_status' => array(
		'header'=>get_string('status', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '10%',
		'show_reference' => true,
	),	
	'sort_order' => array(
		'header'=>get_string('order', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '5%',
	),	
	'*' => array(), //action
);

//output the table
echo sis_ui_dump_table('si_grade_template', $options, $fields, 'local_sis');


//content ends here
$PAGE->requires->js('/local/sis/setting/grade/grade.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();