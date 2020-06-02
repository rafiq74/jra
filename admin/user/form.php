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
		$mform->addElement('hidden', 'country', jra_get_country());	
				
		$mform->addElement('text', 'username', get_string('username') . ' (' . get_string('email') . ')', array('size' => 45, 'maxlength' => 100));		
		$mform->setType('username', PARAM_NOTAGS);                   //Set type of element
		$mform->addRule('username', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('username', get_string('err_email', 'form'), 'email', '', 'client', false, false);
		
		$mform->addElement('select', 'user_type', jra_get_string(['user', 'type']), jra_lookup_user_type());

		$mform->addElement('text', 'first_name', get_string('first_name', 'local_jra'), array('size' => 50));
		$mform->addRule('first_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'middle_name', get_string('middle_name', 'local_jra'), array('size' => 30));
		$mform->addElement('text', 'family_name', get_string('family_name', 'local_jra'), array('size' => 30));
		$mform->addRule('family_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('select', 'gender', get_string('gender', 'local_jra'), jra_lookup_gender());

//        $eff_status = jra_lookup_isactive();
//        $mform->addElement('select', 'eff_status', 'Status', $eff_status, $attributes);
//		$mform->addElement('text', 'idnumber', get_string('idnumber'), array('size' => 30));
		
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class user_form_edit extends moodleform 
{
	//Add elements to form
	public function definition() 
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'country', jra_get_country());	
				
		$mform->addElement('header', 'headergradetemplate', get_string('general'));
		$mform->addElement('text', 'appid', get_string('appid', 'local_jra'), array('size' => 15));
		$mform->addElement('static', 'appid_hint', '', get_string('appid_hint', 'local_jra'));
		$mform->addRule('appid', get_string('appid', 'local_jra') . ' ' . get_string('cannot_empty', 'local_jra'), 'required', '', 'client', false, false);
		$mform->addElement('select', 'user_type', get_string('user_type', 'local_jra'), jra_lookup_user_type());

		$mform->addElement('select', 'title', get_string('title', 'local_jra'), jra_lookup_user_title());
		$mform->addElement('text', 'first_name', get_string('first_name', 'local_jra'), array('size' => 50));
		$mform->addRule('first_name', get_string('first_name', 'local_jra') . ' ' . get_string('cannot_empty', 'local_jra'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'father_name', get_string('father_name', 'local_jra'), array('size' => 50));
		$mform->addElement('text', 'grandfather_name', get_string('grandfather_name', 'local_jra'), array('size' => 50));
		$mform->addElement('text', 'family_name', get_string('family_name', 'local_jra'), array('size' => 50));
		$mform->addRule('family_name', get_string('family_name', 'local_jra') . ' ' . get_string('cannot_empty', 'local_jra'), 'required', '', 'client', false, false);
		$mform->addElement('select', 'gender', get_string('gender', 'local_jra'), jra_lookup_gender());
        $eff_status = jra_lookup_isactive();
        $mform->addElement('select', 'eff_status', 'Status', $eff_status, $attributes);
		
		$mform->addElement('header', 'headergradetemplate', get_string('optional_information', 'local_jra'));
		$mform->addElement('text', 'first_name_a', get_string('first_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addElement('text', 'father_name_a', get_string('father_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addElement('text', 'grandfather_name_a', get_string('grandfather_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addElement('text', 'family_name_a', get_string('family_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addElement('text', 'idnumber', get_string('idnumber'), array('size' => 30));
		
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

//same as user_form_edit, but more minimal information
class student_form_edit extends moodleform 
{
	//Add elements to form
	public function definition() 
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$student_id = $this->_customdata['student_id'];

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'country', jra_get_country());	

		$mform->addElement('hidden', 'appid', '');	
		$mform->addElement('hidden', 'user_type', '');	

		$mform->addElement('static', 'appid_hint', get_string('student_id', 'local_jra'), $student_id);

		$mform->addElement('select', 'title', get_string('title', 'local_jra'), jra_lookup_user_title());
		$mform->addElement('text', 'first_name', get_string('first_name', 'local_jra'), array('size' => 50));
		$mform->addRule('first_name', get_string('first_name', 'local_jra') . ' ' . get_string('cannot_empty', 'local_jra'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'father_name', get_string('father_name', 'local_jra'), array('size' => 50));
		$mform->addRule('father_name', get_string('father_name', 'local_jra') . ' ' . get_string('cannot_empty', 'local_jra'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'grandfather_name', get_string('grandfather_name', 'local_jra'), array('size' => 50));
		$mform->addRule('grandfather_name', get_string('grandfather_name', 'local_jra') . ' ' . get_string('cannot_empty', 'local_jra'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'family_name', get_string('family_name', 'local_jra'), array('size' => 50));
		$mform->addRule('family_name', get_string('family_name', 'local_jra') . ' ' . get_string('cannot_empty', 'local_jra'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'first_name_a', get_string('first_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addElement('text', 'father_name_a', get_string('father_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addElement('text', 'grandfather_name_a', get_string('grandfather_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addElement('text', 'family_name_a', get_string('family_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addElement('select', 'gender', get_string('gender', 'local_jra'), jra_lookup_gender());
		
		$mform->addElement('text', 'idnumber', get_string('idnumber'), array('size' => 30));
		
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
		$user = $DB->get_record('si_user', array('id' => $user_id));
		if(!$user)
			throw new moodle_exception('Wrong parameters.');		
		$mform->addElement('hidden', 'user_id', '');		

//		$mform->addElement('header', 'headergradetemplate', get_string('general'));
		
		$mform->addElement('text', 'civil_id', get_string('civil_id', 'local_jra'), array('size' => 20));
		$mform->addRule('civil_id', get_string('civil_id', 'local_jra') . ' ' . get_string('cannot_empty', 'local_jra'), 'required', '', 'client', false, false);
		$id_type = jra_lookup_get_list('personal_info', 'id_type', '', true);
		$mform->addElement('select', 'id_type', get_string('id_type', 'local_jra'), $id_type);
		
		$mform->addElement('text', 'passport_id', get_string('passport_no', 'local_jra'), array('size' => 20));		
		$mform->addElement('date_selector', 'dob', get_string('date_of_birth', 'local_jra'));
		
		$countries = jra_lookup_countries();
		$mform->addElement('select', 'nationality', get_string('nationality', 'local_jra'), $countries);
		$mform->setDefault('nationality', 'SA');
		$mform->addElement('select', 'nationality_at_birth', get_string('nationality_at_birth', 'local_jra'), $countries);
		$mform->setDefault('nationality_at_birth', 'SA');
		$languages = jra_lookup_language();
		$mform->addElement('select', 'language_track', get_string('language_track', 'local_jra'), $languages);	
		$mform->setDefault('language_track', 'en');
		$religions = jra_lookup_get_list('personal_info', 'religion');
		$mform->addElement('select', 'religion', get_string('religion', 'local_jra'), $religions);	
		$marital_status = jra_lookup_marital_status();
        $mform->addElement('select', 'marital_status', get_string('marital_status', 'local_jra') ,$marital_status);
		$mform->addElement('text', 'blood_type', get_string('blood_group', 'local_jra'), array('size' => 5)); 
		
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class personal_address_form extends moodleform 
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
		$mform->addElement('hidden', 'tab', 'address');
		$user = $DB->get_record('si_user', array('id' => $user_id));
		if(!$user)
			throw new moodle_exception('Wrong parameters.');		
		$mform->addElement('hidden', 'user_id', '');		

//		$mform->addElement('header', 'headergradetemplate', get_string('general'));
		
		$address_type = jra_lookup_get_list('personal_info', 'address_type', '', true);
		$mform->addElement('select', 'address_type', get_string('contact_type', 'local_jra'), $address_type);
		
		$mform->addElement('text', 'address1', get_string('address1', 'local_jra'), array('size' => 70));
		$mform->addElement('text', 'address2', get_string('address2', 'local_jra'), array('size' => 70));
		
		$add_array=array();
		$add_array[] =& $mform->createElement('text', 'address_city', get_string('city', 'local_jra'), array('size' => 20));
		$add_array[] =& $mform->createElement('text', 'address_state', get_string('state', 'local_jra'), array('size' => 20));
		$add_array[] =& $mform->createElement('text', 'address_postcode', get_string('postcode', 'local_jra'), array('size' => 10));
		$mform->addGroup($add_array, 'addressar', get_string('city', 'local_jra'), array('&nbsp;&nbsp;&nbsp&nbsp;&nbsp;' . get_string('state', 'local_jra') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp&nbsp;&nbsp;' . get_string('postcode', 'local_jra') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'), false);
		
		$countries = jra_lookup_countries();
		$mform->addElement('select', 'address_country', get_string('country', 'local_jra'), $countries);
		$mform->setDefault('address_country', 'SA');
		
		$mform->addElement('text', 'email_primary', get_string('email', 'local_jra'), array('size' => 60));
		$mform->addElement('text', 'email_secondary', get_string('email_alternate', 'local_jra'), array('size' => 60));
				
		$add_array=array();
		$add_array[] =& $mform->createElement('text', 'phone_mobile', get_string('phone_mobile', 'local_jra'), array('size' => 20));
		$add_array[] =& $mform->createElement('text', 'phone_home', get_string('phone_home', 'local_jra'), array('size' => 20));
		$mform->addGroup($add_array, 'addcontact', get_string('phone_mobile', 'local_jra'), array('&nbsp;&nbsp;&nbsp&nbsp;&nbsp;' . get_string('phone_home', 'local_jra') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'), false);
				
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class employee_info_form extends moodleform 
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
		$mform->addElement('hidden', 'tab', 'employee');
		$user = $DB->get_record('si_user', array('id' => $user_id));
		if(!$user)
			throw new moodle_exception('Wrong parameters.');		
		$mform->addElement('hidden', 'user_id', '');		

//		$mform->addElement('header', 'headergradetemplate', get_string('general'));
		
		$mform->addElement('date_selector', 'join_date', get_string('join_date', 'local_jra'));
		
		$employment_type = jra_lookup_get_list('employee', 'employment_type', '', true);
		$mform->addElement('select', 'employment_type', get_string('employment_type', 'local_jra'), $employment_type);
		$employment_category = jra_lookup_get_list('employee', 'employment_category', '', true);
		$mform->addElement('select', 'employment_category', get_string('employment_category', 'local_jra'), $employment_category);
		
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class student_info_form extends moodleform 
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
		$mform->addElement('hidden', 'tab', 'student_info');
		$user = $DB->get_record('si_user', array('id' => $user_id));
		if(!$user)
			throw new moodle_exception('Wrong parameters.');		
		$mform->addElement('hidden', 'user_id', '');		

//		$mform->addElement('header', 'headergradetemplate', get_string('general'));
		
		$mform->addElement('date_selector', 'admission_date', get_string('admission_date', 'local_jra'));

		$admission_type = jra_lookup_get_list('admission', 'admission_type', '', true);
		$mform->addElement('select', 'admission_type', get_string('admission_type', 'local_jra'), $admission_type);

		$student_type = jra_lookup_get_list('student', 'student_type', '', true);
		$mform->addElement('select', 'student_type', get_string('student_type', 'local_jra'), $student_type);

		$student_category = jra_lookup_get_list('student', 'student_category', '', true);
		$mform->addElement('select', 'student_category', get_string('student_category', 'local_jra'), $student_category);

		$financing_source = jra_lookup_get_list('finance', 'financing_source', '', true);
		$mform->addElement('select', 'financing_source', get_string('financing_source', 'local_jra'), $financing_source);

		$yesno = jra_lookup_yes_no();
		$mform->addElement('select', 'payroll_active', get_string('payroll_active', 'local_jra'), $yesno);

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
		$user = $DB->get_record('si_user', array('id' => $user_id));
		if(!$user)
			throw new moodle_exception('Wrong parameters.');		
		$mform->addElement('hidden', 'user_id', '');		

//		$mform->addElement('header', 'headergradetemplate', get_string('general'));
		
		$yesno = jra_lookup_yes_no();
		$mform->addElement('select', 'enable_login', get_string('enable_login', 'local_jra'), $yesno);
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

class finance_info_form extends moodleform 
{
	//Add elements to form
	public function definition() 
	{
		global $DB;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	

		$user_id = $this->_customdata['uid'];
		$self_update = $this->_customdata['self_update'];
		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'uid', $user_id);
		$mform->addElement('hidden', 'tab', 'finance');
		$user = $DB->get_record('si_user', array('id' => $user_id));
		if(!$user)
			throw new moodle_exception('Wrong parameters.');		
		$mform->addElement('hidden', 'user_id', '');		

		$bank_list = jra_lookup_bank();		
		$mform->addElement('select', 'bank_code', get_string('bank', 'local_jra'), $bank_list);
		
		$con_array=array();
		$con_array[] =& $mform->createElement('static', 'desc2', '');	
		$con_array[] =& $mform->createElement('text', 'iban_01', '', array('size' => 5, 'maxlength' => 4));
		$con_array[] =& $mform->createElement('text', 'iban_02', '', array('size' => 5, 'maxlength' => 4));
		$con_array[] =& $mform->createElement('text', 'iban_03', '', array('size' => 5, 'maxlength' => 4));
		$con_array[] =& $mform->createElement('text', 'iban_04', '', array('size' => 5, 'maxlength' => 4));
		$con_array[] =& $mform->createElement('text', 'iban_05', '', array('size' => 5, 'maxlength' => 4));
		$con_array[] =& $mform->createElement('text', 'iban_06', '', array('size' => 3, 'maxlength' => 2));
		$mform->addGroup($con_array, 'conar13', get_string('iban_no', 'local_jra') , array('<strong>' . jra_output_iban_country_code() . '</strong>&nbsp;&nbsp;', '', '', '', '', ''), false);

//		$mform->addRule('iban_01', 'Grade Scheme Cannot be empty', 'required', '', 'client', false, false);

		$mform->addGroupRule('conar13', get_string('iban_no_empty', 'local_jra'), 'required', null, 6);
		$mform->addGroupRule('conar13', get_string('iban_field', 'local_jra') . ' ' . get_string('must_be_number', 'local_jra'), 'regex', '/^\d+$/', 6);
		$mform->addGroupRule('conar13', get_string('iban_field', 'local_jra') . ' ' . get_string('4_digit', 'local_jra'), 'minlength', 4, 5);
		$mform->addGroupRule('conar13', array(
			'iban_06' => array(
				array(get_string('last', 'local_jra') . ' '. get_string('iban_field', 'local_jra') . ' ' . get_string('2_digit', 'local_jra'), 'minlength', 2, 'server'),
			),
		));		
		
		$mform->addElement('static', 'instruction', '', '(' . get_string('iban_instruction', 'local_jra') . ')');
		if($self_update)
			$cancel = false;
		else
			$cancel = true;
		$this->add_action_buttons($cancel);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) 
	{
		return array();
        $errors = parent::validation($data, $files);

		if($data['iban_01'] == '')
            $errors['iban_01'] = get_string('cannot_empty');
		else if(strlen($data['iban_01']) != 4)
            $errors['iban_01'] = get_string('must_be_4_digit');
		else if(!preg_match('/^\d+$/', $data['iban_01']))
            $errors['iban_01'] = get_string('must_be_integer');
		
		return $errors;
	}
}

class position_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		
		//$mform->addElement('header', 'headergradetemplate', 'country');
		$mform->addElement('text', 'position_code', 'Code', array('size' => 30)); // Add elements to your form
		$mform->addRule('position_code', 'Grade Scheme Cannot be empty', 'required', '', 'client', false, false);
		$mform->addElement('text', 'position', 'Position', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'description', 'Description', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'max_workload', 'Max workload', array('size' => 30)); // Add elements to your form		
        $mform->addElement('textarea', 'job_description', get_string("Job Description", "introtext"), 'wrap="virtual" rows="10" cols="80"');

        $position_type = jra_lookup_position_type();
        $mform->addElement('select', 'position_type', 'Type',$position_type ,$attributes); // Add elements to your form

		$position_category = jra_lookup_position_category();
			
	    $mform->addElement('select', 'position_category', 'Category', $position_category, $attributes);

        $country = jra_lookup_country();
        $mform->addElement('select', 'country', 'country',$country);
				
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}


class employee_position_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		
		$employee_id = jra_lookup_employee();
		$mform->addElement('select', 'employee_id', 'Employee ID',$employee_id  ,$attributes);  // Add elements to your form
		$mform->addRule('employee_id', 'Employee ID Cannot be empty', 'required', '', 'client', false, false);
		$position_id = jra_lookup_position();
		$mform->addElement('select', 'position_id', ' Position Code', $position_id); // Add elements to your form
		$mform->addRule('position_id', 'Position Code Cannot be empty', 'required', '', 'client', false, false);
		$mform->addElement('text', 'max_workload', 'max_workload', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'appointment_description', 'Description', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'acad_org', 'acad_org', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'eff_date', 'eff_date', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'eff_seq', 'eff_seq', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'action_date', 'action_date', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'action_user', 'action_user', array('size' => 30)); // Add elements to your form


		 $appointment_type = jra_lookup_position_type();
        $mform->addElement('select', 'appointment_type', 'appointment_type',$appointment_type ,$attributes); // Add elements to your form

        $appointment_status = jra_lookup_appointment_status();
        $mform->addElement('select', 'appointment_status', 'appointment_status',$appointment_status ,$attributes); // Add elements to your form

		$is_operation = jra_lookup_yes_no();
			
	    $mform->addElement('select', 'is_operation', 'is_operation', $is_operation, $attributes);	

	    $is_primary = jra_lookup_yes_no();
			
	    $mform->addElement('select', 'is_primary', 'is_primary', $is_primary, $attributes);

        $country = jra_lookup_country();
        $mform->addElement('select', 'country', 'country',$country);

        $campus = jra_lookup_campus();
        $mform->addElement('select', 'campus', 'Campus', $campus); // Add elements to your form
				
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