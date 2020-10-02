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
function jra_global_var($var)
{
	global $USER;
	if($var == 'PER_PAGE')
	{
		$record_per_page = jra_get_session('user_record_per_page');
		if($record_per_page == '')
		{
			$var_name = 'user_record_per_age';
			$record_per_page = jra_get_config_user($USER->id, $var_name);
			if($record_per_page == '')
				$record_per_page = '30';
			jra_set_session('user_record_per_page', $record_per_page);
		}
	}
	$a['PER_PAGE'] = $record_per_page;
	$a['PRINT_PER_PAGE'] = 1000000; //for printing, put a very large value
	$a['TEMP_USER_ID'] = '---';
	return $a[$var];
}

//when subfield is used, it means we want the specific value
function jra_get_config($varname, $subfield = '')
{
	global $DB;
	$condition = array(
		'country' => jra_get_country(),
		'name' => $varname,
	);
	if($subfield != '')
	{
		//if subfield is not empty, it means more than one variables. Retrieve as array and return the entire result.
		$result = $DB->get_records('jra_config', $condition);
		return $result;
	}
	else
	{
		$result = $DB->get_record('jra_config', $condition);
		if($result)
			return $result->var_value;
		else
			return '';
	}
}

//if only single variable, put the subfiled name similar to varname to allow value retrieval
function jra_update_config($varname, $subfield, $value)
{
	global $DB;
	$country = jra_get_country();
	//delete the value first.
//	if($subfield != '')
//		$sub = " and subfield = '$subfield'";
//	else
		$sub = '';
	$sql = "delete from {jra_config} where name = '$varname'" . $sub . " and country = '$country'";
	$DB->execute($sql);
	//now add it
	$sql = "insert into {jra_config} (name, subfield, var_value, country) values('$varname', '$subfield', '$value', '$country')";
	$DB->execute($sql);
}

function jra_get_session($name)
{
	if(isset($_SESSION[$name]))
		return $_SESSION[$name];
	else
		return '';
}

function jra_set_session($name, $value)
{
	$_SESSION[$name] = $value;
}

function jra_include_jquery()
{
	global $PAGE;
	$PAGE->requires->jquery();
	$PAGE->requires->jquery_plugin('ui');
	$PAGE->requires->jquery_plugin('ui-css'); 
}

//get the default country. Pass the field like country to get the country of the country
function jra_get_country()
{
	global $DB, $USER;
	//for now we just return MY
	return 'MY';
	
	/////next time
	if(!jra_is_system_admin())
	{
		if($USER->institution == '')
			throw new moodle_exception('Error!!! No institution defined for this user');
	}
	else //side admin, try to initialize it
	{
		if($USER->institution == '')
		{
			$records = $DB->get_records('jra_institute');
			$institute = false;
			foreach($records as $institute)
				break;
			if($institute)
			{
				$u = $DB->get_record('user', array('id' => $USER->id));
				$u->institution = $institute->institute;
				$DB->update_record('user', $u);
				$USER->institution = $institute->institute;
			}
			else
				throw new moodle_exception('Error!!! User must be assigned to at least one institute');				
		}
	}
	if($field == 'institute') //just get the institute, return it
		return $USER->institution;
	else //need to get the field
	{
		$institute = $DB->get_record('jra_institute', array('institute' => $USER->institution));
		if($institute)
			return $institute->{$field};
		else
			throw new moodle_exception('Error!!! No institution defined for this user');
	}
}

//given an array of language string, output it in concatenated form with space
//Capitalization: T = title, S = sentence, A = all cap, L = all lower. Blank will return as it is
function jra_get_string($arr, $capitalization = '')
{
	$str = '';
	foreach($arr as $a)
	{
		if($str != '')
			$str = $str . ' ';
		$str = $str . get_string($a, 'local_jra');
	}
	if($capitalization != '')
	{
		if($capitalization == 'T') //title
			$str = ucwords($str);
		else if($capitalization == 'S') //sentence
			$str = ucfirst(strtolower($str));
		else if($capitalization == 'A') //all cap
			$str = strtoupper($str);
		else
			$str = strtolower($str); //small cap
	}
	return $str;
}

function jra_site_fullname()
{
	return get_string('brand_name', 'local_jra');
}

//get an item from array. 
function jra_array_item($arr, $key)
{
	if(isset($arr[$key]))
		return $arr[$key];
	else
		return null;
}

//convert date to hijrah given a date in the format d-M-Y
function jra_to_hijrah($aDate, $format = "d/m/Y")
{
	global $CFG;
	require_once $CFG->dirroot . '/local/rcyci/lib/jra_hdate.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
	$hdate = new HijriDateTime();
	return $hdate->gregorianToHijrah($aDate, $format);	
}

//given a western number, replace with arabic number
function jra_arabic_number($num)
{
	if($num == '.')
		return ",";
	$western_arabic = array('0','1','2','3','4','5','6','7','8','9');
	$eastern_arabic = array('٠','١','٢','٣','٤','٥','٦','٧','٨','٩');
	if(isset($western_arabic[$num]))
		return str_replace($western_arabic, $eastern_arabic, $num);
	else
		return $num; //return as it is if it is not a number
}

//given a textual number in string, return the arabic counter part
function jra_to_arabic_num($num)
{
	$a = strval($num);	
	$ret = '';
	for($i = 0; $i < strlen($a); $i++)
	{
		$ret = $ret . jra_arabic_number($a[$i]);
	}
	return $ret;
}

//because web service is very difficult to debug, we have a custom print_object function that writes to a database field.
//then a custom debug page will show the content of the output. New session will clear the database. If not, each call will
//write to a new record
function jra_print_object($object, $new_session = false)
{
	global $DB;
	if($new_session)
		jra_reset_debug();
	$rec = new stdClass();
	$rec->output = '<pre>' . htmlspecialchars(print_r($object,true)) . '</pre>';
	$DB->insert_record('jra_debug', $rec, false);
} 

//empty the debug table
function jra_reset_debug()
{
	global $DB;
	$sql = "delete from {jra_debug}";
	$DB->execute($sql);
}

//New generic log approach. Log table must have the same name as the original table with ending _log. 
//No problem if the field is reduced. But must be same name and type
//it will have additional field of change_date, change_user, and change_ip (ip address of the said user), log_action
//log_action is optional
function jra_log_data($table, $data, $change_action = '')
{
	global $DB, $USER;
	$table_name = $table . '_log';
	$log_data = clone $data; //clone the data
	$log_data->id = ''; //remove the id
	$ip_address = $_SERVER['REMOTE_ADDR'];
	$log_data->change_data_id = $data->id;
	$log_data->change_date = time();
	$log_data->change_user = $USER->id;
	$log_data->change_ip = $ip_address;
	$log_data->change_action = $change_action;
	$DB->insert_record($table_name, $log_data);	
}


//function to encrypt a text
//if no key (not recommended), use the default
function jra_encrypt($string, $key = '')
{

    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'manchester by the sea';
    $secret_iv = 'manchester by the sea iv';
    // hash
    $key = hash('sha256', $secret_key);
    
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
    $output = base64_encode($output);
    return $output;
//	return $encrypted;
}

//function to decrypt
//if no key (not recommended), use the default
function jra_decrypt($encrypted, $key = '')
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'manchester by the sea';
    $secret_iv = 'manchester by the sea iv';
    // hash
    $key = hash('sha256', $secret_key);
    
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    $output = openssl_decrypt(base64_decode($encrypted), $encrypt_method, $key, 0, $iv);
    return $output;
	
}

/**
return the translated language. This is useful in user entered field where we cannot control the translation
 */
function jra_lang($key = '') 
{
    $pos = strpos($key, 'lang:');
    if ($pos !== false) {
        list($l, $k) = explode(":", $key);
        if (get_string_manager()->string_exists($k, 'local_jra')) {
            $v = get_string($k, 'local_jra');
            return $v;
        } else {
            return $key;
        }
    } else {
        return $key;
    }
}

//get a setting and save it as session
function jra_get_config_session($setting_name)
{
	$var_name = 'jra_var_' . $setting_name; //prepend with jra_var to avoid duplicate
	$var = jra_get_session($var_name);
	if($var == '')
	{
		$var = jra_get_config($setting_name);
		jra_set_session($var_name, $var);
	}
	return $var;
}

function jra_get_semester()
{
	return jra_get_config_session('default_semester');
}

//truth indicates if we need to return true or false. Otherwise, empty string if not closed and the closed message if closed
//close is determined by start and end date. The current date must be in between them
//if it is opened, if show_open_message is true, it will return the open message
function jra_is_closed($truth = false, $show_open_message = false)
{
	global $DB;
	$semester = jra_get_semester();
	$rec = $DB->get_record('si_semester', array('semester' => $semester));
	$start_date = $rec->start_date;
	$end_date = strtotime(date('d-M-Y', $rec->end_date) . '+ 1 day') - 1; //end time has to add 24 hour minus one to get the final minute
	$now = time();
	if($now >= $start_date && $now <= $end_date)
		$is_closed = false;
	else
		$is_closed = true;
	jra_set_session('jra_is_closed', $is_closed);
	
	if($truth)
		return $is_closed;
	else
	{
		$a = new stdClass();
		$a->start_date = date('d-M-Y, h:i A', $start_date);
		$a->end_date = date('d-M-Y, h:i A', $end_date);
		if(!$is_closed)
		{
			if($show_open_message)
				return jra_ui_alert(get_string('admission_open_period', 'local_jra', $a), 'success', '', false, true);			
			else
				return '';
		}
		else
		{
			return jra_ui_alert(get_string('application_closed', 'local_jra') . '<br />' . get_string('admission_open_period', 'local_jra', $a), 'danger', '', false, true);
		}
	}
}
