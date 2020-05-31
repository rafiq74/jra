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
$PAGE->set_url('/local/sis/setting/course/course_component_action.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

$id = required_param('id', PARAM_INT);
//content starts here
$get_data = $_GET;
if(isset($get_data['action']))
	$action = $get_data['action'];
else
	$action = 0;
if($action == 1 && isset($get_data['ct']) && $get_data['cancel'] == '') //it is an add if there is a class type and user not cancel
{
	$dataid = $get_data['did']; //for updating	
	$data->id = $dataid;
	$data->course_id = $get_data['cid'];
	$data->class_type = $get_data['ct'];
	$data->default_section_size = $get_data['dss'];
	$data->contact_hour_week = $get_data['chw'];
	$data->contact_hour_class = $get_data['chc'];
	$data->final_exam = $get_data['fe'];
	$data->main_component = $get_data['mcc'];
	$data->lms_course_creation = $get_data['lms'];
	$data->teacher_workload_weight = $get_data['tww'];
	$data->room_type = $get_data['rt'];
	$data->label = $get_data['label'];
	$data->institute = sis_get_institute();
	//make sure no duplicate
	$duplicate_condition = array(
		'course_id' => $data->course_id,
		'class_type' => $data->class_type,
	);
	$isDuplicate = sis_query_is_duplicate('si_course_component', $duplicate_condition, $dataid);
	if(!$isDuplicate) //no duplicate, update it
	{
		if($data->id == '') //create new
			$DB->insert_record('si_course_component', $data);	
		else
			$DB->update_record('si_course_component', $data);			
	}
	else //don't update, give warning
	{
		sis_ui_alert(get_string('duplicate_course_component', 'local_sis'), 'danger');
	}
	$id = $data->course_id; //if there is submit, we need to reinitialize the id
}
else if($action == 2 && isset($get_data['dataid']) && $get_data['dataid'] != '') //delete
{
	$DB->delete_records('si_course_component', array('id' => $get_data['dataid']));	
}
if(isset($get_data['cancel']) && $get_data['cancel'] == 1)
	$id = $get_data['cid']; //if there is submit, we need to reinitialize the id even if it is a cancel

$where = " where course_id = '$id'"; //initialize a where clause with institute
$sql = "select * from {si_course_component} $where order by class_type";
$records = $DB->get_records_sql($sql, array());
$table = new html_table();
$table->attributes['class'] = '';
$table->width = "100%";
$table->head[] = get_string('class_type', 'local_sis');
$table->size[] = '15%';
$table->align[] = 'left';
$table->head[] = get_string('main_com', 'local_sis');
$table->size[] = '10%';
$table->align[] = 'center';
$table->head[] = get_string('room_type', 'local_sis');
$table->size[] = '15%';
$table->align[] = 'left';
$table->head[] = get_string('default_size', 'local_sis');
$table->size[] = '10%';
$table->align[] = 'center';
$table->head[] = get_string('hour_per_week', 'local_sis');
$table->size[] = '10%';	
$table->align[] = 'center';
$table->head[] = get_string('hour_per_class', 'local_sis');
$table->size[] = '10%';	
$table->align[] = 'center';
$table->head[] = get_string('workload_weight', 'local_sis');
$table->size[] = '10%';	
$table->align[] = 'center';
$table->head[] = get_string('has_final_exam', 'local_sis');
$table->size[] = '10%';	
$table->align[] = 'center';
$table->head[] = 'Action';
$table->size[] = '10%';	
$table->align[] = 'center';
$data = array();
foreach($records as $record)
{   
	
	$data[] = get_string($record->class_type, 'local_sis');
	$data[] = sis_output_show_yesno($record->main_component);
	$data[] = get_string($record->room_type, 'local_sis');
	$data[] = $record->default_section_size;
	$data[] = $record->contact_hour_week;
	$data[] = $record->contact_hour_class;
	$data[] = $record->teacher_workload_weight;
	$data[] = sis_output_show_yesno($record->final_exam);
	


	$delete_url = "javascript:delete_course_component('$id', '$record->id', '" . get_string('confirm_delete_record', 'local_sis') . "')";
	$update_url = "javascript:update_course_component('$id', '$record->id')";
	
	$data[] = html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => get_string('delete_course_component', 'local_sis'))) . '&nbsp;' . 
			  html_writer::link($update_url, sis_ui_icon('pencil', '1.5', true), array('title' => get_string('update_course_component', 'local_sis')));
	$table->data[] = $data;
	unset($data);				
}

sis_ui_print_table($table);

//content ends here


