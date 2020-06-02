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

function jra_admin_setting_general()
{		
	$data = array();
	//one row of data
	$obj = new stdClass();
	$obj->column = 1;
	$update_all_url = new moodle_url('update_semester.php');
	$obj->left_content = html_writer::link($update_all_url, get_string('update_all_semester', 'local_jra'), array());	
	$data[] = $obj;
	//end of data row	
	$str = jra_ui_multi_column($data, 4, 4);	
	return jra_ui_box($str, get_string('general', 'local_jra'), '', true);
}

function jra_admin_setting_security()
{		
	$data = array();
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = get_string('default') . ' ' . get_string('password');
	$var_name = 'system_default_password';
	$var_value = jra_get_config($var_name);
	$password_list = array(
		'custom' => get_string('custom_password_text', 'local_jra'),
	);
	$obj->right_content = jra_ui_select($var_name, $password_list, $var_value);
	$data[] = $obj;
	//end of data row	
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = get_string('custom_password_text', 'local_jra');
	$var_name = 'system_default_password_custom';
	$var_value = jra_get_config($var_name);
	$obj->right_content = jra_ui_input($var_name, '30', $var_value);
	$data[] = $obj;
	//end of data row	
	$yesno = jra_lookup_yes_no();
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = get_string('force_password_change', 'local_jra');
	$var_name = 'system_force_password_change';
	$var_value = jra_get_config($var_name);
	$obj->right_content = jra_ui_select($var_name, $yesno, $var_value);
	$data[] = $obj;
	//end of data row	
	
	$str = jra_ui_multi_column($data, 4, 4);	
	return jra_ui_box($str, get_string('security', 'local_jra'), '', true);
}

//this is for user. So use jra_get_config_user
function jra_admin_setting_user()
{		
	global $USER;
	$data = array();
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = get_string('default') . ' ' . get_string('language', 'local_jra');
	$var_name = 'user_default_language';
	$var_value = $USER->lang; //for language we use the standard moodle language
	$lang_list = jra_lookup_language();
	$obj->right_content = jra_ui_select($var_name, $lang_list, $var_value);
	$data[] = $obj;
	//end of data row	
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = get_string('default') . ' ' . get_string('effective_date', 'local_jra');
	$var_name = 'user_default_eff_date';
	$var_value = jra_get_config_user($USER->id, $var_name);
	if($var_value == '')
		$var_value = time();
	$d = date('j', $var_value);
	$m = date('n', $var_value);
	$y = date('Y', $var_value);
	$day_list = jra_lookup_get_num_list(1, 31);
	$month_list = jra_lookup_month_list();
	$year_list = jra_lookup_get_year_list($y - 1);
	$obj->right_content = jra_ui_select('user_day', $day_list, $d) . ' ' . jra_ui_select('user_month', $month_list, $m) . ' ' . jra_ui_select('user_year', $year_list, $y);
	$data[] = $obj;
	//end of data row	
	$str = jra_ui_multi_column($data, 4, 4);	
	return jra_ui_box($str, get_string('general', 'local_jra'), '', true);
}

function jra_admin_setting_communication()
{
	$data = array();
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = jra_get_string(['default', 'student', 'email', 'address']);
	$var_name = 'default_student_email_address';
	$var_value = jra_get_config($var_name);
	$obj->right_content = jra_ui_input_fluid($var_name, '30', $var_value, '', 100);
	$data[] = $obj;
	//end of data row	
	$str = jra_ui_multi_column($data, 5, 5);	
	return jra_ui_box($str, get_string('contact', 'local_jra'), '', true);
}

function jra_admin_setting_selfservice_general()
{
	$data = array();
	$yesno = jra_lookup_yes_no();
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = jra_get_string(['enable', 'view', 'transcript']);
	$var_name = 'selfservice_enable_view_transcript';
	$var_value = jra_get_config($var_name);
	$obj->right_content = jra_ui_radio($var_name, $yesno, $var_value);
	$data[] = $obj;
	//end of data row	
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = jra_get_string(['enable', 'view', 'course', 'checklist']);
	$var_name = 'selfservice_enable_view_checklist';
	$var_value = jra_get_config($var_name);
	$obj->right_content = jra_ui_radio($var_name, $yesno, $var_value);
	$data[] = $obj;
	//end of data row	
	$str = jra_ui_multi_column($data, 5, 5);	
	return jra_ui_box($str, jra_get_string(['general']), '', true);
}

function jra_admin_setting_system_eula()
{
	$data = array();
	$yesno = jra_lookup_yes_no();
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = jra_get_string(['required', 'for', 'student']);
	$var_name = 'student_require_eula';
	$var_value = jra_get_config($var_name);
	$obj->right_content = jra_ui_radio($var_name, $yesno, $var_value);
	$data[] = $obj;
	//end of data row	
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = jra_get_string(['required', 'for', 'employee']);
	$var_name = 'employee_require_eula';
	$var_value = jra_get_config($var_name);
	$obj->right_content = jra_ui_radio($var_name, $yesno, $var_value);
	$data[] = $obj;
	//end of data row	
	$str = jra_ui_multi_column($data, 5, 5);	
	return jra_ui_box($str, get_string('eula', 'local_jra'), '', true);
}

function jra_admin_setting_system_login()
{
	$data = array();
	$yesno = jra_lookup_yes_no();
	//one row of data
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = jra_get_string(['enable', 'student']) . ' ' . get_string('login');
	$var_name = 'enable_student_login';
	$var_value = jra_get_config($var_name);
	$obj->right_content = jra_ui_radio($var_name, $yesno, $var_value);
	$data[] = $obj;
	//end of data row	
	$str = jra_ui_multi_column($data, 5, 5);	
	return jra_ui_box($str, get_string('login'), '', true);
}


