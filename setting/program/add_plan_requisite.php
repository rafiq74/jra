


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
require_once '../../classes/course.php'; 
require_once 'lib.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/program/add_plan_requisite.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

$id = required_param('id', PARAM_INT);
$plan = $DB->get_record('si_plan', array('id' => $id));
if(!$plan)
	throw new moodle_exception('Invalid plan id.');

//get the program
$program = $DB->get_record('si_program', array('id' => $plan->program_id));

//2nd level data in tabs
$tab == 'course_list'; //tab is always course_list
$bc = get_string('add_course', 'local_sis');

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('program', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('plan', 'local_sis'), new moodle_url('plan.php'));
$PAGE->navbar->add(get_string('view', 'local_sis') . ' ' . get_string('academic_plan', 'local_sis'), new moodle_url('view_plan.php', $_GET));
$PAGE->navbar->add($plan->plan . ' : ' . $bc);

$page = 'view_plan';

echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('academic_plan_course_requisite','local_sis'));

$currenttab = 'plan'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

$return_params = $_GET;
$return_url = new moodle_url('view_plan.php', $return_params);

echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, sis_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_sis'), array('title' => get_string('back', 'local_sis'))) . '</div>';

$data = array();

//one row of data
$obj = new stdClass();
$obj->title = get_string('program', 'local_sis');
$obj->content = $program->program . ' - ' . $program->program_name;
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('academic_plan', 'local_sis');
$obj->content = $plan->plan . ' - ' . $plan->plan_name;
$data[] = $obj;
//end of data row
$str = sis_ui_data_detail($data);
sis_ui_box($str, get_string('academic_plan_details', 'local_sis'));

//display the form
$course_id = optional_param('course_id', '', PARAM_INT);
$level = optional_param('level', '', PARAM_INT);
$level_requisite = optional_param('level_requisite', '', PARAM_INT);

$form = sis_setting_program_add_requisite_form($plan->catalogue_id, $course_id, $level, $level_requisite);
$left = sis_ui_box($form, get_string('course', 'local_sis'), '', true);

echo '<br />';
$right = '<div id="ajax-content">';
$right = $right . '';	
$right = $right . '</div>';	
$right = sis_ui_box($right, get_string('requisite', 'local_sis') . ' ' . get_string('course_list', 'local_sis'), false, true);
$str = '';
$str = $str . '<div class="row">';
$str = $str . '	<div class="col-md-6">';
$str = $str . $left;
$str = $str . '	</div>';
$str = $str . '	<div class="col-md-6">';
$str = $str . $right;
$str = $str . '	</div>';
$str = $str . '</div>';

echo $str;

$PAGE->requires->js('/local/sis/setting/program/program.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();

echo '<script>';
echo "show_plan_requisite('$id', '$course_id', 'minimal')";
echo '</script>';