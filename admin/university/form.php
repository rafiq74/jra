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
require_once '../../lib/jra_lookup_lib.php';
require_once $CFG->libdir.'/formslib.php';

class semester_form extends moodleform 
{
	//Add elements to form
	public function definition() {
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', jra_get_institute());	
		
        $mform->addElement('text', 'semester', get_string('semester', 'local_jra'), array('size' => 50)); // Add elements to your form
		$mform->addRule('semester', 'Semester cannot be empty', 'required', '', 'client', false, false);
		$mform->addRule('semester', get_string('no_space_input', 'local_jra'), 'regex', '/^[a-zA-Z0-9-_]*$/', 'client', false, false);
		$mform->addElement('text', 'description', get_string('description', 'local_jra') . ' (' . get_string('english', 'local_jra') . ')', array('size' => 50)); // Add elements to your form
		$mform->addElement('text', 'description_a', get_string('description', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50)); // Add elements to your form
		$admission_type = jra_lookup_admission_type();
        $mform->addElement('select', 'admission_type', jra_get_string(['admission_type']), $admission_type, $attributes);
		$sem_num = jra_lookup_get_num_list(1, 10);
        $mform->addElement('select', 'semester_num', jra_get_string(['semester', 'number']), $sem_num, $attributes);
		$mform->setDefault('semester_year', date('Y', time()));
		$sem_year = jra_lookup_get_year_list();
        $mform->addElement('select', 'semester_year', ucfirst(get_string('year')), $sem_year, $attributes);
		$mform->addElement('date_selector', 'start_date', jra_get_string(['date', 'open']));
		$mform->addElement('date_selector', 'end_date', jra_get_string(['date', 'close']));
		$mform->addElement('text', 'secondary_weight', jra_get_string(['secondary_school_result', 'weight']), array('size' => 15));
		$mform->addRule('secondary_weight', get_string('err_required', 'form'), 'required', '', 'client', false, false);		
		$mform->addRule('secondary_weight', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);		

		$mform->addElement('text', 'tahseli_weight', jra_get_string(['tahseli', 'weight']), array('size' => 15));
		$mform->addRule('tahseli_weight', get_string('err_required', 'form'), 'required', '', 'client', false, false);		
		$mform->addRule('tahseli_weight', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);		

		$mform->addElement('text', 'qudorat_weight', jra_get_string(['qudorat', 'weight']), array('size' => 15));
		$mform->addRule('qudorat_weight', get_string('err_required', 'form'), 'required', '', 'client', false, false);		
		$mform->addRule('qudorat_weight', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);		
        $yes_no = jra_lookup_yes_no();
        $mform->addElement('select', 'display', jra_get_string(['display', 'result']), $yes_no, $attributes);
		$mform->addElement('date_selector', 'confirm_end_date', jra_get_string(['last_date_confirm']));
//		$mform->addElement('date_selector', 'placement_test_date', jra_get_string(['placement', 'test', 'date']));
//        $mform->addElement('text', 'placement_test_venue', jra_get_string(['message_if_approve']), array('size' => 80)); // Add elements to your form
		$mform->addElement('textarea', 'placement_test_venue', jra_get_string(['message_if_approve']), 'wrap="virtual" rows="5" cols="50"');
		$mform->addElement('hidden', 'campus', '');	
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class semester_detail_form extends moodleform 
{
	//Add elements to form
	public function definition() {
		global $DB;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();
		$semester_id = $this->_customdata['sid'];
		$week = $this->_customdata['week'];
		$sem = $DB->get_record('si_semester', array('id' => $semester_id));
		if(!$sem)
			throw new moodle_exception(get_string('wrong_parameter', 'local_jra'));	
			
		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'sid', $semester_id);	
		$mform->addElement('hidden', 'semester_id', $sem->id);	
		$mform->addElement('hidden', 'semester', $sem->semester);	
		$mform->addElement('hidden', 'week', $week);	
		$mform->addElement('hidden', 'detail_week', $week);	
		$mform->addElement('hidden', 'institute', jra_get_institute());	
		
//		$mform->addElement('checkbox', 'is_break', get_string('vacation_week', 'local_jra'), get_string('yes'));

		$radioarray=array();
		$radioarray[] = $mform->createElement('radio', 'is_break', '', get_string('yes'), 'Y', $attributes);
		$radioarray[] = $mform->createElement('radio', 'is_break', '', get_string('no'), 'N', $attributes);
		$mform->addGroup($radioarray, 'radioar', get_string('vacation_week', 'local_jra'), array(' '), false);
		$mform->setDefault('is_break', 'N');
        $mform->addElement('text', 'label', get_string('reason', 'local_jra'), array('size' => 50)); // Add elements to your form
	    $mform->addElement('textarea', 'description', get_string('week_information', 'local_jra'),'wrap="virtual" rows="5" cols="80"');
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

    

?>
