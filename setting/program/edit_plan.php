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
$PAGE->set_url('/local/sis/setting/program/edit_plan.php', $urlparams);
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
$bc = 'update_academic_plan';

//make sure the course is valid
$plan = $DB->get_record('si_plan', array('id' => $id));
if(!$plan)
	throw new moodle_exception(get_string('invalid_parameter', 'local_sis'));	

$co = new stdClass;
$co->institute = $plan->institute;
$co->catalogue_id = $plan->program_id;
$program = sis_setting_program_get_program($co);

$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('program', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('view', 'local_sis') . ' ' . get_string('program', 'local_sis'), new moodle_url('view_program.php', array('id' => $plan->program_id)));
$PAGE->navbar->add(get_string($bc, 'local_sis'), new moodle_url('edit_plan.php' . $qs));

//put before header so we can redirect
$return_url = new moodle_url('view_program.php', array('id' => $program->id));
$same_effective_date = false;
$mform = new plan_form(null, array('pid' => $plan->program_id));
if ($mform->is_cancelled()) 
{
    redirect($cancel_url->out());
} 
else if ($data = $mform->get_data()) 
{		
	if(isset($data->cancel))
	{
		unset($params['op']);
		$return_url = new moodle_url('edit_plan.php', array('id' => $data->id));
	    redirect($return_url->out());
		die;
	}
	//as in editing, plan is not editable, so, it will not submitted in the data. We will have to retrieve it
	$plan = $DB->get_record('si_plan', array('id' => $data->id));
	$data->plan = $plan->plan;
	if(isset($data->default_date))
		$data->eff_date = sis_earliest_date();

	//validate that the update has the latest eff_date. don't allow to back date.
	$max_plan = sis_setting_program_get_plan($data);
	if($data->eff_date <= $max_plan->eff_date)
	{
		if(isset($data->saveasbutton)) //save as, so don't allow equal
		{
			$isDuplicate = true;
		}
		else //it is a correct history
		{
			if($data->eff_date < $max_plan->eff_date)
				$isDuplicate = true;
		}
	}
	else
		$isDuplicate = false; //we always allow
	
	//validate that there is no duplicate
	if(!$isDuplicate) //no duplicate, update it
	{
		if($data->default_plan == 'Y') //if it is default plan, set all others to no
		{
			$sql = "update {si_plan} set default_plan = 'N' where program_id = '$data->program_id'";
			$DB->execute($sql);
		}
		if(isset($data->default_date))
			$data->eff_date = sis_earliest_date();

		if(isset($data->saveasbutton)) //save as, so always create new
		{
			if($plan->eff_date != $data->eff_date) //make sure no duplicate effective date
			{
				//initialize the new record with missing field
				$data->id = '';
				$data->catalogue_id = $plan->catalogue_id;
				$DB->insert_record('si_plan', $data);					
			}
			else
				$same_effective_date = true;
		}
		else
		{
			$DB->update_record('si_plan', $data);
		}			
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
	$view = sis_get_session('plan_edit_view');
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
sis_set_session('plan_edit_view', $view);

//content code starts here
sis_ui_page_title(get_string($bc, 'local_sis'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, sis_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_sis'), array('title' => get_string('back', 'local_sis'))) . '</div>';

if($operation == 'edit')
{
	if($dataid)
	{
		//get the program data (false if not created)
		$toform = $DB->get_record('si_plan', array('id' => $dataid));
		$mform->set_data($toform);
	}
	$mform->display();
}
else
{
	sis_setting_program_show_plan_edit($id, $view);
}

echo $OUTPUT->footer();