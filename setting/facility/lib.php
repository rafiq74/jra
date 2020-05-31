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

function sis_facility_search_form($data)
{
	$activeList = sis_lookup_isactive();

	$str = '<form id="form1" name="form1" method="post" onsubmit="return building_search()" action="">';
	$str = $str . 'Search : ' . sis_ui_input('search', '20', $data['search'], 'handleKeyPress(event)');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . 'Active Status : ' . sis_ui_select('active', $activeList, $data['active'], 'building_search()', '');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<span class="pull-right"><input type="button" name="button3" id="button3" value="Search" onclick="building_search()"/></span>';
	$str = $str . sis_ui_hidden('sort', 1);
	$str = $str . '</form>';
	return $str;
}

function sis_lookup_search_form($data)
{
	$str = '<form id="form1" name="form1" method="post" onsubmit="return usage_search()" action="">';
	$str = $str . 'Search : ' . sis_ui_input('search', '20', $data['search'], 'handleKeyPress(event)');
	$str = $str . '<span class="pull-right"><input type="button" name="button3" id="button3" value="Refresh" onclick="usage_search()"/></span>';
	$str = $str . sis_ui_hidden('sort', 1);
	$str = $str . '</form>';
	return $str;
}

function sis_usage_get_name()
{
	global $DB;
	$sql = "select distinct value from {si_lookup} order by id";
	$rec = $DB->get_records_sql($sql);
	return $rec;
}
