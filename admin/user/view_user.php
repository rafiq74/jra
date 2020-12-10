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
require_once '../../lib/jra_lib.php';
require_once '../../lib/jra_ui_lib.php';
require_once '../../lib/jra_output_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../user/lib.php'; //user library
require_once 'lib.php'; //local library

$post_data = $_POST;
if(isset($post_data['uid']))
	$id = $post_data['uid'];
else
	$id = required_param('id', PARAM_INT);

$urlparams = array('id' => $id);
$PAGE->set_url('/local/jra/admin/user/view_user.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
jra_access_control($access_rules);

if(isset($post_data['tab']))
	$tab = $post_data['tab'];
else
{
	//Check if a tab has to be active by default
	if(isset($_GET['tab']))
		$tab = $_GET['tab'];
	else
		$tab = jra_get_session('jra_view_user_tab');
}
$user = $DB->get_record('jra_user', array('id' => $id));
if(!$user)
	throw new moodle_exception('Invalid user id.');

//2nd level data in tabs

if($tab == '' || $tab == 'contact')
	$bc = get_string('contact', 'local_jra');
else if($tab == 'personal')
	$bc = get_string('personal_info', 'local_jra');
else if($tab == 'account')
	$bc = get_string('account', 'local_jra');
else
{
	$tab = '';
	$bc = get_string('contact', 'local_jra');
}

jra_set_session('jra_view_user_tab', $tab);
//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['user', 'management']), new moodle_url('index.php'));
$PAGE->navbar->add(jra_get_string(['view', 'user']), new moodle_url('view_user.php', $urlparams));
$PAGE->navbar->add(jra_output_show_user_name($user) . ' : ' . $bc);


echo $OUTPUT->header();
//content code starts here
jra_ui_page_title(jra_get_string(['user','details']));

$currenttab = jra_get_session('jra_user_tab');
if($currenttab == '')
	$currenttab = 'user';
include('tabs.php');
echo $OUTPUT->box_start('jra_tabbox');

if($currenttab == 'user')
{
	$return_params = jra_get_session('jra_user_return_params');
	$return_link = 'index';
}
else
{
	$return_params = jra_get_session('v_jra_userlogin_return_params');
	$return_link = 'login_list';
}
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url($return_link . '.php', $return_params);

echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, jra_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_jra'), array('title' => get_string('back', 'local_jra'))) . '</div>';

$detail_data = array();

$obj = new stdClass();
$obj->title = get_string('app_id');
$obj->content = $user->app_id;
$detail_data[] = $obj;
//one row of data
$obj = new stdClass();
$obj->title = get_string('username');
$obj->content = $user->username;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('name');
$obj->content = jra_output_show_user_name($user);
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = jra_get_string(['user', 'type']);
$obj->content = ucfirst($user->user_type);
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('gender', 'local_jra');
$obj->content = jra_output_show_gender($user->gender);
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('status', 'local_jra');
$obj->content = jra_output_show_active($user->active_status);
$detail_data[] = $obj;
//end of data row

$contact_active = 'active';
$personal_active = '';
$account_active = '';

if($tab)
{
	$contact_active = $tab == 'contact' ? 'active' : '';
	$personal_active = $tab == 'personal' ? 'active' : '';
	$account_active = $tab == 'account' ? 'active' : '';
}

$get_params = $_GET; //get all the query string

//build the content
ob_start();
if($contact_active == 'active')
	include('personal_contact.php');
else if($personal_active == 'active')
	include('personal_info.php');
else if($account_active == 'active')
	include('personal_account.php');
$content = ob_get_clean();

//build url for tab 1
$get_params['tab'] = 'contact';
unset($get_params['op']);
$contact_url = new moodle_url('view_user.php', $get_params);
//build the content
$tab_content = jra_admin_user_contact_info($id, $contact_active, $content);
$tab_pages['contact'] = array(
	'active' => $contact_active,
	'url' => $contact_url,
	'title' => get_string('contact', 'local_jra'),
	'content' => $tab_content,
);

//build url for tab 2
$get_params['tab'] = 'personal';
unset($get_params['op']);
$personal_url = new moodle_url('view_user.php', $get_params);
$tab_content = jra_admin_user_personal_info($id, $personal_active, $content);
$tab_pages['personal'] = array(
	'active' => $personal_active,
	'url' => $personal_url,
	'title' => get_string('personal_info', 'local_jra'),
	'content' => $tab_content,
);

//build url for tab 4
$get_params['tab'] = 'account';
unset($get_params['op']);
$account_url = new moodle_url('view_user.php', $get_params);
//build the content
$tab_content = jra_admin_user_account_info($id, $account_active, $content);
$tab_pages['account'] = array(
	'active' => $account_active,
	'url' => $account_url,
	'title' => get_string('account', 'local_jra'),
	'content' => $tab_content,
);

$content = jra_ui_tab($tab_pages);

//one row of data
$obj = new stdClass();
$obj->title = '';
$obj->content = $content;
$obj->full = true; //use full width
$detail_data[] = $obj;
//end of data row

$str = jra_ui_data_detail($detail_data);
jra_ui_box($str, jra_get_string(['user', 'information']));

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/admin/user/user.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript
echo $OUTPUT->footer();
