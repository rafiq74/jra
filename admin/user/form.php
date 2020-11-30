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

class user_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore!
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'institute', jra_get_institute());

		$mform->addElement('text', 'username', get_string('username') . ' (' . get_string('email') . ')', array('size' => 45, 'maxlength' => 100));
		$mform->setType('username', PARAM_NOTAGS);                   //Set type of element
		$mform->addRule('username', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('username', get_string('err_email', 'form'), 'email', '', 'client', false, false);

		$mform->addElement('select', 'user_type', jra_get_string(['user', 'type']), jra_lookup_user_type());
		$mform->setDefault('user_type', 'employee');
		$mform->addElement('text', 'first_name', get_string('first_name', 'local_jra'), array('size' => 30));
		$mform->addRule('first_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'father_name', get_string('father_name', 'local_jra'), array('size' => 30));
		//$mform->addRule('father_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'grandfather_name', get_string('grandfather_name', 'local_jra'), array('size' => 30));
		//$mform->addRule('grandfather_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'family_name', get_string('family_name', 'local_jra'), array('size' => 30));
		$mform->addRule('family_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'national_id', jra_get_string(['national_id']), array('size' => 20, 'maxlength' => 10));
//		$mform->addRule('national_id', get_string('err_required', 'form'), 'required', '', 'client', false, false);

		$mform->addElement('text', 'first_name_a', get_string('first_name_a', 'local_jra'), array('size' => 30));
		$mform->addRule('first_name_a', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'father_name_a', get_string('father_name_a', 'local_jra'), array('size' => 30));
		//$mform->addRule('father_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'grandfather_name_a', get_string('grandfather_name_a', 'local_jra'), array('size' => 30));
		//$mform->addRule('grandfather_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'family_name_a', get_string('family_name_a', 'local_jra'), array('size' => 30));
		$mform->addRule('family_name_a', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'national_id', jra_get_string(['national_id']), array('size' => 20, 'maxlength' => 10));
		//		$mform->addRule('national_id', get_string('err_required', 'form'), 'required', '', 'client', false, false);

		$mform->addRule('national_id', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);
		$d = new stdClass();
		$d->format = 10;
		$mform->addRule('national_id', get_string('err_maxlength', 'form', $d), 'maxlength', 10, 'client', false, false);
		$mform->addRule('national_id', get_string('err_minlength', 'form', $d), 'minlength', 10, 'client', false, false);

		$mform->addElement('select', 'gender', get_string('gender', 'local_jra'), jra_lookup_gender());

        $user_status = jra_lookup_user_status();
        $mform->addElement('select', 'active_status', 'Status', $user_status, $attributes);
//		$mform->addElement('text', 'idnumber', get_string('idnumber'), array('size' => 30));

		$this->add_action_buttons($cancel=true);
	}

	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}


class user_contact_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		global $DB;
		$mform = $this->_form; // Don't forget the underscore!
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');

		$user_id = $this->_customdata['uid'];
		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'uid', $user_id);
		$mform->addElement('hidden', 'tab', 'contact');
		$user = $DB->get_record('jra_user', array('id' => $user_id));
		if(!$user)
			throw new moodle_exception('Wrong parameters.');
		$mform->addElement('hidden', 'user_id', '');

//		$mform->addElement('header', 'headergradetemplate', get_string('general'));

		$address_type = jra_lookup_get_list('personal_info', 'address_type', '', true);
		$mform->addElement('select', 'address_type', jra_get_string(['contact', 'type']), $address_type);

		$mform->addElement('text', 'address1', get_string('address1', 'local_jra'), array('size' => 70));
		$mform->addRule('address1', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'address2', get_string('address2', 'local_jra'), array('size' => 70));

		$mform->addElement('text', 'address_city', get_string('city', 'local_jra'), array('size' => 20));
		$mform->addRule('address_city', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'address_state', get_string('state', 'local_jra'), array('size' => 20));
		$mform->addRule('address_state', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'address_postcode', get_string('postcode', 'local_jra'), array('size' => 10));
		$mform->addRule('address_postcode', get_string('err_required', 'form'), 'required', '', 'client', false, false);

		$countries = jra_lookup_countries();
		$mform->addElement('select', 'address_country', get_string('country', 'local_jra'), $countries);
		$mform->setDefault('address_country', jra_get_country());

		$mform->addElement('text', 'phone_mobile', get_string('phone_mobile', 'local_jra'), array('size' => 20));
		$mform->addRule('phone_mobile', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'phone_home', get_string('phone_home', 'local_jra'), array('size' => 20));

		$mform->addElement('text', 'email_primary', get_string('email', 'local_jra'), array('size' => 60));
		$mform->addRule('email_primary', get_string('err_email', 'form'), 'email', '', 'client', false, false);

		$this->add_action_buttons($cancel=true);
	}

	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class personal_info_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		global $DB;
		$mform = $this->_form; // Don't forget the underscore!
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');

		$user_id = $this->_customdata['uid'];
		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'uid', $user_id);
		$mform->addElement('hidden', 'tab', 'personal');
		$user = $DB->get_record('jra_user', array('id' => $user_id));
		if(!$user)
			throw new moodle_exception('Wrong parameters.');
		$mform->addElement('hidden', 'user_id', '');

//		$mform->addElement('header', 'headergradetemplate', get_string('general'));

		$mform->addElement('text', 'national_id', jra_get_string(['national_id']), array('size' => 20));

		$mform->addElement('text', 'passport_id', get_string('passport_no', 'local_jra'), array('size' => 20));
		$mform->addElement('date_selector', 'dob', get_string('date_of_birth', 'local_jra'));

		$countries = jra_lookup_countries();
		$mform->addElement('select', 'nationality', get_string('nationality', 'local_jra'), $countries);
		$mform->setDefault('nationality', jra_get_country());
		$marital_status = jra_lookup_marital_status();
        $mform->addElement('select', 'marital_status', get_string('marital_status', 'local_jra') ,$marital_status);

		$this->add_action_buttons($cancel=true);
	}

	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}


class account_info_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		global $DB;
		$mform = $this->_form; // Don't forget the underscore!
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');

		$user_id = $this->_customdata['uid'];
		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'uid', $user_id);
		$mform->addElement('hidden', 'tab', 'account');
		$user = $DB->get_record('jra_user', array('id' => $user_id));
		if(!$user)
			throw new moodle_exception('Wrong parameters.');
		$mform->addElement('hidden', 'user_id', '');

//		$mform->addElement('header', 'headergradetemplate', get_string('general'));

		$yesno = jra_lookup_yes_no();
		$mform->addElement('select', 'enable_login', jra_get_string(['enable', 'login']), $yesno);
		$mform->addElement('select', 'suspended', get_string('suspended', 'local_jra'), $yesno);
		$mform->setDefault('suspended', 'N');
	    $mform->addElement('textarea', 'suspend_message', get_string('suspend_message', 'local_jra'), 'wrap="virtual" rows="5" cols="80"');

		$this->add_action_buttons($cancel=true);
	}

	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

/* use get_string('err_alphanumeric', 'form');
$string['err_alphanumeric']='You must enter only letters or numbers here.';
$string['err_email']='You must enter a valid email address here.';
$string['err_lettersonly']='You must enter only letters here.';
$string['err_maxlength']='You must enter not more than $a->format characters here.';
$string['err_minlength']='You must enter at least $a->format characters here.';
$string['err_nopunctuation']='You must enter no punctuation characters here.';
$string['err_nonzero']='You must enter a number not starting with a 0 here.';
$string['err_numeric']='You must enter a number here.';
$string['err_rangelength']='You must enter between {$a->format[0]} and {$a->format[1]} characters here.';
$string['err_required']='You must supply a value here.';
*/

?>
