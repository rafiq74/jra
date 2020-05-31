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

class institute_form extends moodleform 
{
	//Add elements to form
	public function definition() {
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		
		$mform->addElement('text', 'institute', get_string('code', 'local_sis'), array('size' => 50)); 
		$mform->addRule('institute', get_string('code', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$mform->addRule('institute', get_string('no_space_input', 'local_sis'), 'regex', '/^[a-zA-Z0-9-_]*$/', 'client', false, false);
		$mform->addElement('text', 'institute_name', get_string('name', 'local_sis') . ' (' . get_string('english', 'local_sis') . ')', array('size' => 50)); 
		$mform->addElement('text', 'institute_name_a', get_string('name', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')', array('size' => 50)); 

		$mform->addElement('text', 'prefix', get_string('institute', 'local_sis') . ' ' . get_string('prefix', 'local_sis'), array('size' => 10)); 

		$is_active = sis_lookup_isactive();
		$mform->addElement('select', 'eff_status', get_string('status', 'local_sis'), $is_active, $attributes);
		$countries = sis_lookup_countries();
		$mform->addElement('select', 'country', get_string('country'), $countries, $attributes);
		$mform->addElement('text', 'iban_code', get_string('iban_code', 'local_sis'), array('size' => 10));
		$mform->addElement('text', 'currency', get_string('currency', 'local_sis'), array('size' => 10));
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class campus_form extends moodleform 
{
	//Add elements to form
	public function definition() 
	{
		global $CFG, $USER ,$DB;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', sis_get_institute());	
		$mform->addElement('text', 'campus', get_string('code', 'local_sis'), array('size' => 50)); 
		$mform->addRule('campus', get_string('code', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$mform->addRule('campus', get_string('no_space_input', 'local_sis'), 'regex', '/^[a-zA-Z0-9-_]*$/', 'client', false, false);
		$mform->addElement('text', 'campus_name', get_string('name', 'local_sis'), array('size' => 50)); 
		$mform->addElement('text', 'campus_name_a', get_string('name', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')', array('size' => 50)); 
		$is_active = sis_lookup_isactive();
			
	    $mform->addElement('select', 'eff_status', get_string('status', 'local_sis'), $is_active, $attributes);
	
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	//return an empty array if pass valication
	function validation($data, $files) 
	{
		return array();
	}
}

class organization_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG, $USER ,$DB;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', sis_get_institute());	
		$mform->addElement('text', 'organization', get_string('code', 'local_sis'), array('size' => 50)); 
	    $mform->addRule('organization', get_string('code', 'local_sis') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$mform->addRule('organization', get_string('no_space_input', 'local_sis'), 'regex', '/^[a-zA-Z0-9-_]*$/', 'client', false, false);

		$mform->addElement('text', 'organization_name', get_string('name', 'local_sis'), array('size' => 50)); 	
		$mform->addElement('text', 'organization_name_a', get_string('name', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')', array('size' => 50)); 
        
        $type = sis_lookup_get_list('organization', 'type');
		$mform->addElement('select', 'organization_type', get_string('type', 'local_sis'), $type);
		$mform->addElement('hidden', 'parent','0');
		
		$is_active =sis_lookup_isactive();		
		$mform->addElement('select', 'eff_status', get_string('status', 'local_sis'), $is_active, $attributes);

        $campus = sis_lookup_campus();
        $mform->addElement('select', 'campus', get_string('campus', 'local_sis'), $campus);


		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class post_form extends moodleform 
{
	//Add elements to form
	public function definition() 
	{
		global $CFG, $USER ,$DB;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$pid = $this->_customdata['pid'];
		$type = $this->_customdata['type'];
		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'pid', $pid);
		$mform->addElement('hidden', 'type', $type);
		$mform->addElement('hidden', 'institute', sis_get_institute());	

		if($type == 'acad_org')
		{
			$organization = $DB->get_record('si_organization', array('id' => $pid));
			if(!$organization)
				throw new moodle_exception('Wrong parameters.');
			$mform->addElement('hidden', 'organization', $organization->organization);
		}
		else if($type == 'campus')
		{
			$organization = $DB->get_record('si_campus', array('id' => $pid));
			if(!$organization)
				throw new moodle_exception('Wrong parameters.');
			$mform->addElement('hidden', 'organization', $organization->campus);
		}
		else if($type == 'institute')
		{
			$organization = $DB->get_record('si_institute', array('id' => $pid));
			if(!$organization)
				throw new moodle_exception('Wrong parameters.');
			$mform->addElement('hidden', 'organization', $organization->institute);
		}
		else
			throw new moodle_exception('Wrong parameters.');
		$mform->addElement('hidden', 'acad_org', $organization->id);
		$mform->addElement('hidden', 'category', $type);

		$posts = sis_lookup_post();
		$mform->addElement('select', 'position_id', get_string('post', 'local_sis'), $posts);		
		
		$employees = sis_lookup_employee_list();
		$mform->addElement('select', 'user_id', get_string('employee', 'local_sis'), $employees);		

		$mform->addElement('text', 'custom_name', sis_get_string(['custom', 'name']), array('size' => 50)); 
		$mform->addElement('text', 'custom_name_a', sis_get_string(['custom', 'name']) . ' (' . get_string('arabic', 'local_sis') . ')', array('size' => 50)); 

		$appointment_type = sis_lookup_appointment_type();
		$mform->addElement('select', 'appointment_type', get_string('appointment', 'local_sis'), $appointment_type);		
		
		$is_active = sis_lookup_isactive();
			
	    $mform->addElement('select', 'eff_status', get_string('status', 'local_sis'), $is_active, $attributes);
	
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	//return an empty array if pass valication
	function validation($data, $files) 
	{
		return array();
	}
}

class section_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG, $USER ,$DB;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
        $organization_list = sis_lookup_organization_list();
        $mform->addElement('select', 'parent', get_string('parent_organization', 'local_sis'),$organization_list);		
		$mform->addElement('text', 'organization', 'Code', array('size' => 50)); 
	    $mform->addRule('organization', 'Code cannot be empty', 'required', '', 'client', false, false);

		$mform->addElement('text', 'organization_name', 'Name EN', array('size' => 50)); 	
		$mform->addElement('text', 'organization_name_a', 'Name AR', array('size' => 50)); 
        
		$mform->addElement('hidden', 'organization_type', 'section');	
		$is_active =sis_lookup_isactive();
		
		$mform->addElement('select', 'eff_status', 'Status', $is_active, $attributes);

		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}

}
?>
