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
require_once $CFG->libdir.'/formslib.php';


class class_type_form extends moodleform 
{
	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
		$attributes = array();
		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('text', 'value', get_string('class_type', 'local_sis'), array('size' => 30));
		$mform->addRule('value', get_string('class_type', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$lang = sis_lookup_language();		
		$mform->addElement('select', 'lang', get_string('language', 'local_sis'), $lang, $attributes);						
		$this->add_action_buttons($cancel=true);		
	}
	
	function validation($data, $files)
	{
		return array();
	}

}

class course_type_form extends moodleform 
{
	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
		$attributes = array();
		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('text', 'value', get_string('course_type', 'local_sis'), array('size' => 30)); 
		$mform->addRule('value', get_string('course_type', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$lang = sis_lookup_language();		
		$mform->addElement('select', 'lang', get_string('language', 'local_sis'), $lang, $attributes);						
		$this->add_action_buttons($cancel=true);		
	}
	
	function validation($data, $files)
	{
		return array();
	}

}

class course_form extends moodleform 
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
		$mform->addElement('text', 'code', get_string('code', 'local_sis'), array('size' => 15));
//		$mform->addRule('code', get_string('code', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'server', false, false);
		$mform->addElement('text', 'course_num', get_string('num', 'local_sis'), array('size' => 15));
//		$mform->addRule('course_num', get_string('num', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'server', false, false);
		$mform->addElement('text', 'course_name', get_string('name'), array('size' => 40));
//		$mform->addRule('course_name', get_string('name') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'server', false, false);
		$num_list = sis_lookup_get_num_list(1, 12);
		$mform->addElement('select', 'default_credit', get_string('credit', 'local_sis'), $num_list, $attributes);
		$mform->addElement('duration', 'final_exam_duration', sis_get_string(['final_exam', 'duration']));
		$is_active = sis_lookup_isactive();
		$mform->addElement('select', 'eff_status', get_string('status', 'local_sis'), $is_active, $attributes);
		$acad_org = sis_lookup_organization();
		$mform->addElement('select', 'acad_org', get_string('department', 'local_sis'), $acad_org, $attributes);
		
	    $mform->addElement('textarea', 'description', get_string('description', 'local_sis'),'wrap="virtual" rows="5" cols="80"');

		$mform->addElement('checkbox', 'correct_history', get_string('correct_history', 'local_sis'), false,
			array('onClick' => '
				if($(this).is(":checked"))
					$("#id_submitbutton").prop( "disabled", false);
				else
					$("#id_submitbutton").prop( "disabled", true);				
			'));		
		
		$mform->disabledIf('submitbutton', 'id', 'neq', '');
		$mform->hideIf('correct_history', 'id', 'eq', '');

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
	
	function validation($data, $files)
	{
		$error = array();
		
		if(!isset($data['cancel'])) //not cancel, validate
		{
	        $errors = parent::validation($data, $files);
			if($data['code'] == '')
	            $errors['code'] = get_string('code', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
			if($data['course_num'] == '')
	            $errors['course_num'] = get_string('num', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
			if($data['course_name'] == '')
	            $errors['course_name'] = get_string('name', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis');
		}
		return $errors;
	}
}

class component_form extends moodleform 
{
	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
		$id = $this->_customdata['id'];
		$dataid = $this->_customdata['dataid'];
		$attributes = array();
		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'course_id', $this->_customdata['id']);
		$mform->addElement('hidden', 'is_cancel', '');		
		$mform->addElement('hidden', 'dataid', $dataid);
		
		$class_type_list = sis_lookup_get_list('course', 'class_type');
		$room_type_list = sis_lookup_get_list('facility', 'room_type');
		$yesno = sis_lookup_yes_no();


		$mform->addElement('select', 'class_type', get_string('class_type', 'local_sis'), $class_type_list,  $attributes);
		$mform->addElement('select', 'main_component', get_string('main_course_component', 'local_sis'), $yesno, $attributes); // Add elements to your form

		$mform->addElement('select', 'room_type', get_string('room_type', 'local_sis'), $room_type_list,  $attributes);		

		$mform->addElement('text', 'default_section_size',get_string('default_section_size', 'local_sis'), array('size' => 5));
		$mform->setDefault('default_section_size', 30);		
		
		$num_list = sis_lookup_get_num_list(1, 60);		
		$mform->addElement('select', 'contact_hour_week', get_string('contact_hour_week', 'local_sis'), $num_list,  $attributes);		
		
		$num_list = sis_lookup_get_num_list(1, 10);		
		$mform->addElement('select', 'contact_hour_class', get_string('contact_hour_class', 'local_sis'), $num_list,  $attributes);		
		
		$mform->addElement('text', 'teacher_workload_weight', get_string('teacher_workload_weight', 'local_sis'), array('size' => 5)); 
		$mform->setDefault('teacher_workload_weight', 1);
		
		$mform->addElement('select', 'final_exam', get_string('has_final_exam', 'local_sis'), $yesno, $attributes);
		$mform->addElement('select', 'lms_course_creation', get_string('lms_course_creation', 'local_sis'), $yesno, $attributes);

		$mform->addElement('text', 'label', get_string('section', 'local_sis') . ' ' . get_string('label', 'local_sis'), array('size' => 10));

		//$this->add_action_buttons($cancel=true);		
		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'), array('onclick' => 'document.mform1.is_cancel.value = 1'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
	}
	
	function validation($data, $files)
	{
		return array();
	}
}

class equivalent_form extends moodleform 
{
	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
		$id = $this->_customdata['id'];
		$dataid = $this->_customdata['dataid'];
		$attributes = array();
		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'course_id', $this->_customdata['id']);
		$mform->addElement('hidden', 'is_cancel', '');		
		$mform->addElement('hidden', 'dataid', $dataid);
		
		$course_list = sis_lookup_get_course_list(true, true);

		$mform->addElement('select', 'equal_course_id', get_string('equivalent_course', 'local_sis'), $course_list,  $attributes);		
		$mform->addElement('text', 'credit_weight', get_string('credit_weight', 'local_sis'), array('size' => 5)); 
		$mform->setDefault('credit_weight', 1);
		
		//$this->add_action_buttons($cancel=true);		
		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'), array('onclick' => 'document.mform1.is_cancel.value = 1'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
	}
	
	function validation($data, $files)
	{
		return array();
	}

}



?>
