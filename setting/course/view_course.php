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
require_once '../../classes/course.php';
require_once 'lib.php'; //local library
//require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/course/view_course.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);
require_login(); //always require login

$id = required_param('id', PARAM_INT);
$course = $DB->get_record('si_course', array('id' => $id));
if(!$course)
	throw new moodle_exception('Invalid course id.');
	
//2nd level data in tabs
//Check if a tab has to be active by default
if(isset($_GET['tab']))
	$tab = $_GET['tab'];	
else
	$tab = sis_get_session('course_component_tab');

if($tab == '' || $tab == 'component')
	$bc = get_string('course_component', 'local_sis');
else
	$bc = get_string('course_equivalent', 'local_sis');
//Breadcrumb
sis_set_session('sis_home_tab', 'academic');
sis_set_session('course_component_tab', $tab);
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('course'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('view', 'local_sis') . ' ' . get_string('course'), new moodle_url('view_course.php', $_GET));
$PAGE->navbar->add($course->course_code . ' : ' . $bc);

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());


echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('course_catalogue','local_sis'));

$currenttab = 'course_catalogue'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

$return_params = sis_get_session('si_course_return_params', $_GET);
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);

echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, sis_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_sis'), array('title' => get_string('back', 'local_sis'))) . '</div>';

$data = array();
//one row of data
$obj = new stdClass();
$obj->title = get_string('course_code', 'local_sis');
$obj->content = $course->course_code;
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('course_name', 'local_sis');
$obj->content = $course->course_name;
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('default_credit', 'local_sis');
$obj->content = $course->default_credit;
$data[] = $obj;
//end of data row
/*
//one row of data
$obj = new stdClass();
$obj->title = get_string('status', 'local_sis');
$obj->content = sis_output_show_active($course->course->eff_status);
$data[] = $obj;
//end of data row
*/
//one row of data
$obj = new stdClass();
$obj->title = get_string('eff_date', 'local_sis');
$obj->content = date('d-M-Y', $course->eff_date);
$data[] = $obj;
//end of data row

$component_active = 'active';
$equivalent_active = '';
if($tab && ($tab == 'component' || $tab == 'equivalent'))
{
	$component_active = $tab == 'component' ? 'active' : '';
	$equivalent_active = $tab == 'equivalent' ? 'active' : '';
}

$get_params = $_GET; //get all the query string
//build url for tab 1
$get_params['tab'] = 'component';
$component_url = new moodle_url('view_course.php', $get_params);
//build the content
$tab_content = sis_setting_course_component_content($id, $component_active);	
$tab_pages['component'] = array(
	'active' => $component_active,
	'url' => $component_url,
	'title' => get_string('course_component', 'local_sis'),
	'content' => $tab_content,
);

//build url for tab 2
$get_params['tab'] = 'equivalent';
$equivalent_url = new moodle_url('view_course.php', $get_params);
//build the content
$tab_content = sis_setting_course_equivalent_content($id, $equivalent_active);	
$tab_pages['equivalent'] = array(
	'active' => $equivalent_active,
	'url' => $equivalent_url,
	'title' => get_string('course_equivalent', 'local_sis'),
	'content' => $tab_content,
);

$content = sis_ui_tab($tab_pages);

//one row of data
$obj = new stdClass();
$obj->title = '';
$obj->content = $content;
$obj->full = true; //use full width
$data[] = $obj;
//end of data row

$str = sis_ui_data_detail($data);

//creeate the page navigator
$records = $DB->get_records('si_course', array('catalogue_id' => $course->catalogue_id), 'eff_date desc');
$old_courses = '<span class="pull-right">' . sis_ui_record_navigator($records, $id, 'view_course.php') . '</span>';
sis_ui_box($str, get_string('course_catalogue_detail', 'local_sis') . $old_courses);

echo $OUTPUT->box_end(); //1st level tab

//content ends here
$PAGE->requires->js('/local/sis/setting/course/course.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();
echo '<script>';
if($component_active != '')
	echo 'show_course_component(' . $id . ')';
if($equivalent_active != '')
	echo 'show_course_equivalent(' . $id . ')';
echo '</script>';