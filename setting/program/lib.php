<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Certificate module internal API,
 * this is in separate file to reduce memory use on non-certificate pages.
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function sis_setting_program_plan_course_list($id, $tab_active)
{
	$str = '';
	if($tab_active != '')
	{
		$add_url = new moodle_url('/local/sis/setting/program/add_plan_course.php', array('id' => $id));	
		$str = $str . '<span class="pull-right rc-secondary-tab">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add_course', 'local_sis'), array('title' => get_string('add_course', 'local_sis'))) . '</span>';
		$str = $str . '<div id="ajax-content">';
		$str = $str . '';	
		$str = $str . '</div>';	
	}
	return $str;
}

function sis_setting_program_plan_requisite($id, $actual_plan_id, $tab_active)
{
	global $PAGE;
	$str = '';
	if($tab_active != '')
	{
		$level = optional_param('level', '', PARAM_INT);
		
		$course_list = sis_lookup_course_list_plan($id, $level, true, get_string('select_course', 'local_sis'));
		$course_level = sis_lookup_plan_course_level($id, true);

		$qs = $_GET;
		unset($qs['level']);
		unset($qs['level_requisite']);
		unset($qs['course_id']);
		$refresh_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);

		$str = $str . '<form name="form1" action="">';
		$str = $str . sis_ui_hidden('program_id', $plan->program_id);
		$str = $str . sis_ui_hidden('plan_id', $id);
		$str = $str . sis_ui_hidden('course_level_requisite', '');
		$str = $str . '<div class="row pb-3">';
		//one row
		$str = $str . '	<div class="col-md-12 pt-3">' . get_string('course_level', 'local_sis') . '&nbsp;&nbsp;&nbsp;' . 
			sis_ui_select('course_level', $course_level, $level, "refresh_requisite('$refresh_url')") . 
			'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 
			get_string('course', 'local_sis') . '&nbsp;&nbsp;&nbsp;' . 
			sis_ui_select('course_id', $course_list, $course_id, "show_plan_requisite('$actual_plan_id', $(this).val(), 'full')") . 
			'</div>';
		//end of one row
		$str = $str . '</div>';
		$str = $str . '</form>';
		
		$str = $str . '<div id="ajax-content">';
		$str = $str . '';	
		$str = $str . '</div>';	
	}
	return $str;
}

function sis_setting_program_plan_rule($id, $tab_active)
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

function sis_setting_program_add_requisite_form($id, $course_id = '', $level = '', $level_requisite = '')
{
	global $DB, $PAGE;
	$plan = $DB->get_record('si_plan', array('id' => $id));
	if(!$plan)
		throw new moodle_exception('Invalid plan id.');
	$course_list = sis_lookup_course_list_plan($id, $level, true, get_string('select_course', 'local_sis'));
	$course_list_requisite = sis_lookup_course_list_plan($id, $level_requisite, true);
	$course_level = sis_lookup_plan_course_level($id, true);
	$req_type = sis_lookup_requisite();
	$qs = $_GET;
	unset($qs['level']);
	unset($qs['level_requisite']);
	unset($qs['course_id']);
	$refresh_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
	$isactive = sis_lookup_isactive();
	$str = '<form name="form1" action="">';
	$str = $str . sis_ui_hidden('program_id', $plan->program_id);
	$str = $str . sis_ui_hidden('plan_id', $id);
	$str = $str . '<div class="row">';
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('course_level', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . get_string('course', 'local_sis') . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . sis_ui_select('course_level', $course_level, $level, "refresh_requisite('$refresh_url')") . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . sis_ui_select('course_id', $course_list, $course_id, "show_plan_requisite('$id', $(this).val(), 'minimal')") . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('course_level', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . get_string('requisite', 'local_sis') . ' ' . get_string('course', 'local_sis') . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . sis_ui_select('course_level_requisite', $course_level, $level_requisite, "refresh_requisite('$refresh_url')") . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . sis_ui_select('course_id_requisite', $course_list_requisite) . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('type', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . sis_ui_select('requisite_type', $req_type) . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('eff_status', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . sis_ui_select('eff_status', $isactive) . '</div>';
	//end of one row
	//one row
	$add_pre_requisite = "javascript:add_plan_requisite('" . get_string('same_requisite_course', 'local_sis') . "')";
	$str = $str . '	<div class="col-md-3 pt-3"></div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . sis_ui_button_link($add_pre_requisite, get_string('add_requisite', 'local_sis'), 'primary') . '</div>';
	//end of one row
	$str = $str . '</div>';
	$str = $str . '</form>';
	$str = $str . '<script>';
	$str = $str . '
	  $( function() {
		$("#datepicker").datepicker();
		$("#datepicker").datepicker("option", "dateFormat", "dd-MM-yy");
	  } );
	';
	$str = $str . '</script>';
	return $str;
}

function sis_setting_program_update_requisite_form($id, $course_id = '', $record)
{
	global $DB;
	$plan = $DB->get_record('si_plan', array('id' => $id));
	if(!$plan)
		throw new moodle_exception('Invalid plan id.');
	$req_type = sis_lookup_requisite();
	$isactive = sis_lookup_isactive();
	$str = '<form name="form2" action="">';
	$str = $str . sis_ui_hidden('program_id', $plan->program_id);
	$str = $str . sis_ui_hidden('plan_id', $id);
	$str = $str . sis_ui_hidden('record_id', $record->id);
	$str = $str . sis_ui_hidden('course_id', $record->course_id);
	$str = $str . sis_ui_hidden('requisite_type', $record->requisite_type);
	$str = $str . '<div class="row">';
	//one row
	$str = $str . '	<div class="col-md-2 pt-3">' . get_string('type', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-10 pt-3">' . $req_type[$record->requisite_type] . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-2 pt-3">' . get_string('eff_status', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-10 pt-3">' . sis_ui_select('eff_status', $isactive, $record->eff_status) . '</div>';
	//end of one row
	//one row
	$update_pre_requisite = "javascript:save_plan_requisite()";
	$cancel_pre_requisite = "javascript:show_plan_requisite('$id', '$course_id', 'full')";
	$str = $str . '	<div class="col-md-2 pt-3"></div>';
	$str = $str . '	<div class="col-md-10 pt-3">' . sis_ui_button_link($update_pre_requisite, get_string('update'), 'primary') . '&nbsp;' . sis_ui_button_link($cancel_pre_requisite, get_string('cancel'), 'primary') . '</div>';
	//end of one row
	$str = $str . '</div>';
	$str = $str . '</form>';
	return $str;
}

function sis_setting_program_add_course_form($id)
{
	global $DB;
	$plan = $DB->get_record('si_plan', array('id' => $id));
	if(!$plan)
		throw new moodle_exception('Invalid plan id.');
	$course_list = sis_lookup_get_course_list(true, true, true);
	$course_type = sis_lookup_get_list('course', 'course_type');
	$num_list = sis_lookup_get_num_list(1, 12);
	$num_list = array_merge(array('0' => get_string('default')), $num_list);
	$num_list_level = sis_lookup_get_num_list(1, 20);
	$yesno = sis_lookup_yes_no();
	$isactive = sis_lookup_isactive();
	$str = '<form name="form1" action="">';
	$str = $str . sis_ui_hidden('program_id', $plan->program_id);
	$str = $str . sis_ui_hidden('plan_id', $id);
	$str = $str . '<div class="row">';
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('effective_date', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . '
		<div class="input-group mb-3">
		  <input type="text" id="datepicker" name="eff_date" class="form-control" readonly>
		  <div class="input-group-append">
			<span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
		  </div>
		</div>' . sis_ui_checkbox('default_date', false) . '&nbsp;&nbsp;&nbsp;' . get_string('set_to_course_eff_date', 'local_sis') . '
	' . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('course', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . sis_ui_select('course_id', $course_list) . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('course_type', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . sis_ui_select('course_type', $course_type) . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('probation_if_fail', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . sis_ui_select('probation_fail', $yesno, 'N') . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('level', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . sis_ui_select('course_level', $num_list_level) . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('credit', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . sis_ui_select('credit', $num_list) . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('compulsory', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . sis_ui_select('compulsory', $yesno) . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('must_pass', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . sis_ui_select('must_pass', $yesno) . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('in_cgpa', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . sis_ui_select('in_cgpa', $yesno) . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('eff_status', 'local_sis') . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . sis_ui_select('eff_status', $isactive) . '</div>';
	//end of one row
	//one row
	$add_url = "javascript:add_plan_course()";
	$str = $str . '	<div class="col-md-3 pt-3"></div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . sis_ui_button_link($add_url, get_string('add'), 'primary') . '</div>';
	//end of one row
	$str = $str . '</div>';
	$str = $str . '</form>';
	
	$str = $str . '<script>';
	$str = $str . '
	  $( function() {
		$("#datepicker").datepicker();
		$("#datepicker").datepicker("option", "dateFormat", "dd-MM-yy");
	  } );
	';
	$str = $str . '</script>';
	
	return $str;
}

function sis_setting_program_init_plan_program()
{
	$records = sis_lookup_program(false);
	foreach($records as $key => $record)
		return $key;
	return false;
}

function sis_setting_program_plan_list($program)
{
	global $DB;
	$max_program = sis_setting_program_get_program($program);
	$sql = "select * from {si_plan} a where " . sis_query_eff_date('si_plan', 'a', array('catalogue_id'), false) . " and a.institute = '$program->institute' and program_id = '$program->catalogue_id' order by plan_sequence, plan"; //effective date
	$records = $DB->get_records_sql($sql);
	if(!$records)
		$records = array();
	$table = new html_table();
	$table->attributes['class'] = '';
	$table->width = "100%";
	$table->head[] = get_string('plan', 'local_sis');
	$table->size[] = '15%';
	$table->align[] = 'left';
	$table->head[] = get_string('plan_name', 'local_sis');
	$table->size[] = '25%';
	$table->align[] = 'left';
	$table->head[] = get_string('plan_rules', 'local_sis');
	$table->size[] = '15%';
	$table->align[] = 'left';
	$table->head[] = get_string('seq', 'local_sis');
	$table->size[] = '10%';
	$table->align[] = 'center';
	$table->head[] = get_string('eff_status', 'local_sis');
	$table->size[] = '10%';
	$table->align[] = 'center';
	$table->head[] = get_string('eff_date', 'local_sis');
	$table->size[] = '15%';	
	$table->align[] = 'center';
	$table->head[] = 'Action';
	$table->size[] = '10%';	
	$table->align[] = 'center';
	$data = array();
	foreach($records as $record)
	{   
		
		$data[] = $record->plan;
		$data[] = $record->plan_name;
		$data[] = $record->plan_rule;
		$data[] = $record->plan_sequence;
		$data[] = sis_output_show_active($record->eff_status);
		$data[] = sis_output_formal_date($record->eff_date);
	
		$view_url = new moodle_url('/local/sis/setting/program/view_plan.php', array('id' => $record->id));
//		$delete_url = "javascript:delete_plan('$id', '$record->id')";
		$update_url = new moodle_url('/local/sis/setting/program/edit_plan.php', array('id' => $record->id));	
		
		$data[] = html_writer::link($view_url, sis_ui_icon('search', '1.5', true), array('title' => get_string('view_plan', 'local_sis'))) . '&nbsp;' . 
		html_writer::link($update_url, sis_ui_icon('pencil', '1.5', true), array('title' => get_string('update_plan', 'local_sis'))); 
//		html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => get_string('delete_plan', 'local_sis')));
		$table->data[] = $data;
		unset($data);				
	}

	$str = '';
	$add_url = new moodle_url('/local/sis/setting/program/add_plan.php', array('id' => $max_program->id));
	$str = $str . '<div class="pull-right pb-3">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add_academic_plan', 'local_sis'), array('title' => get_string('add_academic_plan', 'local_sis'))) . '</div>';
	
	$str = $str . sis_ui_print_table($table, true, true);
	return $str;
}

function sis_setting_program_show_program_edit($id, $view)
{
	global $DB;
	//first we retrieve the catalogue_id
	$co = $DB->get_record('si_program', array('id' => $id));	
	$records = $DB->get_records('si_program', array('catalogue_id' => $co->catalogue_id), 'eff_date desc');
	$detail_data = array();
	$now = time();
	$currentFound = false;
	$stopFurther = false;
	$count = 1;
	foreach($records as $program_data)
	{
		//one row of data
		$obj = new stdClass();
		$col_title = get_string('eff_date', 'local_sis') . ':<br />' . sis_output_formal_date($program_data->eff_date);
		$obj->title = $col_title;
		$isCurrent = 'secondary';
		if($program_data->eff_date <= $now)
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
		$obj->content = sis_setting_program_display_program($program_data, $isCurrent);
		
		$params = array('id' => $id, 'op' => 'edit', 'dataid' => $program_data->id);
		$edit_url = new moodle_url('/local/sis/setting/program/edit_program.php', $params);	
		if($count == 1)
			$obj->edit = '<span class="pull-right">' . html_writer::link($edit_url, sis_ui_icon('pencil', '1', true), array('title' => get_string('edit', 'local_sis'))) . '</span>';
		else
			$obj->edit = ''; //don't allow to edit for non current
		//we create the key by adding the effective date with the sequence. In this way, the biggest number will be produce, so if we sort the array, we will have the largest key on top.
		$key = $program_data->eff_date;
		$detail_data[$key] = $obj;
		//end of data row
		if($stopFurther)
			break;
		$count++;
	}
	$total_record = count($records);
	if($count < $total_record)
	{
		$view_url = new moodle_url('edit_program.php', array('id' => $id, 'view' => 'all'));
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
		$view_url = new moodle_url('edit_program.php', array('id' => $id, 'view' => 'active'));
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

function sis_setting_program_display_program($program_data, $highlight)
{
	$detail_data = array();

	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('program_id', 'local_sis');
	$obj->content = $program_data->catalogue_id;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('program', 'local_sis');
	$obj->content = $program_data->program;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('program_name', 'local_sis');
	$obj->content = $program_data->program_name;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('grading_scheme', 'local_sis');
	$obj->content = $program_data->grading_scheme;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = sis_get_string(['probation_cgpa', 'prep']);
	$obj->content = $program_data->probation_cgpa_prep;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = sis_get_string(['dismissed_cgpa', 'prep']);
	$obj->content = $program_data->dismissed_cgpa_prep;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('probation_cgpa', 'local_sis');
	$obj->content = $program_data->probation_cgpa;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('dismissed_cgpa', 'local_sis');
	$obj->content = $program_data->dismissed_cgpa;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = sis_get_string(['maximum', 'semester', 'preparatory']);
	$obj->content = $program_data->max_semester_prep;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = sis_get_string(['minimum', 'elective', 'courses', 'required']);
	$obj->content = $program_data->elective_required;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = sis_get_string(['maximum', 'semester']);
	$obj->content = $program_data->max_semester;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('campus', 'local_sis');
	$obj->content = sis_output_show_campus($program_data->campus);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('status', 'local_sis');
	$obj->content = sis_output_show_active($program_data->eff_status);
	$detail_data[] = $obj;
	//end of data row

	$content = sis_ui_data_detail($detail_data, 4);
	$str = sis_ui_alert($content, $highlight, '', false, true);
	return $str;
}

function sis_setting_program_show_plan_edit($id, $view)
{
	global $DB;
	//first we retrieve the catalogue_id
	$co = $DB->get_record('si_plan', array('id' => $id));	
	$program = $DB->get_record('si_program', array('id' => $co->program_id));
	$records = $DB->get_records('si_plan', array('catalogue_id' => $co->catalogue_id), 'eff_date desc');
	$detail_data = array();
	$now = time();
	$currentFound = false;
	$stopFurther = false;
	$count = 1;
	foreach($records as $plan_data)
	{
		//one row of data
		$obj = new stdClass();
		$col_title = get_string('eff_date', 'local_sis') . ':<br />' . sis_output_formal_date($plan_data->eff_date);
		$obj->title = $col_title;
		$isCurrent = 'secondary';
		if($plan_data->eff_date <= $now)
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
		$obj->content = sis_setting_program_display_plan($plan_data, $program, $isCurrent);
		
		$params = array('id' => $id, 'op' => 'edit', 'dataid' => $plan_data->id);
		$edit_url = new moodle_url('/local/sis/setting/program/edit_plan.php', $params);	
		if($count == 1)
			$obj->edit = '<span class="pull-right">' . html_writer::link($edit_url, sis_ui_icon('pencil', '1', true), array('title' => get_string('edit', 'local_sis'))) . '</span>';
		else
			$obj->edit = ''; //don't allow to edit for non current
		//we create the key by adding the effective date with the sequence. In this way, the biggest number will be produce, so if we sort the array, we will have the largest key on top.
		$key = $plan_data->eff_date;
		$detail_data[$key] = $obj;
		//end of data row
		if($stopFurther)
			break;
		$count++;
	}
	$total_record = count($records);
	if($count < $total_record)
	{
		$view_url = new moodle_url('edit_plan.php', array('id' => $id, 'view' => 'all'));
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
		$view_url = new moodle_url('edit_plan.php', array('id' => $id, 'view' => 'active'));
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

function sis_setting_program_display_plan($plan_data, $program, $highlight)
{
	$detail_data = array();

	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('plan_id', 'local_sis');
	$obj->content = $plan_data->catalogue_id;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('program', 'local_sis');
	$obj->content = $program->program . ' - ' . $program->program_name;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('academic_plan', 'local_sis');
	$obj->content = $plan_data->plan;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('plan_name', 'local_sis');
	$obj->content = $plan_data->plan_name;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('plan_name', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')';
	$obj->content = $plan_data->plan_name_a;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('plan_rule', 'local_sis');
	$obj->content = $plan_data->plan_rule;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('status', 'local_sis');
	$obj->content = sis_output_show_active($plan_data->eff_status);
	$detail_data[] = $obj;
	//end of data row

	$content = sis_ui_data_detail($detail_data);
	$str = sis_ui_alert($content, $highlight, '', false, true);
	return $str;
}

function sis_setting_program_show_course_edit($id, $plan_id, $view)
{
	global $DB;
	$plan_course = $DB->get_record('si_plan_course', array('id' => $id));
		
	//first we retrieve the catalogue_id
	$records = $DB->get_records('si_plan_course', array('plan_id' => $plan_course->plan_id, 'course_id' => $plan_course->course_id), 'eff_date desc');
	$detail_data = array();
	$now = time();
	$currentFound = false;
	$stopFurther = false;
	$count = 1;
	foreach($records as $plan_course_data)
	{
		//one row of data
		$obj = new stdClass();
		$col_title = get_string('eff_date', 'local_sis') . ':<br />' . sis_output_formal_date($plan_course_data->eff_date);
		$obj->title = $col_title;
		$isCurrent = 'secondary';
		if($plan_course_data->eff_date <= $now)
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
		$obj->content = sis_setting_program_display_course($plan_course_data, $plan_course->course_id, $isCurrent);
		
		$params = array('id' => $plan_id, 'op' => 'edit', 'course_id' => $plan_course_data->id);
		$edit_url = new moodle_url('/local/sis/setting/program/update_plan_course.php', $params);	
		if($count == 1)
			$obj->edit = '<span class="pull-right">' . html_writer::link($edit_url, sis_ui_icon('pencil', '1', true), array('title' => get_string('edit', 'local_sis'))) . '</span>';
		else
			$obj->edit = ''; //don't allow to edit for non current
		//we create the key by adding the effective date with the sequence. In this way, the biggest number will be produce, so if we sort the array, we will have the largest key on top.
		$key = $plan_course_data->eff_date;
		$detail_data[$key] = $obj;
		//end of data row
		if($stopFurther)
			break;
		$count++;
	}
	$total_record = count($records);
	if($count < $total_record)
	{
		$view_url = new moodle_url('update_plan_course.php', array('id' => $plan_id, 'course_id' => $id, 'view' => 'all'));
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
		$view_url = new moodle_url('update_plan_course.php', array('id' => $plan_id, 'course_id' => $id, 'view' => 'active'));
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
	return $str;	
}

function sis_setting_program_display_course($plan_course_data, $catalogue_id, $highlight)
{
	global $DB;
	$sql = "select *
		from 
			{si_course} a 
		where " . sis_query_eff_date('si_course', 'a', array('catalogue_id'), true, $plan_course_data->eff_date) . "
			and a.deleted = 0 
			and a.catalogue_id = '$catalogue_id'
	";
	$course = $DB->get_record_sql($sql);
	
	$detail_data = array();	
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('course_id', 'local_sis');
	$obj->content = $plan_course_data->course_id;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('course', 'local_sis');
	$obj->content = sis_output_show_course($course);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('level', 'local_sis');
	$obj->content = $plan_course_data->course_level;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('course_type', 'local_sis');
	$obj->content = get_string($plan_course_data->course_type, 'local_sis');
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('credit', 'local_sis');
	$obj->content = $plan_course_data->credit;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('compulsory', 'local_sis');
	$obj->content = sis_output_show_yesno($plan_course_data->compulsory);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('must_pass', 'local_sis');
	$obj->content = sis_output_show_yesno($plan_course_data->must_pass);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('probation_if_fail', 'local_sis');
	$obj->content = sis_output_show_yesno($plan_course_data->probation_fail);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('in_cgpa', 'local_sis');
	$obj->content = sis_output_show_yesno($plan_course_data->in_cgpa);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('status', 'local_sis');
	$obj->content = sis_output_show_active($plan_course_data->eff_status);
	$detail_data[] = $obj;
	//end of data row

	$content = sis_ui_data_detail($detail_data);
	$str = sis_ui_alert($content, $highlight, '', false, true);
	return $str;
}



//get a course with max effective date
function sis_setting_program_get_program($data, $custom_eff_date = '')
{
	global $DB;
	$sql = "select * from {si_program} a where " . sis_query_eff_date('si_program', 'a', array('catalogue_id'), true, $custom_eff_date) . " and a.institute = '$data->institute' and catalogue_id = '$data->catalogue_id'"; //effective date
	return $DB->get_record_sql($sql); //false if not found	
}

//get a course with max effective date
function sis_setting_program_get_plan($data, $custom_eff_date = '')
{
	global $DB;
	$sql = "select * from {si_plan} a where " . sis_query_eff_date('si_plan', 'a', array('catalogue_id'), true, $custom_eff_date) . " and a.institute = '$data->institute' and program_id = '$data->program_id' and plan = '$data->plan'"; //effective date
	return $DB->get_record_sql($sql); //false if not found	
}

//get the plan course with maximum effective date
function sis_setting_program_get_plan_course($data, $custom_eff_date = '')
{
	global $DB;
	$sql = "select * from {si_plan_course} a where " . sis_query_eff_date('si_plan_course', 'a', array('course_id'), true, $custom_eff_date) . " and a.institute = '$data->institute' and a.plan_id = '$data->plan_id' and a.course_id = '$data->course_id'"; //effective date
	return $DB->get_record_sql($sql); //false if not found	
}

//get the plan course with 2nd maximum effective date. This is for editing purpose
function sis_setting_program_get_plan_next_course($data, $custom_eff_date = '')
{
	global $DB;
	$sql = "select * from {si_plan_course} where institute = '$data->institute' and plan_id = '$data->plan_id' and course_id = '$data->course_id' order by eff_date desc";
	$recs = $DB->get_record_sql($sql); //false if not found	
	$count = 1;
	foreach($recs as $rec)
	{
		if($count == 2)
			return $rec;
		$count++;
	}
	return false;
}
