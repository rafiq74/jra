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
 * @license   http://www.gnu.org/copycenter/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php';
require_once '../../lib/sis_output_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/organization/view_campus.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);

sis_access_control($access_rules);

if(isset($_POST['delete_id'])) //only allow site admin to delete
{
	//we have to remove any cascading record (even if we don't delete the user physically)
	$cascade = array(
	);
	sis_query_delete_cascade('si_organization_post', $_POST['delete_id'], $cascade);
}

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

sis_set_session('sis_home_tab', 'setup');
$PAGE->navbar->add(get_string('setup', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'setup')));
$PAGE->navbar->add(get_string('campus', 'local_sis'), new moodle_url('campus.php'));
$PAGE->navbar->add(sis_get_string(['view', 'campus']), new moodle_url('view_campus.php'));

echo $OUTPUT->header();//content code starts here
//content code starts here
sis_ui_page_title(sis_get_string(['view', 'campus']));
$currenttab = 'campus'; //change this according to tab
include('tabs.php');

//$id = required_param('id', PARAM_INT);
if(isset($_GET['id']))
	$id = $_GET['id'];
else
	$id = 0;
	
echo $OUTPUT->box_start('sis_tabbox');
$return_params = sis_get_session('si_campus_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('campus.php', $return_params);

//content code starts here
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, sis_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_sis'), array('title' => get_string('back', 'local_sis'))) . '</div>';

$campus = $DB->get_record('si_campus', array('id' => $id));
if($campus)
{
	$detail_data = array();
	//one row of data
	$detail_obj = new stdClass();
	$detail_obj->column = 2;
	$detail_obj->left_content = '<strong>' . get_string('campus', 'local_sis') . '</strong>';
	$detail_obj->right_content = $campus->campus;
	$detail_data[] = $detail_obj;
	//end of data row
	//one row of data
	$detail_obj = new stdClass();
	$detail_obj->column = 2;
	$detail_obj->left_content = '<strong>' . get_string('name', 'local_sis') . '</strong>';
	$detail_obj->right_content = $campus->campus_name;
	$detail_data[] = $detail_obj;
	//end of data row
	
	//display the positions
	$content = sis_setting_organization_post($campus, 'campus');
	//one row of data
	$detail_obj = new stdClass();
	$detail_obj->column = 2;
	$detail_obj->left_content = '<strong>' . get_string('posts', 'local_sis') . '</strong>';
	$detail_obj->right_content = $content;
	$detail_data[] = $detail_obj;
	//end of data row
	
	$str = sis_ui_multi_column($detail_data, 2);
	echo $str;
}
else
	sis_ui_alert(get_string('record_not_found', 'local_sis'), 'danger');
	


echo $OUTPUT->box_end();
$PAGE->requires->js('/local/sis/setting/organization/organization.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript
echo $OUTPUT->footer();