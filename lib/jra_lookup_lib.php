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
	$where = " and country = '" . jra_get_country() . "'"; //initialize a where clause with country
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

function jra_lookup_insert($value, $lang, $category, $subcategory, $country, $sort_order = 1, $description = '')
{
	global $DB;
	$data = new stdClass();
	$data->value = $value;
	$data->lang = $lang;
	$data->category = $category;
	$data->subcategory = $subcategory;
	$data->sort_order = $sort_order;
	$data->description = $description;
	$data->country = $country;
	$DB->insert_record('jra_lookup', $data);	
}

function jra_lookup_update($id, $value, $lang = '', $category = '', $subcategory = '', $country = '', $sort_order = '')
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
function jra_lookup_duplicate($value, $lang, $category, $subcategory, $country, $id = '')
{
	global $DB;
	$where = "where country = '$country' and category = '$category' and subcategory = '$subcategory' and value = '$value' and lang = '$lang'";
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

function jra_lookup_language()
{
	return array(
		'en' => get_string('english', 'local_jra'), 
		'ms' => get_string('malay', 'local_jra'),
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

function jra_lookup_user_type()
{
	return array(
		'public' => get_string('public', 'local_jra'), 
		'employee' => get_string('employee', 'local_jra'),
	);
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
		'W' => get_string('widowed', 'local_jra'),
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


function jra_lookup_state($blank = '', $country = 'MY')
{
	global $DB;	
	$states = jra_get_session('jra_state'); //try to see if the state session is defined
	if(!is_array($states)) //state not defined
	{
		$states = $DB->get_records_menu('jra_state', ['country' => $country], 'sort_order', 'state_code, state');
		jra_set_session('jra_state', $states);
	}
	if($blank != '')
		$arr = array_merge(['' => $blank], $states);
	else
		$arr = $states;
	return $arr;
}

function jra_lookup_city($state_code = '', $blank = '', $country = 'MY')
{
	global $DB;
	$condition = array();
	$condition['country'] = $country;
	if($state_code != '')
		$condition['state_code'] = $state_code;
	$cities = $DB->get_records_menu('jra_city', $condition, 'city', 'id, city');
	if($blank != '')
		$arr = array_merge(['' => $blank], $cities);
	else
		$arr = $cities;
	return $arr;
}