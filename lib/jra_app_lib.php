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

function jra_app_get_age($aDate)
{
	$now = time();
	$age = date('Y', $now) - date('Y', $aDate);
	if($age > 0)
		return $age;
	else
		return '-';
}

//get age by hijrah
function jra_app_get_age_hijri($aDate)
{
	$now = date('d-M-Y', time());
	$h = jra_to_hijrah($now);
	$a = explode('/', $h);
	if(count($a) == 3)
		$y = $a[2];
	else
		return '-';
	$b = explode('/', $aDate);
	if(count($b) == 3)
		$y2 = $b[0]; //for user supply, year starts first
	else
		return '-';
	$age = $y - $y2;
	if($age > 0)
		return $age;
	else
		return '-';
}

//a date starts with 0:00. We have to make it to 23:59
function jra_app_is_end_date($aDate)
{
	$aTime = strtotime(date('d-M-Y', $aDate) . ' + 1 day') - 1;
	$now = time();
	if($now <= $aTime)
		return false;
	else
		return true;
}

//a date starts with 0:00. We have to make it to 23:59
function jra_app_get_end_date($aDate)
{
	return strtotime(date('d-M-Y', $aDate) . ' + 1 day') - 1;
}

