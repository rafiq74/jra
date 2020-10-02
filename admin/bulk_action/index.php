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
 * @license   http://www.gnu.org/copycenter/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../lib/jra_lib.php'; 
require_once '../../lib/jra_ui_lib.php';
require_once '../../lib/jra_app_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once '../../user/lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/setting/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'system' => ''
); //super admin role only
jra_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['bulk', 'action']), new moodle_url('index.php'));

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string(['bulk', 'action']));
$currenttab = 'general'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('jra_tabbox');

$output = '';
$output .= '<div class="pt-3">';
//one button
$url = new moodle_url('index.php', array('action' => 1)); //1 for lookup
$output .= jra_ui_button(get_string('update_lookup_values', 'local_jra'), $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('index.php', array('action' => 2)); //1 for lookup
$output .= jra_ui_button('Update App Ref #', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('index.php', array('action' => 3)); //1 for lookup
$output .= jra_ui_button('Update Aggregate Computation', $url, 'primary', '', '', true);
//end of one button

$output .= '</div>';
echo $output;

$action = optional_param('action', 0, PARAM_INT);

if($action == 1)
	jra_admin_bulk_action_lookup();
else if($action == 2)
{
	$semester = jra_get_semester();
	$applicants = $DB->get_records('si_applicant', array('semester' => $semester));
	foreach($applicants as $applicant)
	{
		$obj = new stdClass();
		$obj->id = $applicant->id;
		$obj->appid = jra_app_ref_number($obj);
		$DB->update_record('si_applicant', $obj);	
	}
}
else if($action == 3) //update aggregation
{
	jra_app_recompute_aggregate();
}

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/admin/bulk_action/bulk_action.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();