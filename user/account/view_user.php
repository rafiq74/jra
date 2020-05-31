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
 * This page is provided for compatability and redirects the user to the default grade report
 *
 * @package   core_grades
 * @copyright 2005 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php';
require_once '../../lib/sis_output_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once '../../lib/sis_lookup_lib.php';
require_once '../lib.php'; //parent local library
require_once 'lib.php'; //local library

$post_data = $_POST;
if(isset($post_data['uid']))
	$id = $post_data['uid'];
else
	$id = required_param('id', PARAM_INT);

$urlparams = array('id' => $id);
$PAGE->set_url('/local/sis/user/account/view_user.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
sis_access_control($access_rules);

if(isset($post_data['tab']))
	$tab = $post_data['tab'];
else
{
	//Check if a tab has to be active by default
	if(isset($_GET['tab']))
		$tab = $_GET['tab'];	
	else
		$tab = sis_get_session('sis_view_user_tab');
}
$user = $DB->get_record('si_user', array('id' => $id));
if(!$user)
	throw new moodle_exception('Invalid user id.');

//2nd level data in tabs

if($tab == '' || $tab == 'personal')
	$bc = get_string('personal_info', 'local_sis');
else if($tab == 'address')
	$bc = get_string('address', 'local_sis');
else if($tab == 'employee' && sis_is_employee($user->user_type))
	$bc = get_string('employee_info', 'local_sis');
else if($tab == 'student_info' && sis_is_student($user->user_type))
	$bc = get_string('student_info', 'local_sis');
else if($tab == 'account')
	$bc = get_string('account', 'local_sis');
else if($tab == 'finance')
	$bc = get_string('finance', 'local_sis');
else
{
	$tab = '';
	$bc = get_string('personal_info', 'local_sis');
}

sis_set_session('sis_view_user_tab', $tab);
//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'system');
$PAGE->navbar->add(get_string('system', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'system')));
$PAGE->navbar->add(get_string('user', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('view', 'local_sis') . ' ' . get_string('user', 'local_sis'), new moodle_url('view_user.php', $_GET));
$PAGE->navbar->add($user->appid . ' : ' . $bc);


echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('user_detail','local_sis'));

$currenttab = sis_get_session('sis_user_tab');
if($currenttab == '')
	$currenttab = 'user';
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

if($currenttab == 'user')
{
	$return_params = sis_get_session('si_user_return_params');
	$return_link = 'index';
}
else
{
	$return_params = sis_get_session('v_si_userlogin_return_params');
	$return_link = 'login_list';
}
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url($return_link . '.php', $return_params);

echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, sis_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_sis'), array('title' => get_string('back', 'local_sis'))) . '</div>';

$detail_data = array();
//one row of data
$obj = new stdClass();
$obj->title = get_string('appid', 'local_sis');
$obj->content = $user->appid;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('name');
$obj->content = $user->first_name . ' ' . $user->family_name;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('user_type', 'local_sis');
$obj->content = ucfirst($user->user_type);
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('gender', 'local_sis');
$obj->content = $user->gender;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('status', 'local_sis');
$obj->content = sis_output_show_active($user->eff_status);
$detail_data[] = $obj;
//end of data row

$personal_active = 'active';
$address_active = '';
$employee_active = '';
$student_active = '';
$account_active = '';
if($tab && ($tab == 'personal' || $tab == 'address' || $tab == 'account' || $tab == 'finance'))
{
	$personal_active = $tab == 'personal' ? 'active' : '';
	$address_active = $tab == 'address' ? 'active' : '';
	$account_active = $tab == 'account' ? 'active' : '';
	$finance_active = $tab == 'finance' ? 'active' : '';
}
else if($tab == 'employee' && sis_is_employee($user->user_type))
{
	$personal_active = '';
	$employee_active = 'active';
}
else if($tab == 'student_info' && sis_is_student($user->user_type))
{
	$personal_active = '';
	$student_active = 'active';
}

$get_params = $_GET; //get all the query string

//build the content
ob_start();
if($personal_active == 'active')
	include('personal_info.php');
else if($address_active == 'active')
	include('personal_address.php');
else if($employee_active == 'active' && sis_is_employee($user->user_type))
	include('personal_employee.php');
else if($student_active == 'active' && sis_is_student($user->user_type))
	include('personal_student.php');
else if($account_active == 'active')
	include('personal_account.php');
else if($finance_active == 'active')
	include('finance_info.php');
$content = ob_get_clean();		

//build url for tab 1
$get_params['tab'] = 'personal';
unset($get_params['op']);
$personal_url = new moodle_url('view_user.php', $get_params);
$tab_content = sis_user_account_personal_info($id, $personal_active, $content);	
$tab_pages['personal'] = array(
	'active' => $personal_active,
	'url' => $personal_url,
	'title' => get_string('personal_info', 'local_sis'),
	'content' => $tab_content,
);

//build url for tab 2
$get_params['tab'] = 'address';
unset($get_params['op']);
$address_url = new moodle_url('view_user.php', $get_params);
//build the content
$tab_content = sis_user_account_address_info($id, $address_active, $content);	
$tab_pages['address'] = array(
	'active' => $address_active,
	'url' => $address_url,
	'title' => get_string('contact', 'local_sis'),
	'content' => $tab_content,
);

if(sis_is_employee($user->user_type))
{
	//build url for tab 3
	$get_params['tab'] = 'employee';
	unset($get_params['op']);
	$employee_url = new moodle_url('view_user.php', $get_params);
	//build the content
	$tab_content = sis_user_account_employee_info($id, $employee_active, $content);	
	$tab_pages['employee'] = array(
		'active' => $employee_active,
		'url' => $employee_url,
		'title' => get_string('employee_info', 'local_sis'),
		'content' => $tab_content,
	);
}
else
{
	//build url for tab 3
	$get_params['tab'] = 'student_info';
	unset($get_params['op']);
	$student_url = new moodle_url('view_user.php', $get_params);
	//build the content
	$tab_content = sis_user_account_student_info($id, $student_active, $content);	
	$tab_pages['student'] = array(
		'active' => $student_active,
		'url' => $student_url,
		'title' => get_string('student_info', 'local_sis'),
		'content' => $tab_content,
	);
}

//build url for tab 4
$get_params['tab'] = 'account';
unset($get_params['op']);
$account_url = new moodle_url('view_user.php', $get_params);
//build the content
$tab_content = sis_user_account_account_info($id, $account_active, $content);	
$tab_pages['account'] = array(
	'active' => $account_active,
	'url' => $account_url,
	'title' => get_string('account', 'local_sis'),
	'content' => $tab_content,
);

//build url for tab 5
$get_params['tab'] = 'finance';
unset($get_params['op']);
$finance_url = new moodle_url('view_user.php', $get_params);
//build the content
$tab_content = sis_user_account_finance_info($id, $finance_active, $content);	
$tab_pages['finance'] = array(
	'active' => $finance_active,
	'url' => $finance_url,
	'title' => get_string('finance', 'local_sis'),
	'content' => $tab_content,
);

$content = sis_ui_tab($tab_pages);


//one row of data
$obj = new stdClass();
$obj->title = '';
$obj->content = $content;
$obj->full = true; //use full width
$detail_data[] = $obj;
//end of data row

$str = sis_ui_data_detail($detail_data);
sis_ui_box($str, get_string('user_info', 'local_sis'));

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/user/account/account.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript
echo $OUTPUT->footer();