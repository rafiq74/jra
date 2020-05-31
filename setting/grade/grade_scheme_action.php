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
require_once '../../lib/sis_query_lib.php';
require_once '../../lib/sis_lookup_lib.php';
require_once '../../lib/sis_output_lib.php';
require_once 'lib.php'; //local library

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);


$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);
$id = required_param('id', PARAM_INT);

$get_data = $_GET;
if(isset($get_data['action']))
	$action = $get_data['action'];
else
	$action = 0;
	
if($action == 1 && isset($get_data['g']) && $get_data['cancel'] == '') //it is an add if there is a class type and user not cancel
{
	$dataid = $get_data['did']; //for updating	
	$data->id = $dataid;
	$data->grade_scheme_id = $get_data['gid'];
	$data->grade = urldecode($get_data['g']);
	$data->description = urldecode($get_data['gd']);
	$data->grade_point = $get_data['gp'];
	$data->range_from = $get_data['rf'];
	$data->range_to = $get_data['rt'];
	$data->status = $get_data['s'];
	$data->is_enrolled = $get_data['ie'];
	$data->exempted = $get_data['ex'];
	$data->sort_order = 1;
	$data->eff_date = sis_earliest_date();
	$data->institute = sis_get_institute();
		
	//make sure no duplicate
	$duplicate_condition = array(
		'grade_scheme_id' => $data->grade_scheme_id,
		'grade' => $data->grade,
	);
	$isDuplicate = sis_query_is_duplicate('si_grade_letter', $duplicate_condition, $dataid);
	if(!$isDuplicate) //no duplicate, update it
	{
		if($data->id == '') //create new
			$DB->insert_record('si_grade_letter', $data);	
		else
		{
			$DB->update_record('si_grade_letter', $data);			
			$sql = "update {si_section_student} set grade = '$data->grade' where grade_id = '$data->id'";
			$DB->execute($sql);
		}
	}
	else //don't update, give warning
	{
		sis_ui_alert(get_string('duplicate_grade_letter', 'local_sis'), 'danger');
	}
	$id = $data->grade_scheme_id; //if there is submit, we need to reinitialize the id
}
else if($action == 2 && isset($get_data['delete_id']) && $get_data['delete_id'] != '') //delete
{
	$DB->delete_records('si_grade_letter', array('id' => $get_data['delete_id']));	
}
if($get_data['cancel'] == 1)
{
	$id = $get_data['gid'];
}

if($id == '' && isset($get_data['cancel']) && $get_data['cancel'] != '') //get it from session if user cancel
	$id = sis_get_session('sis_selected_grade_scheme');

$gradeletter = sis_grade_print_grade_letter($id);

echo $gradeletter;