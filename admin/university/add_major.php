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
require_once '../../lib/jra_app_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/semester/add_major.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admission',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$id = optional_param('id', false, PARAM_INT);
if($id)
{
	$qs = '?id=' . $id;
	$bc = ['update', 'major'];
}
else
{
	$qs = '';
	$bc = ['add', 'major'];
}

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['major']), new moodle_url('index.php'));
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('add_major.php', $urlparams));


$return_url = new moodle_url('major.php');
//put before header so we can redirect
$mform = new university_form();
if ($mform->is_cancelled())
{
    redirect($return_url);
}

else if ($data = $mform->get_data())
{

	//validate that there is no duplicate
	$isDuplicate = false;
	$duplicate_condition = array(
		'name' => $data->name
	);


	$isDuplicate = jra_query_is_duplicate('si_major', $duplicate_condition, $data->id);
	if(!$isDuplicate) //no duplicate, update it
	{
		if($data->id == '') //create new
			$DB->insert_record('si_major', $data);
		else
			$DB->update_record('si_major', $data);

		redirect($return_url);
	}

}

echo $OUTPUT->header();

if($isDuplicate)
	jra_ui_alert(get_string('duplicate_major', 'local_jra'), 'danger');

//content code starts here
jra_ui_page_title(jra_get_string($bc));

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$toform = $DB->get_record('si_major', array('id' => $id));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();

echo $OUTPUT->footer();
