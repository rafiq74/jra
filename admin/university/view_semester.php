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
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/academic/semester/view_semester.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admission',
	'subrole' => 'all',
);
sis_access_control($access_rules);

$id = required_param('id', PARAM_INT);

//verify that the semester exist
$semester = $DB->get_record('si_semester', array('id' => $id));
if(!$semester)
	throw new moodle_exception(get_string('wrong_parameter', 'local_sis'));	

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('semester', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('semester_timeline', 'local_sis'), new moodle_url('view_semester.php', array('id' => $id)));

$return_params = sis_get_session('si_semester_return_params', $_GET);
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);

echo $OUTPUT->header();

//content code starts here
sis_ui_page_title(get_string('semester_timeline', 'local_sis'));

//content code starts here
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, sis_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_sis'), array('title' => get_string('back', 'local_sis'))) . '</div>';

$detail_data = array();
//one row of data
$obj = new stdClass();
$obj->column = 2;
$obj->left_content = '<strong>' . get_string('semester', 'local_sis') . '</strong>';
$obj->right_content = sis_output_show_semester($semester);
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->column = 2;
$obj->left_content = '<strong>' . get_string('date', 'local_sis') . '</strong>';
$obj->right_content = sis_output_formal_date($semester->start_date) . ' - ' . sis_output_formal_date($semester->end_date);
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->column = 2;
$obj->left_content = '<strong>' . get_string('num_of_academic_week', 'local_sis') . '</strong>';
$obj->right_content = $semester->academic_week;
$detail_data[] = $obj;
//end of data row

$timeline_option = array();
$timeline_option['enable_edit_detail'] = true;
$timeline_option['show_detail'] = true;
$timeline_option['show_attendance'] = false;
//one row of data
$content = sis_output_semester_timeline($semester, $timeline_option);
$obj = new stdClass();
$obj->column = 2;
$obj->left_content = '<strong>' . get_string('timeline', 'local_sis') . '</strong>';
$obj->right_content = $content;
$detail_data[] = $obj;
//end of data row

$str = sis_ui_multi_column($detail_data, 2);

echo $str;

echo $OUTPUT->footer();

$PAGE->requires->js('/local/sis/academic/semester/semester.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript
