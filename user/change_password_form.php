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

require_once $CFG->libdir.'/formslib.php';

class login_change_password_form extends moodleform {

    function definition() {
        global $USER, $CFG;

        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        // visible elements
        $mform->addElement('static', 'username', get_string('username'), strtoupper($USER->username));

        $policies = array();
        if (!empty($CFG->passwordpolicy)) {
            $policies[] = print_password_policy();
        }
        if (!empty($CFG->passwordreuselimit) and $CFG->passwordreuselimit > 0) {
            $policies[] = get_string('informminpasswordreuselimit', 'auth', $CFG->passwordreuselimit);
        }
        if ($policies) {
            $mform->addElement('static', 'passwordpolicyinfo', '', implode('<br />', $policies));
        }
        $mform->addElement('password', 'password', get_string('oldpassword'));
        $mform->addRule('password', get_string('required'), 'required', null, 'client');
        $mform->setType('password', PARAM_RAW);

        $mform->addElement('password', 'newpassword1', get_string('newpassword'));
        $mform->addRule('newpassword1', get_string('required'), 'required', null, 'client');
        $mform->setType('newpassword1', PARAM_RAW);

        $mform->addElement('password', 'newpassword2', get_string('newpassword').' ('.get_String('again').')');
        $mform->addRule('newpassword2', get_string('required'), 'required', null, 'client');
        $mform->setType('newpassword2', PARAM_RAW);


        // hidden optional params
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // buttons
        if (get_user_preferences('auth_forcepasswordchange')) {
            $this->add_action_buttons(false);
        } else {
            $this->add_action_buttons(true);
        }
    }

/// perform extra password change validation
    function validation($data, $files) {
        global $USER;
        $errors = parent::validation($data, $files);

        if ($data['newpassword1'] <> $data['newpassword2']) {
            $errors['newpassword1'] = get_string('passwordsdiffer');
            $errors['newpassword2'] = get_string('passwordsdiffer');
            return $errors;
        }

/* No need this one
		//this one makes sure user choose a different password than the current one
        if ($data['password'] == $data['newpassword1']){
            $errors['newpassword1'] = get_string('mustchangepassword');
            $errors['newpassword2'] = get_string('mustchangepassword');
            return $errors;
        }
		
		//this one makes sure user do not use previous password
        if (user_is_previously_used_password($USER->id, $data['newpassword1'])) {
            $errors['newpassword1'] = get_string('errorpasswordreused', 'core_auth');
            $errors['newpassword2'] = get_string('errorpasswordreused', 'core_auth');
        }
*/
        $errmsg = '';//prevents eclipse warnings
        if (!check_password_policy($data['newpassword1'], $errmsg)) {
            $errors['newpassword1'] = $errmsg;
            $errors['newpassword2'] = $errmsg;
            return $errors;
        }

        return $errors;
    }
}

class forget_password_form extends moodleform 
{
	//Add elements to form
	public function definition() 
	{
		global $DB;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	

		$mform->addElement('text', 'username', get_string('username'), array('size' => 50));
		$mform->addRule('username', get_string('username') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		
		$mform->addElement('text', 'email', get_string('email'), array('size' => 50));
		$mform->addRule('email', get_string('email') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		
		$this->add_action_buttons($cancel=true, get_string('submit'));		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class login_reset_password_form extends moodleform {

    function definition() {
        global $USER, $CFG;

        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        $policies = array();
        if (!empty($CFG->passwordpolicy)) {
            $policies[] = print_password_policy();
        }
        if (!empty($CFG->passwordreuselimit) and $CFG->passwordreuselimit > 0) {
            $policies[] = get_string('informminpasswordreuselimit', 'auth', $CFG->passwordreuselimit);
        }
        if ($policies) {
            $mform->addElement('static', 'passwordpolicyinfo', '', implode('<br />', $policies));
        }

		$id = $this->_customdata['id'];
		$token = $this->_customdata['token'];
		$mform->addElement('hidden', 'id', $id);	
		$mform->addElement('hidden', 'token', $token);	
        $mform->addElement('password', 'newpassword1', get_string('newpassword'));
        $mform->addRule('newpassword1', get_string('required'), 'required', null, 'client');
        $mform->setType('newpassword1', PARAM_RAW);

        $mform->addElement('password', 'newpassword2', get_string('newpassword').' ('.get_String('again').')');
        $mform->addRule('newpassword2', get_string('required'), 'required', null, 'client');
        $mform->setType('newpassword2', PARAM_RAW);


        // hidden optional params
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // buttons
        if (get_user_preferences('auth_forcepasswordchange')) {
            $this->add_action_buttons(false);
        } else {
            $this->add_action_buttons(true);
        }
    }

/// perform extra password change validation
    function validation($data, $files) {
        global $USER;
        $errors = parent::validation($data, $files);

        if ($data['newpassword1'] <> $data['newpassword2']) {
            $errors['newpassword1'] = get_string('passwordsdiffer');
            $errors['newpassword2'] = get_string('passwordsdiffer');
            return $errors;
        }

/* No need this one
		//this one makes sure user choose a different password than the current one
        if ($data['password'] == $data['newpassword1']){
            $errors['newpassword1'] = get_string('mustchangepassword');
            $errors['newpassword2'] = get_string('mustchangepassword');
            return $errors;
        }
		
		//this one makes sure user do not use previous password
        if (user_is_previously_used_password($USER->id, $data['newpassword1'])) {
            $errors['newpassword1'] = get_string('errorpasswordreused', 'core_auth');
            $errors['newpassword2'] = get_string('errorpasswordreused', 'core_auth');
        }
*/
        $errmsg = '';//prevents eclipse warnings
        if (!check_password_policy($data['newpassword1'], $errmsg)) {
            $errors['newpassword1'] = $errmsg;
            $errors['newpassword2'] = $errmsg;
            return $errors;
        }

        return $errors;
    }
}
