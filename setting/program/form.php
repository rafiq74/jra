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
 * Change password form definition.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once '../../lib/sis_lookup_lib.php';
require_once '../../classes/course.php'; 
require_once $CFG->libdir.'/formslib.php';

class program_form extends moodleform 
{
	//Add elements to form
	public function definition() 
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', sis_get_institute());	
		
//		$mform->addElement('header', 'headergradetemplate', get_string('program', 'local_sis'));
		$mform->addElement('date_selector', 'eff_date', get_string('effective_date', 'local_sis'));
		$mform->addElement('checkbox', 'default_date', get_string('set_to_1900', 'local_sis'));		
        $mform->addElement('text', 'program', get_string('program_code', 'local_sis'), array('size' => 30));
//		$mform->addRule('program', get_string('program_code', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', 'client', false, false);
//		$mform->addRule('program', get_string('no_space_input', 'local_sis'), 'regex', '/^[a-zA-Z0-9-_]*$/', 'client', false, false);
		$mform->addElement('text', 'program_name', get_string('program_name', 'local_sis'), array('size' => 50));
//		$mform->addRule('program_name', get_string('program_name', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', 'client', false, false);
		$mform->addElement('text', 'program_name_a', get_string('program_name', 'local_sis') . ' (' . get_string('arabic', 'local_sis') .')', array('size' => 50)); 
	    $academic_career = sis_lookup_academic_career();         
	    $mform->addElement('select', 'academic_career', 'Academic Career',$academic_career, $attributes);	
	    $grading_scheme = sis_lookup_grade_scheme();         
        $mform->addElement('select', 'grading_scheme', 'Grading Scheme',$grading_scheme, $attributes);		
        $organization = sis_lookup_organization();
        $mform->addElement('select', 'organization', get_string('organization', 'local_sis'), $organization ,$attributes);
        $campus = sis_lookup_campus();
        $mform->addElement('select', 'campus', get_string('campus', 'local_sis'), $campus ,$attributes);
		$mform->addElement('text', 'probation_cgpa_prep', sis_get_string(['probation_cgpa', 'prep']), array('size' => 10)); 
		$mform->addElement('text', 'dismissed_cgpa_prep', sis_get_string(['dismissed_cgpa', 'prep']), array('size' => 10)); 
		$mform->addElement('text', 'probation_cgpa', get_string('probation_cgpa', 'local_sis'), array('size' => 10)); 
		$mform->addElement('text', 'dismissed_cgpa', get_string('dismissed_cgpa', 'local_sis'), array('size' => 10)); 
		$num_list = sis_lookup_get_num_list(1, 30);
        $mform->addElement('select', 'max_semester_prep', sis_get_string(['maximum', 'semester', 'preparatory']), $num_list ,$attributes);
        $mform->addElement('select', 'max_semester', sis_get_string(['maximum', 'semester', 'program']), $num_list ,$attributes);
		
		$num_list = sis_lookup_get_num_list(0, 10);
        $mform->addElement('select', 'elective_required', sis_get_string(['minimum', 'elective', 'courses', 'required']), $num_list ,$attributes);
		$mform->addElement('text', 'min_credit_grad', get_string('min_credit_grad', 'local_sis'), array('size' => 10)); 
//		$mform->addRule('min_credit_grad', get_string('min_credit_grad', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', 'client', false, false);
//		$mform->addRule('min_credit_grad', get_string('min_credit_grad', 'local_sis') . ' ' . get_string('must_be_integer', 'local_sis'), 'numeric', 'client', false, false);

		$mform->addElement('text', 'default_sem_min_credit', get_string('default_sem_min_credit', 'local_sis'), array('size' => 10)); 
//		$mform->addRule('default_sem_min_credit', get_string('default_sem_min_credit', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', 'client', false, false);
//		$mform->addRule('default_sem_min_credit', get_string('default_sem_min_credit', 'local_sis') . ' ' . get_string('must_be_integer', 'local_sis'), 'numeric', 'client', false, false);

		$mform->addElement('text', 'default_sem_max_credit', get_string('default_sem_max_credit', 'local_sis'), array('size' => 10)); 
//		$mform->addRule('default_sem_max_credit', get_string('default_sem_max_credit', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', 'client', false, false);
//		$mform->addRule('default_sem_max_credit', get_string('default_sem_max_credit', 'local_sis') . ' ' . get_string('must_be_integer', 'local_sis'), 'numeric', 'client', false, false);

        $eff_status = sis_lookup_isactive();
        $mform->addElement('select', 'eff_status', 'Status', $eff_status, $attributes);
	    $mform->addElement('textarea', 'description', get_string('description', 'local_sis'),'wrap="virtual" rows="5" cols="80"');
		
		$mform->addElement('checkbox', 'correct_history', get_string('correct_history', 'local_sis'), false,
			array('onClick' => '
				if($(this).is(":checked"))
					$("#id_submitbutton").prop( "disabled", false);
				else
					$("#id_submitbutton").prop( "disabled", true);				
			'));		
		
		$mform->disabledIf('submitbutton', 'id', 'neq', '');
		$mform->disabledIf('program', 'id', 'neq', '');
		$mform->hideIf('correct_history', 'id', 'eq', '');
		$mform->hideIf('default_date', 'id', 'neq', '');

		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'), array(
			'onclick' => '
				document.mform1.id_code.value = "x";
				document.mform1.id_course_num.value = "x";
				document.mform1.id_course_name.value = "x";
				document.mform1.is_cancel.value = 1;
			'
		));
		$buttonarray[] =& $mform->createElement('submit', 'saveasbutton', get_string('saveas', 'local_sis'));
		$mform->hideIf('saveasbutton', 'id', 'eq', '');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
//		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) 
	{
		$errors = array();
		if(!isset($data['cancel'])) //not cancel, validate
		{
	        $errors = parent::validation($data, $files);
			if(isset($data['program']))
			{
				if($data['program'] == '')
					$errors['program'] = get_string('program', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
				else if(strpos($data['program'], ' ') !== false)
				{
					$errors['program'] = get_string('program', 'local_sis') . ' ' . get_string('no_space_input', 'local_sis');
				}
			}
			if($data['program_name'] == '')
	            $errors['program_name'] = get_string('program_name', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
			if($data['probation_cgpa_prep'] == '')
	            $errors['probation_cgpa_prep'] = sis_get_string(['probation_cgpa', 'preparatory']) . ' ' . get_string('cannot_empty', 'local_sis');
			else if(!preg_match('/^\d*\.?\d*$/', $data['probation_cgpa_prep']))
			{
	            $errors['probation_cgpa_prep'] = sis_get_string(['probation_cgpa', 'preparatory']) . ' ' . get_string('must_be_number', 'local_sis');
			}
			if($data['dismissed_cgpa_prep'] == '')
	            $errors['dismissed_cgpa_prep'] = sis_get_string(['dismissed_cgpa', 'preparatory']) . ' ' . get_string('cannot_empty', 'local_sis');
			else if(!preg_match('/^\d*\.?\d*$/', $data['dismissed_cgpa_prep']))
			{
	            $errors['dismissed_cgpa_prep'] = sis_get_string(['dismissed_cgpa', 'preparatory']) . ' ' . get_string('must_be_number', 'local_sis');
			}
			if($data['probation_cgpa'] == '')
	            $errors['probation_cgpa'] = get_string('probation_cgpa', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
			else if(!preg_match('/^\d*\.?\d*$/', $data['probation_cgpa']))
			{
	            $errors['probation_cgpa'] = get_string('probation_cgpa', 'local_sis') . ' ' . get_string('must_be_number', 'local_sis');
			}
			if($data['dismissed_cgpa'] == '')
	            $errors['dismissed_cgpa'] = get_string('dismissed_cgpa', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
			else if(!preg_match('/^\d*\.?\d*$/', $data['dismissed_cgpa']))
			{
	            $errors['dismissed_cgpa'] = get_string('dismissed_cgpa', 'local_sis') . ' ' . get_string('must_be_number', 'local_sis');
			}
			if($data['min_credit_grad'] == '')
	            $errors['min_credit_grad'] = get_string('min_credit_grad', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
			else if(!preg_match('/^\d+$/', $data['min_credit_grad']))
			{
	            $errors['min_credit_grad'] = get_string('min_credit_grad', 'local_sis') . ' ' . get_string('must_be_number', 'local_sis');
			}
			if($data['min_credit_grad'] == '')
	            $errors['min_credit_grad'] = get_string('min_credit_grad', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
			else if(!preg_match('/^\d+$/', $data['min_credit_grad']))
			{
	            $errors['min_credit_grad'] = get_string('min_credit_grad', 'local_sis') . ' ' . get_string('must_be_number', 'local_sis');
			}
			if($data['default_sem_min_credit'] == '')
	            $errors['default_sem_min_credit'] = get_string('default_sem_min_credit', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
			else if(!preg_match('/^\d+$/', $data['default_sem_min_credit']))
			{
	            $errors['default_sem_min_credit'] = get_string('default_sem_min_credit', 'local_sis') . ' ' . get_string('must_be_number', 'local_sis');
			}
			if($data['default_sem_max_credit'] == '')
	            $errors['default_sem_max_credit'] = get_string('default_sem_max_credit', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
			else if(!preg_match('/^\d+$/', $data['default_sem_max_credit']))
			{
	            $errors['default_sem_max_credit'] = get_string('default_sem_max_credit', 'local_sis') . ' ' . get_string('must_be_number', 'local_sis');
			}
		}
		return $errors;
	}
}

class plan_form extends moodleform 
{
	//Add elements to form
	public function definition() 
	{
		global $DB ;
		$mform = $this->_form; // Don't forget the underscore! 
		$attributes = array();
		$program_id = $this->_customdata['pid'];
		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'pid', $program_id);
		$mform->addElement('hidden', 'institute', sis_get_institute());	
		$program = $DB->get_record('si_program', array('id' => $program_id));
		if(!$program)
			throw new moodle_exception('Wrong parameters.');
		
		$mform->addElement('hidden', 'program_id', '');		
		
		$mform->addElement('static', 'program', get_string('program', 'local_sis'), $program->program . ' - ' . $program->program_name);
//		$mform->addElement('header', 'headergradetemplate', get_string('plan', 'local_sis'));
		$mform->addElement('date_selector', 'eff_date', get_string('effective_date', 'local_sis'));
		$mform->addElement('checkbox', 'default_date', get_string('set_to_1900', 'local_sis'));		
		$mform->addElement('text', 'plan', get_string('plan', 'local_sis'), array('size' => 20));
//		$mform->addRule('plan', get_string('plan', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
//		$mform->addRule('plan', get_string('no_space_input', 'local_sis'), 'regex', '/^[a-zA-Z0-9-_]*$/', 'client', false, false);
		$mform->addElement('text', 'plan_name', get_string('plan_name', 'local_sis'), array('size' => 50));
//		$mform->addRule('plan_name', get_string('plan_name', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'plan_name_a', get_string('plan_name', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')', array('size' => 50));
		$plan_rule = sis_lookup_plan_rule();
		$mform->addElement('select', 'plan_rule', get_string('plan_rules', 'local_sis'), $plan_rule, $attributes);
		$num_list = sis_lookup_get_num_list(1, 10);
		$mform->addElement('select', 'plan_sequence', get_string('plan_sequence', 'local_sis'), $num_list, $attributes);
		$yesno = sis_lookup_yes_no();
		$mform->addElement('select', 'default_plan', get_string('default_plan', 'local_sis'), $yesno, $attributes);
		$mform->setDefault('default_plan', 'N');
		$eff_status = sis_lookup_isactive();
		$mform->addElement('select', 'eff_status', 'Status', $eff_status, $attributes);
		$mform->addElement('textarea', 'description','Description','wrap="virtual" rows="5" cols="80"');
		
		$mform->addElement('checkbox', 'correct_history', get_string('correct_history', 'local_sis'), false,
			array('onClick' => '
				if($(this).is(":checked"))
					$("#id_submitbutton").prop( "disabled", false);
				else
					$("#id_submitbutton").prop( "disabled", true);				
			'));		
		
		$mform->disabledIf('submitbutton', 'id', 'neq', '');
		$mform->disabledIf('plan', 'id', 'neq', '');
		$mform->hideIf('correct_history', 'id', 'eq', '');
		$mform->hideIf('default_date', 'id', 'neq', '');

		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'), array(
			'onclick' => '
				document.mform1.id_code.value = "x";
				document.mform1.id_course_num.value = "x";
				document.mform1.id_course_name.value = "x";
				document.mform1.is_cancel.value = 1;
			'
		));
		$buttonarray[] =& $mform->createElement('submit', 'saveasbutton', get_string('saveas', 'local_sis'));
		$mform->hideIf('saveasbutton', 'id', 'eq', '');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		
//		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files)
	{
		$error = array();
		if(!isset($data['cancel'])) //not cancel, validate
		{
	        $errors = parent::validation($data, $files);
			if(isset($data['plan']))
			{
				if($data['plan'] == '')
					$errors['plan'] = get_string('plan', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
				else if(strpos($data['plan'], ' ') !== false)
				{
					$errors['plan'] = get_string('plan', 'local_sis') . ' ' . get_string('no_space_input', 'local_sis');
				}
			}
			if($data['plan_name'] == '')
	            $errors['plan_name'] = get_string('plan_name', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
		}
		return $errors;
	}
}

class plan_course_form extends moodleform 
{

	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
		$plan_id = $this->_customdata['pid'];
		$attributes = array();
		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'pid', $plan_id);	
		$mform->addElement('hidden', 'institute', sis_get_institute());	
		
		$mform->addElement('date_selector', 'eff_date', get_string('effective_date', 'local_sis'));
		$mform->addElement('checkbox', 'default_date', get_string('set_to_1900', 'local_sis'));		

		$yesno = sis_lookup_yes_no();

		$add_array=array();
		$course_type = sis_lookup_get_list('course', 'course_type');
		$add_array[] = $mform->createElement('select', 'course_type', get_string('course_type', 'local_sis'), $course_type, $attributes);
		$add_array[] =& $mform->createElement('select', 'probation_fail', get_string('probation_if_fail', 'local_sis'), $yesno, $attributes);		
		$mform->addGroup($add_array, 'group4', get_string('course_type', 'local_sis'), array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . get_string('probation_if_fail', 'local_sis') . '&nbsp;&nbsp;&nbsp;'), false);

		$add_array=array();
		$num_list = sis_lookup_get_num_list(1, 20);
		$add_array[] =& $mform->createElement('select', 'course_level', get_string('level', 'local_sis'), $num_list, $attributes);
		$num_list = sis_lookup_get_num_list(1, 12);
		$add_array[] =& $mform->createElement('select', 'credit', get_string('credit', 'local_sis'), $num_list, $attributes);		
		$mform->addGroup($add_array, 'group1', get_string('level', 'local_sis'), array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . get_string('credit', 'local_sis') . '&nbsp;&nbsp;&nbsp;'), false);


		$add_array=array();
		$add_array[] =& $mform->createElement('select', 'compulsory', get_string('compulsory', 'local_sis'), $yesno, $attributes);
		$add_array[] =& $mform->createElement('select', 'must_pass', get_string('must_pass', 'local_sis'), $yesno, $attributes);		
		$mform->addGroup($add_array, 'group2', get_string('compulsory', 'local_sis'), array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . get_string('must_pass', 'local_sis') . '&nbsp;&nbsp;&nbsp;'), false);


		$add_array=array();
		$add_array[] =& $mform->createElement('select', 'in_cgpa', get_string('in_cgpa', 'local_sis'), $yesno, $attributes);
		$eff_status = sis_lookup_isactive();
		$add_array[] =& $mform->createElement('select', 'eff_status', get_string('status', 'local_sis'), $eff_status, $attributes);		
		$mform->addGroup($add_array, 'group3', get_string('in_cgpa', 'local_sis'), array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . get_string('status', 'local_sis') . '&nbsp;&nbsp;&nbsp;'), false);
		
		$mform->addElement('checkbox', 'correct_history', get_string('correct_history', 'local_sis'), false,
			array('onClick' => '
				if($(this).is(":checked"))
					$("#id_submitbutton").prop( "disabled", false);
				else
					$("#id_submitbutton").prop( "disabled", true);				
			'));		
		
		$mform->disabledIf('submitbutton', 'id', 'neq', '');
		$mform->disabledIf('program', 'id', 'neq', '');
		$mform->hideIf('correct_history', 'id', 'eq', '');
		$mform->hideIf('default_date', 'id', 'neq', '');

		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'), array(
			'onclick' => '
				document.mform1.id_code.value = "x";
				document.mform1.id_course_num.value = "x";
				document.mform1.id_course_name.value = "x";
				document.mform1.is_cancel.value = 1;
			'
		));
		$buttonarray[] =& $mform->createElement('submit', 'saveasbutton', get_string('saveas', 'local_sis'));
		$mform->hideIf('saveasbutton', 'id', 'eq', '');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		
		//$this->add_action_buttons($cancel=true);		
//		$mform->addElement('submit', 'submitbutton', get_string('add'));
	}
	
	function validation($data, $files)
	{
		return array();
	}
}
    

class academic_career_form extends moodleform 
{
	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
		$attributes = array();
		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', sis_get_institute());	
		$mform->addElement('date_selector', 'eff_date', get_string('effective_date', 'local_sis'));
		$mform->addElement('checkbox', 'default_date', get_string('set_to_1900', 'local_sis'));		
		$mform->addElement('text', 'acad_career', get_string('academic_career', 'local_sis'), array('size' => 25)); 
		$mform->addRule('acad_career', get_string('academic_career', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'name', get_string('name'), array('size' => 40)); 
		$mform->addElement('text', 'name_a', get_string('name') . ' (' . get_string('arabic', 'local_sis') . ')', array('size' => 40));
		$eff_status = sis_lookup_isactive();
		$mform->addElement('select', 'eff_status', get_string('eff_status', 'local_sis'), $eff_status, $attributes);
		$mform->addElement('textarea', 'description','Description','wrap="virtual" rows="5" cols="80"');
		
		$this->add_action_buttons($cancel=true);		
	}
	
	function validation($data, $files)
	{
		return array();
	}
	
}

class program_status_form extends moodleform 
{
	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
		$attributes = array();
		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', sis_get_institute());	
		$mform->addElement('text', 'program_status', get_string('program_status', 'local_sis'), array('size' => 10)); 
		$mform->addRule('program_status', get_string('program_status', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'program_action', get_string('action'), array('size' => 20)); 
		$num_list = sis_lookup_get_num_list(1, 100);
		$mform->addElement('select', 'sort_order', get_string('sort_order', 'local_sis'), $num_list, $attributes);
		
		$mform->addElement('text', 'description', get_string('description'), array('size' => 60));
		
		$this->add_action_buttons($cancel=true);		
	}
	
	function validation($data, $files)
	{
		return array();
	}
	
}

?>
