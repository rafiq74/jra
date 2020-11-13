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
require_once '../../lib/jra_system_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once '../../user/lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/bulk_action/view.php', $urlparams);
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

jra_set_session('jra_home_tab', 'system');
$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['view']), new moodle_url('index.php'));

$action = optional_param('action', 0, PARAM_INT);

$redirect_url = new moodle_url('view.php', array()); //1 for lookup
if($action == 1)
{
	//create an alternate jra_user that join english and arabic name for search purpose
	$sql = "
		CREATE OR REPLACE VIEW v_jra_user AS
		select
			id, username, email, user_type, user_category, password, idnumber, national_id, passport_id, nationality, title, first_name, father_name, grandfather_name, family_name, first_name_a, father_name_a, grandfather_name_a, family_name_a, CONCAT(IFNULL(first_name, ''), IFNULL(IF(father_name = '', '', CONCAT(' ', father_name)), ''), IFNULL(IF(grandfather_name = '', '', CONCAT(' ', grandfather_name)), ''), IFNULL(IF(family_name = '', '', CONCAT(' ', family_name)), '')) as fullname, CONCAT(IFNULL(first_name_a, ''), IFNULL(IF(father_name_a = '', '', CONCAT(' ', father_name_a)), ''), IFNULL(IF(grandfather_name_a = '', '', CONCAT(' ', grandfather_name_a)), ''), IFNULL(IF(family_name_a = '', '', CONCAT(' ', family_name_a)), '')) as fullname_a, gender, dob, marital_status, active_status, enable_login, deleted, suspended, country, date_created
		from
			m_jra_user
		where
			deleted <= 1
		order by
			first_name, middle_name, family_name
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = jra_ui_alert('View v_jra_user created', 'success', '', true, true);
		jra_ui_set_flash_message($msg, 'jra_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
else if($action == 2)
{
	$sql = "
		CREATE OR REPLACE VIEW v_jra_userlogin AS
		select
			id, username, email, user_type, user_category, password, idnumber, national_id, passport_id, nationality, title, first_name, father_name, grandfather_name, family_name, first_name_a, father_name_a, grandfather_name_a, family_name_a, CONCAT(IFNULL(first_name, ''), IFNULL(IF(father_name = '', '', CONCAT(' ', father_name)), ''), IFNULL(IF(grandfather_name = '', '', CONCAT(' ', grandfather_name)), ''), IFNULL(IF(family_name = '', '', CONCAT(' ', family_name)), '')) as fullname, CONCAT(IFNULL(first_name_a, ''), IFNULL(IF(father_name_a = '', '', CONCAT(' ', father_name_a)), ''), IFNULL(IF(grandfather_name = '', '', CONCAT(' ', grandfather_name)), ''), IFNULL(IF(family_name_a = '', '', CONCAT(' ', family_name_a)), '')) as fullname_a, gender, dob, marital_status, active_status, enable_login, deleted, suspended, country
		from
			m_jra_user
		where
			deleted = 0
			and (active_status = 'A' or active_status = 'P')
			and enable_login = 'Y'
		order by
			first_name, middle_name, family_name
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = jra_ui_alert('View v_jra_userlogin created', 'success', '', true, true);
		jra_ui_set_flash_message($msg, 'jra_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
else if($action == 3)
{
	//create an alternate jra_user that join english and arabic name for search purpose
	$sql = "
		CREATE OR REPLACE VIEW v_si_applicant AS
		select
			a.id, appid, a.user_id, semester, idnumber, national_id, id_type, nationality, nationality_at_birth, title, first_name, father_name, grandfather_name, family_name, first_name_a, father_name_a, grandfather_name_a, family_name_a, CONCAT(IFNULL(first_name, ''), IFNULL(IF(father_name = '', '', CONCAT(' ', father_name)), ''), IFNULL(IF(grandfather_name = '', '', CONCAT(' ', grandfather_name)), ''), IFNULL(IF(family_name = '', '', CONCAT(' ', family_name)), '')) as fullname, CONCAT(IFNULL(first_name_a, ''), IFNULL(IF(father_name_a = '', '', CONCAT(' ', father_name_a)), ''), IFNULL(IF(grandfather_name_a = '', '', CONCAT(' ', grandfather_name_a)), ''), IFNULL(IF(family_name_a = '', '', CONCAT(' ', family_name_a)), '')) as fullname_a, gender, dob, dob_hijri, marital_status, city, religion, blood_type, tahseli, tahseli_weight, qudorat, qudorat_weight, secondary, secondary_weight, aggregation, graduated_from, graduated_year,graduated_max_gpa, graduated_gpa, graduated_major, national_id_file, tahseli_file, qudorat_file, secondary_file, transcript_file, uni_approval_file, tabeiah_file, study_stream, program_apply, status, status_date, status_user, admit_status, admit_status_date, placement_test_score, ranking, acceptance, acceptance_date, student_id, a.date_created, a.date_updated, deleted, a.institute, address1, address2, address_state, b.address_city, b.phone_mobile, email_primary as email, contact_name, contact_relationship, contact_mobile
		from
			m_si_applicant a inner join m_si_applicant_contact b on a.id = b.applicant_id and a.institute = b.institute and a.user_id = b.user_id
		order by
			semester, a.id
	";

	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = jra_ui_alert('View v_si_applicant created', 'success', '', true, true);
		jra_ui_set_flash_message($msg, 'jra_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string(['bulk', 'action']));
$currenttab = 'view'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('jra_tabbox');

//if there is any flash message
jra_ui_show_flash_message('jra_create_view');

$output = '';

$output .= '<div class="pt-3">';
$output .= '<h4>Views for jra</h4>';
$output .= '<div>';
$output .= '<div class="pt-3">';

//one button
$url = new moodle_url('view.php', array('action' => 1)); //1 for user
$output .= jra_ui_button('v_jra_user', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 2)); //2 for user login
$output .= jra_ui_button('v_jra_userlogin', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 3)); //3 for si_applicant
$output .= jra_ui_button('v_si_applicant', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';

$output .= '</div>';

//this is for tplus views
$output .= '<div class="pt-3">';
$output .= '<h4>Another set of view creation buttons</h4>';
$output .= '<div>';

$output .= '<div class="pt-3">';
//one button
$url = new moodle_url('view.php', array('action' => 20)); //20 for course
$output .= jra_ui_button('v_tp_course', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
$output .= '</div>';

echo $output;

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/admin/bulk_action/bulk_action.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();
