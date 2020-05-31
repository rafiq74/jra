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
$PAGE->set_url('/local/sis/setting/organization/add_section.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);

sis_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'setup');
$PAGE->navbar->add(get_string('setup', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'setup')));
$PAGE->navbar->add(get_string('organization', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('add_section', 'local_sis'), new moodle_url('add_section.php'));

echo $OUTPUT->header();


//put before header so we can redirect
$mform = new  section_form();
if ($mform->is_cancelled()) 
{
    redirect('section.php');
} 
else if ($data = $mform->get_data()) 
{	
	//validate that there is no duplicate
	$duplicate = $DB->get_record('si_organization', array(
		'parent' => $data->parent,
		'organization_type' => 'section',
		'organization' => $data->organization,
	));
	if(!$duplicate)
	{
		//preprocessing
		$org = $DB->get_record('si_organization', array('id' => $data->parent));
		$data->campus = $org->campus;
		$data->institute = $org->institute;
	
		if($data->id == '') //create new
			$DB->insert_record('si_organization', $data);	
		else
			$DB->update_record('si_organization', $data);			
		redirect('section.php');
	}
	else
		sis_ui_alert(get_string('duplicate_section', 'local_sis'), 'danger');
}



//content code starts here
sis_ui_page_title('SIS Add Section');

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$toform = $DB->get_record('si_organization', array('id' => $id));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();








echo $OUTPUT->footer();