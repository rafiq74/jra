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
require_once '../../lib/sis_lookup_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once '../../lib/sis_timetable_lib.php';
require_once '../../user/lib.php';
require_once '../../student/lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/admin/setting/migrate.php', $urlparams);
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
$PAGE->navbar->add(get_string('bulk_action', 'local_sis'), new moodle_url('index.php'));

echo $OUTPUT->header();

//content code starts here
sis_ui_page_title(get_string('data_migration','local_sis'));
$currenttab = 'general'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

$output = '';
$output .= '<div class="pt-3">';
//one button
$url = new moodle_url('migrate.php', array('action' => 2)); //import from data
$output .= sis_ui_button(get_string('execute', 'local_sis'), $url, 'primary', '', '', true);
//end of one button

$output .= '</div>';
echo $output;
set_time_limit(0);
$action = optional_param('action', 0, PARAM_INT);

$institute = sis_get_institute();

/* update national id in si_user
$sql = "select a.id, b.civil_id as national_id from {si_user} a inner join {si_personal_data} b on a.id = b.user_id";

$x = $DB->get_records_sql($sql);
foreach($x as $y)
{
	print_object($y);
	$DB->update_record('si_user', $y);
}
*/
$now = time();

if($action == 1) //for import of iban number. Don't delete
{
	$info = $DB->get_records('si_data_import', array(
	));
//	print_object($info);
	$banks = $DB->get_records('si_personal_finance');
	foreach($banks as $bank)
		break;
		
//	print_object($bank);
	$arr = array();
	foreach($info as $a)
	{
		$stud = $DB->get_record('si_user', array('appid' => $a->field2));
		if($stud)
		{
			//try to get the finance record
			$data = $DB->get_record('si_personal_finance', array('user_id' => $stud->id));
			$b = $a->field3; //iban
			if(strlen($b) != 24)
				print_object($stud->appid . ' ERROR In IBAN!!!!!!!!!!!');
			if(!$data)
			{
				$data = new stdClass();
				$data->user_id = $stud->id;
				$data->bank_code = $bank->bank_code;
				$data->bank_name = $bank->bank_name;
				$data->bank_name_a = $bank->bank_name_a;
				$data->account_no = '';
				$data->iban_01 = strval($b[2] . $b[3] . $b[4] . $b[5]);
				$data->iban_02 = strval($b[6] . $b[7] . $b[8] . $b[9]);
				$data->iban_03 = strval($b[10] . $b[11] . $b[12] . $b[13]);
				$data->iban_04 = strval($b[14] . $b[15] . $b[16] . $b[17]);
				$data->iban_05 = strval($b[18] . $b[19] . $b[20] . $b[21]);
				$data->iban_06 = strval($b[22] . $b[23]);
				$data->account_type = '';
				$data->user_updated = $USER->id;
				$data->date_created = $now;
				$data->date_updated = $now;
				$data->institute = 'HIEI';
	//			$DB->insert_record('si_personal_finance', $data);
	//			print_object($data);
	//			$arr[] = $data;
				print_object('insert finance');
			}
			else
			{
				$data->bank_code = $bank->bank_code;
				$data->bank_name = $bank->bank_name;
				$data->bank_name_a = $bank->bank_name_a;
				$data->account_no = '';
				$data->iban_01 = strval($b[2] . $b[3] . $b[4] . $b[5]);
				$data->iban_02 = strval($b[6] . $b[7] . $b[8] . $b[9]);
				$data->iban_03 = strval($b[10] . $b[11] . $b[12] . $b[13]);
				$data->iban_04 = strval($b[14] . $b[15] . $b[16] . $b[17]);
				$data->iban_05 = strval($b[18] . $b[19] . $b[20] . $b[21]);
				$data->iban_06 = strval($b[22] . $b[23]);
				$data->account_type = '';
				$data->user_updated = $USER->id;
				$data->date_created = $now;
				$data->date_updated = $now;
//				print_object($data);
				print_object('update finance');
//				$DB->update_record('si_personal_finance', $data);
			}
			//activate the payroll
			$student = $DB->get_record('si_student', array('user_id' => $stud->id));
			if($student)
			{
				$student->payroll_active = 'Y';
//					$DB->update_record('si_student', $student);
				print_object('update si_student');
			}
			else
			{
				$data = new stdClass();
				$data->user_id = $stud->id;
				$data->appid = $stud->appid;
				$data->semester = '20192';
				$data->admission_date = $now;
				$data->admission_type = 'public';			
				$data->student_category = 'full_time';
				$data->student_type = 'regular';
				$data->batch = '20192';
				$data->financing_source = 'regular';
				$data->payroll_active = 'Y';
				
				$data->date_created = $now;
				$data->date_updated = $now;
				$data->institute = 'HIEI';
//				print_object($data);
//				$DB->insert_record('si_student', $data);
				print_object('insert si_student');
			}
		}
		else
		{
			print_object('not found');
		}
	}
}
//don't delete action 2
//this is a very important function to import data to sis plus. Use the excel on sheet data_import to import into the table
//uncomment and comment each part by stages to create and import the user data
else if($action == 2) //execute migrate from data import table
{
	$now = time();
	$info = $DB->get_records('si_data_import', array(
	));
	$arr = array();
	foreach($info as $a)
	{
//		print_object($a);
		/* Create the user in si_user
		$data = new stdClass();
		$data->appid = $a->field1;		
		$data->user_type = 'student';		
		$data->title = 'Mr';		
		$data->first_name = ucfirst($a->field2);		
		$data->father_name = ucfirst($a->field3);		
		$data->grandfather_name = ucfirst($a->field4);		
		$data->family_name = ucfirst($a->field5);		
		$data->first_name_a = $a->field6;		
		$data->father_name_a = $a->field7;		
		$data->grandfather_name_a = $a->field8;		
		$data->family_name_a = $a->field9;		
		$data->gender = 'M';
		$data->eff_status = 'A';
		$data->enable_login = 'Y';
		$data->deleted = '0';
		$data->date_created = $now;
		$data->date_updated = $now;
		$data->institute = 'HIEI';
		$arr[] = $data;		
		*/		
		$stud = $DB->get_record('si_user', array('appid' => $a->field1));
		/* for civil id
		$rec = $DB->get_record('si_personal_data', array('user_id' => $stud->user_id));
		if(!$rec)
		{
			$data = new stdClass();
			$data->user_id = $stud->id;
			$data->civil_id = $a->field10;
			$data->id_type = 'saudi_national_id';
			$data->marital_status = 'S';
			$data->city = '8';
			$data->nationality = 'SA';
			$data->nationality_at_birth = 'SA';
			$data->native_language = 'sa';
			$data->language_track = 'en';
			$data->religion = 'Islam';
			$data->blood_type = '';
			$data->date_created = $now;
			$data->date_updated = $now;
			$data->institute = 'HIEI';
			$arr[] = $data;		
		}
		*/
		/* for contact
		$rec = $DB->get_record('si_personal_contact', array('user_id' => $stud->user_id));
		if(!$rec)
		{
			$data = new stdClass();
			$data->user_id = $stud->id;
			$data->address_type = 'primary';
			$data->address1 = '';
			$data->address2 = '';
			$data->phone_home = $a->field11;
			$data->phone_mobile = $a->field11;
			$data->email_primary = $a->field12;
			$data->email_secondary = $a->field12;
			
			$data->date_created = $now;
			$data->date_updated = $now;
			$data->institute = 'HIEI';
			$arr[] = $data;		
		}
		*/		
		/* for si_student
		$rec = $DB->get_record('si_student', array('user_id' => $stud->user_id));
		if(!$rec)
		{
			$data = new stdClass();
			$data->user_id = $stud->id;
			$data->appid = $stud->appid;
			$data->semester = 'CRTP002';
			$data->admission_date = $now;
			$data->admission_type = 'public';			
			$data->student_category = 'full_time';
			$data->student_type = 'crtp';
			$data->batch = 'CRTP002';
			$data->financing_source = 'regular';
			$data->payroll_active = 'Y';
			
			$data->date_created = $now;
			$data->date_updated = $now;
			$data->institute = 'HIEI';
			$arr[] = $data;		
		}
		*/
		/* for import of iban
		$rec = $DB->get_record('si_personal_finance', array('user_id' => $stud->user_id));
		if(!$rec)
		{
			$bank = $DB->get_record('si_bank', array('bank_code' => $a->field13));
			if($bank)
			{
				$iban = $a->field14;
				if($iban != '')
				{
					$data = new stdClass();
					$data->user_id = $stud->id;
					$data->bank_code = $bank->bank_code;
					$data->bank_name = $bank->bank_name;
					$data->bank_name_a = $bank->bank_name_a;
					$data->account_no = '';
					$data->iban = $iban;
					$data->iban_01 = strval($iban[2] . $iban[3] . $iban[4] . $iban[5]);
					$data->iban_02 = strval($iban[6] . $iban[7] . $iban[8] . $iban[9]);
					$data->iban_03 = strval($iban[10] . $iban[11] . $iban[12] . $iban[13]);
					$data->iban_04 = strval($iban[14] . $iban[15] . $iban[16] . $iban[17]);
					$data->iban_05 = strval($iban[18] . $iban[19] . $iban[20] . $iban[21]);
					$data->iban_06 = strval($iban[22] . $iban[23]);
					$data->account_type = '';
					$data->user_updated = $USER->id;
					$data->date_created = $now;
					$data->date_updated = $now;
					$data->institute = 'HIEI';
					$arr[] = $data;
				}
			}
			else
				print_object('No bank found');
		}
		*/
		/*
		//creating the academic program
		$program = $DB->get_record('si_program', array('id' => $a->field15));
		$program_status = $DB->get_record('si_program_status', array('program_status' => 'AC', 'program_action' => 'matriculation', 'institute' => $institute));
		$eff_date = strtotime('25-August-2019');
		
		$data = new stdClass();
		$data->user_id = $stud->id;
		$data->appid = $stud->appid;
		$data->program_id = $program->catalogue_id;
		$data->program = $program->program;
		$data->program_status_id = $program_status->id;
		$data->program_status = $program_status->program_status;
		$data->program_action = $program_status->program_action;
		$data->description = $program_status->description;
		$data->action_date = $now;
		$data->eff_date = $eff_date;
		$data->eff_date_end = '';
		$data->eff_seq = 1;
		$data->campus = '';
		$data->institute = 'HIEI';
		$data->user_updated = $USER->id;
		$data->date_updated = $now;
		$data->eff_date_temp = date('d-M-Y', $data->eff_date);
		$data->eff_date_end_temp = '';
		if(isset($arr[$stud->id])) //already has a record, set the previous effective date
		{
			foreach($arr[$stud->id] as $x)
			{
				if($x->eff_date_end == '')
				{
					$end_date = strtotime(date('d-M-Y', $data->eff_date) . ' - 1 day'); //reduce by 1 day because end date_is one day before the new effective date.
					$x->eff_date_end = $end_date;
					$x->eff_date_end_temp = date('d-M-Y', $x->eff_date_end);
				}
			}
		}
//			$DB->insert_record('si_student_program', $data);
		$arr[$stud->id][] = $data;
		*/
		/* create the student study plan
		$program = $DB->get_record('si_program', array('id' => $a->field15));
		$plan = $DB->get_record('si_plan', array('program_id' => $program->id));
		$eff_date = strtotime('25-August-2019');		
		$now = time();
		
		$data = new stdClass();
		$data->user_id = $stud->id;
		$data->appid = $stud->appid;
		
		$data->program_id = $program->catalogue_id;
		$data->program = $program->program;
		$data->plan_id = $plan->catalogue_id;
		$data->plan = $plan->plan;
		$data->eff_date = $eff_date;							
		$data->plan_status = 'AC';							
		$data->plan_action = 'activate';
		$data->plan_sequence = $plan->plan_sequence;
		$data->institute = $program->institute;
		$data->user_updated = $USER->id; //moodle user id
		$data->date_updated = $now;
		$arr[] = $data;		
//		$DB->insert_record('si_student_plan', $data);
//		print_object($data);
		*/		
	}
	print_object($arr);
//	$DB->insert_records('si_personal_finance', $arr);		
}
//with action 2, 3, 4, and 5 no longer use
//don't delete anything from 3 onward. It is used to create student_prgram and study plan
else if($action == 3) //run this part before running 4 to preprocess the imported data
{
	$eff_date = sis_earliest_date();
	$sql = "select appid, id from {si_user} where institute = '$institute' and user_type = 'student'";
	$users = $DB->get_records_sql($sql);
	
	$info = $DB->get_records('si_data_import', array(
		'institute' => $institute,
	));
	
	foreach($info as $a)
	{
		if(isset($users[$a->field1]))
		{
			$u = $users[$a->field1];
			$a->field7 = $u->id;
		}
		$a->field8 = strtotime($a->field6);
		print_object($a);
//		$DB->update_record('si_data_import', $a);	
	}
}
else if($action == 4) //this part is to import into si_student_program (make sure run 3 first)
{
	$sql = "select * from {si_data_import} where institute = '$institute' order by field7, field8";
	$info = $DB->get_records_sql($sql);
	$arr = array();
	foreach($info as $a)
	{
		$data = new stdClass();
		$data->user_id = $a->field7;
		$data->appid = $a->field1;
		$data->program_id = 7;
		$data->program = 'ELT';
		$data->program_status_id = $a->field2;
		$data->program_status = $a->field3;
		$data->program_action = $a->field4;
		$data->description = $a->field5;
		$data->action_date = $now;
		$data->eff_date = $a->field8;
		$data->eff_date_end = '';
		$data->eff_seq = 1;
		$data->campus = '';
		$data->institute = $institute;
		$data->user_updated = 2;
		$data->date_updated = $now;
		$data->eff_date_temp = date('d-M-Y', $data->eff_date);
		$data->eff_date_end_temp = '';
		if(isset($arr[$a->field7])) //already has a record, set the previous effective date
		{
			foreach($arr[$a->field7] as $x)
			{
				if($x->eff_date_end == '')
				{
					$end_date = strtotime(date('d-M-Y', $data->eff_date) . ' - 1 day'); //reduce by 1 day because end date_is one day before the new effective date.
					$x->eff_date_end = $end_date;
					$x->eff_date_end_temp = date('d-M-Y', $x->eff_date_end);
				}
			}
		}
//		$DB->insert_record('si_student_program', $data);
		$arr[$a->field7][] = $data;
	}
	print_object($arr);
}
else if($action == 5) //create student study plan
{
	$program = $DB->get_record('si_program', array('id' => 7));
	print_object($program);	
	$plan = $DB->get_record('si_plan', array('id' => 10));
	print_object($plan);	
	$sql = "SELECT * FROM {si_student_program} where institute = 'HIEI' and program_status = 'AC'";
	$recs = $DB->get_records_sql($sql);
	$now = time();
	foreach($recs as $r)
	{
		$pl_data = new stdClass();
		$pl_data->user_id = $r->user_id;
		$pl_data->appid = $r->appid;
		$pl_data->program_id = $program->id;
		$pl_data->program = $program->program;
		$pl_data->plan_id = $plan->id;
		$pl_data->plan = $plan->plan;
		$pl_data->eff_date = $r->eff_date;							
		$pl_data->plan_status = 'AC';							
		$pl_data->plan_action = 'activate';
		$pl_data->plan_sequence = $plan->plan_sequence;
		$pl_data->institute = $program->institute;
		$pl_data->user_updated = $USER->id; //moodle user id
		$pl_data->date_updated = $now;
//		$DB->insert_record('si_student_plan', $pl_data);
		print_object($pl_data);
	}
}
else if($action == 6)
{
	$now = time();
	$info = $DB->get_records('si_data_import', array(
	));
	$arr = array();
	foreach($info as $a)
	{
		$a->field9 = strtotime($a->field7);
		$a->field11 = date('d-M-Y', $a->field9);
		if($a->field8 != '')
		{
			$a->field10 = strtotime($a->field8);
			$a->field12 = date('d-M-Y', $a->field10);
		}
		$arr[$a->field2][] = $a;
	}
	die;
	foreach($arr as $course_code => $course_list)
	{
		foreach($course_list as $index => $data)
		{
			print_object($index);
			if($index == 0) //first encounter, try to get the course
			{
				//try to find the course in si_course
				$course = $DB->get_record('si_course', array('course_code' => $data->field2, 'institute' => 'HIEI'));
				if($course) //found the course, update the effective date
				{
					$course->course_name = $data->field3; //update the course name
					$course->idnumber = $data->field1; //save the logsis idnumber
					$course->eff_date = $data->field9;
					$course->default_credit = $data->field4;
					$data->field13 = $course;
//					$DB->update_record('si_course', $course);
				}
				else //not found the course, then have to create it
				{
					$code = explode(' ', $data->field2);
					$course = new stdClass();
					$course->deleted = 0;
					$course->code = trim($code[0]);
					$course->course_num = trim($code[1]);
					$course->course_code = $course->code . ' ' . $course->course_num;
					$course->course_name = $data->field3;
					$course->default_credit = $data->field4;
					$course->description = '';
					$course->idnumber = $data->field1;
					$course->eff_status = 'A';
					$course->eff_date = $data->field9;
					$course->institute = 'HIEI'; //temporary so in case anything wrong we can remove it
					
//					$id = $DB->insert_record('si_course', $course);
//					$x = $DB->get_record('si_course', array('id' => $id));
//					$x->catalogue_id = $id;
//					$DB->update_record('si_course', $x);
					
				}
			}
			else //next encounter, always create
			{
				//try to see if we can retrieve existing record
				$course = $DB->get_record('si_course', array('course_code' => $data->field2, 'institute' => 'HIEI'));
				if($course) //found the course, update the effective date
				{
					$course->id = ''; //remove it to create new
					$course->idnumber = $data->field1; //save the logsis idnumber
					$course->eff_date = $data->field9;
					$course->default_credit = $data->field4;
					$data->field13 = $course;
//					$DB->insert_record('si_course', $course);
				}
				else
				{
					print_object('Something is wrong');
				}
			}
		}
	}
}
else if($action == 7)
{
	$now = time();
	$sql = "select a.id, a.field1, a.field2, a.field3, a.field4, a.field5, b.id as course_id, b.catalogue_id, b.course_code, b.course_name, b.idnumber, b.default_credit from {si_data_import} a inner join {si_course} b on a.field1 = b.idnumber where a.institute = 'HIEI'";
	$info = $DB->get_records_sql($sql);
	$arr = array();
	print_object(count($info));
	foreach($info as $a)
	{
		$data = new stdClass();
		$data->semester = $a->field2;
		$data->course_id = $a->course_id;
		$data->course_code = $a->course_code;
		$data->course_name = $a->course_name;
		if($a->field4 == 'N')
		{
			$data->section = 'LC' . str_pad($a->field3, 2, '0', STR_PAD_LEFT);;
			$data->class_type = 'lecture';
			$data->graded = 'Y';
			$data->main_component = 'Y';
			$data->final_exam = 'Y';
		}
		else
		{
			$data->section = 'LB' . str_pad($a->field3, 2, '0', STR_PAD_LEFT);;
			$data->class_type = 'lab';
			$data->graded = 'N';
			$data->main_component = 'N';
			$data->final_exam = 'N';
		}
		$data->room_type = '';
		$data->capacity = $a->field5;
		$data->capacity_reserve = 3;
		$data->capacity_minimum = 3;
		$data->capacity_enrolled = 0;
		$data->section_status = 'OP';
		$data->enable_user_enrol = 'N';
		$data->teacher_workload_weight = 1;
		$data->lms_course_creation = 'Y';
		//try to get the course component
		$co_com = $DB->get_record('si_course_component', array('course_id' => $data->course_id, 'class_type' => $data->class_type));
		if($co_com)
		{
			$data->room_type = $co_com->room_type;
			$data->contact_hour_week = $co_com->contact_hour_week;
			$data->contact_hour_class = $co_com->contact_hour_class;
		}
		else
		{
			//create the course component
			$co = new stdClass();
			$co->course_id = $data->course_id;
			$co->class_type = $data->class_type;
			$co->default_section_size = 30;
			if($data->class_type == 'lecture')
			{
				$co->room_type = 'classroom';
				$co->final_exam = 'Y';
				$co->main_component = 'Y';
			}
			else
			{
				$co->room_type = 'workshop_area';				
				$co->final_exam = 'N';
				$co->main_component = 'N';
			}			
			$co->contact_hour_week = $a->default_credit;
			$co->contact_hour_class = 1;
			$co->teacher_workload_weight = 1;
			$co->lms_course_creation = 'Y';
			$co->institute = 'HIEI';
			print_object($co);
//			$DB->insert_record('si_course_component', $co);
			
			$data->room_type = '';
			$data->contact_hour_week = $a->default_credit;
			$data->contact_hour_class = 1;
		}
		$data->user_updated = $USER->id;
		$data->date_created = $now;
		$data->date_updated = $now;
		$data->institute = 'HIEI';
//		$DB->insert_record('si_section', $data);
		print_object($data);
	}
	
//	print_object($arr);
}
else if($action == 8)
{
	$now = time();
	//first, get the grade letter array
	$gl = $DB->get_records('si_grade_letter', array('institute' => 'HIEI', 'grade_scheme_id' => '9'));
	$grade = array();
	foreach($gl as $g)
	{
		$grade[$g->grade] = $g->id;
	}
	$grade['P'] = 41; //for P
	$grade['-'] = 0; //for ungraded
	
	//for old grade scheme, 20151 and before
	$gl = $DB->get_records('si_grade_letter', array('institute' => 'HIEI', 'grade_scheme_id' => '7'));
	$grade_old = array();
	foreach($gl as $g)
	{
		$grade_old[$g->grade] = $g->id;
	}
	$grade_old['P'] = 41; //for P
	$grade_old['-'] = 0; //for ungraded
	
	$info = $DB->get_records('si_data_import', array(
		'field15' => '',
	));
	$arr = array();
	$count = 1;
	foreach($info as $a)
	{
		//set the grade
		if($a->field2 <= 20151)
		{
			if(isset($grade_old[$a->field6]))
			{
				$a->field7 = $grade_old[$a->field6];
			}
		}
		else
		{
			if(isset($grade[$a->field6]))
			{
				$a->field7 = $grade[$a->field6];
			}
		}
		//get the student
		$student = $DB->get_record('si_user', array('appid' => $a->field1));
		if($student)
			$a->field9 = $student->id;
			
		//get the section
		$aClass = $DB->get_record('si_section', array('auto_enrol_section_5' => $a->field3)); //for theory
		if($aClass) //found
		{
			$data = new stdClass();
			$data->section_id = $aClass->id;
			$data->semester = $a->field2;
			$data->course_id = $aClass->course_id;
			$data->course_code = $aClass->course_code;
			$data->course_name = $aClass->course_name;
			$data->class_type = $aClass->class_type;
			$data->section = $aClass->section;
			$data->user_id = $student->id;
			$data->appid = $student->appid;
			$data->program = 'ELT';
			$data->course_weight = 1;
			$data->graded = 'Y';
			$data->grade_id = $a->field7;
			$data->grade = $a->field6;
			$data->grade_num = 0;
			$data->grade_date = 0;
			$data->grade_user = $USER->id;
			$data->attempt = 1;
			$data->is_transferred = 'N';
			$data->transfer_id = 0;
			$data->enrollment_method = 'migrate';
			$data->user_updated = $USER->id;
			$data->date_created = $now;
			$data->date_updated = $now;
			$data->institute = 'HIEI';
			$data->action_date = date('d-M-Y, h:i:s', $now);
//			$DB->insert_record('si_section_student', $data);
		}
		
		if($a->field4 != '') //has lab
		{
			//get the section
			$aClass = $DB->get_record('si_section', array('auto_enrol_section_5' => $a->field4)); //for lab
			if($aClass) //found
			{
				$data = new stdClass();
				$data->section_id = $aClass->id;
				$data->semester = $a->field2;
				$data->course_id = $aClass->course_id;
				$data->course_code = $aClass->course_code;
				$data->course_name = $aClass->course_name;
				$data->class_type = $aClass->class_type;
				$data->section = $aClass->section;
				$data->user_id = $student->id;
				$data->appid = $student->appid;
				$data->program = 'ELT';
				$data->course_weight = 1;
				$data->graded = 'N';
				$data->grade_id = 0;
				$data->grade = '';
				$data->grade_num = 0;
				$data->grade_date = 0;
				$data->grade_user = 0;
				$data->attempt = 1;
				$data->is_transferred = 'N';
				$data->transfer_id = 0;
				$data->enrollment_method = 'migrate';
				$data->user_updated = $USER->id;
				$data->date_created = $now;
				$data->date_updated = $now;
				$data->institute = 'HIEI';
				$data->action_date = date('d-M-Y, h:i:s', $now);
//				$DB->insert_record('si_section_student', $data);
			}
		}
		$a->field15 = 1; //flag it to indicate it has been processed
		$DB->update_record('si_data_import', $a);
		$arr[] = $a;
		$count++;
		if($count == 1000)
			break;
	}
	print_object($arr);
}
else if($action == 9)
{
	$now = time();
	$info = $DB->get_records('si_data_import', array(
	));
	$arr = array();
	$count = 1;
	foreach($info as $a)
	{
		//get the student
		$student = $DB->get_record('si_user', array('appid' => $a->field1));
		if($student)
		{
			$a->field14 = $student->id;
			$data = new stdClass();
			$data->user_id = $student->id;
			$data->appid = $student->appid;
			$data->program_id = '7';
			$data->program = 'ELT';
			$data->semester = $a->field2;
			$data->min_credit = 12;
			$data->min_credit_approved = 12;
			$data->max_credit = 20;
			$data->max_credit_approved = 20;

			$data->enable_self_enrollment = 'N';
			if($data>semester == '20191')
			{
				$data->term_status = 'AC';
				$data->term_status_reason = 'activated';
			}
			else
			{
				$data->term_status = 'CM';
				$data->term_status_reason = 'completed';
			}
			$data->academic_level_entry = 0;
			$data->academic_level_exit = 0;
			$data->academic_level_actual = 0;
			$data->sem_credit_registered = $a->field3;
			$data->sem_credit_net = $a->field4;
			$data->sem_credit_passed = $a->field5;
			$data->sem_credit_nogpa = 0;
			$data->sem_point = $a->field6;
			$data->sem_gpa = $a->field7;
			$data->sem_credit_registered_m = 0;
			$data->sem_credit_net_m = 0;
			$data->sem_credit_passed_m = 0;
			$data->sem_credit_nogpa_m = 0;
			$data->sem_point_m = 0;
			$data->sem_gpa_m = 0;
			$data->cum_credit_registered = $a->field9;
			$data->cum_credit_net = $a->field9;
			$data->cum_credit_passed = $a->field10;
			$data->cum_credit_nogpa = 0;
			$data->cum_point = $a->field11;
			$data->cum_gpa = $a->field12;
			$data->cum_credit_registered_m = 0;
			$data->cum_credit_net_m = 0;
			$data->cum_credit_passed_m = 0;
			$data->cum_credit_nogpa_m = 0;
			$data->cum_point_m = 0;
			$data->cum_gpa_m = 0;
			$data->lock_computation = 'Y';
			$data->user_updated = $USER->id;
			$data->date_created = $now;
			$data->date_updated = $now;
			$data->institute = 'HIEI';
			$arr[] = $data;
		}
	}
//	$DB->insert_records('si_student_term', $arr);
	print_object($arr);
}
else if($action == 10)
{
	$now = time();
	$info = $DB->get_records('si_data_import', array(
	));
	$day_index_list = sis_timetable_day_index_list();
	$arr = array();
	$count = 1;
	foreach($info as $a)
	{
		//get the section
		$section = $DB->get_record('si_section', array('auto_enrol_section_5' => $a->field4));
		if($section)
		{
			$a->field10 = sis_timetable_oracle_time($a->field2);
			$a->field11 = sis_timetable_oracle_time($a->field3);
			$a->field12 = sis_timetable_format_formal_time($a->field10);
			$a->field13 = sis_timetable_format_formal_time($a->field11);
			$a->field9 = sis_timetable_get_class_duration($a->field10, $a->field11) * 60;
			$a->field14 = sis_timetable_to_tplus_time($a->field12);
			$a->field15 = sis_timetable_to_tplus_time($a->field13);
			$data = new stdClass();
			$data->semester = $a->field5;
			$data->course_id = $section->course_id;
			$data->course_code = $section->course_code;
			$data->course_name = $section->course_name;
			$data->section = $section->section;
			$data->section_code = '';
			$data->class_type = $section->class_type;
			$data->class_num = 1;
			$data->capacity = $section->capacity;
			$data->room_type = $section->room_type;
			$data->section_color = 0;
			$data->alias = '';
			if($a->field8 != '') //has lecturer
			{
				$user = $DB->get_record('si_user', array('appid' => $a->field8, 'institute' => 'HIEI'));
				if($user)
				{
					$data->user_id = $user->id;
					$data->appid = $user->appid;
					$data->lecturer_code = $user->appid;
					$data->lecturer_name = sis_output_show_user_name($user, false, false, false);
					$data->lecturer_id = $user->idnumber;
					$data->lecturer_num = 1;
					$data->lecturer_color = 0;
					$data->lecturer_role = 'lecturer';
					$data->lecturer_grade_access = 'Y';
					$data->lecturer_acad_org = '';
				}
				else
				{
					$data->user_id = 0;
					$data->appid = '';
					$data->lecturer_code = '';
					$data->lecturer_name = '';
					$data->lecturer_id = '';
					$data->lecturer_num = 0;
					$data->lecturer_color = 0;
					$data->lecturer_role = '';
					$data->lecturer_grade_access = '';
					$data->lecturer_acad_org = '';
				}
			}
			else
			{
				$data->user_id = 0;
				$data->appid = '';
				$data->lecturer_code = '';
				$data->lecturer_name = '';
				$data->lecturer_id = '';
				$data->lecturer_num = 0;
				$data->lecturer_color = 0;
				$data->lecturer_role = '';
				$data->lecturer_grade_access = '';
				$data->lecturer_acad_org = '';
			}
			$data->batch = '';
			$data->merge_group = '';
			$data->same_time_group = '';
			//get the room
			$room = $DB->get_record('si_room', array('room' => $a->field6, 'building' => $a->field7, 'institute' => 'HIEI'));
			if($room)
			{
				$data->room_group = $room->building;
				$data->room_code = $room->room;
				$data->campus = $room->campus;
				if($data->room_type == '') //no room type, initialize with this room type
				{
					$data->room_type = $room->room_type;
				}
			}
			else
			{
				$data->room_group = '';
				$data->room_code = '';
				$data->campus = '';
			}
			$data->duration = $a->field9;
			$data->day_text = $a->field1;
			$data->day_num = $day_index_list[$a->field1];
			$data->start_time = $a->field14;
			$data->end_time = $a->field15;
			$data->start_time_raw = $a->field12;
			$data->end_time_raw = $a->field13;
			
			$sem = sis_lookup_get_semester($a->field5);
			$data->start_date = $sem->start_date;
			$data->end_date = $sem->end_date;
			$data->idnumber = '';
			$data->acad_org = '';
			$data->institute = 'HIEI';
			$data->user_updated = $USER->id;
			$data->date_updated = $now;
						
			$arr[] = $data;
			$count++;
		}
	}
//	$DB->insert_records('si_section_schedule', $arr);
	print_object($arr);
}
else if($action == 11)
{
	echo '<br />';
	$now = time();
	$join_date = strtotime('1-January-2015');	
	$info = $DB->get_records('si_data_import', array(
	));
	$arr = array();
	foreach($info as $a)
	{
		$user = $DB->get_record('si_user', array('appid' => $a->field1));
		if($user)
		{
			$data = $DB->get_record('si_employee', array('user_id' => $user->id));
			if($data) //has address, update
			{
			}
			else //add
			{
				$data = new stdClass();
				$data->user_id = $user->id;
				$data->join_date = $join_date;
				$data->employment_type = 'permanent';
				$data->employment_category = 'academic';
				$data->appid = $user->appid;
				$data->eff_status = 'A';
				$data->eff_date = sis_earliest_date();
				$data->date_created = $now;
				$data->date_updated = $now;
				$data->institute = 'HIEI';
				$arr[] = $data;
			}
		}
	}
//	$DB->insert_records('si_employee', $arr);
	print_object($arr);
}
else if($action == 12) //migration of attendance. Don't delete
{
	echo '<br />';
	$now = time();
	$info = $DB->get_records('si_data_import', array(
	));
	$arr = array();
	$count = 1;
	$institute = sis_get_institute();
	$day_index = sis_timetable_day_index_list();
	$semester = $DB->get_record('si_semester', array('semester' => '20191', 'institute' => 'HIEI'));
	$semester_timeline = sis_output_semester_timeline_data($semester); //get the week with attendance
	$period = array();
	$tplus_time = sis_timetable_get_time_in_minute('07:00');
	for($i = 1; $i < 18; $i++)
	{
		$start_time = sis_timetable_to_php_time($tplus_time);
		$period[$i] = $start_time;
		$tplus_time = $tplus_time + 60;
	}
	foreach($info as $a)
	{
		$user = $DB->get_record('si_user', array('appid' => $a->field1));
		$section = $DB->get_record('si_section', array('auto_enrol_section_5' => $a->field4));		
		if($user && $section)
		{
			$attend_date = strtotime($a->field5);
			$attend_week = 0;
			foreach($semester_timeline as $st)
			{
				if($attend_date >= $st['start_week'] && $attend_date <= $st['end_week'])
					$attend_week = $st['actual_week'];
			}
			$start_time = $period[$a->field7];
			$start_time_raw = sis_timetable_format_formal_time($start_time);
			$end_time = $period[$a->field7 + 1];
			$end_time_raw = sis_timetable_format_formal_time($end_time);
			$data = new stdClass();
			$data->section_id = $section->id;
			$data->course_id = $section->course_id;
			$data->user_id = $user->id;
			$data->appid = $user->appid;
			$data->semester = $a->field2;
			$data->attend_date_raw = $a->field5;
			$data->attend_date = $attend_date;
			$data->attend_day_text = date('l', $attend_date);
			$data->attend_day_num = $day_index[$data->attend_day_text];
			$data->attend_week = $attend_week;
			$data->attend_start_time = $start_time;
			$data->attend_start_time_raw = $start_time_raw;
			$data->attend_end_time = $end_time;
			$data->attend_end_time_raw = $end_time_raw;
			$data->attend_absence = 'Y';
			$data->weight = 1;
			$data->instructor_id = 0;
			$data->instructor_appid = '';
			$data->recorded_by = $USER->id;
			$data->recorded_time = $now;
			$data->institute = 'HIEI';
			
			$arr[] = $data;
//			sis_user_reset_password($user->id);
		}
		$count++;
//		if($count > 10)
//			break;
	}
//	$DB->insert_records('si_absence', $arr);
	print_object($arr);
}
else if($action == 13)
{
	echo '<br />';
	$now = time();
	$info = $DB->get_records('si_data_import', array(
	));
	$arr = array();
	foreach($info as $a)
	{
		$user = $DB->get_record('si_user', array('appid' => $a->field1));
		if($user)
		{
			$data = new stdClass();
			$data->user_id = $user->id;
			$data->appid = $user->appid;
			
			$room = $DB->get_record('si_room', array('room_num' => $a->field2, 'institute' => 'HIEI'));
			if($room)
			{
				$data->room_id = $room->id;
				$data->room = $room->room;
				$data->building = $room->building;
				$data->status = 'CI';
				$data->action_date = $now;
				$data->action_user = $USER->id;
				$data->date_updated = $now;
				$data->user_updated = $USER->id;
				$data->institute = 'HIEI';
				$data->remark = '';
				$data->room_num = $room->room_num;
				
				$student_status = sis_student_get_active_program($user->id, 'HIEI', false);
				$data->from_date = $student_status->eff_date;
//				$data->student_status = $student_status;
				$arr[] = $data;
			}
		}
	}
//	$DB->insert_records('si_student_dormitory', $arr);
	print_object($arr);
}
else if($action == 14) //function to add 35 minutes to schedule
{
	$schedules = $DB->get_records('si_section_schedule', array('institute' => 'HIEI', 'semester' => '20192'));
	foreach($schedules as $schedule)
	{
		$start_time_raw = $schedule->start_time_raw;
		$x = explode(':', $start_time_raw);
		if(isset($x[1]) && $x[1] == '00')
		{
			$schedule->start_time = $schedule->start_time + 35;
			$schedule->end_time = $schedule->end_time + 35;
			$schedule->start_time_raw = sis_timetable_minute_to_formal_time($schedule->start_time);
			$schedule->end_time_raw = sis_timetable_minute_to_formal_time($schedule->end_time);
			print_object($schedule);
//			$DB->update_record('si_section_schedule', $schedule);
		}
	}
}
else if($action == 15)
{
	echo '<br />';
	$now = time();
	$info = $DB->get_records('si_data_import', array(
	));
	$arr = array();
	foreach($info as $a)
	{
		$data = new stdClass();
		$n_arr = explode(' ', $a->field1);
		$name = '';
		$name_split = array();
		foreach($n_arr as $n)
		{
			if($n == '')
				continue;
			if($name != '')
				$name = $name . ' ';
			$name = $name . ucfirst($n);
			$name_split[] = ucfirst($n);
		}
		$data->fullname = $name;
		$data->national_id = $a->field2;
		$data->first_name = $name_split[0];
		$data->father_name = $name_split[1];
		$data->grandfather_name = $name_split[2];
		$data->family_name = $name_split[3];
		$data->qudrat = $a->field3;
		$data->tahseeli = $a->field4;
		$data->secondary_mark = $a->field5;
		$data->secondary_average = $a->field6;
		$data->mobile_phone . '0' . $a->field7;
		
		$data->title = 'Mr';
		$data->gender = 'M';
		$data->nationality = 'SA';
		$data->batch = '20192';
		$data->date_created = $now;
		$data->date_updated = $now;
		$data->status = 'pending';
		$data->status_date = $now;
		$data->institute = 'HIEI';
		$arr[] = $data;
	}
//	$DB->insert_records('si_admission', $arr);
	print_object($arr);
}
else if($action == 16)
{
	echo '<br />';
	$now = time();
	$info = $DB->get_records('si_data_import', array(
	));
	$arr = array();
	foreach($info as $a)
	{
		$stud = $DB->get_record('si_user', array('national_id' => $a->field2));
		if($stud)
		{
			$rec = $DB->get_record('si_personal_contact', array('user_id' => $stud->id));
			if(!$rec)
			{
				$data = new stdClass();
				$data->user_id = $stud->id;
				$data->address_type = 'primary';
				$data->address1 = '';
				$data->address2 = '';
				$data->phone_home = $a->field17;
				$data->phone_mobile = $a->field17;
				$data->email_primary = '';
				$data->email_secondary = '';
				
				$data->date_created = $now;
				$data->date_updated = $now;
				$data->institute = 'HIEI';
				print_object('new');
			}
			else
			{
				$data = new stdClass();
				$data->id = $rec->id;
				$data->phone_home = '0' . $a->field7;
				$data->phone_mobile = '0' . $a->field7;
//				$DB->update_record('si_personal_contact', $data);
				print_object($data);
			}
		}
	}
}
else if($action == 17) //refactor the moodle course
{
	$courses = $DB->get_records('course');
	foreach($courses as $course)
	{
		if($course->idnumber != '')
		{
			$arr = explode(' : ', $course->shortname);
			print_object($arr);
			print_object($course->shortname . ' ' . $course->idnumber);
			$course_code = trim($arr[0]);
			$course->shortname = $course_code;
			$course->idnumber = $course_code;
//			$DB->update_record('course', $course);
		}
	}
}
echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/admin/bulk_action/bulk_action.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();