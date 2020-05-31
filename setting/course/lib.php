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
 * This file contains main class for the course format Weeks
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns navigation controls (tabtree) to be displayed on cohort management pages
 *
 * @param context $context system or category context where cohorts controls are about to be displayed
 * @param moodle_url $currenturl
 * @return null|renderable
 */


//get a course with max effective date
function sis_setting_course_get_course($data, $custom_eff_date = '')
{
	global $DB;
	$sql = "select * from {si_course} a where " . sis_query_eff_date('si_course', 'a', array('catalogue_id'), true, $custom_eff_date) . " and a.institute = '$data->institute' and code = '$data->code' and course_num = '$data->course_num' and deleted = 0"; //effective date and seq
	return $DB->get_record_sql($sql); //false if not found	
}

function sis_setting_course_show_course_edit($id, $view)
{
	global $DB;
	//first we retrieve the catalogue_id
	$co = $DB->get_record('si_course', array('id' => $id));	
	$records = $DB->get_records('si_course', array('catalogue_id' => $co->catalogue_id), 'eff_date desc');
	$detail_data = array();
	$now = time();
	$currentFound = false;
	$stopFurther = false;
	$count = 1;
	foreach($records as $course_data)
	{
		//one row of data
		$obj = new stdClass();
		$col_title = get_string('eff_date', 'local_sis') . ':<br />' . sis_output_formal_date($course_data->eff_date);
		$obj->title = $col_title;
		$isCurrent = 'secondary';
		if($course_data->eff_date <= $now)
		{
			if(!$currentFound)
			{
				$isCurrent = 'info';
				$currentFound = true;
				if($view == 'active')
					$stopFurther = true;
			}
		}
		else
			$isCurrent = 'warning';
		$obj->content = sis_setting_course_display_course($course_data, $isCurrent);
		
		$params = array('id' => $id, 'op' => 'edit', 'dataid' => $course_data->id);
		$edit_url = new moodle_url('/local/sis/setting/course/edit_course.php', $params);	
		if($count == 1)
			$obj->edit = '<span class="pull-right">' . html_writer::link($edit_url, sis_ui_icon('pencil', '1', true), array('title' => get_string('edit', 'local_sis'))) . '</span>';
		else
			$obj->edit = ''; //don't allow to edit for non current
		//we create the key by adding the effective date with the sequence. In this way, the biggest number will be produce, so if we sort the array, we will have the largest key on top.
		$key = $course_data->eff_date;
		$detail_data[$key] = $obj;
		//end of data row
		if($stopFurther)
			break;
		$count++;
	}
	$total_record = count($records);
	if($count < $total_record)
	{
		$view_url = new moodle_url('edit_course.php', array('id' => $id, 'view' => 'all'));
		$view_link = html_writer::link($view_url, sis_ui_icon('ellipsis-v', '2', true), array('title' => get_string('view_all', 'local_sis')));
		//one row of data
		$obj = new stdClass();
		$obj->title = '';
		$obj->content = $view_link;
		$obj->edit = '';
		$key = '999999999999';
		$detail_data[$key] = $obj;
	}
	if($total_record > 1 && $count > $total_record)
	{
		$view_url = new moodle_url('edit_course.php', array('id' => $id, 'view' => 'active'));
		$view_link = html_writer::link($view_url, sis_ui_icon('ellipsis-h', '2', true), array('title' => get_string('view_active', 'local_sis')));
		//one row of data
		$obj = new stdClass();
		$obj->title = '';
		$obj->content = $view_link;
		$obj->edit = '';
		$key = '999999999999';
		$detail_data[$key] = $obj;
	}
	
//	krsort($detail_data); //sort the array by key in descending order
	$str = sis_ui_data_detail($detail_data, 2, 0, true);
	echo $str;	
}

function sis_setting_course_display_course($course_data, $highlight)
{
	$detail_data = array();

	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('course_id', 'local_sis');
	$obj->content = $course_data->catalogue_id;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('course_code', 'local_sis');
	$obj->content = $course_data->course_code;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('course_name', 'local_sis');
	$obj->content = $course_data->course_name;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('default_credit', 'local_sis');
	$obj->content = $course_data->default_credit;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = sis_get_string(['final_exam', 'duration']);
	$obj->content = sis_setting_format_duration($course_data->final_exam_duration / 60);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('department', 'local_sis');
	$obj->content = strtoupper($course_data->acad_org);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('eff_date', 'local_sis');
	$obj->content = date('d-M-Y', $course_data->eff_date);
	$detail_data[] = $obj;
	//end of data row

	$content = sis_ui_data_detail($detail_data);
	$str = sis_ui_alert($content, $highlight, '', false, true);
	return $str;
}

//print the add component form
function sis_setting_course_component_form()
{
	$class_type_list = sis_lookup_get_list('course', 'class_type');
	$str = '<form id="form1" name="form1" method="post" onsubmit="return search_user()" action="">';
	
	$data = array();
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('class_type', 'local_sis');
	$obj->content = sis_ui_select('class_type', $class_type_list);
	$data[] = $obj;
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('main_course_component', 'local_sis');
	$obj->content = sis_ui_checkbox('main_component') . ' ' . get_string('yes', 'local_sis');
	$data[] = $obj;
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('default_section_size', 'local_sis');
	$obj->content = sis_ui_input('default_section_size', '5');
	$data[] = $obj;
	//one row of data
	$num_list = sis_lookup_get_num_list(1, 60);
	$obj = new stdClass();
	$obj->title = get_string('contact_hour_week', 'local_sis');
	$obj->content = sis_ui_select('contact_hour_week', $num_list);
	$data[] = $obj;
	//one row of data
	$num_list = sis_lookup_get_num_list(1, 10);
	$obj = new stdClass();
	$obj->title = get_string('contact_hour_class', 'local_sis');
	$obj->content = sis_ui_select('contact_hour_class', $num_list);
	$data[] = $obj;
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('has_final_exam', 'local_sis');
	$obj->content = sis_ui_checkbox('final_exam') . ' ' . get_string('yes', 'local_sis');
	$data[] = $obj;
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('teacher_workload_weight', 'local_sis');
	$obj->content = sis_ui_input('teacher_workload_weight', '10');
	$data[] = $obj;
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('lms_course_creation', 'local_sis');
	$obj->content = sis_ui_checkbox('lms_course_creation') . ' ' . get_string('yes', 'local_sis');
	$data[] = $obj;

	$str = $str . sis_ui_data_detail($data);
	$str = $str . '</form>';
	return $str;	
}
 
//given duration in minutes, show in x H y M
function sis_setting_format_duration($aDuration)
{
	$h = floor($aDuration / 60);
	$h = str_pad($h, 2, '0', STR_PAD_LEFT);
	$m = $aDuration % 60;
	$m = str_pad($m, 2, '0', STR_PAD_LEFT);
	$theDuration = $h . " H " . $m . " M";
	return $theDuration;		
}
 
function sis_setting_course_component_content($id, $tab_active)
{
	$str = '';
	if($tab_active != '')
	{
		$add_url = 'javascript:add_course_component('.$id.')';
		$str = $str . '<div class="pull-right rc-secondary-tab">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add_component', 'local_sis'), array('title' => get_string('add_component', 'local_sis'))) . '</div>';
		$str = $str . '<div id="ajax-content">';
		$str = $str . '';	
		$str = $str . '</div>';	
	}
	return $str;
}

function sis_setting_course_equivalent_content($id, $tab_active)
{
	$str = '';
	if($tab_active != '')
	{
		$add_url = 'javascript:add_course_equivalent('.$id.')';
		$str = $str . '<div class="pull-right rc-secondary-tab">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add_course_equivalent', 'local_sis'), array('title' => get_string('add_course_equivalent', 'local_sis'))) . '</div>';
		$str = $str . '<div id="ajax-content">';
		$str = $str . '';	
		$str = $str . '</div>';	
	}
	return $str;
}

 
function sis_grade_print_course()
{
	global $DB;
	$rec = $DB->get_records('si_course');

	$table = new html_table();
	$table->attributes['class'] = 'table table-bordered table-striped';
	$table->width = "100%";
	$table->header = false;

     $add_url = "javascript:add_course()";		
     $add_btn = $html .html_writer::link($add_url, 'Add Course', array('title' => 'Add Course', 'class' => 'btn btn-primary pull-right'));		

	$table->head[] = 'Course Catalogue' . $add_btn;
	$table->size[] = '5%';
	$table->align[] = 'left';

	foreach($rec as $r)
	{
		$url = $add_url = new moodle_url('/local/sis/setting/course/index.php', array('id' => $r->id));	
		$data[] = html_writer::link($url, $r->course_name, array('title' => 'Show Course Name Detail'));		
		$table->data[] = $data;
		unset($data);				
	}
	return sis_ui_print_table($table, false, true);
}


function sis_print_course_detail($id)
{
	//global $DB;
	//$rec = $DB->get_records('si_grade_letter');
	
	global $DB;
	$id = $_GET['id'];
	$arr = $DB->get_records('si_course', array('id' => $id));
	
	$table = new html_table();
	$table->attributes['class'] = 'table table-bordered table-striped';
	$table->head = array('code','name ','number','Action');
	$table->align = array('center','center','center','center','center');
	//$add_url = "javascript:add_letter()";	
	foreach($arr as $ar)
	{	
		$data[] = $ar->code;
		$data[] = $ar->course_name;
		$data[] = $ar->course_number	;
		
		$delete_url = "javascript:delete_course_records('$ar->id')";
		$data[] = html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => 'Delete room'));
		
		$table->data[] = $data;
		unset($data);				
	}
	return sis_ui_print_table($table, false, true);	
}
