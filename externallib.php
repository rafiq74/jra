<?php

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
 * External Web Service Template
 *
 * @package    localtpclass
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot.'/local/jra/lib/jra_lib.php'); //jra global library
require_once($CFG->dirroot.'/local/jra/lib/jra_db_lib.php'); //jra class database libraries

class local_jra_external extends external_api 
{

///////// Post Data Function ///////////////////////

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function jra_post_data_parameters() {
        return new external_function_parameters(
                array('data' => new external_value(PARAM_TEXT, 'A valid jra Class post data object', VALUE_DEFAULT, ''),
			)
        );
    }

    /**
     * Accept data posted from jra
     * @accept data posted from jra
     */
    public static function jra_post_data($data) {
        global $USER;
 
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::jra_post_data_parameters(),
                array(
					'data' => $data,
				));

        //Context validation
        //OPTIONAL but in most web service it should present
//        $context = get_context_instance(CONTEXT_USER, $USER->id);
//        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
//        if (!has_capability('moodle/user:viewdetails', $context)) {
//            throw new moodle_exception('cannotviewprofile');
//        }
		
		//implement the function here
		$data = json_decode($_POST['data']); //decode the result. post data always received as a list. So result will be an array of object
		//do processing
		//for post, we received the data as an array. So get at least one object
		foreach($data as $obj)
			break;
		if(isset($obj))
			$validate = jra_validate_user($obj);
		else
			$validate = false;
		$return_message = array();
		if($validate)
		{
			$result = jra_db_post_data($data); //universal function to start the export process
			$return_message['status'] = '1'; //generic status field
			$return_message['result'] = $result; //if we put the result here, what ever received will be returned. Can be used for debug
		}
		else
		{
			$result = array();		
			$return_message['status'] = '0'; //generic status field
			$return_message['result'] = $result; //if we put the result here, what ever received will be returned. Can be used for debug
		}
		
		//if successful, return 1 as status, else return 0
		$return_message = json_encode($return_message); //encode it in json
		//return the result (always return in array pair as it will translate into JSON and mapped into class property : value pair
//		jra_print_object($return_message);
		return array('message' => $return_message);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function jra_post_data_returns() {
        return new external_single_structure(
                array(
					'message' => new external_value(PARAM_TEXT, 'The result of post_data function'),
					)
				);
    }

///////// End Of Post Data Function ///////////////////////

///////// Get Data Function ///////////////////////

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function jra_get_data_parameters() {
        return new external_function_parameters(
                array('data' => new external_value(PARAM_TEXT, 'A valid jra Class get data object', VALUE_DEFAULT, ''),
			)
        );
    }

    /**
     * Accept data get from jra
     * @accept data get from jra
     */
    public static function jra_get_data($data) {
        global $USER;
 
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::jra_get_data_parameters(),
                array(
					'data' => $data,
				));

        //Context validation
        //OPTIONAL but in most web service it should present
//        $context = get_context_instance(CONTEXT_USER, $USER->id);
//        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
//        if (!has_capability('moodle/user:viewdetails', $context)) {
//            throw new moodle_exception('cannotviewprofile');
//        }
		
		//implement the function here
		$data = json_decode($_POST['data']); //decode the result. post data always received as a list. So result will be an array of object
		//do processing
		
		$validate = jra_validate_user($data);
		$return_message = array();
		if($validate)
		{
			$result = jra_db_get_data($data); //universal function to start the export process
			$return_message['status'] = '1'; //generic status field
			$return_message['result'] = $result; //if we put the result here, what ever received will be returned. Can be used for debug
		}
		else
		{
			$result = array();		
			$return_message['status'] = '0'; //generic status field
			$return_message['result'] = $result; //if we put the result here, what ever received will be returned. Can be used for debug
		}
		
		//if successful, return 1 as status, else return 0
		$return_message = json_encode($return_message); //encode it in json
		//return the result (always return in array pair as it will translate into JSON and mapped into class property : value pair
//		jra_print_object($return_message);
		return array('message' => $return_message);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function jra_get_data_returns() {
        return new external_single_structure(
                array(
					'message' => new external_value(PARAM_TEXT, 'The result of get_data function'),
					)
				);
    }

///////// End Of Get Data Function ///////////////////////

}
