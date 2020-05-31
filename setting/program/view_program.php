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
require_once '../../lib/sis_lookup_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once 'lib.php'; //local library
//require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/program/view_program.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);
require_login(); //always require login

$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

$id = required_param('id', PARAM_INT);
$program = $DB->get_record('si_program', array('id' => $id));
if(!$program)
	throw new moodle_exception('Wrong parameters.');

sis_set_session('setting_plan_program', $id); //set the session so when user view the plan, it is the selected program	

//Breadcrumb
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('program', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('view', 'local_sis') . ' ' . get_string('program', 'local_sis'), new moodle_url('view_program.php', $_GET));

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

//Any form processing code
if($isAdmin && isset($_GET['dataid'])) //only allow site admin to delete
{
	$cascade = array(
		'si_plan_course' => 'plan_id',
	);
	sis_query_delete_cascade('si_plan', $_GET['dataid'], $cascade);
}

sis_set_session('edit_plan_return_url', 'view_program');

echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('program','local_sis'));

$currenttab = 'program'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

$return_params = sis_get_session('si_program_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);

echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, sis_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_sis'), array('title' => get_string('back', 'local_sis'))) . '</div>';

$data = array();
//one row of data
$obj = new stdClass();
$obj->title = get_string('program', 'local_sis');
$obj->content = $program->program;
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('program_name', 'local_sis');
$obj->content = $program->program_name;
$data[] = $obj;
//end of data row
//one row of data
$acad_career = sis_lookup_academic_career();
$obj = new stdClass();
$obj->title = get_string('academic_career', 'local_sis');
$obj->content = $acad_career[$program->academic_career];
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('grading_scheme', 'local_sis');
$obj->content = $program->grading_scheme;
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('campus', 'local_sis');
$obj->content = sis_output_show_campus($program->campus);
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('status', 'local_sis');
$obj->content = sis_output_show_active($program->eff_status);
$data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('eff_date', 'local_sis');
$obj->content = date('d-M-Y', $program->eff_date);
$data[] = $obj;
//end of data row

$content = sis_setting_program_plan_list($program);
//one row of data
$obj = new stdClass();
$obj->title = get_string('academic_plans', 'local_sis');
$obj->content = $content;
//$obj->full = true; //use full width
$data[] = $obj;
//end of data row

$str = sis_ui_data_detail($data);
//creeate the page navigator
$records = $DB->get_records('si_program', array('catalogue_id' => $program->catalogue_id), 'eff_date desc');
$old_programs = '<span class="pull-right">' . sis_ui_record_navigator($records, $id, 'view_program.php') . '</span>';
sis_ui_box($str, get_string('academic_program_details', 'local_sis') . $old_programs);

echo $OUTPUT->box_end(); //1st level tab

//content ends here
$PAGE->requires->js('/local/sis/setting/program/program.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();