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
$PAGE->set_url('/local/sis/setting/organization/add_institute.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array('system' => ''); //super admin role only
sis_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column

$id = optional_param('id', false, PARAM_INT);
if($id)
{
	$qs = '?id=' . $id;
	$bc = 'update_institute';
}
else
{
	$qs = '';
	$bc = 'add_institute';
}
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'setup');
$PAGE->navbar->add(get_string('setup', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'setup')));
$PAGE->navbar->add(get_string('organization', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('institute', 'local_sis'), new moodle_url('institute.php'));
$PAGE->navbar->add(get_string($bc, 'local_sis'), new moodle_url('add_institute.php' . $qs));

//put before header so we can redirect
$mform = new institute_form();
if ($mform->is_cancelled()) 
{
    redirect('institute.php');
} 
else if ($data = $mform->get_data()) 
{	
//validate that there is no duplicate
	$isDuplicate = false;
	$duplicate = $DB->get_record('si_institute', array('institute' => $data->institute));
	if($duplicate)
	{
		if($data->id != '') //updating
		{
			$original = $DB->get_record('si_institute', array('id' => $data->id));
			if($duplicate->id != $original->id)
				$isDuplicate = true;
		}
		else
			$isDuplicate = true;			
	}
	if(!$isDuplicate) //no duplicate, update it
	{
		$data->date_created = time();
		if($data->id == '') //create new
			$DB->insert_record('si_institute', $data);	
		else
			$DB->update_record('si_institute', $data);			
		redirect('institute.php');
	}
}

echo $OUTPUT->header();
if($isDuplicate)
	sis_ui_alert(get_string('duplicate_institute', 'local_sis'), 'danger');

//content code starts here
sis_ui_page_title(get_string($bc, 'local_sis'));

if($id)
{
	$toform = $DB->get_record('si_institute', array('id' => $id));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();
echo $OUTPUT->footer();