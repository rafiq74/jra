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
require_once '../../lib/jra_lib.php'; 
require_once '../../lib/jra_ui_lib.php'; 
require_once '../../lib/jra_lookup_lib.php'; 
require_once '../../lib/jra_query_lib.php'; 
require_once 'lib.php';

require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/plan/add_plan.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$id = optional_param('id', false, PARAM_INT);
if($id)
{
	$qs = '?id=' . $id;
	$bc = ['update', 'plan'];
}
else
{
	$qs = '';
	$bc = ['add', 'plan'];
}

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['subscription', 'plan']), new moodle_url('index.php'));
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('add_plan.php', $urlparams));

$return_params = jra_get_session('jra_plan_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);

//put before header so we can redirect
$isDuplicate = false; //we allow course to duplicate
$mform = new plan_form();
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
	//validate that there is no duplicate in plan. The plan_code must be unique
	$data->plan_code = $data->plan_code_temp; //copy the temporary value to the actual field
	$duplicate_condition = array(
		'plan_code' => $data->plan_code,
		'country' => $data->country,
	);
	$isDuplicate = jra_query_is_duplicate('jra_plan', $duplicate_condition, $data->id);
	if(!$isDuplicate) //no duplicate, update it
	{
		if($data->id == '') //create new
		{
			$DB->insert_record('jra_plan', $data);	
		}
		else //here we don't allow update, because is in edit_plan page
		{
			/*
			$cascade = array(
				//array key is the field in $data and value is the field in the cascade table
				'si_section' => array(
					'course_code' => 'course_code',
					'course_name' => 'course_name',
				),
			);
			jra_query_update_cascade('si_course', $data, $cascade);
			*/
		}
	    redirect($return_url);
	}
}

echo $OUTPUT->header();

if($isDuplicate)
	jra_ui_alert(get_string('duplicate_plan', 'local_jra'), 'danger');
//content code starts here
jra_ui_page_title(jra_get_string($bc));

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$toform = $DB->get_record('si_course', array('id' => $id));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();


$PAGE->requires->js('/local/jra/admin/plan/plan.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();