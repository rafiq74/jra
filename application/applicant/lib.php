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


function jra_application_applicant_show_contact($id)
{
	global $DB;
	$records = $DB->get_records('jra_user_contact', array('user_id' => $id));
		
	$detail_data = array();
	foreach($records as $user_data)
	{
		//one row of data
		$obj = new stdClass();
		$obj->title = get_string($user_data->address_type, 'local_jra');
		$obj->content = jra_admin_user_format_contact($user_data);
		
		$params = array('id' => $id, 'tab' => 'contact', 'op' => 'edit', 'dataid' => $user_data->id);
		$edit_url = new moodle_url('view_user.php', $params);			
		$obj->edit = '<span class="pull-right">' . html_writer::link($edit_url, jra_ui_icon('pencil', '1', true), array('title' => get_string('edit', 'local_jra'))) . '</span>';
		$detail_data[$user_data->address_type] = $obj;
		//end of data row
	}
	$address_type = jra_lookup_get_list('personal_info', 'address_type', '', true); //resort according to importance
	$sorted_list = array();
	foreach($address_type as $key => $val)
	{
		if(isset($detail_data[$key]))
			$sorted_list[] = $detail_data[$key];
	}
	$str = jra_ui_data_detail($sorted_list, 2, 3, true);
	echo $str;
}

function jra_application_applicant_format_contact($user_data)
{
	$str = $user_data->address1;
	if($user_data->address2 != '')
	{
		if($str != '')
			$str = $str . '<br />';
		$str = $str . $user_data->address2;
	}
	$city_state = '';
	if($user_data->address_city != '')
		$city_state = $user_data->address_city;
	if($user_data->address_state != '')
	{
		if($city_state != '')
			$city_state = $city_state . ', ' . $user_data->address_state;
		else
			$city_state = $user_data->address_state;
	}
	if($user_data->address_postcode != '')
	{
		if($city_state != '')
			$city_state = $city_state . ' ' . $user_data->address_postcode;
		else
			$city_state = $user_data->address_postcode;
	}
	if($city_state != '')
	{
		if($str != '')
			$str = $str . '<br />';
		$str = $str . $city_state;
	}
	/*
	if($user_data->address_country != '')
	{
		if($str != '')
			$str = $str . '<br />';
		$str = $str . jra_lookup_countries($user_data->address_country);
	}
	*/
	return $str;
}

function jra_application_completed_document($applicant, $check = true)
{
	global $DB;
	$semester = $DB->get_record('si_semester', array('semester' => $applicant->semester));
	
	$arr = array();
	$arr['national'] = $applicant->national_id_file;
	$count = 0;	
	if($applicant->national_id_file != '')
		$count++;
	if($semester->admission_type == 'crtp')
	{
		$total_file = 3;
		$arr['transcript'] = $applicant->transcript_file;
		$arr['university'] = $applicant->uni_approval_file;
		if($applicant->transcript_file != '')
			$count++;
		if($applicant->uni_approval_file != '')
			$count++;
	}
	else
	{
		$total_file = 4;
		$arr['secondary'] = $applicant->secondary_file;
		$arr['tahseli'] = $applicant->tahseli_file;
		$arr['qudorat'] = $applicant->qudorat_file;
		if($applicant->secondary_file != '')
			$count++;
		if($applicant->tahseli_file != '')
			$count++;
		if($applicant->qudorat_file != '')
			$count++;
	}
	if($check) //only want to know if it is completed
	{
		if($count == $total_file)
			return true;
		else
			return false;
	}
	else //need the array
		return $arr;
}
function jra_application_list_supporting_document($applicant, $read_only, $table_only = false)
{
	global $OUTPUT;
	if(!$applicant)
		$str = '';
	else
	{
		$arr = jra_application_completed_document($applicant, false);
		$table = '
				  <table class="table table-hover">
					<thead class="text-warning">
					  <tr>
						<th width="5%">' . get_string('no', 'local_jra') . '</th>
						<th width="70%">' . jra_get_string(['supporting_document']). '</th>
						<th width="15%" class="text-center">' . get_string('file') . '</th>';
		if(!$read_only)
			$table = $table . '<th width="10%" class="text-center">' . get_string('action', 'local_jra') . '</th>';
		$table = $table . '</tr>
					</thead>
					<tbody>';
		$count = 1;
		$dir = jra_file_supporting_document_path(jra_get_semester());
		foreach($arr as $key => $value)
		{
			if($key == 'national')
				$doc = jra_get_string(['national_id']);
			else if($key == 'secondary')
				$doc = get_string('secondary_school_document', 'local_jra');
			else
				$doc = get_string($key, 'local_jra');

			//if want to use physical icon
			//$icon = new pix_icon('f/' . $filetype . '-48', strtoupper($filetype), 'moodle', array('class' => 'iconmedium'));
			//echo $OUTPUT->render($icon)

			if($value != '')
			{
				$filetype = jra_file_get_extension($value);
				if($filetype != 'pdf')
					$ico = 'image';
				else
					$ico = 'pdf';
				$file_url = new moodle_url('file.php', array('path' => $dir, 'file' => $value));
				$view_link = html_writer::link($file_url, jra_ui_icon('file-' . $ico . '-o', '', true) . ' ' . $file, array('title' => strtoupper($ico), 'target' => '_blank'));
				$a = new moodle_url('upload_document.php');
				$delete_url = "javascript:delete_file(" . $applicant->id. ", '" . $key . "', '" . $value . "', '" . $a->out(false) . "', '" . get_string('confirm_delete_file', 'local_jra') . "')";
				$delete_link = html_writer::link($delete_url, jra_ui_icon('trash-o', '', true) . ' ' . $file, array('title' => get_string('delete')));
			}
			else
			{
				$view_link = '-';
				$delete_link = '-';
			}
			
			$table = $table . '<tr>
							<td>' . $count . '.</td>
							<td>' . $doc . '</td>
							<td align="center">' . $view_link . '</td>';
			if(!$read_only)
				$table = $table . '<td align="center">' . $delete_link . '</td>';
			$table = $table . '</tr>';
			$count++;
		}
					  
		$table = $table . '</tbody>
				  </table>';
		if($table_only)
			return $table;
		$str = '
		<form name="form_file" method="post">
		' . jra_ui_hidden('delete_id', '') . '
		' . jra_ui_hidden('delete_module', '') . '
		' . jra_ui_hidden('delete_file', '') . '
		<div class="row mt-5">
			<div class="col-md-8">
				<div class="card">
					<div class="card-header">
						<strong>' . jra_get_string(['uploaded', 'documents']) . '</strong>
					</div>
					<div class="card-body">
						' . $table . '
					</div>
				</div>
			</div>
			<div class="col-md-4">
			</div>
		</div>
		</form>
		';
	}
	return $str;
}