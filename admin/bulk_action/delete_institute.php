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
require_once '../../lib/sis_lookup_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/admin/setting/delete_institute.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'system' => ''
); //super admin role only
sis_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

sis_set_session('sis_home_tab', 'system');
$PAGE->navbar->add(get_string('system', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'system')));
$PAGE->navbar->add(get_string('bulk_action', 'local_sis'), new moodle_url('index.php'));

echo $OUTPUT->header();

//content code starts here
sis_ui_page_title('Delete All Data for Institute');
$currenttab = 'delete'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

sis_ui_show_flash_message('delete_institute_data');

sis_ui_alert('Please use this module with care as mistake can corrupt the data of the entire institute', 'warning', 'Alert', false);
$output = '<form name="form1" action="delete_institute_data.php">';
$output .= '<div class="pt-3">';
$institute_list = sis_lookup_institute();
//one button
$output .= 'Target Institute : ' . sis_ui_select('institute_target', $institute_list, '');
//end of one button
$output .= '<p>&nbsp;</p>';
//one button
$output .= '<button type="submit" class="btn btn-primary" value="Delete">Delete Data in Target Institute</button>';
//end of one button

$output .= '</div>';
$output .= '</form>';
echo $output;

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/admin/bulk_action/bulk_action.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();