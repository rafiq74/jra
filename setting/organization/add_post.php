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
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/organization/add_post.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
sis_access_control($access_rules);

$post_data = $_POST;
if(isset($post_data['pid']))
	$id = $post_data['pid'];
else
	$id = required_param('id', PARAM_INT);

if(isset($post_data['type']))
	$type = $post_data['type'];
else
	$type = required_param('type', PARAM_TEXT);

if($type != 'acad_org' && $type != 'campus' && $type != 'institute')
	throw new moodle_exception(get_string('wrong_parameter', 'local_sis'));	
	
$dataid = optional_param('dataid', false, PARAM_INT);
if($dataid)
{
	$qs = '?id=' . $id.  '&dataid=' . $dataid;
	$bc = ['update', 'post'];
}
else
{
	$qs = '?id=' . $id;
	$bc = ['add', 'post'];
}

$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'setup');
$PAGE->navbar->add(get_string('setup', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'setup')));
$PAGE->navbar->add(get_string('organization', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(sis_get_string($bc), new moodle_url('add_post.php' . $qs));

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

//put before header so we can redirect
if($type == 'acad_org')
{
	$return_url = new moodle_url('view_organization.php', array('id' => $id));
}
else if($type == 'campus')
{
	$return_url = new moodle_url('view_campus.php', array('id' => $id));
}
else if($type == 'institute')
{
	$return_url = new moodle_url('view_institute.php', array('id' => $id));
}
$mform = new post_form(null, array('pid' => $id, 'type' => $type));
if ($mform->is_cancelled()) 
{
    redirect($return_url->out(false));
} 
else if ($data = $mform->get_data()) 
{		
	//validate that there is no duplicate
	$duplicate_condition = array(
		'position_id' => $data->position_id,
		'user_id' => $data->user_id,
		'category' => $data->category,
		'organization' => $data->organization,
		'appointment_type' => $data->appointment_type,
		'institute' => $data->institute,
	);
	$isDuplicate = sis_query_is_duplicate('si_organization_post', $duplicate_condition, $data->id);
	if(!$isDuplicate) //no duplicate, update it
	{
		$now = time();
		$data->date_updated = $now;
		$data->user_updated = $USER->id;
		if($data->id == '') //create new
		{
			$user = $DB->get_record('si_user', array('id' => $data->user_id));
			$data->appid = $user->appid;
			$position = $DB->get_record('si_position', array('id' => $data->position_id));
			$data->position = $position->position;
			$DB->insert_record('si_organization_post', $data);	
		}
		else
		{
			$user = $DB->get_record('si_user', array('id' => $data->user_id));
			$data->appid = $user->appid;
			$DB->update_record('si_organization_post', $data);	
		}
	    redirect($return_url);
	}
}

echo $OUTPUT->header();

if($isDuplicate)
	sis_ui_alert(get_string('duplicate_organization_post', 'local_sis'), 'danger');

//content code starts here
sis_ui_page_title(sis_get_string($bc));

if($dataid)
{
	$toform = $DB->get_record('si_organization_post', array('id' => $dataid));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();

echo $OUTPUT->footer();