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

require_once '../../../config.php';
require_once '../lib/jra_lib.php';
require_once '../lib/jra_ui_lib.php';
require_once '../lib/jra_asset_lib.php';
require_once '../lib/jra_output_lib.php';
require_once 'lib.php'; //local library (for index we name it as locallib because there is already an official lib.php from moodle

require_login(); //always require login

//Role checking code here
//if(!jra_is_system_admin()) //not admin, do not allow
//	throw new moodle_exception('Access denied. This module is only accessible by administrator.');

$urlparams = $_GET;
$PAGE->set_url('/local/jra/dashboard/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

echo $OUTPUT->header();
//content code starts here
jra_ui_page_title(get_string('dashboard', 'local_jra'));

//if there is any flash message
jra_ui_show_flash_message('jra_user_dashboard_message');

//session to tell the other page that the previous page comes from dashboard
jra_set_session('source_page', $PAGE->url);

$action_item = array();
$action_item[] = array(
	'title' => jra_get_string(['add', 'new', 'asset']), // - for divider
	'url' => $CFG->wwwroot . '/local/jra/user/asset/add_asset.php',
	'target' => '', //_blank
	'icon' => 'plus-circle',
);
$rentable_menu = '<div class="row pull-right pr-2">' . jra_ui_dropdown_menu($action_item, get_string('action', 'local_jra'), 'primary', 'btn-sm') . '</div>';

if(isset($USER->jra_user)) //is a normal jra user
{
	$data = array();
	//one row of data
	$asset_list = jra_asset_list_asset($USER->jra_user->id);
	$obj = new stdClass();
	$obj->column = 2;
	$obj->left_content = jra_ui_box($asset_list, jra_get_string(['rentable', 'assets']) . $rentable_menu, '', true);
	$obj->right_content = jra_ui_box('Hello', jra_get_string(['rentable', 'assets']), '', true);
	$data[] = $obj;
	//end of data row
	
	$str = jra_ui_multi_column($data, 8);
}
else //it is system administrator
{
	$str = '';
}
echo $str;

//content code ends here
echo $OUTPUT->footer();

$PAGE->requires->js('/local/jra/dashboard/dashboard.js'); //global javascript
$PAGE->requires->js('/local/jra/script.js'); //global javascript