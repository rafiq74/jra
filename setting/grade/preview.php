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
require_once 'lib.php'; //local library
//require_once 'form.php';
$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/grade/preview.php', $urlparams);
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
$PAGE->navbar->add(get_string('grade', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('preview', 'local_sis'), new moodle_url('preview.php'));

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

echo $OUTPUT->header();
//content code starts here

if(isset($_GET['id']))

	$id = $_GET['id'];
	$templates = $DB->get_record('si_grade_letter', array('id' => $id));
	echo $OUTPUT->box_start('sis_tabbox');
    $table = new html_table();
    $table->attributes['class'] = 'table table-bordered table-striped';
    $table->head = array('Scheme ID','Description', 'Status' , 'Enrolled','Order','Date','action');

	$data[] = $templates->grade_scheme_id;
	$data[] = $templates->description;
    $data[] = $templates->status;
    $data[] = $templates->is_enrolled;
	$data[] = $templates->sort_order;
	$data[] = $templates->eff_date;
	


	$update_url = new moodle_url('/local/sis/setting/grade/index.php');		
	$data[] = html_writer::link($update_url, sis_ui_icon('pencil', '1', true), array('title' => 'Back to index'));
	$table->data[] = $data;
	unset($data);				

	$str = $str . sis_ui_print_table($table);

	echo $OUTPUT->box_end();
$PAGE->requires->js('/local/sis/setting/grade/grade.js');
echo $OUTPUT->footer();
