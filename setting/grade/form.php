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
class grade_scheme_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', sis_get_institute());	
		$mform->addElement('hidden', 'is_cancel', '');		
		
		//$mform->addElement('header', 'headergradetemplate', 'Institute');
		$mform->addElement('text', 'grade_scheme', 'Grade Scheme', array('size' => 30)); // Add elements to your form
		$mform->addRule('grade_scheme', 'Grade Scheme Cannot be empty', 'required', '', 'client', false, false);
		$mform->addRule('grade_scheme', get_string('no_space_input', 'local_sis'), 'regex', '/^[a-zA-Z0-9-_]*$/', 'client', false, false);
		$is_active = sis_lookup_isactive();
			
	    $mform->addElement('select', 'eff_status', 'Status', $is_active, $attributes);

//		$this->add_action_buttons($cancel=true);		
		//$this->add_action_buttons($cancel=true);		
		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'), array('onclick' => 'document.mform1.is_cancel.value = 1'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class letter_scheme_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();
		$id = $this->_customdata['id'];
		$dataid = $this->_customdata['dataid'];

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'grade_scheme_id', $this->_customdata['id']);		
		$mform->addElement('hidden', 'is_cancel', '');		
		$mform->addElement('hidden', 'dataid', $dataid);
		$mform->addElement('hidden', 'sort_order', '1');
		
		$mform->addElement('text', 'grade', get_string('grade', 'local_sis'),array('size' => 10));
		$mform->addRule('grade', get_string('grade', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
	    $mform->addElement('text', 'description', get_string('grade_description', 'local_sis'), array('size' => 40));
		$mform->addRule('description', get_string('grade_description', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
	   	$mform->addElement('text', 'grade_point', get_string('grade_point', 'local_sis'), array('size' => 10));
		$mform->addRule('grade_point', get_string('grade_point', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);		
	   	$mform->addElement('text', 'range_from', get_string('range_from', 'local_sis'),array('size' => 10));
		$mform->addRule('range_from', get_string('range_from', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
	   	$mform->addElement('text', 'range_to', get_string('range_to', 'local_sis'),array('size' => 10));
		$mform->addRule('range_to', get_string('range_to', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
	   	$status = sis_lookup_grade_pass_status(); 
	   	$mform->addElement('select', 'status', get_string('grade_pass_status', 'local_sis'), $status);
        $is_enrolled = sis_lookup_yes_no();
	   	$mform->addElement('hidden', 'eff_date','');
	   	$mform->addElement('select', 'is_enrolled', get_string('student_enrolled', 'local_sis'), $is_enrolled); // Add elements to your form
	   	$mform->addElement('select', 'exempted', 'CGPA ' . sis_get_string(['exempted']), $is_enrolled); // Add elements to your form

		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'), array('onclick' => 'document.mform1.is_cancel.value = 1'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class grade_category_form extends moodleform 
{
	//Add elements to form
	public function definition()
	{
		$mform = $this->_form; // Don't forget the underscore! 
		$attributes = array();
		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', sis_get_institute());			
		$class_type_list = sis_lookup_get_list('course', 'class_type');		
		$mform->addElement('select', 'class_type', get_string('class_type', 'local_sis'), $class_type_list, $attributes);
		$mform->addElement('text', 'category', get_string('category', 'local_sis'), array('size' => 60)); 
		$mform->addRule('category', get_string('category', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'description', get_string('description', 'local_sis'), array('size' => 60)); 
		
        $yesno = sis_lookup_yes_no();
		$mform->addElement('select', 'student_display', get_string('visible_to_student', 'local_sis'), $yesno, $attributes);
		$mform->addElement('select', 'is_final_exam', get_string('final_exam', 'local_sis'), $yesno, $attributes);
				
		$num_list = sis_lookup_get_num_list(1, 15);
		$mform->addElement('select', 'sort_order', get_string('sort_order', 'local_sis'), $num_list, $attributes);
		$eff_status = sis_lookup_isactive();
		$mform->addElement('select', 'eff_status', get_string('eff_status', 'local_sis'), $eff_status, $attributes);
		
		$this->add_action_buttons($cancel=true);		
	}
	
	function validation($data, $files)
	{
		return array();
	}
	
}
