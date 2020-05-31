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
$PAGE->set_url('/local/sis/setting/course/course_equivalent_action.php', $urlparams);
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
	
if($action == 1 && isset($get_data['eci']) && $get_data['cancel'] == '') //it is an add if there is a class type and user not cancel
{
	$dataid = $get_data['did']; //for updating	
	$data->id = $dataid;
	$data->course_id = $get_data['cid'];
	$data->equal_course_id = $get_data['eci'];
	$data->credit_weight = $get_data['cw'];
	$data->institute = sis_get_institute();
	//make sure no duplicate
	if($data->course_id != $data->equal_course_id) //pointing to self, so, wrong
	{
		$duplicate_condition = array(
			'course_id' => $data->course_id,
			'equal_course_id' => $data->equal_course_id,
		);
		$isDuplicate = sis_query_is_duplicate('si_course_equivalent', $duplicate_condition, $dataid);
		if(!$isDuplicate) //no duplicate, update it
		{
			if($data->id == '') //create new
				$DB->insert_record('si_course_equivalent', $data);	
			else
				$DB->update_record('si_course_equivalent', $data);			
		}
		else //don't update, give warning
		{
			sis_ui_alert(get_string('duplicate_course_equivalent', 'local_sis'), 'danger');
		}
	}
	else
		sis_ui_alert(get_string('self_course_equivalent', 'local_sis'), 'danger');
	$id = $data->course_id; //if there is submit, we need to reinitialize the id
}
else if($action == 2 && isset($get_data['dataid']) && $get_data['dataid'] != '') //delete
{
	$DB->delete_records('si_course_equivalent', array('id' => $get_data['dataid']));	
}
if(isset($get_data['cancel']) && $get_data['cancel'] == 1)
	$id = $get_data['cid']; //if there is submit, we need to reinitialize the id even if it is a cancel

$where = " where a.course_id = '$id'"; //initialize a where clause with institute
$sql = "select a.id, b.code, b.course_num, b.course_code, b.course_name, a.credit_weight from {si_course_equivalent} a inner join {si_course} b on a.equal_course_id = b.id $where
union ";
$where = " where a.equal_course_id = '$id'"; //initialize a where clause with institute
$sql = $sql . "select a.id, b.code, b.course_num, b.course_code, b.course_name, a.credit_weight from {si_course_equivalent} a inner join {si_course} b on a.course_id = b.id $where 
 order by course_code
";
$records = $DB->get_records_sql($sql, array());
$table = new html_table();
$table->attributes['class'] = '';
$table->width = "100%";
$table->head[] = get_string('no', 'local_sis');
$table->size[] = '5%';
$table->align[] = 'center';
$table->head[] = get_string('equivalent_course', 'local_sis');
$table->size[] = '70%';
$table->align[] = 'left';
$table->head[] = get_string('credit_weight', 'local_sis');
$table->size[] = '20%';
$table->align[] = 'center';
$table->head[] = 'Action';
$table->size[] = '5%';	
$table->align[] = 'center';
$data = array();
$count = 1;
foreach($records as $record)
{   	
	$data[] = $count;
	$data[] = $record->course_code . ' - ' . $record->course_name;
	$data[] = $record->credit_weight;

	$delete_url = "javascript:delete_course_equivalent('$id', '$record->id', '" . get_string('confirm_delete_record', 'local_sis') . "')";
	$data[] = html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => get_string('delete_course_component')));
	$table->data[] = $data;
	unset($data);				
	$count++;
}

sis_ui_print_table($table);

//content ends here


