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


function sis_setting_organization_post($organization, $option)
{
	global $DB;
	$sql = "select a.id, a.user_id, a.appid, a.position_id, a.position, a.appointment_type, a.custom_name, a.custom_name_a, a.eff_status, b.fullname, b.fullname_a from {si_organization_post} a inner join v_si_user b on a.user_id = b.id where a.category = '$option' and a.acad_org = '$organization->id' and a.institute = '$organization->institute'";
	$records = $DB->get_records_sql($sql);
	if(!$records)
		$records = array();
	$table = new html_table();
	$table->attributes['class'] = '';
	$table->width = "100%";
	$table->head[] = get_string('post', 'local_sis');
	$table->size[] = '30%';
	$table->align[] = 'left';
	$table->head[] = get_string('employee', 'local_sis');
	$table->size[] = '30%';
	$table->align[] = 'left';
	$table->head[] = get_string('appointment', 'local_sis');
	$table->size[] = '15%';
	$table->align[] = 'left';
	$table->head[] = get_string('eff_status', 'local_sis');
	$table->size[] = '10%';
	$table->align[] = 'center';
	$table->head[] = 'Action';
	$table->size[] = '10%';	
	$table->align[] = 'center';
	$data = array();
	foreach($records as $record)
	{   
		
		$data[] = $record->position;
		if($record->custom_name != '')
			$name = $record->custom_name;
		else
			$name = $record->fullname;
		$data[] = $name;
		$data[] = get_string($record->appointment_type, 'local_sis');
		$data[] = sis_output_show_active($record->eff_status);
	
		$delete_url = "javascript:delete_post('".$record->id."')";
//		$delete_url = "javascript:delete_plan('$id', '$record->id')";
		$update_url = new moodle_url('/local/sis/setting/organization/add_post.php', array('id' => $organization->id, 'dataid' => $record->id, 'type' => $option));	
		
		$data[] = html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => sis_get_string('delete', 'post'))) . '&nbsp;' . 
		html_writer::link($update_url, sis_ui_icon('pencil', '1.5', true), array('title' => sis_get_string('update', 'post'))); 
//		html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => get_string('delete_plan', 'local_sis')));
		$table->data[] = $data;
		unset($data);				
	}

	$str = '';
	$add_url = new moodle_url('/local/sis/setting/organization/add_post.php', array('id' => $organization->id, 'type' => $option));
	$str = $str . '<div class="pull-right pb-3">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . sis_get_string(['add', 'post']), array('title' => sis_get_string(['add', 'post']))) . '</div>';
	
	$str = $str . sis_ui_print_table($table, true, true);
	
	$str .= '<form name="form_list" method="post">';
	$str .= sis_ui_hidden('delete_id', '');
	$str .= '</form>';
	$str .= '
		 <script>
			function delete_post(id)
			{
				if(confirm("'.get_string('confirm_delete_record', 'local_sis').'"))
				{
					document.form_list.delete_id.value = id;
					document.form_list.submit();
				}
			}
		</script>
	';
	
	return $str;
}
