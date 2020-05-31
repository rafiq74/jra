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
$PAGE->set_url('/local/sis/setting/course/edit_course.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);
sis_access_control($access_rules);

$id = required_param('id', PARAM_INT);
$dataid = optional_param('dataid', false, PARAM_INT);
$operation = optional_param('op', '', PARAM_TEXT);

if($dataid)
{
	$qs = '?id=' . $id.  '&dataid=' . $dataid;
}
else
{
	$qs = '?id=' . $id;
}
$bc = 'update_course';

//make sure the course is valid
$co = $DB->get_record('si_course', array('id' => $id));
if(!$co)
	throw new moodle_exception(get_string('invalid_parameter', 'local_sis'));	

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

$same_effective_date = false;
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
		unset($params['op']);
		$return_url = new moodle_url('edit_course.php', array('id' => $data->id));
	    redirect($return_url->out());
		die;
	}
	if(isset($data->default_date))
		$data->eff_date = sis_earliest_date();
	//validate that the update has the latest eff_date. don't allow to back date.
	$max_course = sis_setting_course_get_course($data);
	if($data->eff_date <= $max_course->eff_date)
	{
		if(isset($data->saveasbutton)) //save as, so don't allow equal
		{
			$isDuplicate = true;
		}
		else //it is a correct history
		{
			if($data->eff_date < $max_course->eff_date)
				$isDuplicate = true;
		}
	}
	else
		$isDuplicate = false; //we always allow
	if(!$isDuplicate) //no duplicate, update it
	{
		$var_name = 'admin_course_separator';
		$separator = sis_get_config($var_name);
		$data->course_code = $data->code . $separator . $data->course_num;
			
		if(isset($data->saveasbutton)) //save as, so always create new
		{
			//get the current record
			$cur = $DB->get_record('si_course', array('id' => $data->id));
			if($cur->eff_date != $data->eff_date) //make sure no duplicate effective date
			{
				//initialize the new record with missing field
				$data->id = '';
				$data->deleted = 0;
				$data->catalogue_id = $cur->catalogue_id;
				$new_id = $DB->insert_record('si_course', $data);	
				//we have to duplicate and course equivalent and course component
				$components = $DB->get_records('si_course_component', array('course_id' => $cur->id));
				$com_array = array();
				foreach($components as $com)
				{
					$com->id = '';
					$com->course_id = $new_id;
					$com_array[] = $com;
				}
				if(count($com_array) > 0)
					$DB->insert_records('si_course_component', $com_array);
				$equivalents = $DB->get_records('si_course_equivalent', array('course_id' => $cur->id));
				$eq_array = array();
				foreach($equivalents as $eq)
				{
					$eq->id = '';
					$eq->course_id = $new_id;
					$eq_array[] = $eq;
				}
				if(count($eq_array) > 0)
					$DB->insert_records('si_course_equivalent', $eq_array);
			}
			else
				$same_effective_date = true;
		}
		else
		{
			$DB->update_record('si_course', $data);
		}
			
//	    redirect($return_url);
	}
}

echo $OUTPUT->header();

if($isDuplicate)
{
	sis_ui_alert(get_string('less_effective_date', 'local_sis'), 'danger');
	$operation = 'edit';
}
if($same_effective_date)
{
	sis_ui_alert(get_string('same_effective_date', 'local_sis'), 'danger');
	$operation = 'edit';
}
$view = optional_param('view', '', PARAM_TEXT);
if($view == '') //try to get from session
{
	$view = sis_get_session('course_catalogue_edit_view');
	if($view == '')
		$view = 'active';
}
if($view == 'all')
{
	$view_flip = 'active';
}
else
{
	$view_flip = 'all';
}
sis_set_session('course_catalogue_edit_view', $view);

//content code starts here
sis_ui_page_title(get_string($bc, 'local_sis'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, sis_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_sis'), array('title' => get_string('back', 'local_sis'))) . '</div>';

if($operation == 'edit')
{
	if($dataid)
	{
		//get the program data (false if not created)
		$toform = $DB->get_record('si_course', array('id' => $dataid));
		$mform->set_data($toform);
	}
	$mform->display();
}
else
{
	sis_setting_course_show_course_edit($id, $view);
}

echo $OUTPUT->footer();