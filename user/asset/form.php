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

// normally should be in another file named xxxxx_form.php
class demo_hierselect_form extends moodleform {
    function definition() {
        global $CFG;


        $mform = & $this->_form;

        // add hierselect element
        // level 0 array
        $letters = array();
        $letters[0] = 'A';
        $letters[1] = 'B';
        $letters[2] = 'C';

        // level 1 array
        $words = array();
        $words[0][0] = 'Aardvark';
        $words[0][1] = 'Apple';
        $words[0][2] = 'Armadillo';
        $words[1][0] = 'Ball';
        $words[1][1] = 'Banana';
        $words[2][0] = 'Cat';
        $words[2][1] = 'Chicken';
        $words[2][2] = 'Can';
        $words[2][3] = 'Cow';

        $attribs = array('size' => '4'); // height of lists is 4 items 
        $hier = &$mform->addElement('hierselect', 'list', get_string('categories'), $attribs);
        $hier->setOptions(array($letters, $words));
        $mform->addRule('list', null, 'required');


        $this->add_action_buttons();
    }
}


class asset_form extends moodleform 
{
	//Add elements to form
	public function definition() 
	{
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		$mform->addElement('hidden', 'country', jra_get_country());	

		//keep the below line for ajax testing. Switch the id to ajax_output for the result to be dump here
//		$mform->addElement('static', 'description', 'AJAX Output', '<div id="ajax_output">AJAX Output here</div>');

		$mform->addElement('select', 'category', get_string('category', 'local_jra'), jra_lookup_asset_category(jra_get_string(['select', 'category'])), array(
			'onchange' => '
				 var category=$("#id_category").val();
				 $.ajax({
					type: "post",
					url: "subcategory_action.php",
					data: "category=" + category,
					success: function(data){
						  $("#id_subcategory").html(data);
					}
				 });
			'
		));
		$mform->addRule('category', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		//subcategory is ajax dependent. If there is session, use it.
		$subcategory = jra_get_session('jra_asset_subcategory_option');
		if(!is_array($subcategory))
			$subcategory = array('' => get_string('select_subcategory', 'local_jra'));
		$mform->addElement('select', 'subcategory', jra_get_string(['asset', 'type']), $subcategory);
		$mform->addRule('subcategory', get_string('err_required', 'form'), 'required', '', 'client', false, false);

		$mform->addElement('text', 'title', get_string('title', 'local_jra'), array('size' => 80));
		$mform->addRule('title', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		
		$mform->addElement('textarea', 'description', get_string('description', 'local_jra'), 'wrap="virtual" rows="5" cols="77"');

		$location = jra_lookup_state(jra_get_string(['select', 'location']));
		$mform->addElement('select', 'location', get_string('location', 'local_jra'), $location, array(
			'onchange' => '
				 var location=$("#id_location").val();
				 $.ajax({
					type: "post",
					url: "location_action.php",
					data: "location=" + location,
					success: function(data){
						  $("#id_area").html(data);
					}
				 });
			'																									   
		));
		$area = jra_get_session('jra_asset_area_option');
		if(!is_array($area))
			$area = array('' => get_string('select_area', 'local_jra'));
		$mform->addRule('location', get_string('err_required', 'form'), 'required', '', 'client', false, false);
		$mform->addElement('select', 'area', get_string('area', 'local_jra'), $area);
		$mform->addRule('area', get_string('err_required', 'form'), 'required', '', 'client', false, false);

		
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