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

function jra_admin_plan_edit_plan($id, $view)
{
	global $DB;
	//first we retrieve the plan_code
	$co = $DB->get_record('jra_plan', array('id' => $id));
	//next we retrieve all the same plan sorted by effective date descending
	$records = $DB->get_records('jra_plan', array('plan_code' => $co->plan_code), 'eff_date desc');
	$detail_data = array();
	$now = time();
	$currentFound = false;
	$stopFurther = false;
	$count = 1;
	foreach($records as $plan_data)
	{
		//one row of data
		$obj = new stdClass();
		$col_title = get_string('eff_date', 'local_jra') . ':<br />' . jra_output_formal_date($plan_data->eff_date);
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
		$obj->content = jra_admin_plan_display_plan($plan_data, $isCurrent);
		
		$params = array('id' => $id, 'op' => 'edit', 'dataid' => $plan_data->id);
		$edit_url = new moodle_url('edit_plan.php', $params);	
		
		$delete_url = "javascript:show_delete_confirm_modal('$plan_data->id')";			
		$deleteStr = html_writer::link($delete_url, jra_ui_icon('times-circle', '1', true), array('title' => jra_get_string(['delete', 'plan'])));
		
		$obj->edit = '<span class="pull-right">' . html_writer::link($edit_url, jra_ui_icon('pencil', '1', true), array('title' => jra_get_string(['edit', 'plan']))) . jra_ui_space(2) . $deleteStr . '</span>';
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
		$view_link = html_writer::link($view_url, jra_ui_icon('sort-amount-asc', '2', true), array('title' => jra_get_string(['view', 'all'])));
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
		$view_link = html_writer::link($view_url, jra_ui_icon('window-minimize', '2', true), array('title' => jra_get_string(['view', 'active'])));
		//one row of data
		$obj = new stdClass();
		$obj->title = '';
		$obj->content = $view_link;
		$obj->edit = '';
		$key = '999999999999';
		$detail_data[$key] = $obj;
	}
	
//	krsort($detail_data); //sort the array by key in descending order
	$str = jra_ui_data_detail($detail_data, 2, 0, true);
	echo $str;	
}

function jra_admin_plan_display_plan($plan_data, $highlight)
{
	$detail_data = array();

	//one row of data
	$obj = new stdClass();
	$obj->title = jra_get_string(['plan', 'id']);
	$obj->content = $plan_data->id;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = jra_get_string(['plan', 'code']);
	$obj->content = $plan_data->plan_code;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('title', 'local_jra');
	$obj->content = $plan_data->title;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('currency', 'local_jra');
	$obj->content = $plan_data->currency;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$interval = ucfirst($plan_data->duration_denomination);
	$obj = new stdClass();
	$obj->title = jra_get_string(['plan', 'interval']);
	$obj->content = $interval;
	$detail_data[] = $obj;
	//end of data row

	//showing the cost table
	$table = new html_table();
	$table->attributes['class'] = 'custom';
	$table->width = "100%";
	$table->head[] = get_string('level', 'local_jra');
	$table->align[] = 'left';
	$table->size[] = '15%';
	$table->head[] = get_string('cost', 'local_jra');
	$table->align[] = 'left';
	$table->size[] = '20%';
	$table->head[] = get_string('interval', 'local_jra');
	$table->align[] = 'left';
	$table->size[] = '20%';
	$table->head[] = get_string('message', 'local_jra');
	$table->align[] = 'left';
	$table->size[] = '45%';
	$row_data[] = get_string('level', 'local_jra') . ' 1';
	$row_data[] = number_format($plan_data->cost1, 2);
	$row_data[] = $plan_data->duration1 . ' ' . $interval;
	$row_data[] = $plan_data->message1 == '' ? '-' : $plan_data->message1;
	$table->data[] = $row_data;
	unset($row_data); //unset row_data
	$row_data[] = get_string('level', 'local_jra') . ' 2';
	$row_data[] = $plan_data->cost2 == 0 ? '-' : number_format($plan_data->cost2, 2);
	$row_data[] = $plan_data->cost2 == 0 ? '-' : $plan_data->duration2 . ' ' . $interval;
	$row_data[] = $plan_data->cost2 == 0 ? '-' : $plan_data->message2;
	$table->data[] = $row_data;
	unset($row_data); //unset row_data
	$row_data[] = get_string('level', 'local_jra') . ' 3';
	$row_data[] = $plan_data->cost3 == 0 ? '-' : number_format($plan_data->cost3, 2);
	$row_data[] = $plan_data->cost3 == 0 ? '-' : $plan_data->duration3 . ' ' . $interval;
	$row_data[] = $plan_data->cost3 == 0 ? '-' : $plan_data->message3;
	$table->data[] = $row_data;
	unset($row_data); //unset row_data
	$row_data[] = get_string('level', 'local_jra') . ' 4';
	$row_data[] = $plan_data->cost4 == 0 ? '-' : number_format($plan_data->cost4, 2);
	$row_data[] = $plan_data->cost4 == 0 ? '-' : $plan_data->duration4 . ' ' . $interval;
	$row_data[] = $plan_data->cost4 == 0 ? '-' : $plan_data->message4;
	$table->data[] = $row_data;
	unset($row_data); //unset row_data
	$row_data[] = get_string('level', 'local_jra') . ' 5';
	$row_data[] = $plan_data->cost5 == 0 ? '-' : number_format($plan_data->cost5, 2);
	$row_data[] = $plan_data->cost5 == 0 ? '-' : $plan_data->duration5 . ' ' . $interval;
	$row_data[] = $plan_data->cost5 == 0 ? '-' : $plan_data->message5;
	$table->data[] = $row_data;
	unset($row_data); //unset row_data
	
	$cost = jra_ui_print_table($table, true, true);

	
	//one row of data
	$obj = new stdClass();
	$obj->title = jra_get_string(['cost']);
	$obj->content = $cost;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('status', 'local_jra');
	$obj->content = jra_output_show_active($plan_data->eff_status);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('icon', 'local_jra');
	$obj->content = $plan_data->icon == '' ? '-' : jra_ui_icon($plan_data->icon, '', true);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('remark', 'local_jra');
	$obj->content = $plan_data->remark == '' ? '-' : $plan_data->remark;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('eff_date', 'local_jra');
	$obj->content = date('d-M-Y', $plan_data->eff_date);
	$detail_data[] = $obj;
	//end of data row

	$content = jra_ui_data_detail($detail_data);
	$str = jra_ui_alert($content, $highlight, '', false, true);
	return $str;
}

function jra_admin_plan_add_plan_user($to_add, $plan, $start_date = '', $end_date = '')
{
	global $DB, $USER;
	$now = time();
	$country = jra_get_country();
	if($start_date == '')
		$start_date = jra_output_today();
	if($end_date == '')
		$end_date = strtotime(date('d-M-Y', $start_date) . ' + 1 year'); //for now
	foreach($to_add as $u)
	{
		$data = new stdClass();
		$data->id = '';
		$data->user_id = $u->id;
		$data->plan_code = $plan->plan_code;
		$data->title = $plan->title;
		$data->start_date = $start_date;
		$data->end_date = $end_date;
		$data->action = 'add';
		$data->action_remark = '';
		$data->eff_status = 'A';
		$data->date_created = $now;
		$data->date_updated = $now;
		$data->action_user = $USER->id;
		$data->country = $country;
		$duplicate_condition = array(
			'user_id' => $data->user_id,
			'plan_code' => $data->plan_code,
		);
		$isDuplicate = jra_query_is_duplicate('jra_plan_user', $duplicate_condition, $data->id);
		if(!$isDuplicate) //no duplicate, update it, otherwise, don't do anything
		{
			$DB->insert_record('jra_plan_user', $data);	
		}
	}	
}

function jra_admin_plan_remove_plan_user($to_remove)
{
	global $DB, $USER;
	$cascade = array();
	foreach($to_remove as $u)
	{
		jra_query_delete_cascade('jra_plan_user', $u->id, $cascade);		
	}
}

function jra_admin_plan_total_subscriber($plan)
{
	global $DB;
	$condition = array(
		'plan_code' => $plan->plan_code,
		'country' => jra_get_country(),
	);
	return $DB->count_records('jra_plan_user', $condition);
}

