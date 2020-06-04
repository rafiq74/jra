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
   This file contains RCYCI Output rendering functions, such as logo and print header
*/

// This is the library for custom user interface
defined('MOODLE_INTERNAL') || die();

//depending on the current language, pick the right field. Alternative language always add _a at the end
//we also allow user to force to default language
function jra_output_show_field_language($data, $field, $lang = true)
{
	if($lang)
	{
		$lang = current_language();
		if($lang != 'en')
		{
			$theField = $field . '_a';
		}
		else
			$theField = $field;
		$a = $data->{$theField};
		if($a != '')
			return $a;
		else //fall back if no arabic
			return $data->{$field};
	}
	else
		return $data->{$field};
}

//force to display the alternate language field
function jra_output_show_field_force_alternate($data, $field)
{
	$theField = $field . '_a';
	$a = $data->{$theField};
	if($a != '')
		return $a;
	else //fall back if no arabic
		return $data->{$field};
}

//get the text in drop down that say all
function jra_output_select_all_text()
{
	return '- ' . get_string('all', 'local_jra') . ' -';
}

function jra_output_show_user_name($user, $full = true, $lang = true, $force_alternate = false)
{
	$arr = array();
	if($full)
	{
		if(!$force_alternate)
		{
			$arr[] = jra_output_show_field_language($user, 'first_name', $lang);
			$arr[] = jra_output_show_field_language($user, 'middle_name', $lang);
			$arr[] = jra_output_show_field_language($user, 'family_name', $lang);
		}
		else
		{
			$arr[] = jra_output_show_field_force_alternate($user, 'first_name');
			$arr[] = jra_output_show_field_force_alternate($user, 'middle_name');
			$arr[] = jra_output_show_field_force_alternate($user, 'family_name');
		}
 	}
	else
	{
		if(!$force_alternate)
		{
			$arr[] = jra_output_show_field_language($user, 'first_name', $lang);
			$arr[] = jra_output_show_field_language($user, 'middle_name', $lang);
			$arr[] = jra_output_show_field_language($user, 'family_name', $lang);
		}
		else
		{
			$arr[] = jra_output_show_field_force_alternate($user, 'first_name');
			$arr[] = jra_output_show_field_force_alternate($user, 'middle_name');
			$arr[] = jra_output_show_field_force_alternate($user, 'family_name');
		}
	}
	$str = '';
	foreach($arr as $a)
	{
		if($a != '')
		{
			if($str != '')
				$str = $str . ' ';
			$str = $str . $a;
		}
	}
	return $str;
}

function jra_output_currency($currency, $amount)
{
	return $currency . ' ' . number_format(round($amount, 2), 2);
}

//given a name, return no space so it will not wrap
function jra_output_no_space($name)
{
	$name = str_replace(' ', '_', $name);
	$name = str_replace('-', '_', $name);
	return $name;
}

//display the active status
function jra_output_show_active($eff_status)
{
	if($eff_status == 'A')
		$status = 'active';
	else
		$status = 'inactive';
	return get_string($status, 'local_jra');
}

//display the active status
function jra_output_show_yesno($status)
{
	if($status == 'Y')
		$status = 'yes';
	else
		$status = 'no';
	return get_string($status, 'local_jra');
}

//display the active status
function jra_output_show_gender($gender)
{
	if($gender == 'M')
		$gender = 'male';
	else
		$gender = 'female';
	return get_string($gender, 'local_jra');
}

function jra_output_formal_date($timestamp)
{
	return date('d-M-Y', $timestamp);
}

function jra_output_formal_datetime($timestamp)
{
	return date('d-M-Y, h:i:s', $timestamp);
}


/********************************************************
******* UNVERIFIED FUNCTIONS ****************************/
//given a semester, format it according to semester and year