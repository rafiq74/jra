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
require_once '../lib/jra_lookup_lib.php';
require_once $CFG->libdir.'/formslib.php';

class stripe_form extends moodleform 
{
	//Add elements to form
	public function definition() {
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();
		$strip_url = new moodle_url('https://www.stripe.com');
		$strip_html = jra_ui_link(jra_ui_icon('cc-stripe', '3', true), $strip_url, array('target' => '_blank'));
        $mform->addElement('static', 'stripe', 'Service Provider ', $strip_html); // Add elements to your form
		
        $mform->addElement('text', 'stripe_secret_key', 'Stripe Secret Key', array('size' => 50)); // Add elements to your form
		$mform->addElement('text', 'stripe_publishable_key', 'Stripe Publishable Key', array('size' => 50)); // Add elements to your form
		$mform->addElement('advcheckbox', 'stripe_validate_billing', 'Require users to enter their billing address', 'Yes', array('group' => 1), array(0, 1));
		$mform->addElement('advcheckbox', 'stripe_validate_postcode', 'Validate the billing postal code', 'Yes', array('group' => 2), array(0, 1));
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}
?>
