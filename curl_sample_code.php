<?php
/*
	This is the sample code to retrieve data from Timetable Plus web output using our Web Service API
	In general, the process to retrieve data from Timetable Plus web output has 2 steps.
	1. Obtain an authentication token from our web service server.
	2. Call the function to retrieve data by passing certain parameters
	
	The return data is in JSON format.
	
	We recommend to use CURL to execute the webservice. In your PHP extension, make sure you enable CURL.
	
	Below is the sample code from start to finish.

*/

set_time_limit(0);
	
$url_root = 'https://www.timetableplus.com'; //do not change this one
$username = 'rafiq'; //any unirazak account to log in to timetable plus
$password = 'yiccis123*'; //password for the user
// (To change password, go to https://www.timetableplus.com/tplusweb. In the log in page, click forgotten your user name 
// or password. Then enter your emaile to reset your password)
/////OBTAIN A TOKEN///////////////////
$token_url = $url_root . '/tplusweb/login/token.php?username='.$username.'&password='.$password.'&service=tplus';

//create a curl object
$curl = curl_init(); //initialize a curl object
curl_setopt_array($curl, array(
  CURLOPT_URL => $token_url, //token url
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
	"cache-control: no-cache"
  ),
));

$response = curl_exec($curl); //execute
$err = curl_error($curl); 
if ($err) //print out if there is any error in execution
{
  echo "cURL Error #:" . $err;
  die;
}
//if no error
$result = json_decode($response); //result in JSON format. Decode it
print_object($result); //see the content of the decoded result

if(!isset($result->token)) //authentication failed
{
	echo $result->error;
	die;
}

/////TOKEN OBTAINED SUCCESSFULLY////////////////////
/////CALL THE WEB SERVICE FUNCTION//////////////////

$token = $result->token; //get the token

//next, construct the URL to execute the web service function

/***********************************************
PARAMETERS FOR CLASS
************************************************/
//first, create an object containing all the parameters and encode the object into JSON format
$obj = new stdClass();
//values that are fixed for UCTS
$obj->module = 'tpclass'; //must be tpclass
$obj->shortname = 'UNIRAZAK_Class'; //fix. Must match the short name of the module
$obj->campus = 'UNIRAZAK'; //this is fix		
$obj->user_name = $username; //same as the account to obtain the token. Only user name required. No password 
//end of fixed value
$obj->semester = '072019'; //semester must match the semester defined in Timetable System
$obj->course = 'ACC0004'; //enter the course code where the timetable result is to be retrieved. Capitalize it
/***********************************************
END OF PARAMETERS FOR CLASS
************************************************/

/***********************************************
PARAMETERS FOR EXAM
************************************************/
$obj = new stdClass();
//values that are fixed for UCTS
$obj->module = 'tpexam'; //must be tpclass
$obj->shortname = 'UNIRAZAK_Exam'; //fix. Must match the short name of the module
$obj->campus = 'UNIRAZAK'; //this is fix		
$obj->user_name = $username; //same as the account to obtain the token. Only user name required. No password 
//end of fixed value
$obj->table = 'exam_section'; //exam_section for course, exam_student for student and exam_proctor for invigilator
$obj->semester = 'MARCH 2019'; //semester must match the semester defined in Timetable System
$obj->course = 'ROB3263'; //enter the course code where the timetable result is to be retrieved. Capitalize it. Empty string to retrieve all for exam_section
$obj->student = ''; //student id. Compulsory if use exam_student
$obj->proctor = ''; //Invigilator code. Empty string to retrieve all

//Note:
//for exam_student, either one or both of the parameters must exist $obj->course or $obj->student_id.
/***********************************************
END OF PARAMETERS FOR EXAM
************************************************/

$data = json_encode($obj); //encode the data as json

//keep everything in one line. Do not use carriage return, do not leave any blank space. Don't forget the urlencode
$url = $url_root . '/tplusweb/webservice/rest/server.php?wstoken='.$token. '&wsfunction=local_tplus_get_data_client&moodlewsrestformat=json&data=' . urlencode($data);

//set the curl object to the new url
curl_setopt_array($curl, array(
  CURLOPT_URL => $url, //token url
));

$response = curl_exec($curl); //execute
$err = curl_error($curl); 
if ($err) //print out if there is any error in execution
{
  echo "cURL Error #:" . $err;
  die;
}

//if no error
$result = json_decode($response); //result in JSON format store in an object property call message
if(isset($result->message))
{
	$final_result = json_decode($result->message); //decode again the message
	print_object($final_result); //see the content of the final result
}
else
	echo 'No result';
curl_close($curl); //close curl

//helper function to dump the result
//function print_object($object)
//{
//	echo '<pre>' . htmlspecialchars(print_r($object,true)) . '</pre>';
//} 

/*
The final result is an object with 2 properties. 
[status] => 1 indicates the operation is successful
[result] => is an array of schedules. You can obtain the schedule by:
$schedule = $final_result->result;

stdClass Object
(
    [status] => 1
    [result] => Array
        (
            [0] => stdClass Object
                (
                    [course_code] => CSS 3112
                    [course_name] => Introduction to Programming
					
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
definition of fields (the self explanatory will be ignored
	[course_code] => EEE 3943
	[course_name] => Integrated Design Project
	[section_code] => GROUP 01 LABORATORY 01 LECTURER 12			//ignore this one
	[subgroup] => GROUP01											//group of student
	[class_type] => LABORATORY
	[class_num] => 1												//ignore this one as it is just a running number
	[class_size] => 30
	[class_duration] => 180											//duration is in minute
	[class_duration_week] => 0										//ignore this one
	[section_color] => 0											//ignore this one
	[room_type] => -Any Type-										//Lecture Hall, Lab, Gym, etc
	[specific_room] => 
	[room_group] => BLOCK 2 (BEE)									//building
	[batch] => 														//batch of student
	[merge_group] => 												//ignore this one
	[same_time_group] => 											//ignore this one
	[workload_weight] => 1.00										//ignore this one
	[lecturer_code] => A.RAZAK BIN YAACOB							//only use this field as, lecturer_code = lecturer name
	[lecturer_name] => A.RAZAK BIN YAACOB							//ignore this one
	[lecturer_idnumber] => 237										//field for employee id
	[alias] => 														//ignore this one
	[idnumber] => 													//ignore this one
	[dept_code] => BACHELOR OF ELECTRICAL ENGINEERING IN POWER		//department or program
	[room_code] => ELECTRICAL LAB 2									//venue
	[day_text] => Monday											//day of class
	[start_time] => 720												//start time in minutes from 12.00am (refer below)
	[end_time] => 900												//end time in minutes from 12.00am (refer below)
	
	/////////////////////////////////////////////////
	NOTE:
	1. for start and end time, it is recorded as minutes from 12.00 am. So to obtain the actual time, just divide by 60.
	Eg, start_time = 720 => (720 / 60) = 12.00pm
	
	2. If 2 or more lecturers are teaching a same course at the same time (team teaching), the result will be exported as separate row
*/
?>