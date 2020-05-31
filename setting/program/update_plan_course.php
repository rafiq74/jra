


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
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/program/update_plan_course.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

$post_data = $_POST;
if(isset($post_data['pid']))
	$id = $post_data['pid'];
else
	$id = required_param('id', PARAM_INT);

if(isset($post_data['id']))
	$plan_course_id = $post_data['id'];
else
	$plan_course_id = required_param('course_id', PARAM_INT);

$plan = $DB->get_record('si_plan', array('id' => $id));
if(!$plan)
	throw new moodle_exception('Invalid plan id.');

//get the program
$program = $DB->get_record('si_program', array('id' => $plan->program_id));

$operation = optional_param('op', '', PARAM_TEXT);

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

$view = optional_param('view', '', PARAM_TEXT);
if($view == '') //try to get from session
{
	$view = sis_get_session('plan_course_edit_view');
	if($view == '')
		$view = 'active';
}
if($view == 'all')
{
	$view_flip = 'active';
}
else
{
	$view_flip = 'all';
}
sis_set_session('plan_course_edit_view', $view);

$return_url = new moodle_url('view_plan.php', array('id' => $id));

$record = $DB->get_record('si_plan_course', array('id' => $plan_course_id));
if($record)
{
	$sql = "select * from v_si_course_list where catalogue_id = '$record->course_id'";
	$req_course = $DB->get_record_sql($sql);
	
	$mform = new plan_course_form(null, array('pid'=>$id));
	
	//no use as form is submitted to grade_scheme_action
	if ($mform->is_cancelled()) 
	{
		redirect($return_url);
	} 
	else if ($post = $mform->get_data()) 
	{	
		if(isset($post->cancel))
		{
			$cancel_params = array('id' => $id, 'course_id' => $plan_course_id);
			$cancel_url = new moodle_url('update_plan_course.php', $cancel_params);
			redirect($cancel_url->out());
		}
		if(isset($post->default_date))
			$post->eff_date = sis_earliest_date();
		$post->program_id = $record->program_id;
		$post->plan_id = $record->plan_id;
		$post->course_id = $record->course_id;
		$max_course = sis_setting_program_get_plan_next_course($post);
		if(!$max_course || $post->eff_date <= $max_course->eff_date)
		{
			if(isset($post->saveasbutton)) //save as, so don't allow equal
			{
				$isDuplicate = true;
			}
			else //it is a correct history
			{
				if($post->eff_date < $max_course->eff_date)
					$isDuplicate = true;
			}
		}
		else
			$isDuplicate = false;

		if(!$isDuplicate) //no duplicate, update it
		{				
			if(isset($post->saveasbutton)) //save as, so always create new
			{
				if($max_course->eff_date != $post->eff_date) //make sure no duplicate effective date
				{
					//initialize the new record with missing field
					$post->id = '';
					$new_id = $DB->insert_record('si_plan_course', $post);	
					$done_params = array('id' => $id, 'course_id' => $new_id);
					$done_url = new moodle_url('update_plan_course.php', $done_params);
					redirect($done_url);
				}
				else
					$same_effective_date = true;
			}
			else
			{
				$DB->update_record('si_plan_course', $post);
				$done_params = array('id' => $id, 'course_id' => $post->id);
				$done_url = new moodle_url('update_plan_course.php', $done_params);
				redirect($done_url);
			}
		}
	}

	$mform->set_data($record);
}
else
	$form = sis_ui_alert(get_string('wrong_parameter', 'local_sis'), 'danger', '', false, true);

echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('academic_plan','local_sis'));

if($isDuplicate)
{
	sis_ui_alert(get_string('less_effective_date', 'local_sis'), 'danger');
	$operation = 'edit';
}
if($same_effective_date)
{
	sis_ui_alert(get_string('same_effective_date', 'local_sis'), 'danger');
	$operation = 'edit';
}

if(isset($mform))
{
	if($operation == 'edit')
		$form = sis_ui_box($mform->render(), get_string('update_course', 'local_sis') . ' : ' . sis_output_show_course($req_course), '', true);
	else
		$form = sis_setting_program_show_course_edit($plan_course_id, $id, $view);
}
$currenttab = 'plan'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

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
$tab_content = $form;	
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
$tab_content = sis_setting_program_plan_requisite($plan->catalogue_id, $id, $requisite_active);	
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
//$records = $DB->get_records('si_plan', array('catalogue_id' => $plan->catalogue_id), 'eff_date desc');
//$old_plan = '<span class="pull-right">' . sis_ui_record_navigator($records, $id, 'view_plan.php') . '</span>';

$str = sis_ui_data_detail($data);
sis_ui_box($str, get_string('academic_plan_details', 'local_sis'));

echo $OUTPUT->box_end();





$PAGE->requires->js('/local/sis/setting/program/program.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();
