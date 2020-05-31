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
require_once '../../lib/sis_lookup_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/grade/add_category.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);
require_login(); //always require login

$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

$id = optional_param('id', false, PARAM_INT);
if($id)
{
	$qs = '?id=' . $id;
	$bc = 'update_grade_category';
}
else
{
	$qs = '';
	$bc = 'add_grade_category';
}

//Breadcrumb
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string($bc, 'local_sis'), new moodle_url('category.php'));

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

$return_params = sis_get_session('si_grade_template_return_params');
if($return_params == '')
	$return_params = array();

$return_url = new moodle_url($option . 'category.php', $return_params);

//put before header so we can redirect
$success = false;
$mform = new grade_category_form();
if ($mform->is_cancelled()) 
{
    redirect($return_url);
} 
else if ($data = $mform->get_data()) 
{	
	//validate that there is no duplicate
	$duplicate_condition = array(
		'class_type' => $data->class_type,
		'category' => $data->category,
		'institute' => $data->institute,
	);
	$isDuplicate = sis_query_is_duplicate('si_grade_template', $duplicate_condition, $data->id);
	if(!$isDuplicate) //no duplicate, update it
	{
		$now = time();
		if($data->id == '') //create new
		{
			$DB->insert_record('si_grade_template', $data);
		}
		else
		{
			$DB->update_record('si_grade_template', $data);			
		    redirect($return_url);
		}
		$success = true;
	}
}

echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string($bc, 'local_sis'));

if($isDuplicate)
	sis_ui_alert(get_string('duplicate_grade_template', 'local_sis'), 'danger');

if($success)
{
	sis_ui_alert(get_string('the_grade_category', 'local_sis') . ' ' . $data->category . ' ' . get_string('has_been_successfully_created', 'local_sis'), 'success');
}

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$toform = $DB->get_record('si_grade_template', array('id' => $id));
	if($toform)
	{
		$mform->set_data($toform);
	}
}


$mform->display();


//content ends here
$PAGE->requires->js('/local/sis/setting/grade/grade.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();