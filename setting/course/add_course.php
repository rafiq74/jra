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
require_once '../../lib/sis_query_lib.php'; 
require_once 'lib.php';

require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/course/add_course.php', $urlparams);
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
	$bc = 'update_course';
}
else
{
	$qs = '';
	$bc = 'add_course';
}

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('course'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string($bc, 'local_sis'), new moodle_url('add_course.php' . $qs));

$return_params = sis_get_session('si_course_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);

//put before header so we can redirect
$mform = new course_form();
if ($mform->is_cancelled()) 
{
    redirect($return_url);
} 
else if ($data = $mform->get_data()) 
{	
	if(isset($data->cancel))
	{
	    redirect($return_url->out());
		die;
	}
	//validate that there is no duplicate. Validation is more complex than the normal validation
	//It must not duplicate with a course code and course num for an active course (i.e. max effective date)
	$isDuplicate = false; //we allow course to duplicate
	if(!$isDuplicate) //no duplicate, update it
	{
		$var_name = 'admin_course_separator';
		$separator = sis_get_config($var_name);
		$data->course_code = $data->code . $separator . $data->course_num;
		$data->deleted = 0;
		if(isset($data->default_date))
			$data->eff_date = sis_earliest_date();
		if($data->id == '') //create new
		{
			$new_id = $DB->insert_record('si_course', $data);	
			$new_rec = $DB->get_record('si_course', array('id' => $new_id));
			if($new_rec)
			{
				$new_rec->catalogue_id = $new_id; //update the catalogue id as the id of the cirst course
				$DB->update_record('si_course', $new_rec);
			}
		}
		else //here we don't allow update, because is in edit_course page
		{
			/*
			$cascade = array(
				//array key is the field in $data and value is the field in the cascade table
				'si_section' => array(
					'course_code' => 'course_code',
					'course_name' => 'course_name',
				),
			);
			sis_query_update_cascade('si_course', $data, $cascade);
			*/
		}
	    redirect($return_url);
	}
}

echo $OUTPUT->header();

if($isDuplicate)
	sis_ui_alert(get_string('duplicate_course', 'local_sis'), 'danger');
//content code starts here
sis_ui_page_title(get_string($bc, 'local_sis'));

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$toform = $DB->get_record('si_course', array('id' => $id));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();

echo $OUTPUT->footer();