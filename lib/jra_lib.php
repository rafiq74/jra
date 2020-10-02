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

jra_bootstraper(); //run the bootstraper

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

//this is for user variable
//when subfield is used, it means we want the specific value
function jra_get_config_user($moodle_user_id, $varname, $subfield = '')
{
	global $DB;
	$condition = array(
		'country' => jra_get_country(),
		'name' => $varname,
		'moodle_user_id' => $moodle_user_id,
	);
	if($subfield != '')
	{
		//if subfield is not empty, it means more than one variables. Retrieve as array and return the entire result.
		$result = $DB->get_records('jra_config_user', $condition);
		return $result;
	}
	else
	{
		$result = $DB->get_record('jra_config_user', $condition);
		if($result)
			return $result->var_value;
		else
			return '';
	}
}

//this is for user variable
//if only single variable, put the subfiled name similar to varname to allow value retrieval
function jra_update_config_user($moodle_user_id, $varname, $subfield, $value)
{
	global $DB;
	$country = jra_get_country();
	//delete the value first.
	if($subfield != '')
		$sub = " and subfield = '$subfield'";
	else
		$sub = '';
	$sql = "delete from {jra_config_user} where name = '$varname'" . $sub . " and country = '$country' and moodle_user_id = '$moodle_user_id'";
	$DB->execute($sql);
	//now add it
	$sql = "insert into {jra_config_user} (moodle_user_id, name, subfield, var_value, country) values('$moodle_user_id', '$varname', '$subfield', '$value', '$country')";
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

//reset the config variable session
function jra_kill_config_session($setting_name)
{
	$var_name = 'jra_var_' . $setting_name; //prepend with jra_var to avoid duplicate
	jra_set_session($var_name, ''); //set empty
}

//truth indicates if we need to return true or false. Otherwise, empty string if not closed and the closed message if closed
//close is determined by start and end date. The current date must be in between them
//if it is opened, if show_open_message is true, it will return the open message
function jra_is_closed($truth = false, $show_open_message = false)
{
	global $DB;
	
//	$is_close = jra_get_session('jra_is_closed');
//	if($is_close == '')
	{
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
	}
	if($truth)
		return $is_closed;
	else
	{
		$a = new stdClass();
		$a->start_date = jra_output_formal_datetime_12($start_date);
		$a->end_date = jra_output_formal_datetime_12($end_date);
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

function jra_include_jquery()
{
	global $PAGE;
	$PAGE->requires->jquery();
	$PAGE->requires->jquery_plugin('ui');
	$PAGE->requires->jquery_plugin('ui-css'); 
}

function jra_allow_application()
{
	global $USER;
	if((isset($USER->jra_user) && $USER->jra_user->active_status != 'A') || jra_get_user_type() != 'public')
		throw new moodle_exception('Error!!! User not active to perform this action');
}

//get the default institute. Pass the field like country to get the country of the institute
function jra_get_institute()
{
	return jra_get_config_session('default_institute');
}

function jra_get_semester()
{
	return jra_get_config_session('default_semester');
}
//get the default country. Pass the field like country to get the country of the country
function jra_get_country()
{
	global $DB, $USER;
	//for now we just return MY
	return 'SA';
	
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

function jra_get_user_type()
{
	global $USER;
	if(isset($USER->jra_user))
		return $USER->jra_user->user_type;
	else //if not set in jra_user means he is a moodle user. Could be admin. So return employee
		return 'employee';
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

//get the moodle user given a jra_user record
//we use jra_user record because we can have one function to decide on which is the right matching field
//avoid passing id as when the field changes, there will be many places to change.
function jra_get_moodle_user($user_data)
{
	global $DB;
	$m_user = $DB->get_record('user', array('idnumber' => $user_data->id));
	return $m_user; //false if not found
}

//assign a user to moodle role. roleid is the id of the moodle role
function jra_assign_moodle_role($user_id, $roleid)
{
	global $DB;
	$jra_user = $DB->get_record('jra_user', array('id' => $user_id));
	if($jra_user)
	{
		$moodle_user = jra_get_moodle_user($jra_user);
		if($moodle_user)
		{
			$systemcontext = context_system::instance();			
			role_assign($roleid, $moodle_user->id, $systemcontext->id);
		}
	}
//	role_unassign($role->id, $moodle_user->id, $systemcontext->id);
}

//unassign a user to moodle role. roleid is the id of the moodle role
function jra_unassign_moodle_role($user_id, $roleid)
{
	global $DB;
	$jra_user = $DB->get_record('jra_user', array('id' => $user_id));
	if($jra_user)
	{
		$moodle_user = jra_get_moodle_user($jra_user);
		if($moodle_user)
		{
			$systemcontext = context_system::instance();
			role_unassign($roleid, $moodle_user->id, $systemcontext->id);
		}
	}
}

function jra_user_preference($var_name, $subfield = '', $moodle_user_id = '')
{
	global $USER;
	if($moodle_user_id == '')
		$moodle_user_id = $USER->id;
	$var_value = jra_get_config_user($moodle_user_id, $var_name, $subfield);
	return $var_value;
}

////////ACCESS CONTROL///////////////////////
//main function for access control
//params is an array of roles
/*
$params = [role] => [subrole] => [parameter1, parameter2, parameter3]
					[subrole] => [parameters]
					[subrole] => [parameters]
					
$params['admin'] is a special role for super admin (site administrator)
*/
//check if user is jra system admin. We avoid using is_siteadmin because it only allows 1 user.
//for now we use is_siteadmin. But later we can change to other role. This role must be a moodle defined role, not jra role
function jra_is_system_admin()
{
	if(is_siteadmin()) //for super admin, always allow
		return true; 
	else
		return false;
}

/* params is a list of allowable access pass from the page access by user
system - moodle site administrator
admin - jra administrator

error_fail will throw error if failed, else return false

$params structure:
$params = array(
	'role' => 'admin',
	'subrole' => 'all',
);

*/

//check if user is the application admin, allow all modules except for those system
function jra_is_app_admin()
{
	global $USER;
	if(jra_is_system_admin()) //system admin always true
		return true;
	$user_roles = jra_get_user_role($USER->idnumber);					
	if(isset($user_roles['admin'])) //if has admin, give access to all, so no need to go further
		return true;
	return false;
}

function jra_access_control($params = array(), $error_fail = true)
{
	global $USER, $OUTPUT;
	$failed = false;
	if(jra_get_user_type() == 'public') //never allow student to enter any page with access control
		$failed = true;
	else
	{
		if(jra_is_system_admin()) //for super admin, always allow
			return true; 
		else //check for access
		{
			if(isset($params['system'])) //if it require system admin, then it failed because user is not moodle site administrator
				$failed = true;
			else
			{
				if(count($params) > 0) //has role to check
				{
					//get the user access role
					$user_roles = jra_get_user_role($USER->idnumber);			
					
					if(!isset($user_roles['admin'])) //if has admin, give access to all, so no need to go further
					{
						$role = $params['role'];
						if(is_array($role)) //more than one permissable role, role is the array of access_rules
						{
							$failed = true;
							foreach($role as $r) //repeat for every roles until we find one that pass
							{
								$failed = jra_validate_user_role($user_roles, $r);
								if(!$failed) 
								{
									break;
								}
							}
						}
						else
						{
							$failed = jra_validate_user_role($user_roles, $params);
						}
					}
				}
			}
		}
	}
	if($failed)
	{
		if($error_fail)
		{
			jra_throw_access_denied();
		}
		else
			return false;
	}
	else 
		return true;
}

function jra_validate_user_role($user_roles, $params)
{
	$failed = false;
	$role = $params['role'];
	if(isset($user_roles[$role])) //if has the role. Check for subrole
	{
		if(!isset($user_roles[$role]['all'])) //if has all, then pass. If not, go in further
		{
			$subrole = $params['subrole']; //get the subrole and check if user has the subrole
			if(is_array($subrole)) //more than one permissable role
			{
				$failed = true;
				foreach($subrole as $sr)
				{
					if(isset($user_roles[$role][$sr])) //if no matching subrole, failed
					{
						$failed = false;
						break;
					}
				}
			}
			else //only one permissable role
			{
				if(!isset($user_roles[$role][$subrole])) //if no matching subrole, failed
					$failed = true;
			}
		}
	}
	else //if no role set, failed
		$failed = true;
	return $failed;
}

function jra_throw_access_denied($msg = 'access_error')
{
	global $PAGE, $OUTPUT, $SITE;
	$PAGE->set_pagelayout('jra');
	$PAGE->set_title(jra_site_fullname());
	$PAGE->set_heading(jra_site_fullname());
	echo $OUTPUT->header();
	throw new moodle_exception(get_string($msg, 'local_jra'));	
}

//get build the role array of a given user
function jra_get_user_role($user_id, $role = '')
{
	global $DB;
	$condition = array('user_id' => $user_id);
	if($role != '')
		$condition['role'] = $role;
	$roles = $DB->get_records('jra_user_role', $condition);
	$user_roles = array();
	foreach($roles as $role)
	{
		$user_roles[$role->role][$role->subrole] = explode(',', $role->role_value);
	}
	jra_set_session('jra_user_roles', $user_roles); //set session is to avoid secondary page to repeat the process. Not because we want to use the session for role
	return $user_roles;
}

//check if user has the access to a certain role and subrole in the user roles assignment array
//roles is the user access role retrieved from DB
//this function is used mainly in the main menu to determine which menu to show
//permitted_roles is an array of array where the item is an array of roles and array of sub roles
function jra_has_access($roles, $permitted_roles)
{
	$failed = false;
	if(jra_get_user_type() == 'student') //never allow student to enter any page with access control
		$failed = true;
	else
	{
		if(jra_is_system_admin()) //for super admin, always allow
			return true; 
		else //check for access
		{
			if(isset($roles['admin'])) //if user is system admin, always allow
				return true;
			else
			{
				foreach($permitted_roles as $role)
				{
					if(isset($roles[$role['role']]))
					{
						if(!isset($role['subrole']) || empty($role['subrole'])) //no need to check for subrole, pass
							return true;
						else
						{
							if(isset($roles[$role['role']]['all'])) //has all access, return true
								return true;
							else
							{
								$subroles = $role['subrole'];
								foreach($subroles as $subrole)
								{
									if(isset($roles[$role['role']][$subrole]))
										return true;
								}
								return false; //have to loop until the end before we return false
							}
						}
					}
				}
				return false;
			}
		}
	}
}

//get a list of roles (for now hard coded)
//do not use the role admin as it is reserved for site administrator
function jra_get_roles()
{
	$arr['admin'] = get_string('system', 'local_jra') . ' ' . get_string('administrator');
	$arr['admission'] = get_string('admission', 'local_jra') . ' ' . get_string('administrator');
	return $arr;
}

function jra_get_subroles($role)
{	
	$arr['admin'] = array(
		'all' => jra_get_string(['all', 'operations']),
		);
	$arr['admission'] = array(
		'all' => jra_get_string(['all', 'operations']),
		);
	
	$ret = $arr[$role];		
	return $ret;
}

////////ACCESS CONTROL///////////////////////

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

//given the role short name, check if user has the role
function jra_has_moodle_role($user, $role)
{
	global $DB;
	$role = $DB->get_record('role', array('shortname' => $role));
	$context = get_context_instance(CONTEXT_SYSTEM);
	$roles = get_role_users($role->id, $context);
	if(isset($roles[$user->id]))	
		return true;
	else
		return false;
}

//this is the function that will run on every page to check if any function needs to be executed first
function jra_bootstraper($redir = true)
{
	global $CFG, $DB, $USER;
	if($USER->auth == 'db' && !isset($USER->jra_user))
	{
		$u = $DB->get_record('jra_user', array('id' => $USER->idnumber));
		if($u)
			$USER->jra_user = $u; //remember the session
	}
	if (isloggedin()) //this part only for logged in user
	{
		jra_eula(); //end user licence agreement
		jra_need_change_password();
	}	
	jra_reset_session();
	//check if user is suspended
//	jra_is_suspended();
	//for course page bootstraper
		//Check for QC Survey
//		if(jra_has_incomplete_survey($COURSE, $USER)) //if has survey, redirect to do survey
//		{
//			$survey_url = new moodle_url($CFG->wwwroot.'/local/rcyci/survey/survey.php', array('id' => $COURSE->id));		
//			redirect($survey_url);
//		}		
	return false;
}

//some sessions need to be reset on every page
function jra_reset_session()
{
	jra_kill_config_session('default_semester');
	jra_set_session('jra_is_closed', ''); //also kill the is_closed session
}

function jra_need_change_password($redir = true)
{
	global $CFG, $USER, $DB;
	if(isset($_SESSION['jra_change_password']))
	{
		if($_SESSION['jra_change_password'] === true)
		{			
			if($redir)
			{
				$url = new moodle_url($CFG->wwwroot . '/local/jra/user/change_password.php');
			    redirect($url);
			}
			else
				return true;
		}
	}
	else //first time, check if user need to reset his password
	{
		$_SESSION['jra_change_password'] = false;
		if($USER->auth == 'db') //only if it is from external user
		{
			$u = $DB->get_record('jra_user', array('id' => $USER->idnumber));
			if($u && $u->password_change == 'Y')
			{
				$_SESSION['jra_change_password'] = true;
				if($redir)
				{
					$url = new moodle_url($CFG->wwwroot . '/local/jra/user/change_password.php');
					redirect($url);
				}
				else
					return true;
			}
		}
	}
}

function jra_eula($redir = true)
{
	global $CFG, $USER, $DB;
	if(isset($_SESSION['jra_eula']))
	{
		if($_SESSION['jra_eula'] === 2)
		{			
			if($redir)
			{
				$url = new moodle_url($CFG->wwwroot . '/local/jra/user/eula.php');
			    redirect($url);
			}
			else
				return true;
		}
		else if($_SESSION['jra_eula'] === 3)
		{
			if($USER->jra_user->eula == 'R')
			{
				$url = new moodle_url($CFG->wwwroot . '/local/jra/user/eula_reject.php');
				redirect($url);
			}
		}
	}
	else //first time, check if user need to accept the eula
	{
		$_SESSION['jra_eula'] = 1; //pass eula
		if($USER->auth == 'db') //only if it is from external user
		{
			$u = $DB->get_record('jra_user', array('id' => $USER->idnumber));
			$var_name = $u->user_type . '_require_eula';
			$var_value = jra_get_config($var_name);
			if($var_value == 'Y')
			{
				if($u->eula == '')
				{
					$_SESSION['jra_eula'] = 2; //user has not confirm eula
					if($redir)
					{
						$url = new moodle_url($CFG->wwwroot . '/local/jra/user/eula.php');
						redirect($url);
					}
					else
						return true;
				}
				else if($u->eula == 'R') //user has rejected the eula, so redirect to reject page
				{
					$_SESSION['jra_eula'] = 3; //user rejected eula
					$url = new moodle_url($CFG->wwwroot . '/local/jra/user/eula_reject.php');
					redirect($url);
				}
			}
		}
	}
}

function jra_allow_password_change($m_user)
{
	global $DB;
	return true; //for jra, always return true
}

//convert date to hijrah given a date in the format d-M-Y
function jra_to_hijrah($aDate)
{
	global $CFG;
	$format = "d/m/Y";
	require_once $CFG->dirroot . '/local/jra/lib/jra_hdate.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
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


//tmp course is the most important template course that defines the weekly structure
function jra_get_tmp_course()
{
	global $DB;
	$course = $DB->get_record('course', array('shortname' => 'TMP101'), '*', MUST_EXIST);
	return $course;
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

///////////////////UNVERIFIED FUNCTIONS///////////////////////////////////////

function jra_get_course_teacher($course)
{
	global $DB;
	$role = $DB->get_record('role', array('shortname' => 'editingteacher'));
	$context = get_context_instance(CONTEXT_COURSE, $course->id);
	$teachers = get_role_users($role->id, $context);
	return $teachers;
}


function jra_is_suspended($redir = true)
{
	global $CFG;
	if(isset($_SESSION['jra_suspended']))
	{
		if($_SESSION['jra_suspended'] === true)
		{			
			if($redir)
			{
				$url = new moodle_url($CFG->wwwroot . '/local/jra/suspended.php');
			    redirect($url);
			}
			else
				return true;
		}
	}	
}
