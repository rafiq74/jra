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
$PAGE->set_url('/local/sis/setting/grade/add_grade_scheme_action.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);

sis_access_control($access_rules);

//echo $OUTPUT->header();

//$html = '';

//$url = "javascript:add_grade_letter()";		
//put before header so we can redirect
$submit_url = new moodle_url('/local/sis/setting/grade/index.php', array());
$mform = new grade_scheme_form($submit_url, array(), 'post', '', array('name' => 'mform1', 'onsubmit' => 'return save_grade_scheme()'));

if ($mform->is_cancelled()) 
{
} 
else if ($data = $mform->get_data()) 
{	
//validate that there is no duplicate
	$duplicate = $DB->get_record('si_grade_scheme', array('grade_scheme' => $data->grade_scheme ));
	if(!$duplicate)
	{
	$data->date_created = time();
	if($data->id == '') //create new
		$DB->insert_record('si_grade_scheme', $data);	
	else
		$DB->update_record('si_grade_scheme', $data);			
    redirect('index.php');
}
	else
		sis_ui_alert(get_string('dublicate_grade_scheme', 'local_sis'), 'danger');
		}



//content code starts here

$id = optional_param('id', false, PARAM_INT);
if($id)
{
	$toform = $DB->get_record('si_grade_scheme', array('id' => $id));
	if($toform)
		$mform->set_data($toform);
}

echo sis_ui_page_title2(get_string('add_grading_scheme', 'local_sis'), array('class' => 'pt-2'), true);
echo sis_ui_box_start(true);
$mform->display();
echo sis_ui_box_end();