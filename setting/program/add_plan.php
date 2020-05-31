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

require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/program/add_plan.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

$post_data = $_POST;
if(isset($post_data['pid']))
	$id = $post_data['pid'];
else
	$id = required_param('id', PARAM_INT);
	
$dataid = optional_param('dataid', false, PARAM_INT);
if($dataid)
{
	$qs = '?id=' . $id.  '&dataid=' . $dataid;
	$bc = 'update_academic_plan';
}
else
{
	$qs = '?id=' . $id;
	$bc = 'add_academic_plan';
}

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('program', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('view', 'local_sis') . ' ' . get_string('program', 'local_sis'), new moodle_url('view_program.php', array('id' => $id)));
$PAGE->navbar->add(get_string($bc, 'local_sis'), new moodle_url('add_plan.php' . $qs));

//put before header so we can redirect
$return_file = sis_get_session('edit_plan_return_url');
$return_url = new moodle_url($return_file . '.php', array('id' => $id));
$add_url = new moodle_url('add_plan.php', array('id' => $id));
$mform = new plan_form(null, array('pid' => $id));
if ($mform->is_cancelled()) 
{
    redirect($return_url->out());
} 
else if ($data = $mform->get_data()) 
{		
	if(isset($data->cancel))
	{
	    redirect($return_url->out());
		die;
	}
	//validate that there is no duplicate
	//first, get the catalogue ID of the program
	$program = $DB->get_record('si_program', array('id' => $data->pid));	
	$data->program_id = $program->catalogue_id;
	$duplicate_condition = array(
		'catalogue_id' => $data->program_id,
		'plan' => $data->plan,
	);
	$isDuplicate = sis_query_is_duplicate('si_plan', $duplicate_condition, $data->id);
	if(!$isDuplicate) //no duplicate, update it
	{
		if($data->default_plan == 'Y') //if it is default plan, set all others to no
		{
			$sql = "update {si_plan} set default_plan = 'N' where program_id = '$data->program_id'";
			$DB->execute($sql);
		}
		if(isset($data->default_date))
			$data->eff_date = sis_earliest_date();
		if($data->id == '') //create new
		{
			$new_id = $DB->insert_record('si_plan', $data);	
			$new_rec = $DB->get_record('si_plan', array('id' => $new_id));
			if($new_rec)
			{
				$new_rec->catalogue_id = $new_id; //update the catalogue id as the id of the first plan
				$DB->update_record('si_plan', $new_rec);
			}
		}
		else
			$DB->update_record('si_plan', $data);			
	    redirect($return_url);
	}
}

echo $OUTPUT->header();

if($isDuplicate)
	sis_ui_alert(get_string('duplicate_plan', 'local_sis'), 'danger');

//content code starts here
sis_ui_page_title(get_string($bc, 'local_sis'));

if($dataid)
{
	$toform = $DB->get_record('si_plan', array('id' => $dataid));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();

echo $OUTPUT->footer();