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
require_once '../../lib/cur_lib.php';
require_once '../../../sis/lib/sis_lib.php'; 
require_once '../../../sis/lib/sis_ui_lib.php';
require_once '../../../sis/lib/sis_output_lib.php';
require_once '../../../sis/lib/sis_lookup_lib.php';
require_once '../../../sis/lib/sis_query_lib.php';
require_once '../../lib/cur_lookup_lib.php';
require_once '../../lib/cur_file_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/cur/course/course/view_course_upload.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);
sis_access_control($access_rules);

$id = required_param('id', PARAM_INT);
if($id != '')
{
	$course = $DB->get_record('cu_course', array('id' => $id));
	if(!$course)
		throw new moodle_exception('Invalid course id.');
}

//2nd level data in tabs
//Check if a tab has to be active by default
//Breadcrumb
sis_set_session('sis_home_tab', 'academic');
sis_set_session('course_component_tab', $tab);
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('course'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('view', 'local_sis') . ' ' . get_string('course'), new moodle_url('view_course.php', $_GET));
$PAGE->navbar->add($course->course_code . ' : Credit');

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

$return_url = new moodle_url('index.php', $cu_course_return_params);

//put before header so we can redirect
$mform = new upload_form(null, array('id' => $id));
if ($mform->is_cancelled()) 
{
	redirect($return_url);
} 
else if ($data = $mform->get_data()) 
{	
	$file_params = array(
		'course_code' => $course->course_code . ' ' . $course->course_num,
		'subcategory' => $data->module,
	);
	$name = $mform->get_new_filename('userfile');	
	$data->filepath = cur_file_get_directory('course', $file_params) . '/' . $name;	
	$override = true;
	$dir = $data->filepath . $data->filename;
	$success = $mform->save_file('userfile', $dir, $override);

	
	/*
	$data->filename = 'clo.pdf';
	$data->date_uploaded = time();
	$data->uploaded_by = $USER->id;
	$data->status = 'A';
	$data->institute = sis_get_institute();
	$DB->insert_record('cu_filesystem', $data);

	print_object($data);

	*/
}

echo $OUTPUT->header();

//content code starts here
sis_ui_page_title(get_string('courses','local_sis') . ' : ' . $course->course_code . ' - ' . $course->course_name);

$currenttab = 'upload'; //change this according to tab
include('tabs.php');

echo $OUTPUT->box_start('sis_tabbox');

if(isset($success))
{
	if($success)
		sis_ui_alert('Save successful', 'success');
	else
		sis_ui_alert('Save failed', 'danger');
}

if(isset($course))
{
	$mform->set_data($course);
}

$mform->display();

$file_params = array(
	'course_code' => $course->course_code . ' ' . $course->course_num,
	'subcategory' => 'syllabus',
);
$dir = cur_file_get_directory('course', $file_params);	
$syllabus_files = cur_file_get_file($dir);

$syllabus_str = '';
foreach($syllabus_files as $file)
{
	if($syllabus_str != '')
		$syllabus_str = $syllabus_str . '<br />';
	$file_url = new moodle_url('file.php', array('path' => $dir, 'file' => $file));
	$syllabus_str = $syllabus_str . html_writer::link($file_url, sis_ui_icon('file-pdf-o', '', true) . ' ' . $file, array('target' => '_blank'));
}

$file_params = array(
	'course_code' => $course->course_code . ' ' . $course->course_num,
	'subcategory' => 'guidelines',
);
$dir = cur_file_get_directory('course', $file_params);	
$guidelines_files = cur_file_get_file($dir);

print_object($guidelines_files);


$data = array();
//one row of data
$obj = new stdClass();
$obj->column = 2;
$obj->left_content = sis_ui_box($syllabus_str, 'Syllabus', '', true);
$obj->right_content = sis_ui_box('Guidelines files', 'Guidelines', '', true);
$data[] = $obj;
//end of one row
$str = sis_ui_multi_column($data);	

sis_ui_box($str, 'Uploaded Files');

echo $OUTPUT->box_end(); //1st level tab

$PAGE->requires->js('/local/cur/course/syllabus/syllabus.js');
$PAGE->requires->js('/local/cur/script.js'); //global javascript
//$PAGE->requires->js('/local/sis/vendor/js/bootstrap_treeview/src/js/bootstrap-treeview.js');

echo $OUTPUT->footer();
