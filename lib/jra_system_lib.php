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
   This file contain all the global functions for RCYCI module
*/

// This is the library for global RCYCI functions 
defined('MOODLE_INTERNAL') || die();

//return the object containing global variables
function jra_system_replace_special_char($str)
{
	$str = str_replace("'", $str);
	return $str;
}

function jra_system_default_semester()
{
	$var_name = 'admin_default_semester';
	$var_value = jra_get_config($var_name);
	return $var_value;
}

//get the number of days in between 2 days
function jra_system_day_diff($start_date, $end_date)
{
	$datediff = ($end_date - $start_date);
	$day_num = round($datediff / (60 * 60 * 24)) + 1;  //add one day to include itself			
	return $day_num;
}

//implode array content into sql in string with quote
function jra_system_implode_instr($arr)
{
	if(count($arr))
		$inStr = "'" . implode("','", $arr) . "'";	
	else
		$inStr = '';
	return $inStr;
}

//given a string item separated by comma or new line (multi line entry), explode it to array
function jra_system_explode_string($str)
{
	$users = str_replace(PHP_EOL, ',', $str);
	$users = str_replace(' ', '', $users);
	$arr = explode(',', $users);
	return $arr;
}

function jra_system_report_name($name)
{
	$now = time();
	$filename = $name . '_' . date('Ymd', $now) . '_' . date('hi', $now);
	return $filename;
}

function jra_system_g_to_h_year($gy)
{
	return $gy - 579;
}