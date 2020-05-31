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
$PAGE->set_url('/local/sis/setting/program/add_program.php', $urlparams);
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
	$bc = 'update_program';
}
else
{
	$qs = '';
	$bc = 'add_program';
}
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('program', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string($bc, 'local_sis'), new moodle_url('add_program.php' . $qs));

$return_params = sis_get_session('si_program_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);

$isDuplicate = false;
//put before header so we can redirect
$mform = new program_form();
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
	//validate that there is no duplicate
	$duplicate_condition = array(
		'program' => $data->program,
		'campus' => $data->campus,
		'institute' => $data->institute,
	);
	$isDuplicate = sis_query_is_duplicate('si_program', $duplicate_condition, $data->id);
	if(!$isDuplicate) //no duplicate, update it
	{
		if(isset($data->default_date))
			$data->eff_date = sis_earliest_date();
		if($data->id == '') //create new
		{
			$new_id = $DB->insert_record('si_program', $data);	
			$new_rec = $DB->get_record('si_program', array('id' => $new_id));
			if($new_rec)
			{
				$new_rec->catalogue_id = $new_id; //update the catalogue id as the id of the cirst course
				$DB->update_record('si_program', $new_rec);
			}
			
			
		}
		else
			$DB->update_record('si_program', $data);			
	    redirect($return_url);
	}
}

echo $OUTPUT->header();
if($isDuplicate)
	sis_ui_alert(get_string('duplicate_program', 'local_sis'), 'danger');

//content code starts here
sis_ui_page_title(get_string($bc, 'local_sis'));

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$toform = $DB->get_record('si_program', array('id' => $id));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();

echo $OUTPUT->footer();