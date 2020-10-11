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
require_once '../../lib/jra_output_lib.php';
require_once $CFG->libdir.'/formslib.php';

class applicant_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore!
 		$attributes = array();
		$admission_type = $this->_customdata['admission_type'];

		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'institute', jra_get_institute());

		$mform->addElement('text', 'first_name', get_string('first_name', 'local_jra'), array('size' => 50));
		$mform->addRule('first_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'father_name', get_string('father_name', 'local_jra'), array('size' => 50));
		$mform->addRule('father_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'grandfather_name', get_string('grandfather_name', 'local_jra'), array('size' => 50));
		$mform->addRule('grandfather_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'family_name', get_string('family_name', 'local_jra'), array('size' => 50));
		$mform->addRule('family_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'first_name_a', get_string('first_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addRule('first_name_a', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'father_name_a', get_string('father_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addRule('father_name_a', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'grandfather_name_a', get_string('grandfather_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addRule('grandfather_name_a', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'family_name_a', get_string('family_name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', array('size' => 50));
		$mform->addRule('family_name_a', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'national_id', jra_get_string(['national_id']), array('size' => 20, 'maxlength' => 10));
		$mform->addRule('national_id', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('national_id', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);
		$d = new stdClass();
		$d->format = 10;
		$mform->addRule('national_id', get_string('err_maxlength', 'form', $d), 'maxlength', 10, 'client', false, false);
		$mform->addRule('national_id', get_string('err_minlength', 'form', $d), 'minlength', 10, 'client', false, false);
		$id_type = jra_lookup_get_list('personal_info', 'id_type', '', true);
		$mform->addElement('select', 'id_type', get_string('id_type', 'local_jra'), $id_type);
		$countries = jra_lookup_countries();
		$mform->addElement('select', 'nationality', get_string('nationality', 'local_jra'), $countries);
		$mform->setDefault('nationality', 'SA');

		if($admission_type == 'crtp')
		{
			$gender = jra_lookup_gender();
			$mform->addElement('select', 'gender', get_string('gender', 'local_jra'), $gender);
		}
		else
			$mform->addElement('hidden', 'gender', 'M');
//		$mform->addElement('date_selector', 'dob', get_string('date_of_birth', 'local_jra'));

		$add_array=array();
		$h_day = jra_lookup_get_num_list(1, 30);
		$h_month = jra_lookup_get_hijrah_month(); //always arabic
		$h_year = array();
		for($i = 1440; $i >= 1415; $i--)
			$h_year[$i] = $i;
		$add_array[] =& $mform->createElement('select', 'h_y', 'h_y', $h_year, $attributes);
		$add_array[] =& $mform->createElement('select', 'h_m', 'h_m', $h_month, $attributes);
		$add_array[] =& $mform->createElement('select', 'h_d', 'h_d', $h_day, $attributes);
		$mform->addGroup($add_array, 'group1', get_string('date_of_birth', 'local_jra'), array('&nbsp;&nbsp;'), false);
		$mform->addRule('group1', get_string('err_required', 'form'), 'required', '', 'client', false, false);


		$marital_status = jra_lookup_marital_status();
    $mform->addElement('select', 'marital_status', get_string('marital_status', 'local_jra') ,$marital_status);
		$blood_groups = jra_lookup_blood_type(jra_get_string(['select', 'blood_group']));
		$mform->addElement('select', 'blood_type', get_string('blood_group', 'local_jra'), $blood_groups);


		$this->add_action_buttons($cancel=true);
	}

	//Custom validation should be added here
	function validation($data, $files) {

		return array();
	}
}

class applicant_contact_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		global $DB;
		$mform = $this->_form; // Don't forget the underscore!
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'institute', jra_get_institute());
		$mform->addElement('hidden', 'address_country', 'SA');

		$mform->addElement('text', 'address1', get_string('address1', 'local_jra'), array('size' => 70));
		$mform->addRule('address1', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'address2', get_string('address2', 'local_jra'), array('size' => 70));

//		$mform->addElement('text', 'address_state', get_string('state', 'local_jra'), array('size' => 20));
//		$mform->addRule('address_state', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$cities = jra_lookup_city('', jra_get_string(['select', 'city']));
		$mform->addElement('select', 'address_city', jra_get_string(['city']), $cities);
		$mform->addRule('address_city', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('text', 'address_postcode', get_string('postcode', 'local_jra'), array('size' => 10));

		$d = new stdClass();
		$d->format = 10;

		$mform->addElement('text', 'phone_mobile', get_string('phone_mobile', 'local_jra'), array('size' => 25, 'placeholder' => get_string('phone_example', 'local_jra')));
		$mform->addRule('phone_mobile', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('phone_mobile', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);
		$mform->addRule('phone_mobile', get_string('err_maxlength', 'form', $d), 'maxlength', 10, 'client', false, false);
		$mform->addRule('phone_mobile', get_string('err_minlength', 'form', $d), 'minlength', 10, 'client', false, false);
		$mform->addElement('text', 'phone_home', get_string('phone_home', 'local_jra'), array('size' => 25, 'placeholder' => get_string('phone_example', 'local_jra')));

		$mform->addElement('text', 'contact_name', jra_get_string(['guardian_name']), array('size' => 70));
		$mform->addRule('contact_name', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$kindship = jra_lookup_kindship();
		$mform->addElement('select', 'contact_relationship', jra_get_string(['guardian_relationship']), $kindship);
		$mform->addElement('text', 'contact_mobile', jra_get_string(['guardian_mobile']), array('size' => 25, 'placeholder' => get_string('phone_example', 'local_jra')));
		$mform->addRule('contact_mobile', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('contact_mobile', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);
		$mform->addRule('contact_mobile', get_string('err_maxlength', 'form', $d), 'maxlength', 10, 'client', false, false);
		$mform->addRule('contact_mobile', get_string('err_minlength', 'form', $d), 'minlength', 10, 'client', false, false);

		$this->add_action_buttons($cancel=true);
	}

	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class applicant_academic_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		global $DB;
		$mform = $this->_form; // Don't forget the underscore!
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');
		$semester = $this->_customdata['semester'];
		if($semester->admission_type == 'regular') 
		{
			$mform->addElement('text', 'secondary', get_string('secondary_school_result', 'local_jra'), array('size' => 15));
			$mform->addRule('secondary', get_string('err_required', 'form'), 'required', '', 'client', false, false);
			$mform->addRule('secondary', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);

			$mform->addElement('text', 'tahseli', get_string('tahseli', 'local_jra'), array('size' => 15));
			$mform->addRule('tahseli', get_string('err_required', 'form'), 'required', '', 'client', false, false);
			$mform->addRule('tahseli', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);

			$mform->addElement('text', 'qudorat', get_string('qudorat', 'local_jra'), array('size' => 15));
			$mform->addRule('qudorat', get_string('err_required', 'form'), 'required', '', 'client', false, false);
			$mform->addRule('qudorat', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);
		}
		else 
		{
			$graduated = jra_lookup_university();
			$mform->addElement('select', 'graduated_from', get_string('graduate_from', 'local_jra'), $graduated);
			$mform->addRule('graduated_from', get_string('err_required', 'form'), 'required', '', 'client', false, false);

			$h_year = array();
			$now = time();
			$hDate = jra_to_hijrah(date('d-M-Y', $now));
			$max_year = date('Y', $now);
			$year_arr = explode('/', $hDate);
			$max_year = $year_arr[2];
			$min_year = $max_year - 10;
			for($i = $max_year; $i >= $min_year; $i--)
				$h_year[$i] = $i;
				
			$add_array[] =& $mform->createElement('select', 'graduated_year', 'h_y', $h_year, $attributes);
			$mform->addGroup($add_array, 'graduated_year', get_string('year_graduation', 'local_jra'), array(''),  false);
			$mform->addRule('group1', get_string('err_required', 'form'), 'required', '', 'client', false, false);
			$mform->addRule('graduated_year', get_string('err_required', 'form'), 'required', '', 'client', false, false);

			$majors = jra_lookup_major();
			$mform->addElement('select', 'graduated_major', get_string('major', 'local_jra'), $majors);
			$mform->addRule('graduated_major', get_string('err_required', 'form'), 'required', '', 'client', false, false);

			$mform->addElement('text', 'graduated_gpa', get_string('cgpa', 'local_jra'), array('size' => 5));
			$mform->addRule('graduated_gpa', get_string('err_required', 'form'), 'required', '', 'client', false, false);
			$mform->addRule('graduated_gpa', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);

		}

		$this->add_action_buttons($cancel=true);
	}

	//Custom validation should be added here
	function validation($data, $files) {
        $errors = parent::validation($data, $files);

		$a = new stdClass();
		$a->min = 0;
		$a->max = 100;

        if ($data['secondary'] < 0 || $data['secondary'] > 100) {
            $errors['secondary'] = get_string('secondary_school_result', 'local_jra') . ' ' . get_string('in_between_value', 'local_jra', $a);
            return $errors;
        }
        if ($data['tahseli'] < 0 || $data['tahseli'] > 100) {
            $errors['tahseli'] = get_string('tahseli', 'local_jra') . ' ' . get_string('in_between_value', 'local_jra', $a);
            return $errors;
        }
        if ($data['qudorat'] < 0 || $data['qudorat'] > 100) {
            $errors['qudorat'] = get_string('qudorat', 'local_jra') . ' ' . get_string('in_between_value', 'local_jra', $a);
            return $errors;
        }
        if ($data['graduated_gpa'] < 0 || $data['graduated_gpa'] > 4) 
		{
			$a->max = 4;
            $errors['graduated_gpa'] = get_string('cgpa', 'local_jra') . ' ' . get_string('in_between_value', 'local_jra', $a);
            return $errors;
        }
		else
		{
			
		}
		return $errors;
	}
}

class applicant_academic_form_crtp extends moodleform
{
	//Add elements to form
	public function definition()
	{
		global $DB;
		$mform = $this->_form; // Don't forget the underscore!
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');

		$mform->addElement('text', 'secondary', get_string('secondary_school_result', 'local_jra'), array('size' => 15));
		$mform->addRule('secondary', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('secondary', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);

		$mform->addElement('text', 'tahseli', get_string('tahseli', 'local_jra'), array('size' => 15));
		$mform->addRule('tahseli', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('tahseli', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);

		$mform->addElement('text', 'qudorat', get_string('qudorat', 'local_jra'), array('size' => 15));
		$mform->addRule('qudorat', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('qudorat', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);


		$graduated = jra_lookup_marital_status();
		$mform->addElement('select', 'graduate_from', get_string('graduate_from', 'local_jra'), $graduated);
		$mform->addRule('tahseli', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('tahseli', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);


		$year_graduations = jra_lookup_marital_status();
		$mform->addElement('select', 'year_graduation', get_string('year_graduation', 'local_jra'), $year_graduations);
		$mform->addRule('tahseli', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('tahseli', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);


		$majors = jra_lookup_marital_status();
		$mform->addElement('select', 'major_grad', get_string('major', 'local_jra'), $majors);
		$mform->addRule('tahseli', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('tahseli', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);



		$mform->addElement('text', 'graduate_gpa', get_string('gpa', 'local_jra'), array('size' => 15));
		$mform->addRule('secondary', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('secondary', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);


		$this->add_action_buttons($cancel=true);
	}

	//Custom validation should be added here
	function validation($data, $files) {
        $errors = parent::validation($data, $files);

		$a = new stdClass();
		$a->min = 0;
		$a->max = 100;
        if ($data['secondary'] < 0 || $data['secondary'] > 100) {
            $errors['secondary'] = get_string('secondary_school_result', 'local_jra') . ' ' . get_string('in_between_value', 'local_jra', $a);
            return $errors;
        }
        if ($data['tahseli'] < 0 || $data['tahseli'] > 100) {
            $errors['tahseli'] = get_string('tahseli', 'local_jra') . ' ' . get_string('in_between_value', 'local_jra', $a);
            return $errors;
        }
        if ($data['qudorat'] < 0 || $data['qudorat'] > 100) {
            $errors['qudorat'] = get_string('qudorat', 'local_jra') . ' ' . get_string('in_between_value', 'local_jra', $a);
            return $errors;
        }


		return $errors;
	}
}

class document_upload_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		$module = jra_get_session('jra_document_upload_module');
		$mform = $this->_form; // Don't forget the underscore!
		$attributes = array();
		$institute = jra_get_institute();
		$semester = $this->_customdata['semester'];
		$mform->addElement('hidden', 'id', '');
		$mform->addElement('hidden', 'institute', $institute);
//		$mform->addElement('text', 'title', get_string('title', 'local_cur'), array('size' => 60));
		if($semester->admission_type == 'crtp')
			$type_list = jra_lookup_document_type_crtp();
		else
			$type_list = jra_lookup_document_type();
		$mform->addElement('select', 'module', jra_get_string(['upload', 'document', 'type']), $type_list, $attributes);
		$mform->setDefault('module', $module);
		$mform->addElement('static', 'must_be', '', '(' . get_string('must_be_image_or_pdf', 'local_jra') . ')');
		$max_size = 1024 * 10000;
		$mform->addElement('filepicker', 'userfile', get_string('file'), null, array('maxbytes' => $max_size, 'accepted_types' => jra_file_accepted_document_type()));

		$this->add_action_buttons($cancel=true);
	}

	function validation($data, $files)
	{
		return array();
	}
}




class applicant_finance_form extends moodleform
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

class filter_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		global $DB;
		$mform = $this->_form; // Don't forget the underscore!
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');

		$mform->addElement('text', 'secondary_weight', jra_get_string(['secondary_school_result', 'weight']), array('size' => 15));
		$mform->addRule('secondary_weight', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('secondary_weight', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);

		$mform->addElement('text', 'tahseli_weight', jra_get_string(['tahseli', 'weight']), array('size' => 15));
		$mform->addRule('tahseli_weight', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('tahseli_weight', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);

		$mform->addElement('text', 'qudorat_weight', jra_get_string(['qudorat', 'weight']), array('size' => 15));
		$mform->addRule('qudorat_weight', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addRule('qudorat_weight', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);

		$mform->addElement('text', 'min_aggregate', jra_get_string(['minimum', 'aggregate']), array('size' => 15));
		$mform->addRule('min_aggregate', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);
		$mform->addElement('static', 'min_aggregate_desc', '', '(' . get_string('leave_empty_to_ignore_filter', 'local_jra') . ')');

		$mform->addElement('text', 'num_applicant', jra_get_string(['number', 'of', 'applicants']), array('size' => 15));
		$mform->addRule('num_applicant', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);
		$mform->addElement('static', 'num_applicant_desc', '', '(' . get_string('leave_empty_to_ignore_filter', 'local_jra') . ')');

		$city_list = jra_lookup_city_applicant(get_string('all', 'local_jra'));
		$mform->addElement('select', 'city_filter', jra_get_string(['city']), $city_list, $attributes);


	}

	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class admit_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		global $DB;
		$mform = $this->_form; // Don't forget the underscore!
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');

		$radioarray=array();
		$radioarray[] = $mform->createElement('radio', 'admit_status', '', get_string('yes'), 1, $attributes);
		$radioarray[] = $mform->createElement('radio', 'admit_status', '', get_string('no'), 0, $attributes);
		$mform->addGroup($radioarray, 'radioar', get_string('admit_trainee', 'local_jra'), array(' '), false);

		$mform->addElement('text', 'placement_test_score', jra_get_string(['placement_test_score']), array('size' => 15));
		$mform->addRule('placement_test_score', get_string('err_numeric', 'form'), 'numeric', '', 'client', false, false);

	}

	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

class file_upload_form extends moodleform
{
	//Add elements to form
	public function definition()
	{
		$module = '';
		$mform = $this->_form; // Don't forget the underscore!
		$attributes = array();
		$institute = jra_get_institute();
		$mform->addElement('hidden', 'id', $id);
		$mform->addElement('hidden', 'institute', $institute);

		$docType = $this->accepted_document_type();
		$typeStr = implode(', ', $docType);
		$max_size = 1024 * 10000;
		$mform->addElement('filepicker', 'userfile', get_string('file'), null, array('maxbytes' => $max_size, 'accepted_types' => $docType));
		$mform->addElement('static', 'must_be', '', 'Must be (' . $typeStr . ')');

		//required by the csv processor
		$mform->addElement('hidden', 'delimiter_name', 'comma');
		$mform->addElement('hidden', 'encoding', 'UTF-8');

		$this->add_action_buttons($cancel=true);
	}

	function validation($data, $files)
	{
		return array();
	}

	function accepted_document_type()
	{
		$arr = array();
		$arr[] = 'csv';
//		$arr[] = 'txt';
		return $arr;
	}

}

?>
