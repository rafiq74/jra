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
require_once '../../lib/sis_output_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once 'lib.php'; //local library
//require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/course/index.php', $urlparams);
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
$PAGE->navbar->add(get_string('course'), new moodle_url('index.php'));

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

//Any form processing code
if(sis_is_system_admin() && isset($_POST['delete_id'])) //only allow site admin to delete
{
	/*
	$cascade = array(
		'si_course_component' => 'course_id',
		'si_course_equivalent' => 'course_id',
		'si_plan_course' => 'course_id',
	);
	sis_query_delete_cascade('si_course', $_POST['delete_id'], $cascade);
	*/
	//we never delete a course, but we set the deleted = 1
	//first we retrieve the catalogue_id
	$co = $DB->get_record('si_course', array('id' => $_POST['delete_id']));
	if($co)
	{
		$re = $DB->get_records('si_course', array('catalogue_id' => $co->catalogue_id));
		foreach($re as $r)
		{
			$r->deleted = 1;
			$DB->update_record('si_course', $r);
		}
	}
}

echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('course_catalogue','local_sis'));

$currenttab = 'course_catalogue'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

$add_url = new moodle_url('/local/sis/setting/course/add_course.php', array('action' => '1'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add_course', 'local_sis'), array('title' => get_string('add_course', 'local_sis'))) . '</div>';

$institute = sis_get_institute();

$sql = "select * from {si_course} a";
$conditionText = sis_query_eff_date('si_course', 'a', array('catalogue_id'), true) . " and a.institute = '$institute' and a.deleted = 0"; //effective date

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
	'default_sort_field' => 'course_code',
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'course_code', //default field choose for search
	'view_page' => 'view_course.php',
	'edit_page' => 'edit_course.php',
	'perpage' => sis_global_var('PER_PAGE'), //use large number to remove pagination
	'delete_admin' => true, //only allow to delete if it is siteadmin
//	'debug' => true,
);

//setup the table fields
$fields = array(
//	'#' => array(), //# for numbering
	'catalogue_id' => array(
		'header'=>get_string('course_id', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '10%',
	),
	'course_code' => array(
		'header'=>get_string('course_code', 'local_sis'), //for custom header
		'align' => 'left',
		'size' => '15%',
	),
	'course_name' => array(
		'header'=>get_string('name'), //for custom header
		'align' => 'left',
		'size' => '30%',
	),
	'default_credit' => array(
		'header'=>get_string('credit', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '10%',
	),	
	'eff_status' => array(
		'header'=>get_string('status', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '10%',
	),	
	'eff_date' => array(
		'header'=>get_string('eff_date', 'local_sis'), //for custom header
		'align' => 'center',
		'size' => '15%',
		'format' => 'date',
	),	
	'*' => array(), //action
);

//output the table
echo sis_ui_dump_table('si_course', $options, $fields, 'local_sis');


echo $OUTPUT->box_end();

//content ends here
$PAGE->requires->js('/local/sis/setting/course/course.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();