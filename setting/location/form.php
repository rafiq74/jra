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
class state_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', sis_get_institute());
		
		$mform->addElement('text', 'state', get_string('state', 'local_sis'), array('size' => 30)); // Add elements to your form
		$mform->addRule('state', get_string('state_cannot_empty'), 'required', '', 'server', false, false);
		$mform->addElement('text', 'state_a', get_string('state', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'state_code', get_string('code', 'local_sis'), array('size' => 10)); // Add elements to your form
		$countries = sis_lookup_countries();
		$mform->addElement('select', 'country', get_string('country'), $countries, $attributes);
		$mform->setDefault('country', sis_get_institute('country'));
				
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}
class city_form extends moodleform {
	//Add elements to form
	public function definition() {

		global $CFG, $USER ,$DB;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$city_country = sis_get_session('location_city_country');
		if($city_country == '') //no country, initialize it
			$city_country = sis_location_init_city_country();

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'institute', sis_get_institute());
		$mform->addElement('hidden', 'country', $city_country);
		$state = sis_lookup_state($city_country);			
	    $mform->addElement('select', 'state_id', get_string('state', 'local_sis'), $state, $attributes);

		$mform->addElement('text', 'city', get_string('city', 'local_sis'), array('size' => 30)); // Add elements to your form
		$mform->addRule('city', get_string('city_not_empty', 'local_sis'), 'required', '', 'server', false, false);
		$mform->addElement('text', 'city_a', get_string('city', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')', array('size' => 30)); // Add elements to your form
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}


}

?>
