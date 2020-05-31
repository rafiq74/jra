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
require_once '../../lib/sis_query_lib.php';
require_once '../../lib/sis_output_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/grade/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);
require_login(); //always require login

$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

//Breadcrumb
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('grading_schemes', 'local_sis'), new moodle_url('index.php'));

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

$id = optional_param('id', false, PARAM_INT);
$action = optional_param('action', 0, PARAM_INT);
if($id && $action == 2)
{	
	$cascade = array('si_grade_letter' => 'grade_scheme_id');
	sis_query_delete_cascade('si_grade_scheme', $id, $cascade);
}

echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('grading_schemes','local_sis'));

$mform = new grade_scheme_form();
if($data = $mform->get_data())
{
	if($data->is_cancel == '')
	{
		$duplicate_condition = array(
			'institute' => sis_get_institute(),
			'grade_scheme' => $data->grade_scheme,
		);
		$isDuplicate = sis_query_is_duplicate('si_grade_scheme', $duplicate_condition, $data->id);
		if(!$isDuplicate) //no duplicate, update it
		{	
			if($data->id == '') //create new
				$DB->insert_record('si_grade_scheme', $data);	
			else
				$DB->update_record('si_grade_scheme', $data);
		}
		else
		{
			sis_ui_alert(get_string('duplicate_grade_scheme', 'local_sis'), 'danger');
		}
	}
}
$html = '
 <div class="row">
  <div class="col-sm-4">';
 
//right content
$html = $html . '';

$html = $html . sis_setting_grade_print_grade_scheme();

$html = $html . '</div>';

//left content
$selected_id = sis_get_session('sis_selected_grade_scheme');

if($selected_id != '')
{
	$grade_letters = sis_grade_print_grade_letter($selected_id);
}
else
	$grade_letters = '';


$html = $html . '<div class="col-sm-8">';
$html = $html . '<div id="ajax-content">' . $grade_letters . '</div>';
$html = $html . '</div>';
$html = $html . '</div> '; //end of row

echo $html;

//content ends here
$PAGE->requires->js('/local/sis/setting/grade/grade.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();