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

//if description is true, use description as the value. Key remain the same
function jra_lookup_get_list($category, $subcategory = '', $condition = '', $lang = true, $description = false, $empty_text = '')
{
	global $DB;
	$where = " and institute = '" . jra_get_institute() . "'"; //initialize a where clause with institute
	if($subcategory != '')
		$where = $where . " and subcategory = '$subcategory'";
	if($condition != '')
		$where = $where . " and " . $condition;
	$sql = "select value, description from {jra_lookup} where category = '$category' $where order by sort_order";
	$rec = $DB->get_records_sql($sql);

	$arr = array();
	if($empty_text != '')
		$arr[''] = $empty_text;
	foreach($rec as $r)
	{
		if($lang)
		{
			if($description)
				$arr[$r->value] = get_string($r->description, 'local_jra');
			else
				$arr[$r->value] = get_string($r->value, 'local_jra');
		}
		else
		{
			if($description)
				$arr[$r->value] = $r->description;
			else
				$arr[$r->value] = $r->value;
		}
	}
	return $arr;
}

function jra_lookup_insert($value, $lang, $category, $subcategory, $institute, $sort_order = 1, $description = '')
{
	global $DB;
	$data = new stdClass();
	$data->value = $value;
	$data->lang = $lang;
	$data->category = $category;
	$data->subcategory = $subcategory;
	$data->sort_order = $sort_order;
	$data->description = $description;
	$data->institute = $institute;
	$DB->insert_record('jra_lookup', $data);
}

function jra_lookup_update($id, $value, $lang = '', $category = '', $subcategory = '', $institute = '', $sort_order = '')
{
	global $DB;
	$data = new stdClass();
	$data->id = $id;
	$data->value = $value;
	if($category != '')
		$data->category = $category;
	if($lang != '')
		$data->lang = $lang;
	if($subcategory != '')
		$data->subcategory = $subcategory;
	if($sort_order != '')
		$data->sort_order = $sort_order;
	$DB->update_record('jra_lookup', $data);
}

//check if the value of lookup duplicate
function jra_lookup_duplicate($value, $lang, $category, $subcategory, $institute, $id = '')
{
	global $DB;
	$where = "where institute = '$institute' and category = '$category' and subcategory = '$subcategory' and value = '$value' and lang = '$lang'";
	$sql = "select * from {jra_lookup} $where";
	//validate that there is no duplicate
	$isDuplicate = false;
	$duplicate = $DB->get_record_sql($sql);
	if($duplicate) //found a record, possible duplicate
	{
		if($id != '') //updating
		{
			$original = $DB->get_record('jra_lookup', array('id' => $id));
			if($duplicate->id != $original->id)
				$isDuplicate = true;
		}
		else
			$isDuplicate = true;
	}
	return $isDuplicate;
}

function jra_lookup_currencies() {
	// See https://www.stripe.com/cgi-bin/webscr?cmd=p/sell/mc/mc_intro-outside,
	// 3-character ISO-4217: https://cms.stripe.com/us/cgi-bin/?cmd=
	// _render-content&content_ID=developer/e_howto_api_currency_codes.
	$codes = array(
		'AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY',
		'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'RUB', 'SEK', 'SGD', 'THB', 'TRY', 'TWD', 'USD');
	$currencies = array();
	foreach ($codes as $c) {
		$currencies[$c] = new lang_string($c, 'core_currencies');
	}
	return $currencies;
}

function jra_lookup_get_num_list($start, $end)
{
	$arr = array();
	for($i = $start; $i <= $end; $i++)
		$arr[$i] = $i;
	return $arr;
}

function jra_lookup_plan_interval()
{
	return array(
		'month' => get_string('monthly', 'local_jra'),
		'year' => get_string('yearly', 'local_jra'),
	);
}

function jra_lookup_per_page()
{
	return array(
		'10' => '10',
		'20' => '20',
		'30' => '30',
		'50' => '50',
		'100' => '100',
		'200' => '200',
		'500' => '500',
		'1000' => '1000',
		'1500' => '1500',
		'2000' => '2000',
		'5000' => '5000',
	);
}

//the first year is 1970. If you want to customize, pass the argument to override
function jra_lookup_get_year_list($start = 1970)
{
	$this_year = date('Y', time());
	$end = $this_year  + 5; //allow 5 years ahead
	$arr = array();
	for($i = $end; $i >= $start; $i--)
	{
		$arr[$i] = $i;
	}
	return $arr;
}

//if given code, return the country, else return the entire list
function jra_lookup_countries($code = '')
{
	$countries = get_string_manager()->get_list_of_countries();
	if($code != '')
	{
		if(isset($countries[$code]))
			return $countries[$code];
		else
			return '';
	}
	else
		return $countries;
}

function jra_lookup_is_active()
{
	return array(
		'A' => get_string('active', 'local_jra'),
		'I' => get_string('inactive', 'local_jra'),
	);
}

function jra_lookup_admission_type()
{
	return array(
		'regular' => get_string('regular', 'local_jra'),
		'crtp' => 'CRTP',
	);
}

function jra_lookup_language()
{
	return array(
		'en' => get_string('english', 'local_jra'),
		'ar' => get_string('arabic', 'local_jra'),
	);
}

function jra_lookup_yes_no()
{
	return array('Y' =>get_string('yes', 'local_jra'), 'N' => get_string('no', 'local_jra'));
}

function jra_lookup_gender()
{
	return array(
		'M' => get_string('male', 'local_jra'),
		'F' => get_string('female', 'local_jra'),
	);
}

function jra_lookup_user_type($blank = '')
{
	$arr = array(
		'employee' => get_string('employee', 'local_jra'),
		'student' => get_string('student', 'local_jra'),
		'parent' => get_string('parent', 'local_jra'),
	);
	if($blank != '')
		$arr = array_merge(['' => $blank], $arr);
	return $arr;
}

function jra_lookup_asset_category($blank = '')
{
	$arr = array();
	if($blank != '')
		$arr[''] = $blank;
	$arr['property'] = get_string('properties', 'local_jra');
	$arr['vehicle'] = get_string('vehicles', 'local_jra');
	$arr['service'] = get_string('services', 'local_jra');
	$arr['facility'] = get_string('facilities', 'local_jra');
	$arr['others'] = get_string('others', 'local_jra');
	return $arr;
}

function jra_lookup_kindship($blank = '')
{
	$arr = array();
	if($blank != '')
		$arr[''] = $blank;
	$arr['father'] = get_string('father', 'local_jra');
	$arr['mother'] = get_string('mother', 'local_jra');
	$arr['brother'] = get_string('brother', 'local_jra');
	$arr['grand_father'] = get_string('grandfather', 'local_jra');
	$arr['uncle'] = get_string('uncle', 'local_jra');
	$arr['others'] = get_string('others', 'local_jra');
	return $arr;
}

function jra_lookup_user_title()
{
	return array(
		'Mr' => 'Mr.',
		'Mrs' => 'Mrs.',
		'Miss' => 'Miss',
		'Dr' => 'Dr.',
		'Assc Prof' => 'Assc Prof.',
		'Prof' => 'Prof.',
		'Engr' => 'Engr.',
	);
}

function jra_lookup_month_list()
{
	$months = array(
		'1' => get_string('january', 'local_jra'),
		'2' => get_string('february', 'local_jra'),
		'3' => get_string('march', 'local_jra'),
		'4' => get_string('april', 'local_jra'),
		'5' => get_string('may', 'local_jra'),
		'6' => get_string('june', 'local_jra'),
		'7' => get_string('july', 'local_jra'),
		'8' => get_string('august', 'local_jra'),
		'9' => get_string('september', 'local_jra'),
		'10' => get_string('october', 'local_jra'),
		'11' => get_string('november', 'local_jra'),
		'12' => get_string('december', 'local_jra'),
	);
	return $months;
}

function jra_lookup_marital_status()
{
	return array(
		'S' => get_string('single', 'local_jra'),
		'M' => get_string('married', 'local_jra'),
	);

}

function jra_lookup_active_status()
{
   return array(
		'A' => get_string('active', 'local_jra'),
		'S' => get_string('suspended', 'local_jra') ,
		'B' => get_string('barred', 'local_jra'),
		'O' => get_string('on', 'local_jra'),
	);
}

function jra_lookup_user_status($blank = '')
{
	$arr = array(
		'A' => get_string('active', 'local_jra'),
		'P' => get_string('pending', 'local_jra'),
		'I' => get_string('inactive', 'local_jra'),
	);
	if($blank != '')
		$arr = array_merge(['' => $blank], $arr);
	return $arr;
}

function jra_lookup_admission_status($blank = '')
{
	$arr = array();
	if($blank != '')
		$arr[''] = $blank;
	$arr['5'] = get_string('pending', 'local_jra');
	$arr['11'] = get_string('approved', 'local_jra');
	$arr['12'] = jra_get_string(['waiting', 'list']);
	$arr['13'] = get_string('rejected', 'local_jra');
	return $arr;
}

function jra_lookup_admission_confirm_status()
{
	$arr = array();
	$arr['1'] = get_string('accepted', 'local_jra');
	$arr['2'] = get_string('declined', 'local_jra');
	$arr['3'] = get_string('suspended', 'local_jra');
	$arr[''] = get_string('unconfirmed', 'local_jra');
	$arr['5'] = get_string('locked', 'local_jra');
	return $arr;
}

function jra_lookup_blood_type($blank = '')
{
	$arr = array();
	if($blank != '')
		$arr[''] = $blank;
	$arr['A+'] = 'A+';
	$arr['A-'] = 'A-';
	$arr['B+'] = 'B+';
	$arr['B-'] = 'B-';
	$arr['O+'] = 'O+';
	$arr['O-'] = 'O-';
	$arr['AB+'] = 'AB+';
	$arr['AB-'] = 'AB-';
	return $arr;

}

function jra_lookup_state($blank = '', $country = 'SA')
{
	global $DB;
	if(current_language() != 'en')
		$order = 'state_a';
	else
		$order = 'state';
	$condition = array();
	$condition['country'] = $country;
	$cities = $DB->get_records('jra_city', $condition, $order);
	if($blank != '')
		$arr[''] = $blank;
	foreach($cities as $r)
	{
		$arr[$r->state] = jra_output_show_field_language($r, 'state');
	}
	return $arr;
}

function jra_lookup_city($state = '', $blank = '', $country = 'SA')
{
	global $DB;
	if(current_language() != 'en')
		$order = 'city_a';
	else
		$order = 'city';
	$condition = array();
	$condition['country'] = $country;
	if($state != '')
		$condition['state'] = $state;
	$cities = $DB->get_records('jra_city', $condition, $order);
	$arr = array();
	if($blank != '')
		$arr[''] = $blank;
	foreach($cities as $r)
	{
		$arr[$r->city] = jra_output_show_field_language($r, 'city');
	}
	return $arr;
}

function jra_lookup_city_applicant($blank = '')
{
	global $DB;
	$semester = jra_get_semester();
	$sql = "select distinct address_city from {si_applicant_contact} a inner join {si_applicant} b on a.applicant_id = b.id where semester = '$semester' order by address_city";
	$cities = $DB->get_records_sql($sql);
	$arr = array();
	if($blank != '')
		$arr[''] = $blank;
	foreach($cities as $r)
	{
		$arr[$r->address_city] = $r->address_city;
	}
	return $arr;
}

//get a state given a city
function jra_lookup_get_state($city, $country = 'SA')
{
	global $DB;
	$condition = array();
	$condition['country'] = $country;
	$condition['city'] = $city;
	$city = $DB->get_record('jra_city', $condition);
	return $city;
}

//get semester list
function jra_lookup_semester($blank = '')
{
	global $DB;
	$condition = array(
		'institute' => jra_get_institute(),
	);
	$rec = $DB->get_records('si_semester', $condition, 'start_date desc');
	$arr = array();
	if($blank != '')
		$arr['all'] = $blank;
	foreach($rec as $r)
	{
		$arr[$r->semester] = $r->semester;
	}
	return $arr;
}

//get individual semester
function jra_lookup_get_semester($semester)
{
	global $DB;
	$sem = $DB->get_record('si_semester', array('semester' => $semester, 'institute' => jra_get_institute()));
	return $sem;
}

function jra_lookup_document_type($blank = '')
{
	$arr = array();
	if($blank != '')
		$arr[''] = $blank;
	$arr['national'] = jra_get_string(['national_id']);
	$arr['secondary'] = get_string('secondary_school_document', 'local_jra');
	$arr['tahseli'] = get_string('tahseli', 'local_jra');
	$arr['qudorat'] = get_string('qudorat', 'local_jra');
	return $arr;
}

function jra_lookup_document_type_crtp($blank = '')
{
	$arr = array();
	if($blank != '')
		$arr[''] = $blank;
	$arr['national'] = jra_get_string(['national_id']);
	$arr['transcript'] = get_string('transcript', 'local_jra');
	$arr['uni_approval'] = get_string('uni_approval', 'local_jra');
	$arr['tabeiah'] = get_string('tabeiah', 'local_jra');
	return $arr;
}


//hijrah month list in arabic (we don't use translation)
function jra_lookup_get_hijrah_month()
{
	$arr = array();
	$arr['1'] = get_string('Muharram', 'local_jra');
	$arr['2'] = get_string('Safar', 'local_jra');
	$arr['3'] = get_string('Rabea Awwal', 'local_jra');
	$arr['4'] = get_string('Rabea Thani', 'local_jra');
	$arr['5'] = get_string('Jumad Awwal', 'local_jra');
	$arr['6'] = get_string('Jumad Thani', 'local_jra');
	$arr['7'] = get_string('Rajab', 'local_jra');
	$arr['8'] = get_string('Shaaban', 'local_jra');
	$arr['9'] = get_string('Ramadan', 'local_jra');
	$arr['10'] = get_string('Shawwal', 'local_jra');
	$arr['11'] = get_string('Thul El-Qiadah', 'local_jra');
	$arr['12'] = get_string('Thul El-Hijjah', 'local_jra');
	return $arr;
}

//hijrah month list in arabic (we don't use translation)
function jra_lookup_get_hijrah_month_a()
{
	$arr = array();
	return $arr;
}

function jra_lookup_university($blank = '')
{
	global $DB;
	if(current_language() != 'en')
		$order = 'name_a';
	else
		$order = 'name';
	$records = $DB->get_records('si_university', array(), $order);
	if($blank != '')
		$arr[''] = $blank;
	foreach($records as $r)
	{
		$arr[$r->id] = jra_output_show_field_language($r, 'name');
	}
	return $arr;
}

function jra_lookup_major($blank = '')
{
	global $DB;
	if(current_language() != 'en')
		$order = 'name_a';
	else
		$order = 'name';
	$records = $DB->get_records('si_major', array(), $order);
	if($blank != '')
		$arr[''] = $blank;
	foreach($records as $r)
	{
		$arr[$r->id] = jra_output_show_field_language($r, 'name');
	}
	return $arr;
}


function jra_lookup_maxgpa($blank = '')
{
	$arr = array();
	if($blank != '')
		$arr[''] = $blank;
		$arr['4.00'] = '4.00';
		$arr['5.00'] = '5.00';

	return $arr;

}
