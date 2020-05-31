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
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php';
require_once '../../lib/sis_app_lib.php';
require_once '../../lib/sis_system_lib.php';
require_once '../../lib/sis_lookup_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once '../../user/lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/admin/bulk_action/view.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'system' => ''
); //super admin role only
sis_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

sis_set_session('sis_home_tab', 'system');
$PAGE->navbar->add(get_string('system', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'system')));
$PAGE->navbar->add(get_string('bulk_action', 'local_sis'), new moodle_url('view.php'));

$action = optional_param('action', 0, PARAM_INT);

$redirect_url = new moodle_url('view.php', array()); //1 for lookup
if($action == 1)
{
	$sql = "
		CREATE OR REPLACE VIEW v_si_course_list AS
		select 
			* 
		from 
			m_si_course a 
		where 
			a.eff_date = (select max(a_ed.eff_date) FROM m_si_course a_ed where a.catalogue_id = a_ed.catalogue_id and a_ed.eff_date <= UNIX_TIMESTAMP(SYSDATE())) 
			and a.deleted = 0
		order by 
			course_code asc
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_si_course_list created', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
else if($action == 2) //active student
{
	$sql = "
		CREATE OR REPLACE VIEW v_si_active_student AS
		select 
			a.user_id, a.appid, b.first_name, b.father_name, b.grandfather_name, b.family_name, 
			b.first_name_a, b.father_name_a, b.grandfather_name_a, b.family_name_a, CONCAT(IFNULL(b.first_name, ''), ' ', IFNULL(b.father_name, ''), ' ', IFNULL(b.grandfather_name, ''), ' ', IFNULL(b.family_name, '')) as fullname, CONCAT(IFNULL(b.first_name_a, ''), ' ', IFNULL(b.father_name_a, ''), ' ', IFNULL(b.grandfather_name_a, ''), ' ', IFNULL(b.family_name_a, '')) as fullname_a,
			a.program_id, a.program, a.program_status, a.program_action, b.gender, b.suspended, b.suspend_message, a.campus, a.institute 
		from 
			m_si_student_program a inner join m_si_user b on a.user_id = b.id
		where 
			a.eff_date = (select max(a_ed.eff_date) FROM m_si_student_program a_ed where a.user_id = a_ed.user_id and a_ed.eff_date <= UNIX_TIMESTAMP(SYSDATE())) 
			and a.eff_seq = (select max(a_es.eff_seq) FROM m_si_student_program a_es where a.user_id = a_es.user_id) 
			and a.program_status = 'AC'
		order by 
			a.appid
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_si_active_student created', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
else if($action == 3)
{
	$sql = "
		CREATE OR REPLACE VIEW v_si_userlogin AS
		select 
			a.id as user_id, CONCAT(c.prefix, a.appid) as username, a.password, a.appid, a.first_name, a.father_name, a.grandfather_name, a.family_name, CONCAT(IFNULL(first_name, ''), ' ', IFNULL(father_name, ''), ' ', IFNULL(grandfather_name, ''), ' ', IFNULL(family_name, '')) as fullname, a.user_type, c.prefix, a.national_id,
			a.gender, a.institute, b.email_primary as email, c.country  
		from 
			m_si_user a 
			inner join m_si_personal_contact b on a.id = b.user_id and b.address_type = 'primary'
			inner join m_si_institute c on a.institute = c.institute
		where 
			a.deleted = 0
			and a.eff_status = 'A'
			and a.enable_login = 'Y'
			and c.eff_status = 'A'
		order by 
			a.appid
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_si_userlogin created', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
else if($action == 4)
{
	//create an alternate si_user that join english and arabic name for search purpose
	$sql = "
		CREATE OR REPLACE VIEW v_si_user AS
		select 
			id, appid, user_type, user_category, idnumber, national_id, title, first_name, father_name, grandfather_name, family_name, first_name_a, father_name_a, grandfather_name_a, family_name_a, CONCAT(IFNULL(first_name, ''), IFNULL(IF(father_name = '', '', CONCAT(' ', father_name)), ''), IFNULL(IF(grandfather_name = '', '', CONCAT(' ', grandfather_name)), ''), IFNULL(IF(family_name = '', '', CONCAT(' ', family_name)), '')) as fullname, CONCAT(IFNULL(first_name_a, ''), IFNULL(IF(father_name_a = '', '', CONCAT(' ', father_name_a)), ''), IFNULL(IF(grandfather_name_a = '', '', CONCAT(' ', grandfather_name_a)), ''), IFNULL(IF(family_name_a = '', '', CONCAT(' ', family_name_a)), '')) as fullname_a, gender, eff_status, enable_login, deleted, institute, suspended
		from 
			m_si_user 
		where 
			deleted = 0
			and eff_status = 'A'
		order by 
			appid
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_si_user created', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
else if($action == 5) //get the max effective date of employee position
{
	$sql = "
		CREATE OR REPLACE VIEW v_si_employee_position AS
		select 
			a.*, b.position, b.position_a, b.position_type, c.organization, c.organization_name 
		from 
			m_si_employee_position a inner join m_si_position b on a.position_id = b.id inner join m_si_organization c on a.acad_org = c.id
		where 
			a.eff_date = (select max(a_ed.eff_date) FROM m_si_employee_position a_ed where a.user_id = a_ed.user_id and a_ed.eff_date <= UNIX_TIMESTAMP(SYSDATE())) 
		order by 
			user_id
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_si_employee_position created', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
else if($action == 6) //this is the term for all the active student. Use to get the batch by using distinct on batch
{
	$sql = "
		CREATE OR REPLACE VIEW v_si_active_student_term AS
		select 
			a.user_id, a.appid, a.fullname, a.program, b.semester, CONCAT(b.program, '_', b.semester) as batch, a.institute
		from 
			v_si_active_student a inner join m_si_student_term b on a.user_id = b.user_id 
		order by 
			a.appid		
		
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_si_active_student_term created', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}

//For timetable plus
if($action == 20)
{
	$sql = "
		CREATE OR REPLACE VIEW v_tp_course AS
		select 
			* 
		from 
			m_si_course a 
		where 
			a.eff_date = (select max(a_ed.eff_date) FROM m_si_course a_ed where a.catalogue_id = a_ed.catalogue_id and a_ed.eff_date <= UNIX_TIMESTAMP(SYSDATE())) 
			and a.deleted = 0
		order by 
			course_code asc
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_tp_course', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
if($action == 21)
{
	$sql = "
		CREATE OR REPLACE VIEW v_tp_lecturer AS
		select a.id, a.appid, a.user_type, b.employment_category, a.idnumber, a.national_id, CONCAT(IFNULL(first_name, ''), IFNULL(IF(father_name = '', '', CONCAT(' ', father_name)), ''), IFNULL(IF(grandfather_name = '', '', CONCAT(' ', grandfather_name)), ''), IFNULL(IF(family_name = '', '', CONCAT(' ', family_name)), '')) as fullname, a.gender, a.eff_status, a.institute
		from 
			{si_user} a inner join {si_employee} b on a.id = b.user_id 
		where 
			a.user_type = 'employee' 
			and b.employment_category = 'academic' 
			and a.eff_status = 'A' 
			and a.deleted = 0
		order by
			a.appid, fullname
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_tp_lecturer created', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
if($action == 22)
{
	$sql = "
		CREATE OR REPLACE VIEW v_tp_room AS
		select *
		from 
			{si_room} 
		where 
			eff_status = 'A' and room_usage = 'classroom'
		order by
			building, room
	";
		
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_tp_room created', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
if($action == 23)
{
	$sql = "
		CREATE OR REPLACE VIEW v_tp_student_enrollment AS
		select 
			a.id, a.user_id, a.appid, c.fullname, a.section_id, a.course_code, a.course_name, a.section, a.class_type, a.program, a.graded, a.grade_id, a.grade, CONCAT(a.program, '_', a.semester) as batch, d.organization as acad_org, b.final_exam as has_exam, IFNULL(ROUND(e.final_exam_duration / 60, 0), 0) as final_exam_duration, a.semester, a.institute 
		from 
			{si_section_student} a inner join {si_section} b on a.section_id = b.id inner join v_si_user c on a.user_id = c.id inner join {si_program} d on a.program = d.program and a.institute = d.institute inner join {si_course} e on b.course_id = e.id
		order by 
			a.semester";
		
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_tp_student_enrollment', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
if($action == 24)
{
	$sql = "
		CREATE OR REPLACE VIEW v_tp_section_schedule AS
		select  
			a.semester, a.course_id, a.course_code, a.course_name, a.section, a.section_label, a.section_num, a.class_type, a.room_type, a.capacity, a.teacher_workload_weight, a.acad_org, a.contact_hour_week, a.contact_hour_class, a.campus, b.class_num, b.duration, b.section_code, b.section_color, b.alias, b.user_id, b.appid, b.lecturer_code, b.lecturer_name, b.lecturer_id, b.lecturer_role, b.lecturer_grade_access, b.batch, b.merge_group, b.same_time_group, b.specific_room, b.room_group, b.room_code, b.day_text, b.day_num, b.start_time, b.end_time, b.start_time_raw, b.end_time_raw, b.id as idnumber, b.date_updated, a.institute, b.tree_path, b.deleted, b.update_lock, b.lock_user 
		from 
			{si_section} a left join {si_section_schedule} b on a.course_code = b.course_code and a.section = b.section and a.semester = b.semester and a.institute = b.institute 
		order by 
			a.semester, a.course_code, a.section, class_type, b.day_num, b.start_time
	";
		
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_tp_section_schedule', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}
if($action == 25)
{
	$sql = "
		CREATE OR REPLACE VIEW v_tp_non_academic AS
		select a.id, a.appid, a.user_type, b.employment_category, a.idnumber, a.national_id, CONCAT(IFNULL(first_name, ''), IFNULL(IF(father_name = '', '', CONCAT(' ', father_name)), ''), IFNULL(IF(grandfather_name = '', '', CONCAT(' ', grandfather_name)), ''), IFNULL(IF(family_name = '', '', CONCAT(' ', family_name)), '')) as fullname, a.gender, a.eff_status, a.institute
		from 
			{si_user} a inner join {si_employee} b on a.id = b.user_id 
		where 
			a.user_type = 'employee' 
			and b.employment_category = 'non_academic' 
			and a.eff_status = 'A' 
			and a.deleted = 0
		order by
			a.appid, fullname
	";
	if($DB->execute($sql))
	{
		echo '<br /><br />';
		$msg = sis_ui_alert('View v_tp_non_academic created', 'success', '', true, true);			
		sis_ui_set_flash_message($msg, 'sis_create_view');
		redirect($redirect_url); //redirect to avoid reexecution if press refresh
	}
}

echo $OUTPUT->header();

//content code starts here
sis_ui_page_title(get_string('bulk_action','local_sis'));
$currenttab = 'view'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

//if there is any flash message
sis_ui_show_flash_message('sis_create_view');

$output = '';

$output .= '<div class="pt-3">';
$output .= '<h4>Views for SIS</h4>';
$output .= '<div>';
$output .= '<div class="pt-3">';
//one button
$url = new moodle_url('view.php', array('action' => 1)); //1 for lookup
$output .= sis_ui_button('v_si_course_list', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 2)); //1 for active student
$output .= sis_ui_button('v_si_active_student', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 3)); //1 for user login
$output .= sis_ui_button('v_si_userlogin', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 4)); //1 for user
$output .= sis_ui_button('v_si_user', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 5)); //1 for employee position view
$output .= sis_ui_button('v_si_employee_position', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 6)); //1 for list of active student term (here is where the batch is)
$output .= sis_ui_button('v_si_active_student_term', $url, 'primary', '', '', true);
//end of one button
$output .= '</div>';

//this is for tplus views
$output .= '<div class="pt-3">';
$output .= '<h4>Views for Timetable Plus</h4>';
$output .= '<div>';

$output .= '<div class="pt-3">';
//one button
$url = new moodle_url('view.php', array('action' => 20)); //20 for course
$output .= sis_ui_button('v_tp_course', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 21)); //21 for lecturer
$output .= sis_ui_button('v_tp_lecturer', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 22)); //22 for room
$output .= sis_ui_button('v_tp_room', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 23)); //22 for student enrollment
$output .= sis_ui_button('v_tp_student_enrollment', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 24)); //22 for student enrollment
$output .= sis_ui_button('v_tp_section_schedule', $url, 'primary', '', '', true);
//end of one button
$output .= '&nbsp;&nbsp;&nbsp;';
//one button
$url = new moodle_url('view.php', array('action' => 25)); //22 for student enrollment
$output .= sis_ui_button('v_tp_non_academic', $url, 'primary', '', '', true);
//end of one button
$output .= '</div>';

echo $output;

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/admin/bulk_action/bulk_action.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();