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
 * This file contains main functions for RCYCI Module
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
   This file contains Query helper functions.
*/

// This is the library for custom user interface
defined('MOODLE_INTERNAL') || die();

//active_effective_date retrieve those that is active on the retrival date
function jra_asset_add_asset($data)
{
	global $DB, $USER;
	$now = time();
	$data->eff_status = 'A';
	$data->date_update = $now;
	$success = 'error_execute_operation';
	if(!isset($data->id) || $data->id == '')
	{
		if(isset($USER->jra_user)) //make sure he must be a jra user
		{
			$data->user_id = $USER->jra_user->id;
			$data->asset_owner = 0; //default to self
			$data->date_created = $now;
			if($DB->insert_record('jra_asset', $data))
			{
				$success = ''; //empty string for success
				jra_ui_set_flash_message(jra_ui_alert(get_string('new_asset_create', 'local_jra'), 'success', '', true, true), 'jra_user_dashboard_message');
			}
			else
				$success = 'error_insert_asset';
		}
		else
			$success = 'error_not_allow_insert_asset';
	}
	else
	{
		if($DB->update_record('jra_asset', $data))
		{
			$success = '';
			jra_ui_set_flash_message(jra_ui_alert(get_string('asset_updated', 'local_jra'), 'success', '', true, true), 'jra_user_dashboard_message');
		}
		else
			$success = 'error_update_asset';
	}
	return $success;
}

function jra_asset_list_asset($user_id = '')
{
	global $DB;
	$country = jra_get_country();
	$sql = "select * from {jra_asset} ";
	$conditionText = "country = '$country'"; //effective date
	if($user_id != '')
		$conditionText = $conditionText . " and user_id = '$user_id'";
	$condition = array();
	//setup the table options
	$options = array(
		'sql' => $sql, //incase if we need to use sql
		'condition' => $condition, //and sql condition
		'conditionText' => $conditionText, //and sql textual condition
		'table_class' => 'generaltable', //table class is either generaltable (moodle standard table), or table for plain
		'responsive' => true, //responsive table
		'border-table' => false, //make the table bordered
		'condensed-table' => false, //compact table (not applicable under generaltable)
		'hover-table' => false, //make the table hover (not applicable under generaltable)
		'action' => true, //automatic add form and javascript for action edit and delete
		'sortable' => true, //enable clicking of heading to sort
		'default_sort_field' => 'title',
		'detail_link' => false, //provide javascript to link to detail page
		'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
		'detail_var' => 'id', //variable name in query string for id. var=id
		'search' => true, //allow search
		'default_search_field' => 'title', //default field choose for search
		'edit_page' => 'edit_plan.php',
		'view_page' => 'plan_user.php',
		'perpage' => jra_global_var('PER_PAGE'), //use large number to remove pagination
		'delete_admin' => true, //only allow to delete if it is siteadmin
	//	'debug' => true,
	);
	
	//setup the table fields
	$fields = array(
		'id' => array(
			'header'=>jra_get_string(['plan', 'id']), //for custom header
			'align' => 'left',
			'size' => '5%',
			'sort' => '', //indicates that these must be sorted together
	//		'format' => 'date',
	//		'disable_search' => true,
		),
		'plan_code' => array(
			'header'=>jra_get_string(['plan', 'code']), //for custom header
			'align' => 'left',
			'size' => '10%',
		),
		'title' => array(
			'align' => 'left',
			'size' => '20%',
		),
		'eff_status' => array(
			'header'=>get_string('status', 'local_jra'), //for custom header
			'align' => 'center',
			'size' => '10%',
		),	
		'eff_date' => array(
			'header'=>get_string('eff_date', 'local_jra'), //for custom header
			'align' => 'center',
			'size' => '10%',
			'format' => 'date',
		),	
		'*' => array(
			'view' => array(
					'icon' => 'user',
					'title' => 'plan_user',
				),
		), //action
	);
	
	//output the table
	return jra_ui_dump_table('jra_asset_user', $options, $fields, 'local_jra');
	
}

