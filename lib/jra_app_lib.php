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

function jra_app_get_applicant($id = '', $semester = '')
{
	global $DB, $USER;
	if($id == '')
		$id = $USER->jra_user->id;
	if($semester == '')
		$semester = jra_get_semester();
	$applicant = $DB->get_record('si_applicant', array('user_id' => $id, 'semester' => $semester, 'deleted' => 0));
	return $applicant;
}

function jra_app_get_applicant_stage($applicant)
{
	if($applicant)
		return $applicant->status;
	else
		return 0;
}

//the stage where the application becomes read only
function jra_app_read_only_stage()
{
	return 5;
}

//return true if applicant already admitted
function jra_app_applicant_admitted($applicant)
{
	if($applicant->admit_status == 1 && $applicant->idnumber != '') //admitted and has student id
		return true;
	else
		return false;
}

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

//active_effective_date retrieve those that is active on the retrival date
function jra_app_get_plan($data, $active_effective_date = true, $custom_eff_date = '')
{
	global $DB;
	$sql = "select * from {jra_plan} a where " . jra_query_eff_date('jra_plan', 'a', array('plan_code'), $active_effective_date, $custom_eff_date) . " and a.plan_code = '$data->plan_code' and a.country = '$data->country'"; //effective date
	return $DB->get_record_sql($sql); //false if not found	
}

function jra_app_ref_number($applicant)
{
	return str_pad($applicant->id, 5, '0', STR_PAD_LEFT);
}

function jra_app_recompute_aggregate()
{
	global $DB;
	$semester = $DB->get_record('si_semester', array('semester' => jra_get_semester()));
	if($semester)
	{
		$applicants = $DB->get_records('si_applicant', array('semester' => $semester->semester));
		foreach($applicants as $applicant)
		{
			$obj = new stdClass();
			$obj->id = $applicant->id;
			if($applicant->secondary != '' && $applicant->tahseli != '' && $applicant->qudorat != '')
				$obj->aggregation = jra_app_compute_aggregate($applicant, $semester);
			else
				$obj->aggregation = 0;
			$DB->update_record('si_applicant', $obj);	
		}
	}
}

//calculate the weighted average for aggregation
function jra_app_compute_aggregate($applicant, $semester)
{
	$aggregation = (($applicant->secondary * $semester->secondary_weight) + ($applicant->tahseli * $semester->tahseli_weight) + ($applicant->qudorat * $semester->qudorat_weight)) / ($semester->secondary_weight + $semester->tahseli_weight + $semester->qudorat_weight);
	return number_format($aggregation, 2);
}

