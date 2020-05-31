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
require_once '../../lib/sis_lookup_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/program/view_plan.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);
$page = 'view_plan';
$id = required_param('id', PARAM_INT);
$plan = $DB->get_record('si_plan', array('id' => $id));
if(!$plan)
	throw new moodle_exception('Invalid plan id.');
//get the program
$co = new stdClass;
$co->institute = $plan->institute;
$co->catalogue_id = $plan->program_id;
$program = sis_setting_program_get_program($co);

//2nd level data in tabs
//Check if a tab has to be active by default
if(isset($_GET['tab']))
	$tab = $_GET['tab'];	
else
	$tab = '';

if($tab == '' || $tab == 'course_list')
	$bc = get_string('course_list', 'local_sis');
else if($tab == 'requisite')
	$bc = get_string('course_requisite', 'local_sis');
else
	$bc = get_string('academic_rules', 'local_sis');

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('program', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('view', 'local_sis') . ' ' . get_string('academic_plan', 'local_sis'), new moodle_url('view_plan.php', $_GET));
$PAGE->navbar->add($plan->plan . ' : ' . $bc);

sis_set_session('edit_plan_return_url', 'view_plan');

echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('academic_plan','local_sis'));

$currenttab = 'plan'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

$return_params = array('id' => $program->id);
$return_url = new moodle_url('view_program.php', $return_params);

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
//one row of data
$obj = new stdClass();
$obj->title = get_string('plan_name', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')';
$obj->content = $plan->plan_name_a;
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('plan_rule', 'local_sis');
$obj->content = $plan->plan_rule;
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('status', 'local_sis');
$obj->content = sis_output_show_active($plan->eff_status);
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('eff_date', 'local_sis');
$obj->content = date('d-M-Y', $plan->eff_date);
$data[] = $obj;
//end of data row

$course_list_active = 'active';
$requisite_active = '';
$rule_active = '';
if($tab && ($tab == 'course_list' || $tab == 'requisite' || $tab == 'rule'))
{
	$course_list_active = $tab == 'course_list' ? 'active' : '';
	$requisite_active = $tab == 'requisite' ? 'active' : '';
	$rule_active = $tab == 'rule' ? 'active' : '';
}

$get_params = $_GET; //get all the query string
//build url for tab 1
$get_params['tab'] = 'course_list';
$course_list_url = new moodle_url('view_plan.php', $get_params);
//build the content
$tab_content = sis_setting_program_plan_course_list($id, $course_list_active);	
$tab_pages['course_list'] = array(
	'active' => $course_list_active,
	'url' => $course_list_url,
	'title' => get_string('course_list', 'local_sis'),
	'content' => $tab_content,
);

//build url for tab 2
$get_params['tab'] = 'requisite';
$rule_url = new moodle_url('view_plan.php', $get_params);
//build the content
$tab_content = sis_setting_program_plan_requisite($plan->catalogue_id, $plan->id, $requisite_active);	
$tab_pages['requisite'] = array(
	'active' => $requisite_active,
	'url' => $rule_url,
	'title' => get_string('course_requisite', 'local_sis'),
	'content' => $tab_content,
);

/*
//build url for tab 3
$get_params['tab'] = 'rule';
$rule_url = new moodle_url('view_plan.php', $get_params);
//build the content
$tab_content = sis_setting_program_plan_rule($id, $rule_active);	
$tab_pages['rule'] = array(
	'active' => $rule_active,
	'url' => $rule_url,
	'title' => get_string('academic_rules', 'local_sis'),
	'content' => $tab_content,
);
*/
$content = sis_ui_tab($tab_pages);

//one row of data
$obj = new stdClass();
$obj->title = '';
$obj->content = $content;
$obj->full = true; //use full width
$data[] = $obj;
//end of data row

//creeate the page navigator
$get_params = array('tab' => $tab);
$records = $DB->get_records('si_plan', array('catalogue_id' => $plan->catalogue_id), 'eff_date desc');
$old_plan = '<span class="pull-right">' . sis_ui_record_navigator($records, $id, 'view_plan.php', 'id', $get_params) . '</span>';

$str = sis_ui_data_detail($data);
sis_ui_box($str, get_string('academic_plan_details', 'local_sis') . $old_plan);

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/setting/program/program.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript
echo $OUTPUT->footer();
echo '<script>';
if($course_list_active == 'active')
	echo "show_plan_course('$id', 'full')";
else if($requisite_active)
	echo "show_plan_requisite('$id', '$course_id', 'full')";
echo '</script>';