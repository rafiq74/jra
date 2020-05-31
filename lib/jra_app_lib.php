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
function jra_app_get_plan($data, $active_effective_date = true, $custom_eff_date = '')
{
	global $DB;
	$sql = "select * from {jra_plan} a where " . jra_query_eff_date('jra_plan', 'a', array('plan_code'), $active_effective_date, $custom_eff_date) . " and a.plan_code = '$data->plan_code' and a.country = '$data->country'"; //effective date
	return $DB->get_record_sql($sql); //false if not found	
}
