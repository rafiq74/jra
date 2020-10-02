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

class self_registration_form extends moodleform {

    function definition() {
        global $USER, $CFG;

        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        // visible elements
		$mform->addElement('hidden', 'user_type', 'public');	
		$mform->addElement('hidden', 'country', 'SA');	
		$mform->addElement('hidden', 'gender', 'M');	
				
		$mform->addElement('text', 'username', get_string('username') . ' (' . get_string('email') . ')', array('size' => 45, 'maxlength' => 100));		
		$mform->setType('username', PARAM_NOTAGS);                   //Set type of element
		$mform->addRule('username', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('username', get_string('err_email', 'form'), 'email', '', 'client', false, false);
		
		$mform->addElement('text', 'first_name', get_string('first_name', 'local_jra'), array('size' => 30));
		$mform->addRule('first_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
//		$mform->addElement('text', 'father_name', get_string('father_name', 'local_jra'), array('size' => 30));
//		$mform->addRule('father_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
//		$mform->addElement('text', 'grandfather_name', get_string('grandfather_name', 'local_jra'), array('size' => 30));
//		$mform->addRule('grandfather_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'family_name', get_string('family_name', 'local_jra'), array('size' => 30));
		$mform->addRule('family_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
//		$mform->addElement('text', 'national_id', jra_get_string(['national_id']), array('size' => 20, 'maxlength' => 10));
//		$mform->addRule('national_id', get_string('err_required', 'form'), 'required', '', 'client', false, false);
//		$mform->addRule('national_id', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);
//		$d = new stdClass();
//		$d->format = 10;
//		$mform->addRule('national_id', get_string('err_maxlength', 'form', $d), 'maxlength', 10, 'client', false, false);
//		$mform->addRule('national_id', get_string('err_minlength', 'form', $d), 'minlength', 10, 'client', false, false);
		
//		$mform->addElement('select', 'country', get_string('country', 'local_jra'), jra_lookup_countries());

        $policies = array();
        if (!empty($CFG->passwordpolicy)) {
            $policies[] = print_password_policy();
        }
        if (!empty($CFG->passwordreuselimit) and $CFG->passwordreuselimit > 0) {
            $policies[] = get_string('informminpasswordreuselimit', 'auth', $CFG->passwordreuselimit);
        }		
        if ($policies) 
		{
			if(current_language() == 'en')
	            $mform->addElement('static', 'passwordpolicyinfo', '', implode('<br />', $policies));
			else
	            $mform->addElement('static', 'passwordpolicyinfo', '', get_string('arabic_password_policy', 'local_jra'));			
        }
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

		$mform->addElement('text', 'email', get_string('email'), array('size' => 50));
		$mform->addRule('email', get_string('email') . ' ' . get_string('cannot_empty', 'local_sis'), 'required', '', 'client', false, false);
		$mform->addRule('email', get_string('err_email', 'form'), 'email', '', 'client', false, false);
		
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
