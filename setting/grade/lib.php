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

function sis_setting_grade_print_grade_scheme()
{
	global $DB;
	$condition = array('institute' => sis_get_institute());
	$rec = $DB->get_records('si_grade_scheme', $condition);

	$table = new html_table();
	$table->attributes['class'] = '';
	$table->width = "100%";
	$table->header = false;

     $add_url = "javascript:add_grade_scheme()";		
$add_btn = $html .html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . '&nbsp;' . get_string('add_grading_scheme', 'local_sis'), array('title' => get_string('add_grading_scheme', 'local_sis'), 'class' => 'pull-right'));		

	$table->head[] = get_string('grading_schemes', 'local_sis') . $add_btn;
	$table->size[] = '100%';
	$table->align[] = 'left';

	foreach($rec as $r)
	{
		$show_url = "javascript:show_scheme_detail('$r->id')";		
		$str = html_writer::link($show_url, $r->grade_scheme, array('title' => 'Show grade scheme detail'));	
		
		$delete_url = "javascript:delete_grade_scheme('$r->id', '" . get_string('confirm_delete_grade_scheme', 'local_sis') . "')";
		$update_url = "javascript:update_grade_scheme('$r->id')";		
		
		$str = $str . '<span class="pull-right">(' . sis_output_show_active($r->eff_status) . ')';
		$str = $str . '&nbsp;&nbsp;&nbsp;' . html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => 'Delete Campus'));
		$str = $str . '&nbsp;' . html_writer::link($update_url, sis_ui_icon('pencil', '1.5', true), array('title' => 'Update Campus'));
		$str = $str . '</span>';
		$data[] = $str;
		$table->data[] = $data;
		unset($data);				
	}
	return sis_ui_print_table($table, false, true);	
}


function sis_grade_print_grade_letter($id)
{
	global $DB;
	sis_set_session('sis_selected_grade_scheme', $id);
	$grade = $DB->get_record('si_grade_scheme', array('id' => $id));
	$grade_letters = $DB->get_records('si_grade_letter', array('grade_scheme_id' => $id), 'grade_point desc, range_from desc');	
    $table = new html_table();
   // $add_btn = $html .html_writer::link($add_url, 'Add Letter', array('title' => 'Add grade Letter', 'class' => 'btn btn-primary pull-right'));	
    $table->attributes['class'] = '';
    $table->head = array('Grade','From','To', 'Grade Point','Action');
    $table->align = array('center','center','center','center','center','center');
    //$add_url = "javascript:add_letter()";	
	
	$table = new html_table();
	$table->attributes['class'] = '';
	$table->width = "100%";
	$table->head[] = get_string('grade', 'local_sis');
	$table->size[] = '10%';
	$table->align[] = 'center';
	$table->head[] = get_string('description', 'local_sis');
	$table->size[] = '20%';
	$table->align[] = 'left';
	$table->head[] = get_string('points', 'local_sis');
	$table->size[] = '10%';
	$table->align[] = 'center';
	$table->head[] = get_string('from', 'local_sis');
	$table->size[] = '10%';
	$table->align[] = 'center';
	$table->head[] = get_string('to', 'local_sis');
	$table->size[] = '10%';	
	$table->align[] = 'center';
	$table->head[] = get_string('pass_status', 'local_sis');
	$table->size[] = '10%';	
	$table->align[] = 'center';
	$table->head[] = get_string('enrolled', 'local_sis') . '?';
	$table->size[] = '10%';	
	$table->align[] = 'center';
	$table->head[] = 'CGPA ' . get_string('exempted', 'local_sis') . '?';
	$table->size[] = '10%';	
	$table->align[] = 'center';
	$table->head[] = 'Action';
	$table->size[] = '10%';	
	$table->align[] = 'center';
    foreach($grade_letters as $ar)
	{	
		$data[] = $ar->grade;
		$data[] = $ar->description;
		$data[] = $ar->grade_point;
		$data[] = $ar->range_from;
		$data[] = $ar->range_to;
		$data[] = sis_lookup_grade_pass_status($ar->status);
		$data[] = sis_output_show_yesno($ar->is_enrolled);
		$data[] = sis_output_show_yesno($ar->exempted);
		$delete_url = "javascript:delete_grade_letter('$id', '$ar->id', '" . get_string('confirm_delete_grade_letter', 'local_sis') . "')";
		$update_url = "javascript:update_grade_letter($id, $ar->id)";	
		$data[] = html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => 'Delete ')) . '&nbsp;' . 
				  html_writer::link($update_url, sis_ui_icon('pencil', '1.5', true), array('title' => 'Update Letter'));
		
		$table->data[] = $data;
		unset($data);				
	}
	
	$add_url = "javascript:add_grade_letter($id)";		
	$btn = html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . '&nbsp;' . get_string('add_grade_letter', 'local_sis'), array('title' => get_string('add_grade_letter'), 'class' => 'rc-gradeletter-tab-text pull-right'));			

	$grade_title = get_string('grade_letters', 'local_sis') . ' (' . $grade->grade_scheme . ')';
	$ret = '';
	$ret = $ret . sis_ui_page_title2($grade_title, array('class' => 'pt-2'), true) . $btn;
	$ret = $ret . sis_ui_box_start(true);
	$ret = $ret . sis_ui_print_table($table, false, true);
	$ret = $ret . sis_ui_box_end();
	return $ret;
}

