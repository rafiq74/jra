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


class plan_form extends moodleform 
{
	//Add elements to form
	public function definition()
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
		$attributes = array();
		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'country', jra_get_country());	
		$mform->addElement('date_selector', 'eff_date', get_string('effective_date', 'local_jra'));
		$mform->addElement('text', 'plan_code_temp', jra_get_string(['plan', 'code']), array('size' => 15));
		 //this is the actual plan code where the value will be copied in add_plan. We do this so we can disable plan_code_temp in editing
		$mform->addElement('hidden', 'plan_code', '');
//		$mform->addRule('code', get_string('code', 'local_jra') . ' ' . get_string('cannot_empty', 'local_jra'), 'required', '', 'server', false, false);
		$mform->addElement('text', 'title', jra_get_string(['plan', 'title']), array('size' => 65));
		$mform->addElement('text', 'remark', jra_get_string(['remark']), array('size' => 65));
//		$mform->addRule('course_num', get_string('num', 'local_jra') . ' ' . get_string('cannot_empty', 'local_jra'), 'required', '', 'server', false, false);

		$currencies = jra_lookup_currencies();
		$mform->addElement('select', 'currency', get_string('currency', 'local_jra'), $currencies, $attributes);
		$mform->setDefault('currency', 'MYR');
		
		$duration_denomination = jra_lookup_plan_interval();		
		$mform->addElement('select', 'duration_denomination', jra_get_string(['plan', 'interval']), $duration_denomination, $attributes);

		$num_list = jra_lookup_get_num_list(1, 12);
		
		$mform->addElement('static', 'description', get_string('cost', 'local_jra'), get_string('cost_message', 'local_jra'));
		$add_array=array();
		$add_array[] =& $mform->createElement('text', 'cost1', 'cost1', array('size' => 6));
		$add_array[] =& $mform->createElement('select', 'duration1', 'duration1', $num_list, $attributes);		
		$add_array[] =& $mform->createElement('text', 'message1', 'message1', array('size' => 30));
		$mform->addGroup($add_array, 'group1', jra_ui_space(5) . get_string('level', 'local_jra') . ' 1 (' . get_string('required', 'local_jra') . ')', array('&nbsp;&nbsp;' . get_string('interval', 'local_jra') . '&nbsp;&nbsp;', '&nbsp;&nbsp;' . get_string('message', 'local_jra') . '&nbsp;&nbsp;'), false);

		$add_array=array();
		$add_array[] =& $mform->createElement('text', 'cost2', 'cost2', array('size' => 6));
		$add_array[] =& $mform->createElement('select', 'duration2', 'duration2', $num_list, $attributes);		
		$add_array[] =& $mform->createElement('text', 'message2', 'message2', array('size' => 30));
		$mform->addGroup($add_array, 'group2', jra_ui_space(5) . get_string('level', 'local_jra') . ' 2 (' . get_string('blank_to_disable', 'local_jra') . ')', array('&nbsp;&nbsp;' . get_string('interval', 'local_jra') . '&nbsp;&nbsp;', '&nbsp;&nbsp;' . get_string('message', 'local_jra') . '&nbsp;&nbsp;'), false);

		$add_array=array();
		$add_array[] =& $mform->createElement('text', 'cost3', 'cost3', array('size' => 6));
		$add_array[] =& $mform->createElement('select', 'duration3', 'duration3', $num_list, $attributes);		
		$add_array[] =& $mform->createElement('text', 'message3', 'message3', array('size' => 30));
		$mform->addGroup($add_array, 'group3', jra_ui_space(5) . get_string('level', 'local_jra') . ' 3 (' . get_string('blank_to_disable', 'local_jra') . ')', array('&nbsp;&nbsp;' . get_string('interval', 'local_jra') . '&nbsp;&nbsp;', '&nbsp;&nbsp;' . get_string('message', 'local_jra') . '&nbsp;&nbsp;'), false);

		$add_array=array();
		$add_array[] =& $mform->createElement('text', 'cost4', 'cost4', array('size' => 6));
		$add_array[] =& $mform->createElement('select', 'duration4', 'duration4', $num_list, $attributes);		
		$add_array[] =& $mform->createElement('text', 'message4', 'message4', array('size' => 30));
		$mform->addGroup($add_array, 'group4', jra_ui_space(5) . get_string('level', 'local_jra') . ' 4 (' . get_string('blank_to_disable', 'local_jra') . ')', array('&nbsp;&nbsp;' . get_string('interval', 'local_jra') . '&nbsp;&nbsp;', '&nbsp;&nbsp;' . get_string('message', 'local_jra') . '&nbsp;&nbsp;'), false);

		$add_array=array();
		$add_array[] =& $mform->createElement('text', 'cost5', 'cost5', array('size' => 6));
		$add_array[] =& $mform->createElement('select', 'duration5', 'duration5', $num_list, $attributes);		
		$add_array[] =& $mform->createElement('text', 'message5', 'message5', array('size' => 30));
		$mform->addGroup($add_array, 'group5', jra_ui_space(5) . get_string('level', 'local_jra') . ' 5 (' . get_string('blank_to_disable', 'local_jra') . ')', array('&nbsp;&nbsp;' . get_string('interval', 'local_jra') . '&nbsp;&nbsp;', '&nbsp;&nbsp;' . get_string('message', 'local_jra') . '&nbsp;&nbsp;'), false);

		$is_active = jra_lookup_is_active();
		$mform->addElement('select', 'eff_status', get_string('status', 'local_jra'), $is_active, $attributes);
		$num_list = jra_lookup_get_num_list(1, 50);		
		$mform->addElement('select', 'sort_order', get_string('sort_order', 'local_jra'), $num_list, $attributes);

		$mform->addElement('text', 'icon', get_string('icon', 'local_jra') . ' (' . jra_ui_link('Font-Awesome icon code', 'https://fontawesome.com/v4.7.0/icons/', array('target' => '_blank')) . ')', array('size' => 25));
		$mform->addElement('filepicker', 'filename', get_string('picture', 'local_jra'), null,array('maxbytes' => $maxbytes, 'accepted_types' => '*'));

		$mform->addElement('checkbox', 'correct_history', get_string('correct_history', 'local_jra'), false,
			array('onClick' => '
				if($(this).is(":checked"))
					$("#id_submitbutton").prop( "disabled", false);
				else
					$("#id_submitbutton").prop( "disabled", true);				
			'));		
		
		$mform->disabledIf('submitbutton', 'id', 'neq', '');
		$mform->disabledIf('plan_code_temp', 'id', 'neq', '');
		$mform->hideIf('correct_history', 'id', 'eq', '');

		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'), array(
			'onclick' => '
				document.mform1.id_code.value = "x";
				document.mform1.id_course_num.value = "x";
				document.mform1.id_course_name.value = "x";
				document.mform1.is_cancel.value = 1;
			'
		));
		$buttonarray[] =& $mform->createElement('submit', 'saveasbutton', get_string('saveas', 'local_jra'));
		$mform->hideIf('saveasbutton', 'id', 'eq', '');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

//		$this->add_action_buttons($cancel=true);		
	}
	
	function validation($data, $files)
	{
		$error = array();
		if(!isset($data['cancel'])) //not cancel, validate
		{
	        $errors = parent::validation($data, $files);
			if(isset($data['plan_code_temp']))
			{
				if($data['plan_code_temp'] == '')
					$errors['plan_code_temp'] = get_string('err_required', 'form');
			}
			if($data['title'] == '')
	            $errors['title'] = get_string('err_required', 'form');
			if($data['cost1'] == '')
	            $errors['group1'] = get_string('err_required', 'form');
			else if(!preg_match('/^\d*\.?\d*$/', $data['cost1']))
	            $errors['group1'] = get_string('err_numeric', 'form');
			if($data['cost2'] != '')
			{
				if(!preg_match('/^\d*\.?\d*$/', $data['cost2']))
	            	$errors['group2'] = get_string('err_numeric', 'form');
			}
			if($data['cost3'] != '')
			{
				if(!preg_match('/^\d*\.?\d*$/', $data['cost3']))
	            	$errors['group3'] = get_string('err_numeric', 'form');
			}
			if($data['cost4'] != '')
			{
				if(!preg_match('/^\d*\.?\d*$/', $data['cost4']))
	            	$errors['group4'] = get_string('err_numeric', 'form');
			}
			if($data['cost5'] != '')
			{
				if(!preg_match('/^\d*\.?\d*$/', $data['cost5']))
	            	$errors['group5'] = get_string('err_numeric', 'form');
			}
		}
		return $errors;
		
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
	}
}

?>
