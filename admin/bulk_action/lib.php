<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Certificate module internal API,
 * this is in separate file to reduce memory use on non-certificate pages.
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//call this function to initialize system lookup values
function sis_admin_bulk_action_lookup()
{
	sis_admin_bulk_action_update_lookup('academic', 'employee', 'category', 1);
	sis_admin_bulk_action_update_lookup('non-academic', 'employee', 'category', 1);
	sis_admin_bulk_action_update_lookup('permanent', 'employee', 'employment_type', 1);
	sis_admin_bulk_action_update_lookup('contract', 'employee', 'employment_type', 2);
	sis_admin_bulk_action_update_lookup('part_time', 'employee', 'employment_type', 3);
	sis_admin_bulk_action_update_lookup('academic', 'employee', 'employment_category', 1);
	sis_admin_bulk_action_update_lookup('non_academic', 'employee', 'employment_category', 1);


	sis_admin_bulk_action_update_lookup('regular', 'student', 'category', 1);
	sis_admin_bulk_action_update_lookup('parallel', 'student', 'category', 2);
	sis_admin_bulk_action_update_lookup('regular', 'student', 'student_type', 1);
	sis_admin_bulk_action_update_lookup('parallel', 'student', 'student_type', 2);
	sis_admin_bulk_action_update_lookup('crtp', 'student', 'student_type', 3);
	sis_admin_bulk_action_update_lookup('full_time', 'student', 'student_category', 1);
	sis_admin_bulk_action_update_lookup('part_time', 'student', 'student_category', 2);
	
	sis_admin_bulk_action_update_lookup('Islam', 'personal_info', 'religion', 1);
	sis_admin_bulk_action_update_lookup('Others', 'personal_info', 'religion', 1);
	sis_admin_bulk_action_update_lookup('saudi_national_id', 'personal_info', 'id_type', 1);
	sis_admin_bulk_action_update_lookup('iqamah', 'personal_info', 'id_type', 2);
	sis_admin_bulk_action_update_lookup('primary', 'personal_info', 'address_type', 1);
	sis_admin_bulk_action_update_lookup('secondary', 'personal_info', 'address_type', 2);
	sis_admin_bulk_action_update_lookup('parent', 'personal_info', 'address_type', 3);
	sis_admin_bulk_action_update_lookup('guardian', 'personal_info', 'address_type', 4);
	sis_admin_bulk_action_update_lookup('next_of_kin', 'personal_info', 'address_type', 5);

	sis_admin_bulk_action_update_lookup('public', 'admission', 'admission_type', 1);
	sis_admin_bulk_action_update_lookup('private', 'admission', 'admission_type', 2);
	
	sis_admin_bulk_action_update_lookup('regular', 'finance', 'financing_source', 1);
	sis_admin_bulk_action_update_lookup('parallel', 'finance', 'financing_source', 2);

	sis_admin_bulk_action_update_lookup('AC', 'enrollment', 'term_status', 1, 'activated');
	sis_admin_bulk_action_update_lookup('CM', 'enrollment', 'term_status', 2, 'completed');
	sis_admin_bulk_action_update_lookup('SU', 'enrollment', 'term_status', 3, 'suspended');

	sis_admin_bulk_action_update_lookup('OP', 'enrollment', 'section_status', 1, 'open');
	sis_admin_bulk_action_update_lookup('CO', 'enrollment', 'section_status', 2, 'closed');
	sis_admin_bulk_action_update_lookup('HS', 'enrollment', 'section_status', 3, 'hide_student');
	sis_admin_bulk_action_update_lookup('HA', 'enrollment', 'section_status', 4, 'hide_all');

	sis_admin_bulk_action_update_lookup('earning', 'payroll', 'item_category', 1);
	sis_admin_bulk_action_update_lookup('deduction', 'payroll', 'item_category', 2);
	sis_admin_bulk_action_update_lookup('user', 'payroll', 'item_source', 1);
	sis_admin_bulk_action_update_lookup('system', 'payroll', 'item_source', 2);
	sis_admin_bulk_action_update_lookup('all', 'payroll', 'apply_to', 1);
	sis_admin_bulk_action_update_lookup('individual', 'payroll', 'apply_to', 2);
	sis_admin_bulk_action_update_lookup('system', 'payroll', 'apply_to', 3);
	sis_admin_bulk_action_update_lookup('system', 'payroll', 'record_type', 1);
	sis_admin_bulk_action_update_lookup('manual', 'payroll', 'record_type', 2);
	sis_admin_bulk_action_update_lookup('arrears', 'payroll', 'record_type', 3);
	sis_admin_bulk_action_update_lookup('open', 'payroll', 'status', 1);
	sis_admin_bulk_action_update_lookup('closed', 'payroll', 'status', 2);

	sis_admin_bulk_action_update_lookup('department', 'organization', 'type', 1);
	sis_admin_bulk_action_update_lookup('school', 'organization', 'type', 2);
	sis_admin_bulk_action_update_lookup('faculty', 'organization', 'type', 3);

	sis_admin_bulk_action_update_lookup('lecture', 'course', 'class_type', 1);
	sis_admin_bulk_action_update_lookup('lab', 'course', 'class_type', 2);

	sis_admin_bulk_action_update_lookup('core', 'course', 'course_type', 1);
	sis_admin_bulk_action_update_lookup('major', 'course', 'course_type', 2);
	sis_admin_bulk_action_update_lookup('elective', 'course', 'course_type', 3);
	sis_admin_bulk_action_update_lookup('preparatory', 'course', 'course_type', 4);

	sis_admin_bulk_action_update_lookup('AC', 'academic_plan', 'plan_status', 1, 'activate');
	sis_admin_bulk_action_update_lookup('CM', 'academic_plan', 'plan_status', 2, 'completed');

	sis_admin_bulk_action_update_lookup('classroom', 'facility', 'usage', 1);
	sis_admin_bulk_action_update_lookup('dormitory', 'facility', 'usage', 2);
	sis_admin_bulk_action_update_lookup('office', 'facility', 'usage', 3);
	sis_admin_bulk_action_update_lookup('others', 'facility', 'usage', 4);

	sis_admin_bulk_action_update_lookup('lecturer', 'section', 'lecturer_role', 1);
	sis_admin_bulk_action_update_lookup('assistant_lecturer', 'section', 'lecturer_role', 2);
	sis_admin_bulk_action_update_lookup('coordinator', 'section', 'lecturer_role', 3);
	sis_admin_bulk_action_update_lookup('temporary_lecturer', 'section', 'lecturer_role', 4);

	sis_admin_bulk_action_update_lookup('sick_leave', 'attendance', 'excuse_type', 1);
	sis_admin_bulk_action_update_lookup('death_of_family', 'attendance', 'excuse_type', 2);
	sis_admin_bulk_action_update_lookup('accompany_family', 'attendance', 'excuse_type', 3);
	sis_admin_bulk_action_update_lookup('government_matter', 'attendance', 'excuse_type', 4);
	sis_admin_bulk_action_update_lookup('others', 'attendance', 'excuse_type', 5);


	sis_admin_bulk_action_update_lookup('issue_of_dn', 'grade', 'change_grade_reason', 1);
	sis_admin_bulk_action_update_lookup('withdrawal', 'grade', 'change_grade_reason', 2);
	sis_admin_bulk_action_update_lookup('incomplete', 'grade', 'change_grade_reason', 3);
	sis_admin_bulk_action_update_lookup('grade_correction', 'grade', 'change_grade_reason', 4);

	sis_admin_bulk_action_update_lookup('absent', 'grade', 'comment', 1);
	sis_admin_bulk_action_update_lookup('incomplete', 'grade', 'comment', 2);
	sis_admin_bulk_action_update_lookup('makeup_exam', 'grade', 'comment', 3);
	sis_admin_bulk_action_update_lookup('cheating', 'grade', 'comment', 4);
	sis_admin_bulk_action_update_lookup('others', 'grade', 'comment', 5);

	sis_admin_bulk_action_update_lookup('OH', 'timetable', 'nsch', 1, 'office_hour');
	sis_admin_bulk_action_update_lookup('NS', 'timetable', 'nsch', 2, 'non_schedule');
	sis_admin_bulk_action_update_lookup('CH', 'timetable', 'nsch', 3, 'compensation_hour');
	sis_admin_bulk_action_update_lookup('AH', 'timetable', 'nsch', 4, 'activity_hour');
	sis_admin_bulk_action_update_lookup('FH', 'timetable', 'nsch', 5, 'fixed_hour');


	//this is for student_program_status. It is not in lookup table, but in si_program_status
	sis_bulk_action_program_status();
	sis_bulk_action_bank();
}

function sis_admin_bulk_action_update_lookup($value, $category, $subcategory, $sort_order = 1, $description = '')
{		
	$institute = sis_get_institute();
	$lang = 'en';
	//one row
	if(!si_lookup_duplicate($value, $lang, $category, $subcategory, $institute)) //not exist, add it
	{
		si_lookup_insert($value, $lang, $category, $subcategory, $institute, $sort_order, $description);
		print_object("INSERTED $value into category ($category) and subcategory ($subcategory)");
	}
	//end of one row
}

function sis_bulk_action_bank()
{
	$institute = sis_get_institute();
	//one row
	$data = new stdClass();
	$data->bank_code = 'ALBI';
	$data->swift_code = '';
	$data->iban_code = '';
	$data->bank_name = 'BANK AL BILAD';
	$data->bank_name_a = 'بنك البلاد';
	$data->eff_status = 'A';
	$data->sort_order = 1;
	$data->institute = $institute;
	sis_bulk_action_bank_create($data);
}

function sis_bulk_action_bank_create($data)
{
	global $DB;
	//first, check if it exist
	$rec = $DB->get_record('si_bank', array('bank_code' => $data->bank_code, 'institute' => $data->institute));
	if($rec) //exist, update
	{
		$rec->swift_code = $data->swift_code;
		$rec->iban_code = $data->iban_code;
		$rec->bank_name = $data->bank_name;
		$rec->bank_name_a = $data->bank_name_a;
		$rec->eff_status = $data->eff_status;
		$rec->sort_order = $data->sort_order;
		$DB->update_record('si_bank', $rec);
	}
	else
	{
		$DB->insert_record('si_bank', $data);
		print_object("INSERTED bank, $data->bank_code - $data->bank_name ($data->bank_name_a)");
	}
}
function sis_bulk_action_program_status()
{
	$institute = sis_get_institute();
	//one row
	$data = new stdClass();
	$data->program_status = 'AC';
	$data->program_action = 'activate';
	$data->description = 'activated_from_inactive_status';
	$data->institute = $institute;
	$data->sort_order = 1;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'AC';
	$data->program_action = 'matriculation';
	$data->description = 'matriculated_as_new_student';
	$data->institute = $institute;
	$data->sort_order = 2;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'AC';
	$data->program_action = 'readmission';
	$data->description = 'readmission_from_dismissal';
	$data->institute = $institute;
	$data->sort_order = 3;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'AC';
	$data->program_action = 'returned';
	$data->description = 'returned_from_leave';
	$data->institute = $institute;
	$data->sort_order = 4;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'CM';
	$data->program_action = 'completed';
	$data->description = 'completed_study';
	$data->institute = $institute;
	$data->sort_order = 10;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'DM';
	$data->program_action = 'academic_failure';
	$data->description = 'dismissed_due_to_failure';
	$data->institute = $institute;
	$data->sort_order = 20;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'DM';
	$data->program_action = 'absenteeism';
	$data->description = 'dismissed_due_to_absenteeism';
	$data->institute = $institute;
	$data->sort_order = 21;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'DM';
	$data->program_action = 'no_show';
	$data->description = 'dismissed_due_to_no_show';
	$data->institute = $institute;
	$data->sort_order = 22;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'DM';
	$data->program_action = 'semester_withdrawal';
	$data->description = 'semester_withdrawal_personal_reason';
	$data->institute = $institute;
	$data->sort_order = 23;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'DM';
	$data->program_action = 'college_withdrawal';
	$data->description = 'permanent_withdrawal_from_college';
	$data->institute = $institute;
	$data->sort_order = 24;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'DM';
	$data->program_action = 'expulsion';
	$data->description = 'dismissed_due_to_expulsion';
	$data->institute = $institute;
	$data->sort_order = 25;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'DM';
	$data->program_action = 'deceased';
	$data->description = 'dismissed_due_to_decease';
	$data->institute = $institute;
	$data->sort_order = 26;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'LA';
	$data->program_action = 'leave';
	$data->description = 'study_leave';
	$data->institute = $institute;
	$data->sort_order = 30;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'LA';
	$data->program_action = 'medical';
	$data->description = 'medical_leave';
	$data->institute = $institute;
	$data->sort_order = 31;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'LA';
	$data->program_action = 'matenity';
	$data->description = 'matenity_leave';
	$data->institute = $institute;
	$data->sort_order = 32;
	sis_bulk_action_program_status_create($data);
	//end of one row
	//one row
	$data = new stdClass();
	$data->program_status = 'SU';
	$data->program_action = 'suspension';
	$data->description = 'academic_suspension';
	$data->institute = $institute;
	$data->sort_order = 40;
	sis_bulk_action_program_status_create($data);
	//end of one row

}

function sis_bulk_action_program_status_create($data)
{
	global $DB;
	//first, check if it exist
	$rec = $DB->get_record('si_program_status', array('program_status' => $data->program_status, 'program_action' => $data->program_action, 'institute' => $data->institute));
	if($rec) //exist, update
	{
		$rec->description = $data->description; //update the description
		$rec->sort_order = $data->sort_order;
		$DB->update_record('si_program_status', $rec);
	}
	else
	{
		$DB->insert_record('si_program_status', $data);
		print_object("INSERTED program_status, $data->program_status ($data->program_action)");
	}
}