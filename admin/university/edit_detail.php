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
$PAGE->set_url('/local/sis/academic/semester/edit_detail.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'academic',
	'subrole' => 'all',
);
sis_access_control($access_rules);


$post_data = $_POST;
if(isset($post_data['sid']))
	$id = $post_data['sid'];
else
	$id = required_param('id', PARAM_INT);

$week = required_param('week', PARAM_INT);

$week_text = get_string('week', 'local_sis') . ' ' . str_pad($week, 2, '0', STR_PAD_LEFT);
//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'academic');
$PAGE->navbar->add(get_string('academic', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'academic')));
$PAGE->navbar->add(get_string('semester', 'local_sis'), new moodle_url('index.php', array('id' => $id)));
$PAGE->navbar->add(get_string('semester_timeline', 'local_sis'), new moodle_url('view_semester.php', array('id' => $id)));
$PAGE->navbar->add(get_string('edit_semester_detail', 'local_sis') . ' : ' . $week_text, new moodle_url('edit_detail.php', array('id' => $id, 'week' => $week)));


$return_params = array('id' => $id);
$return_url = new moodle_url('view_semester.php', $return_params);

//put before header so we can redirect
$isDuplicate = false;
$mform = new semester_detail_form(null, array('sid' => $id, 'week' => $week));
if ($mform->is_cancelled()) 
{
    redirect($return_url->out());
} 
else if ($data = $mform->get_data()) 
{		
	if($data->id != '') //update
	{
		$DB->update_record('si_semester_detail', $data);
	}
	else
	{
		//try to get the existing record
		$condition = array(
			'semester_id' => $data->sid,
			'detail_week' => $data->week,
			'institute' => $data->institute,
		);
		$existing = $DB->get_record('si_semester_detail', $condition);
		if(!$existing)	
		{
			$DB->insert_record('si_semester_detail', $data);
		}
		else
		{
			$data->id = $existing->id;
			$DB->update_record('si_semester_detail', $data);			
		}
	}
	redirect($return_url->out());
}

echo $OUTPUT->header();

//content code starts here
sis_ui_page_title(get_string('edit_semester_detail', 'local_sis') . ' : ' . $week_text);

$institute = sis_get_institute();
//try to get existing record
$condition = array(
	'semester_id' => $id,
	'detail_week' => $week,
	'institute' => $institute,
);
$existing = $DB->get_record('si_semester_detail', $condition);

if($existing)
{
	$mform->set_data($existing);
}

$mform->display();

echo $OUTPUT->footer();