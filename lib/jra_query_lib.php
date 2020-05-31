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

//get the effective date sql string
//table is the name of the table and the alias is usually a, b or c which is the sql alias for table
//matching_field (array) is the field that joins between the tables. It is usually the user_id, or course_code. 
//do not use id for matching field as each id is different, so the max will return all the different id
//with_eff_date is true if we want to retrieve the active effective date, or false if we want to retrieve the most recent, even if the most recent is not yet effective (i.e. future date)
//usually, for operation we use true, and for database we use false.
//custom_eff_date if we need a different effective date as SYSDATE
function jra_query_eff_date($table, $table_alias, $matching_field, $with_eff_date, $custom_eff_date = '', $extra_condition = '')
{
	$eff_alias = $table_alias . '_ed';
	$join_field = '';
	foreach($matching_field as $field)
	{
		if($join_field != '')
			$join_field = $join_field . ' and ';
		$join_field = "$table_alias.$field = $eff_alias.$field";
	}
	if($with_eff_date) //need to enforce effective date, so , the date must not exceed current day
	{
		if($custom_eff_date == '')
			$the_eff_date = 'UNIX_TIMESTAMP(SYSDATE())';
		else
			$the_eff_date = $custom_eff_date;
		$eff_date = " and $eff_alias.eff_date <= $the_eff_date";
	}
	else //else, get all the record. The max will make it distinct
		$eff_date = '';	
	if($extra_condition != '')
		$extra_where = ' and ' . $extra_condition;
	else
		$extra_where = '';
	$sql = "$table_alias.eff_date = (select max($eff_alias.eff_date) FROM {" . $table . "} $eff_alias where $join_field $eff_date $extra_where)";
	return $sql;
}

//get the effective sequence sql string. Sequence will allow records with similar effective date to be distinguished
//table is the name of the table and the alias is usually a, b or c which is the sql alias for table
//matching_field (array) is the field that joins between the tables. It is usually the user_id, or course_code. 
//do not use id for matching field as each id is different, so the max will return all the different id
function jra_query_eff_seq($table, $table_alias, $matching_field)
{
	$eff_alias = $table_alias . '_es';
	$join_field = '';
	foreach($matching_field as $field)
	{
		if($join_field != '')
			$join_field = $join_field . ' and ';
		$join_field = "$table_alias.$field = $eff_alias.$field";
	}
	$sql = "$table_alias.eff_seq = (select max($eff_alias.eff_seq) FROM {" . $table . "} $eff_alias where $join_field)";
	return $sql;
}

//add a like clause
function jra_query_like_query($search, $field, &$search_params, $condition = 'and')
{
	global $DB;	
	$conditionWhere = ' ' . $condition;
	if($search != '' && $field != '')
	{
		$conditionWhere = $conditionWhere . ' ' . $DB->sql_like($field, ':field', false);
		$search_params = array('field' => "%$search%");
	}
	else
	{
		$search_params = array();
		$conditionWhere = '';
	}
	return $conditionWhere;
}

//this function put a list of fields to be searched in a like clauses. The search fields will be OR
//it returns something like and (field1 like '%abc%' or field2 like '%abc%')
function jra_query_search_like($search, $fields, &$search_params, $condition = 'and')
{
	global $DB;	
	$conditionWhere = '';
	$search_params = array();
	if($search != '')
	{
		$count = 1;
		foreach($fields as $field)
		{
			if($conditionWhere != '')
				$conditionWhere = $conditionWhere . ' OR';
			$conditionWhere = $conditionWhere . ' ' . $DB->sql_like($field, ':field' . $count, false);
			$search_params['field' . $count] = "%$search%";
			$count++;
		}
		$conditionWhere = $condition . ' (' . $conditionWhere . ')';
	}
	return $conditionWhere;
}

//validate duplicate
//$condition is an array that check for duplicate
function jra_query_is_duplicate($table, $condition, $id)
{
	global $DB;
	$isDuplicate = false;
	$duplicate = $DB->get_record($table, $condition);
	if($duplicate) //found a record, possible duplicate
	{
		if($id != '') //updating
		{
			$original = $DB->get_record($table, array('id' => $id));
			if($duplicate->id != $original->id)
				$isDuplicate = true;
		}
		else
			$isDuplicate = true;
	}
	return $isDuplicate;
}

//delete a list of tables that is cascade to the main table
//table is the table to delete
//id is the id of the main table
//child is an array of table with cascading records, in associative array where key is the table name and value is the field
function jra_query_delete_cascade($table, $id, $child = array())
{
	global $DB;
	$country = jra_get_country(); //make sure match country
	jra_query_delete_multiple($id, $child, $country); //cascade delete
	$DB->delete_records($table, array('id' => $id, 'country' => $country));
}

//delete multiple table at once. All the table must have same id. Usually for cascading delete
function jra_query_delete_multiple($id, $child, $country)
{
	global $DB;
	foreach($child as $key => $value)
	{
		$DB->delete_records($key, array($value => $id, 'country' => $country));
	}
}

//update a list of tables that is cascade to the main table on certain fields
//table is the table to update
//data is the moodle data object with id to update in the main table
//child is an array of table with cascading records, in associative array where key is the table name and value is the field to update. It is always assumed that the foreign key in the table must be the name of the parent table follow by id, eg course_id
function jra_query_update_cascade($table, $data, $field, $child = array())
{
	global $DB;
	$DB->update_record($table, $data);
	$foreign_key = $table . '_id';
	foreach($child as $key => $table_field)
	{
		$to_update = '';
		foreach($table_field as $target => $source)
		{
			$value = $data->$target;
			if($to_update != '')
				$to_update = $to_update . ', ';
			$to_update = $to_update . "$source = '$value'";
		}
		$sql = "update $key set $to_update where $foreign_key = '$data->id'";
		$DB->execute($sql);
	}
}

//get the conditions for user list in a function so if the condition changes it is easy to update
function jra_query_user_list_condition($user_type = '', $deleted = 0)
{
	$arr = array(
		'country' => jra_get_country(),
		'deleted' => $deleted,
	);
	if($user_type != '')
		$arr['user_type'] = $user_type;
	return $arr;
}

function jra_query_get_jra_tables()
{
	global $CFG, $DB;
	$table_schema = $CFG->dbname;
	$where = " and table_name like 'm_jra_%'";
	$sql = "SELECT table_name, table_rows FROM information_schema.tables where table_schema='$table_schema' $where order by table_name";
	
	$tables = $DB->get_records_sql($sql);
	return $tables;
}
