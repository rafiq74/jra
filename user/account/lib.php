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

///////rc roles function//////////////////
function sis_user_account_assign_role($to_add, $role, $subrole, $role_value, $campus = '')
{
	global $DB, $USER;
	$now = time();
	foreach($to_add as $u)
	{
		$data = new stdClass();
		$data->id = '';
		$data->user_id = $u->id;
		$data->role = $role;
		$data->subrole = $subrole;
		$data->role_value = $role_value;
		$data->campus = $campus;
		$data->scope = '';
		$data->added_by = $USER->id;
		$data->date_added = $now;
		$data->institute = sis_get_institute();
		$duplicate_condition = array(
			'user_id' => $data->user_id,
			'role' => $data->role,
			'subrole' => $data->subrole,
		);
		$isDuplicate = sis_query_is_duplicate('si_role_user', $duplicate_condition, $data->id);
		if(!$isDuplicate) //no duplicate, update it, otherwise, don't do anything
		{
			sis_user_account_assign_moodle_role($data);
			$DB->insert_record('si_role_user', $data);	
		}
	}
}

function sis_user_account_assign_moodle_role($data)
{
	global $DB;
	if($data->role == 'admin' || $data->role == 'academic') //system admin
	{
		//admin has announcement
		if($data->role == 'admin')
		{
			$role = $DB->get_record('role', array('shortname' => 'announcement'));
			if($role)
				sis_assign_moodle_role($data->user_id, $role->id);
		}
		if($data->role == 'admin' || $data->subrole == 'elearning')
		{
			$role = $DB->get_record('role', array('shortname' => 'anycourse'));
			if($role)
				sis_assign_moodle_role($data->user_id, $role->id);
		}
	}
}

function sis_user_account_remove_role($to_remove)
{
	global $DB, $USER;
	$cascade = array();
	foreach($to_remove as $u)
	{
		$role = $DB->get_record('si_role_user', array('id' => $u->id));
		if($role)
			sis_user_account_unassign_moodle_role($role);
		sis_query_delete_cascade('si_role_user', $u->id, $cascade);		
	}
}

function sis_user_account_unassign_moodle_role($data)
{
	global $DB;
	if($data->role == 'admin' || $data->role == 'academic') //system admin
	{
		//admin has announcement
		if($data->role == 'admin')
		{
			$role = $DB->get_record('role', array('shortname' => 'announcement'));
			if($role)
				sis_unassign_moodle_role($data->user_id, $role->id);
		}
		if($data->role == 'admin' || $data->subrole == 'elearning')
		{
			$role = $DB->get_record('role', array('shortname' => 'anycourse'));
			if($role)
				sis_unassign_moodle_role($data->user_id, $role->id);
		}
	}
}


//remove any space after , in role value
function sis_user_account_format_role_value($role_value)
{
	$arr = explode(',', $role_value);
	$str = '';
	if(count($arr) > 0)
	{
		foreach($arr as $a)
		{
			if($str != '')
				$str = $str . ',';
			$str = $str . trim($a);
		}
	}
	return $str;
}

function sis_user_account_role_search_form($role, $subrole, $source, $role_value)
{
	$roles = sis_get_roles();
	$str = '';
	$str = $str . get_string('role', 'local_sis') . ' : ' . sis_ui_select('role', $roles, $role, "refresh_role('$source')");
	if($role == '')
	{
		$str = $str . '&nbsp;&nbsp;&nbsp;';
		$str = $str . '<input type="button" name="button2" id="button2" value="Refresh" onclick="refresh_role(\'' . $source. '\')"/>';
	}
	else
	{
		$subroles = sis_get_subroles($role);
		$str = $str . '&nbsp;&nbsp;&nbsp;';
		$str = $str . get_string('operation', 'local_sis') . ' : ' . sis_ui_select('subrole', $subroles, $subrole, "refresh_role('$source')");
		$str = $str . '&nbsp;&nbsp;&nbsp;';
		$str = $str . sis_user_account_role_parameter($role, $subrole, $role_value);
		$str = $str . '&nbsp;&nbsp;&nbsp;';
		
		$search_url = "javascript:refresh_role('$source')";
		
		$str = $str . sis_ui_button(get_string('refresh'), $search_url);
	}
	return $str;
}

//depending on the role, some need a text base parameters, some needs drop down
function sis_user_account_role_parameter($role, $subrole, $role_value)
{
	if($role == 'position') //for these roles, use drop down
	{
		if($subrole == 'md' || $subrole == 'dmd') //list of colleges
			$list = sis_lookup_campus();
		else //for hod, list of departments
		{
			$list = sis_lookup_campus();
		}
		$str = get_string('parameter', 'local_sis') . ' : ' . sis_ui_select('role_value', $list, $role_value);
	}
	else
		$str = get_string('parameter', 'local_sis') . ' : ' . sis_ui_input('role_value', 15, $role_value);
	return $str;
}

//to initialize the default role
function sis_user_account_init_role()
{
	$roles = sis_get_roles();
	foreach($roles as $key => $value)
		return $key;
}

//add a user to role
function sis_user_account_role_add_form($role, $subrole)
{
	global $CFG;
	$str = '<form id="form2" name="form2" method="post" action="">';
	$str = $str . 'Add User (EMPLID) : ' . sis_ui_input('user', 10, '', 'handleKeyPress(event)');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	if($role == 'position') //for these roles, use drop down
	{
		if($subrole == 'md' || $subrole == 'dmd') //list of colleges
			$list = rc_campus();
		else //for hod, list of departments
		{
			$list = rc_ps_get_department_list();
		}
		$str = $str . 'Parameter<sup>*</sup> : ' . sis_ui_select('role_value', $list);
	}
	else
		$str = $str . 'Parameter<sup>*</sup> : ' . sis_ui_input('role_value', 20, '', 'handleKeyPress2(event)');
	
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<input type="button" name="button4" id="button4" value="  Add  " onclick="add_role()"/>';
	$str = $str . sis_ui_hidden('role2', $role);
	$str = $str . sis_ui_hidden('subrole2', $subrole);
	$str = $str . '</form>';
	return $str;
}

///////end of role management/////////////

function sis_user_account_duplicate($data)
{
	$temp_id = sis_global_var('TEMP_USER_ID');
	if($data->appid == $temp_id) //temporary id which will be matriculated later
		return false;
	$duplicate_condition = array(
		'appid' => $data->appid,
		'institute' => $data->institute,
		'deleted' => 0,
	);
	$isDuplicate = sis_query_is_duplicate('si_user', $duplicate_condition, $data->id);
	return $isDuplicate;	
}

function sis_user_account_personal_info($id, $tab_active, $content)
{
	global $DB;
	$str = '';
	if($tab_active != '')
	{
		$params = array('id' => $id, 'op' => 'edit');
		$edit_url = new moodle_url('view_user.php', $params);	
		$str = $str . '<span class="pull-right rc-secondary-tab">' . html_writer::link($edit_url, sis_ui_icon('pencil', '1', true) . ' ' . get_string('edit', 'local_sis'), array('title' => get_string('edit', 'local_sis'))) . '</span>';
		$str = $str . $content;
		$str = $str . '<div id="ajax-content">';
		$str = $str . '</div>';	
	}
	return $str;	
}

function sis_user_account_address_info($id, $tab_active, $content)
{
	$str = '';
	if($tab_active != '')
	{
		$params = array('id' => $id, 'tab' => 'address', 'op' => 'edit');
		$add_url = new moodle_url('view_user.php', $params);	
		$str = $str . '<span class="pull-right rc-secondary-tab">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add_contact', 'local_sis'), array('title' => get_string('add_contact', 'local_sis'))) . '</span>';
		$str = $str . '<div id="ajax-content">';
		$str = $str . $content;	
		$str = $str . '</div>';	
	}
	return $str;	
}

function sis_user_account_employee_info($id, $tab_active, $content)
{
	$str = '';
	if($tab_active != '')
	{
		$params = array('id' => $id, 'tab' => 'employee', 'op' => 'edit');
		$edit_url = new moodle_url('view_user.php', $params);	
		$str = $str . '<span class="pull-right rc-secondary-tab">' . html_writer::link($edit_url, sis_ui_icon('pencil', '1', true) . ' ' . get_string('edit', 'local_sis'), array('title' => get_string('edit', 'local_sis'))) . '</span>';
		$str = $str . $content;
		$str = $str . '<div id="ajax-content">';
		$str = $str . '</div>';	
	}
	return $str;	
}

function sis_user_account_student_info($id, $tab_active, $content)
{
	$str = '';
	if($tab_active != '')
	{
		$params = array('id' => $id, 'tab' => 'student_info', 'op' => 'edit');
		$edit_url = new moodle_url('view_user.php', $params);	
		$str = $str . '<span class="pull-right rc-secondary-tab">' . html_writer::link($edit_url, sis_ui_icon('pencil', '1', true) . ' ' . get_string('edit', 'local_sis'), array('title' => get_string('edit', 'local_sis'))) . '</span>';
		$str = $str . $content;
		$str = $str . '<div id="ajax-content">';
		$str = $str . '</div>';	
	}
	return $str;	
}

function sis_user_account_account_info($id, $tab_active, $content)
{
	$str = '';
	if($tab_active != '')
	{
		$params = array('id' => $id, 'tab' => 'account', 'op' => 'edit');
		$edit_url = new moodle_url('view_user.php', $params);	
		$str = $str . '<span class="pull-right rc-secondary-tab">' . html_writer::link($edit_url, sis_ui_icon('pencil', '1', true) . ' ' . get_string('edit', 'local_sis'), array('title' => get_string('edit', 'local_sis'))) . '</span>';
		$str = $str . $content;
		$str = $str . '<div id="ajax-content">';
		$str = $str . '</div>';	
	}
	return $str;	
}

function sis_user_account_finance_info($id, $tab_active, $content)
{
	global $DB;
	$str = '';
	if($tab_active != '')
	{
		$user_data = $DB->get_record('si_personal_finance', array('user_id' => $id));
		$edit_url = new moodle_url('view_user.php', array('id' => $id, 'tab' => 'finance', 'op' => 'edit'));	
		$reset_url = new moodle_url('view_user.php', array('id' => $id, 'tab' => 'finance', 'op' => 'delete'));	
		
		$msg = get_string('confirm_reset_iban', 'local_sis');
		$js_url = "javascript:reset_iban('" . $reset_url->out(false) . "', '$msg')";
		$str = $str . '<span class="pull-right rc-secondary-tab">';
		if($user_data)
		{
			$str = $str . html_writer::link($js_url, sis_ui_icon('trash', '1', true) . ' ' . get_string('reset'), array('title' => get_string('reset') . ' ' . get_string('iban_no', 'local_sis'))) . '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;';
		}
		$str = $str . html_writer::link($edit_url, sis_ui_icon('pencil', '1', true) . ' ' . get_string('edit', 'local_sis'), array('title' => get_string('edit', 'local_sis')));
		$str = $str . '</span>';
		$str = $str . $content;
		$str = $str . '<div id="ajax-content">';
		$str = $str . '</div>';	
	}
	return $str;	
}

function sis_user_account_show_personal_address($id)
{
	global $DB;
	$records = $DB->get_records('si_personal_contact', array('user_id' => $id));
		
	$detail_data = array();
	foreach($records as $user_data)
	{
		//one row of data
		$obj = new stdClass();
		$obj->title = get_string($user_data->address_type, 'local_sis');
		$obj->content = sis_user_account_format_address($user_data);
		
		$params = array('id' => $id, 'tab' => 'address', 'op' => 'edit', 'dataid' => $user_data->id);
		$edit_url = new moodle_url('view_user.php', $params);			
		$obj->edit = '<span class="pull-right">' . html_writer::link($edit_url, sis_ui_icon('pencil', '1', true), array('title' => get_string('edit', 'local_sis'))) . '</span>';
		$detail_data[$user_data->address_type] = $obj;
		//end of data row
	}
	$address_type = sis_lookup_get_list('personal_info', 'address_type', '', true); //resort according to importance
	$sorted_list = array();
	foreach($address_type as $key => $val)
	{
		if(isset($detail_data[$key]))
			$sorted_list[] = $detail_data[$key];
	}
	$str = sis_ui_data_detail($sorted_list, 2, 3, true);
	echo $str;
}

function sis_user_account_format_address($user_data)
{
	$str = $user_data->address1;
	if($user_data->address2 != '')
	{
		if($str != '')
			$str = $str . '<br />';
		$str = $str . $user_data->address2;
	}
	$city_state = '';
	if($user_data->address_city != '')
		$city_state = $user_data->address_city;
	if($user_data->address_state != '')
	{
		if($city_state != '')
			$city_state = $city_state . ' ' . $user_data->address_state;
		else
			$city_state = $user_data->address_state;
	}
	if($user_data->address_postcode != '')
	{
		if($city_state != '')
			$city_state = $city_state . ' ' . $user_data->address_postcode;
		else
			$city_state = $user_data->address_postcode;
	}
	if($city_state != '')
	{
		if($str != '')
			$str = $str . '<br />';
		$str = $str . $city_state;
	}
	if($user_data->address_country != '')
	{
		if($str != '')
			$str = $str . '<br />';
		$str = $str . sis_lookup_countries($user_data->address_country);
	}
	if($user_data->email_primary != '')
	{
		if($str != '')
			$str = $str . '<br />';
		$str = $str . get_string('email', 'local_sis') . ' (' . get_string('primary', 'local_sis') . ') : ' . $user_data->email_primary;
	}
	if($user_data->email_secondary != '')
	{
		if($str != '')
			$str = $str . '<br />';
		$str = $str . get_string('email', 'local_sis') . ' (' . get_string('secondary', 'local_sis') . ') : ' . $user_data->email_secondary;
	}
	if($user_data->phone_mobile != '')
	{
		if($str != '')
			$str = $str . '<br />';
		$str = $str . get_string('phone_mobile', 'local_sis') . ' : ' . $user_data->phone_mobile;
	}
	if($user_data->phone_home != '')
	{
		if($str != '')
			$str = $str . '<br />';
		$str = $str . get_string('phone_home', 'local_sis') . ' : ' . $user_data->phone_home;
	}
	return $str;
}

function sis_user_account_show_personal_info($user_data)
{
	$detail_data = array();
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string($user_data->id_type, 'local_sis');
	$obj->content = $user_data->civil_id;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('passport_no', 'local_sis');
	$obj->content = $user_data->passport_id;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('date_of_birth', 'local_sis');
	$obj->content = date('d-M-Y', $user_data->dob);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('nationality', 'local_sis');
	$obj->content = sis_lookup_countries($user_data->nationality);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('nationality_at_birth', 'local_sis');
	$obj->content = sis_lookup_countries($user_data->nationality_at_birth);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$languages = sis_lookup_language();
	$obj = new stdClass();
	$obj->title = get_string('language_track', 'local_sis');
	$obj->content = $languages[$user_data->language_track];
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('religion', 'local_sis');
	$obj->content = $user_data->religion;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$marital_status = sis_lookup_marital_status();
	$obj = new stdClass();
	$obj->title = get_string('marital_status', 'local_sis');
	$obj->content = $marital_status[$user_data->marital_status];
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('blood_group', 'local_sis');
	$obj->content = $user_data->blood_type;
	$detail_data[] = $obj;
	//end of data row
	
	$str = sis_ui_data_detail($detail_data);
	echo $str;
}

function sis_user_account_show_employee_info($user_data)
{
	$detail_data = array();
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('join_date', 'local_sis');
	$obj->content = date('d-M-Y', $user_data->join_date);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('employment_type', 'local_sis');
	$obj->content = get_string($user_data->employment_type, 'local_sis');
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('employment_category', 'local_sis');
	$obj->content = get_string($user_data->employment_category, 'local_sis');
	$detail_data[] = $obj;
	//end of data row
	
	$str = sis_ui_data_detail($detail_data);
	echo $str;
}

function sis_user_account_show_student_info($user_data)
{
	$detail_data = array();
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('admission_date', 'local_sis');
	$obj->content = date('d-M-Y', $user_data->admission_date);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('admission_type', 'local_sis');
	$obj->content = get_string($user_data->admission_type, 'local_sis');
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('student_type', 'local_sis');
	$obj->content = get_string($user_data->student_type, 'local_sis');
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('student_category', 'local_sis');
	$obj->content = get_string($user_data->student_category, 'local_sis');
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('financing_source', 'local_sis');
	$obj->content = get_string($user_data->financing_source, 'local_sis');
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('payroll_active', 'local_sis');
	$obj->content = sis_output_show_yesno($user_data->payroll_active);
	$detail_data[] = $obj;
	//end of data row
	
	$str = sis_ui_data_detail($detail_data);
	echo $str;
}

function sis_user_account_show_account_info($user_data)
{
	global $CFG;
	//try to get the moodle user
	$m_user = sis_get_moodle_user($user_data);
	$detail_data = array();
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('enable_login', 'local_sis');
	$obj->content = sis_output_show_yesno($user_data->enable_login);
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('suspended', 'local_sis');
	$obj->content = sis_output_show_yesno($user_data->suspended);
	$detail_data[] = $obj;
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('suspend_message', 'local_sis');
	$obj->content = sis_ui_square(nl2br($user_data->suspend_message), true);
	$detail_data[] = $obj;
	//end of data row
	if($user_data->enable_login == 'Y')
	{
		$prefix = sis_get_institute('prefix');
		//one row of data
		$obj = new stdClass();
		$obj->title = get_string('username');
		$obj->content = $prefix . $user_data->appid;
		$detail_data[] = $obj;
		//end of data row
	}
	//end of data row
	if($m_user && $user_data->enable_login == 'Y')
	{
		//one row of data
		$obj = new stdClass();
		$obj->title = get_string('account_id', 'local_sis');
		$obj->content = $m_user->id;
		$detail_data[] = $obj;
		//end of data row
		//one row of data
		$obj = new stdClass();
		$obj->title = get_string('firstaccess');
		$obj->content = sis_output_formal_datetime($m_user->firstaccess);
		$detail_data[] = $obj;
		//end of data row
		//one row of data
		$obj = new stdClass();
		$obj->title = get_string('lastaccess');
		$obj->content = sis_output_formal_datetime($m_user->lastaccess);
		$detail_data[] = $obj;
		//end of data row
	}
	if(!$m_user && $user_data->enable_login == 'Y')
	{
		//one row of data
		$obj = new stdClass();
		$obj->title = get_string('lastaccess');
		$obj->content = sis_ui_alert(get_string('no_first_login', 'local_sis'), 'info', '', false, true);
		$detail_data[] = $obj;
	}
	if($user_data->enable_login == 'Y')
	{
		//one row of data
		if(sis_is_app_admin())
		{
			if($m_user) //user already has moodle account, enable to go to user profile page so we can perform loginas
			{
				$user_profile_url = new moodle_url($CFG->wwwroot . '/user/profile.php', array(id => $m_user->id));
				$profile_page = '&nbsp;&nbsp;&nbsp;' . html_writer::link($user_profile_url, get_string('user_profile', 'local_sis'),
					array(
						'class' => 'btn btn-primary',
						'aria-label' => get_string('user_profile', 'local_sis'),
						'target' => '_blank',
					));
			}
			else
				$profile_page = '';
		}
		else
			$profile_page = '';
		$params = $_GET;
		$a_url = new moodle_url('view_user.php', $params);
		$reset_password_url = "javascript:confirm_reset_password('".$a_url->out(false)."', '" . get_string('confirm_reset_password', 'local_sis') . "')";
		$var_name = $user_data->user_type . '_require_eula';
		$var_value = sis_get_config($var_name);
		if($var_value == 'Y')
		{
			$reset_eula_url = "javascript:confirm_reset_eula('".$a_url->out(false)."', '" . get_string('confirm_reset_eula', 'local_sis') . "')";
			$eula_button = sis_ui_space(3) . sis_ui_button(get_string('reset_eula', 'local_sis'), $reset_eula_url);
		}
		else
			$eula_button = '';
		$obj = new stdClass();
		$obj->title = '';
		$obj->content = sis_ui_button(get_string('reset_password', 'local_sis'), $reset_password_url) . $eula_button . $profile_page;
		$detail_data[] = $obj;
		//end of one row of data
	}
	$str = sis_ui_data_detail($detail_data);
	echo $str;
}

function sis_user_account_show_financial_info($user_data, $ret = false)
{
	$detail_data = array();
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('bank', 'local_sis');
	$obj->content = $user_data->bank_name . ' (' . $user_data->bank_name_a . ')';
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('iban_no', 'local_sis');
	$obj->content = sis_output_iban($user_data);
	$detail_data[] = $obj;
	//end of data row
	
	$str = sis_ui_data_detail($detail_data);
	if(!$ret)
		echo $str;
	else
		return $str;
}
