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
class building_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', sis_get_institute());	
		
        $mform->addElement('text', 'building', get_string('code', 'local_sis'), array('size' => 50)); // Add elements to your form
		$mform->addRule('building', get_string('code', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'building_name', get_string('name', 'local_sis') . ' (' . get_string('english', 'local_sis') . ')', array('size' => 50)); // Add elements to your form
		$mform->addElement('text', 'building_name_a', get_string('name', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')', array('size' => 50)); // Add elements to your form

		$usage = sis_lookup_get_list('facility', 'usage');
	    $mform->addElement('select', 'usage_building', get_string('usage', 'local_sis'), $usage, $attributes);

        $gender = sis_lookup_gender();		
		$mform->addElement('select', 'gender', get_string('gender', 'local_sis'), $gender, $attributes);							
        $is_active = sis_lookup_isactive();
        $mform->addElement('select', 'eff_status', get_string('status', 'local_sis'), $is_active, $attributes);
        $campus = sis_lookup_campus();
        $mform->addElement('select', 'campus', get_string('campus', 'local_sis'), $campus ,$attributes);
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}
class room_form extends moodleform 
{
	//Add elements to form
	public function definition() {
		global $CFG, $USER  ,$DB ;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();
		$mform->addElement('hidden', 'id', '');		
		$mform->addElement('hidden', 'institute', sis_get_institute());	
	    $building_code = sis_lookup_building();
		$mform->addElement('select', 'building', get_string('building', 'local_sis'), $building_code); // Add elements to your form
		$mform->addElement('text', 'room', get_string('code', 'local_sis'), array('size' => 50)); // Add elements to your form
		$mform->addRule('room', get_string('room_code', 'local_sis') . ' ' . get_string('usage', 'cannot_empty'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'room_name', get_string('name', 'local_sis') . ' (' . get_string('english', 'local_sis') . ')', array('size' => 50)); // Add elements to your form
		$mform->addElement('text', 'room_name_a', get_string('name', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')', array('size' => 50)); // Add elements to your form
		$mform->addElement('text', 'room_num', get_string('room_number', 'local_sis'), array('size' => 50)); // Add elements to your form
	    $mform->addElement('text', 'capacity', get_string('capacity', 'local_sis'), array('size' => 10)); // Add elements to your form
		$mform->addElement('text', 'level', get_string('level', 'local_sis'), array('size' => 50)); // Add elements to your form

		$room_type = sis_lookup_get_list('facility', 'room_type');
		
	    $mform->addElement('select', 'room_type', get_string('room_type', 'local_sis'),$room_type); // Add elements to your form
		
		$usage = sis_lookup_get_list('facility', 'usage');
	    $mform->addElement('select', 'room_usage', get_string('usage', 'local_sis'), $usage, $attributes);
		
		$mform->addRule('capacity', get_string('capacity', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$mform->addRule('capacity', get_string('capacity', 'local_sis') . ' ' . get_string('must_be_number', 'local_sis'), 'numeric', '', 'client', false, false);
	    $exam_usage = sis_lookup_yes_no();
	    $mform->addElement('select', 'exam_use', get_string('exam_use', 'local_sis') ,$exam_usage); // Add elements to your form
        $gender = sis_lookup_gender();		
		$mform->addElement('select', 'gender', get_string('gender', 'local_sis'), $gender, $attributes);			
        $is_active = sis_lookup_isactive();
		$mform->addElement('select', 'eff_status', get_string('status', 'local_sis'), $is_active);									
        $campus = sis_lookup_campus();
        $mform->addElement('select', 'campus', get_string('campus', 'local_sis'), $campus);		   			
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files)
	{
		return array();
	}
}

class usage_form extends moodleform 
{
	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
		$attributes = array();
		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('text', 'value', get_string('usage', 'local_sis'), array('size' => 30)); // Add elements to your form
		$lang = sis_lookup_language();		
		$mform->addElement('select', 'lang', get_string('language', 'local_sis'), $lang, $attributes);			
		
		$this->add_action_buttons($cancel=true);		
	}
	
	function validation($data, $files)
	{
		return array();
	}

}


class room_type_form extends moodleform 
{

	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
		$attributes = array();
		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('text', 'value', get_string('room_type', 'local_sis'), array('size' => 30)); // Add elements to your form
		$lang = sis_lookup_language();		
		$mform->addElement('select', 'lang', get_string('language', 'local_sis'), $lang, $attributes);			
		
		$this->add_action_buttons($cancel=true);		
	}
	
	function validation($data, $files)
	{
		return array();
	}

}

?>
