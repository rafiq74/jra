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

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/program/plan_course_list_action.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

$id = required_param('plan_id', PARAM_INT);
$display = optional_param('display', 'full', PARAM_TEXT); //full or minimal

//content starts here
$get_data = $_GET;
if(isset($get_data['action']))
	$action = $get_data['action'];
else
	$action = 0;

if($action == 1 && isset($get_data['course_id']) && $get_data['course_id'] != '') //it is an add
{
	$data = (object) $get_data; //for updating	
	$data->id = '';
	if($data->default_date == 1)
	{
		$co = $DB->get_record('si_course', array('id' => $get_data['course_id']));
		$data->eff_date = $co->eff_date;
	}
	else
		$data->eff_date = strtotime($data->eff_date);
	$plan = $DB->get_record('si_plan', array('id' => $data->plan_id));
	if($plan)
	{
		$data->plan_id = $plan->catalogue_id;
		$data->program_id = $plan->program_id;
		$duplicate_condition = array(
			'course_id' => $data->course_id,
			'plan_id' => $data->plan_id,
		);
		$isDuplicate = sis_query_is_duplicate('si_plan_course', $duplicate_condition, $data->id);
		if(!$isDuplicate) //no duplicate, update it
		{
			$data->institute = sis_get_institute();
			if($data->credit == 0) //need to use the default credit from course catalogue
			{
				$c = $DB->get_record('si_course', array('id' => $data->course_id));
				$data->credit = $c->default_credit;
			}
			$DB->insert_record('si_plan_course', $data);	
		}
		else //don't update, give warning
		{
			sis_ui_alert(get_string('duplicate_course_plan', 'local_sis'), 'danger');
		}
	}
}
if($action == 3) //update
{
	$data = (object) $get_data; //for updating	
	$data->id = $data->record_id;
	if($data->credit == 0) //need to use the default credit from course catalogue
	{
		$c = $DB->get_record('si_course', array('id' => $data->course_id));
		$data->credit = $c->default_credit;
	}
	$DB->update_record('si_plan_course', $data);	
}
else if($action == 2) //delete
{
	if(sis_is_system_admin())
	{
		//have to delete all the records of same courses with different effective date
		$rec = $DB->get_record('si_plan_course', array('id' => $get_data['id']));		
		$DB->delete_records('si_plan_course', array('plan_id' => $rec->plan_id, 'course_id' => $rec->course_id));	
	}
}

$plan = $DB->get_record('si_plan', array('id' => $id));

$institute = sis_get_institute();

$where = " where " . sis_query_eff_date('si_plan_course', 'a', array('course_id'), false) . " and a.institute = '$institute' and a.plan_id = '$plan->catalogue_id'";
$sql = "
select 
	a.*, b.course_code, b.code, b.course_num, b.course_name from {si_plan_course} a 
	inner join {si_course} b on a.course_id = b.catalogue_id and b.eff_date = (
		select max(x_ed.eff_date) 
		FROM {si_course} x_ed 
		where 
			b.catalogue_id = x_ed.catalogue_id 	
			and x_ed.eff_date <= a.eff_date 
	)
$where
order by 
	a.course_level, b.code, b.course_num
";

$records = $DB->get_records_sql($sql, array());
$table = new html_table();
$table->attributes['class'] = '';
$table->width = "100%";
$table->head[] = get_string('level', 'local_sis');
$table->size[] = '5%';
$table->align[] = 'center';
$table->head[] = get_string('course', 'local_sis');
$table->size[] = $display == 'full' ? '22%' : '80%';
$table->align[] = 'left';
$table->head[] = get_string('credit', 'local_sis');
$table->size[] = $display == 'full' ? '5%' : '10%';
$table->align[] = 'center';
if($display == 'full')
{
	$table->head[] = get_string('type', 'local_sis');
	$table->size[] = '8%';
	$table->align[] = 'left';
	$table->head[] = get_string('compulsory', 'local_sis');
	$table->size[] = '8%';
	$table->align[] = 'center';
	$table->head[] = get_string('must_pass', 'local_sis');
	$table->size[] = '8%';
	$table->align[] = 'center';
	$table->head[] = get_string('prob_fail', 'local_sis');
	$table->size[] = '8%';
	$table->align[] = 'center';
	$table->head[] = get_string('in_cgpa', 'local_sis');
	$table->size[] = '8%';
	$table->align[] = 'center';
	$table->head[] = get_string('eff_status', 'local_sis');
	$table->size[] = '8%';
	$table->align[] = 'center';
	$table->head[] = get_string('eff_date', 'local_sis');
	$table->size[] = '12%';
	$table->align[] = 'center';
}
$table->head[] = 'Action';
$table->size[] = $display == 'full' ? '10%' : '5%';	
$table->align[] = 'center';
$data = array();
$count = 1;
$prevLevel = '';
$sum_level = 0;
$sum_total = 0;
foreach($records as $record)
{   	
	if($prevLevel != $record->course_level)
	{
		$level = $record->course_level;
		//show the total for the level
		if($prevLevel != '') //not first row
		{
			$data[] = '';
			$data[] = '<strong><span class="pull-right">' . get_string('level_total_credit', 'local_sis') . '</span></strong>';
			$data[] = '<strong>' . $sum_level . '</strong>';
			if($display == 'full')
			{
				$data[] = '';
				$data[] = '';
				$data[] = '';
				$data[] = '';
				$data[] = '';
				$data[] = '';
				$data[] = '';
			}
			$data[] = '';
			$table->data[] = $data;
			unset($data);				
		}
		$sum_level = 0;		
	}
	else
		$level = '';
	$data[] = $level;
	$data[] = sis_output_show_course($record);
	$data[] = $record->credit;
	if($display == 'full')
	{
		$data[] = get_string($record->course_type, 'local_sis');
		$data[] = sis_output_show_yesno($record->compulsory);
		$data[] = sis_output_show_yesno($record->must_pass);
		$data[] = sis_output_show_yesno($record->probation_fail);
		$data[] = sis_output_show_yesno($record->in_cgpa);
		$data[] = sis_output_show_active($record->eff_status);		
		$data[] = sis_output_formal_date($record->eff_date);		
	}

	$delete_url = "javascript:delete_plan_course('$id', '$record->id', '$display', '" . get_string('confirm_delete_record', 'local_sis') . "')";
//	$update_url = "javascript:update_plan_course('$id', '$record->id', '$course_id', '$display')";
	$update_url = new moodle_url('/local/sis/setting/program/update_plan_course.php', array('id' => $id, 'course_id' => $record->id));
	$requisite_url = new moodle_url('/local/sis/setting/program/add_plan_requisite.php', array('id' => $id, 'course_id' => $record->course_id));
	$actionStr = '';
	if(sis_is_system_admin())
		$actionStr = $actionStr . html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => get_string('delete_course', 'local_sis')));
	if($display == 'full')
	{
		$actionStr = $actionStr . '&nbsp;' . html_writer::link($update_url, sis_ui_icon('pencil', '1.5', true), array('title' => get_string('update_course', 'local_sis')));
		$actionStr = $actionStr . '&nbsp;' . html_writer::link($requisite_url, sis_ui_icon('link', '1.5', true), array('title' => get_string('course_requisite', 'local_sis')));
	}
	$data[] = $actionStr;
	$table->data[] = $data;
	unset($data);
	if($record->in_cgpa == 'Y')
	{
		$sum_level = $sum_level + $record->credit;
		$sum_total = $sum_total + $record->credit;
	}
	$prevLevel = $record->course_level;
}
//last round, add the total
if($prevLevel != '') //has some rows
{
	//add the total for the last level
	$data[] = '';
	$data[] = '<strong><span class="pull-right">' . get_string('level_total_credit', 'local_sis') . '</span></strong>';
	$data[] = '<strong>' . $sum_level . '</strong>';
	if($display == 'full')
	{
		$data[] = '';
		$data[] = '';
		$data[] = '';
		$data[] = '';
		$data[] = '';
		$data[] = '';
		$data[] = '';
	}
	$data[] = '';
	$table->data[] = $data;
	unset($data);				
	//add the grand total
	$data[] = '';
	$data[] = '<strong><span class="pull-right">' . get_string('plan_total_credit', 'local_sis') . '</span></strong>';
	$data[] = '<strong>' . $sum_total . '</strong>';
	if($display == 'full')
	{
		$data[] = '';
		$data[] = '';
		$data[] = '';
		$data[] = '';
		$data[] = '';
		$data[] = '';
		$data[] = '';
	}
	$data[] = '';
	$table->data[] = $data;
	unset($data);				
}

sis_ui_print_table($table);

//content ends here


