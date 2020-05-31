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
$course_id = optional_param('course_id', '', PARAM_INT); //full or minimal

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
	$duplicate_condition = array(
		'plan_id' => $data->plan_id,
		'course_id' => $data->course_id,
		'course_id_requisite' => $data->course_id_requisite,
	);
	$isDuplicate = sis_query_is_duplicate('si_plan_course_requisite', $duplicate_condition, $data->id);
	if(!$isDuplicate) //no duplicate, update it
	{
		$data->institute = sis_get_institute();
		if($data->requisite_type == 'C') //co-requisite, we have to add the counter co-requisite
		{
			$clo = clone $data;
			$clo->course_id = $data->course_id_requisite;
			$clo->course_id_requisite = $data->course_id;
			$DB->insert_record('si_plan_course_requisite', $clo);	
		}
		$DB->insert_record('si_plan_course_requisite', $data);	
	}
	else //don't update, give warning
	{
		sis_ui_alert(get_string('duplicate_course_requisite', 'local_sis'), 'danger');
	}
}
else if($action == 3 && isset($get_data['course_id']) && $get_data['course_id'] != '') //for now, don't allow update
{
	$data = (object) $get_data; //for updating	
	//we only allow user to change the active or inactive status
	$obj = new stdClass();
	$obj->id = $data->record_id;
	$obj->eff_status = $data->eff_status;
	if($data->requisite_type == 'C') //if it is co-requisite, we have to also update the counter co-requisite
	{
		$rec = $DB->get_record('si_plan_course_requisite', array('id' => $data->record_id));
		$clo = $DB->get_record('si_plan_course_requisite', array(
			'plan_id' => $rec->plan_id, 
			'course_id' => $rec->course_id_requisite,
			'course_id_requisite' => $rec->course_id,
		));
		$clo->eff_status = $data->eff_status;
		$DB->update_record('si_plan_course_requisite', $clo);
		
	}
	$DB->update_record('si_plan_course_requisite', $obj);
}
else if($action == 2) //delete
{
	//if it is co-requisite, we have to delete the counter co as well
	$rec = $DB->get_record('si_plan_course_requisite', array('id' => $get_data['id']));
	if($rec)
	{
		if($rec->requisite_type == C) //delete the counter co
		{
			$clo = $DB->get_record('si_plan_course_requisite', array(
				'program_id' => $rec->program_id,
				'plan_id' => $rec->plan_id,
				'course_id_requisite' => $rec->course_id,
				'requisite_type' => 'C',
			));
			if($clo)
				$DB->delete_records('si_plan_course_requisite', array('id' => $clo->id));	
		}
	}
	$DB->delete_records('si_plan_course_requisite', array('id' => $get_data['id']));	
}

$plan = $DB->get_record('si_plan', array('id' => $id));

$institute = sis_get_institute();

//get the course to show pre-requisite

$where = " where " . sis_query_eff_date('si_plan_course', 'a', array('course_id'), false) . " and a.institute = '$institute' and a.plan_id = '$plan->catalogue_id' and a.course_id = '$course_id'";
$sql = "select a.*, b.code, b.course_num, b.course_name from {si_plan_course} a inner join v_si_course_list b on a.course_id = b.catalogue_id $where order by a.course_level, b.code, b.course_num";

$course = $DB->get_record_sql($sql);
if($course)
{
	if($display == 'minimal')
	{
		sis_ui_box('', '<strong>' . get_string('course') . '</strong> : ' . sis_output_show_course_code($course->code, $course->course_num) . ' - ' . $course->course_name);
	}
	if($display == full)
	{
		$requisite_url = new moodle_url('/local/sis/setting/program/add_plan_requisite.php', array('id' => $id, 'course_id' => $course_id, 'tab' => 'requisite'));
		echo '<span class="pull-right pb-3">' . html_writer::link($requisite_url, sis_ui_icon('link', '1', true) . ' ' . get_string('manage_requisite', 'local_sis'), array('title' => get_string('manage_requisite', 'local_sis'))) . '</span>';
	}
	$where = " where a.plan_id = '$plan->catalogue_id' and a.course_id = '$course_id'";
	$sql = "
	select 
		a.*, c.code, c.course_num, c.course_name, d.course_level 
	from 
		{si_plan_course_requisite} a inner join v_si_course_list b on a.course_id = b.catalogue_id inner join v_si_course_list c on a.course_id_requisite = c.catalogue_id inner join {si_plan_course} d on a.program_id = d.program_id and a.plan_id = d.plan_id and a.course_id_requisite = d.course_id 
	$where 
	order by 
		a.requisite_type, b.code, b.course_num";
	$records = $DB->get_records_sql($sql, array());
	$table = new html_table();
	$table->attributes['class'] = '';
	$table->width = "100%";
	$table->head[] = get_string('requisite', 'local_sis');
	$table->size[] = '15%';
	$table->align[] = 'center';
	$table->head[] = get_string('course', 'local_sis');
	$table->size[] = $display == 'full' ? '60%' : '70%';
	$table->align[] = 'left';
	$table->head[] = get_string('level', 'local_sis');
	$table->size[] = '10%';
	$table->align[] = 'center';
	if($display == 'full')
	{
		$table->head[] = get_string('eff_status', 'local_sis');
		$table->size[] = '10%';
		$table->align[] = 'center';
	}
	$req_type = sis_lookup_requisite(true);
	$table->head[] = 'Action';
	$table->size[] = '5%';	
	$table->align[] = 'center';
	$data = array();
	$count = 1;
	$prevReq = '';
	foreach($records as $record)
	{   	
		if($prevReq != $record->requisite_type)
		{
			$req = $record->requisite_type;
		}
		else
			$req = '';
		$data[] = $req_type[$req];
		$data[] = sis_output_show_course_code($record->code, $record->course_num) . ' - ' . $record->course_name;
		$data[] = $record->course_level;
		if($display == 'full')
		{
			$data[] = sis_output_show_active($record->eff_status);		
		}
	
		$delete_url = "javascript:delete_plan_requisite('$plan->catalogue_id', '$record->id', '$course_id', '$display', '" . get_string('confirm_delete_record', 'local_sis') . "')";
		$update_url = "javascript:update_plan_requisite('$plan->catalogue_id', '$record->id', '$course_id', '$display')";
		$actionStr = html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => get_string('delete_course_requisite', 'local_sis')));
		if($display == 'full') //for now , don't allow update
			$actionStr = $actionStr . '&nbsp;' . html_writer::link($update_url, sis_ui_icon('pencil', '1.5', true), array('title' => get_string('update_course_requisite', 'local_sis')));
		$data[] = $actionStr;
		$table->data[] = $data;
		unset($data);
		$prevReq = $record->requisite_type;
	}
	
	sis_ui_print_table($table);
}
else
	sis_ui_alert(get_string('select_course_requisite', 'local_sis'), 'info', false, false);

//content ends here


