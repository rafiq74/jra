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

function sis_location_city_country_list()
{
	global $DB;
	$countries = sis_lookup_countries();
	$sql = "select distinct country from {si_state} where institute = '" . sis_get_institute() . "'";
	$records = $DB->get_records_sql($sql);
	$arr = array();
	foreach($records as $rec)
		$arr[$rec->country] = $countries[$rec->country];
	return $arr;
}

function sis_location_init_city_country()
{
	global $DB;
	$rec = $DB->get_record('si_state', array('institute' => sis_get_institute()));
	if($rec)
	{
		sis_set_session('location_city_country', $rec->country);
		return $rec->country;
	}
	else
	{
		return false;
	}
}
function sis_city_search_form($data)
{
	$str = '<form id="form1" name="form1" method="post" onsubmit="return city_search()" action="">';
	$str = $str . 'Search : ' . sis_ui_input('search', '20', $data['search'], 'handleKeyPress(event)');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<span class="pull-right"><input type="button" name="button3" id="button3" value="Search" onclick="city_search()"/></span>';
	$str = $str . sis_ui_hidden('sort', 1);
	$str = $str . '</form>';
	return $str;
}

function sis_state_search_form($data)
{
	$activeList = array(
		'A' => 'Active',
		'I' => 'Inactive',
	);
	$str = '<form id="form1" name="form1" method="post" onsubmit="return state_search()" action="">';
	$str = $str . 'Search : ' . sis_ui_input('search', '20', $data['search'], 'handleKeyPress(event)');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<span class="pull-right"><input type="button" name="button3" id="button3" value="Search" onclick="state_search()"/></span>';
	$str = $str . sis_ui_hidden('sort', 1);
	$str = $str . '</form>';
	return $str;
}

