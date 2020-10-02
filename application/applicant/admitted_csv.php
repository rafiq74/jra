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
 * This page is provided for compatability and redirects the user to the default grade report
 *
 * @package   core_grades
 * @copyright 2005 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copycenter/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../lib/jra_lib.php'; 
require_once '../../lib/jra_ui_lib.php';
require_once '../../lib/jra_output_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once '../../lib/jra_app_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../lib/jra_system_lib.php'; 
require_once 'lib.php'; //local library
require_once $CFG->libdir.'/phpexcel/PHPExcel.php'; //beware of case sensitive as in linux server, file name is case sensitive

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/admitted_csv.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admission',
	'subrole' => 'all',
);

jra_access_control($access_rules);

//content code starts here

$semester = $DB->get_record('si_semester', array('semester' => jra_get_semester()));
if(!$semester)
	throw new moodle_exception('Error!!! No semester defined');
	
$per_page_list = jra_lookup_per_page();
$city_list = jra_lookup_city_applicant(get_string('all', 'local_jra'));

$city = jra_ui_filter_value('city', '', 'admittedlist', $city_list, true);

$status_filter = " and (acceptance = '1' or acceptance = '5')";

$city_filter = '';
if($city != '')
	$city_filter = " and address_city = '$city'";


$params = jra_get_session('si_applicant_admitted_return_params');
$search = '';
$field = '';
if(is_array($params))
{
	$search = isset($params['search']) ? $params['search'] : '';
	$field = isset($params['field']) ? $params['field'] : '';
}

$like = jra_query_like_query($search, $field, $search_params);

$sql = "select appid, semester, national_id, id_type, nationality, first_name, father_name, grandfather_name, family_name, fullname, first_name_a, father_name_a, grandfather_name_a, family_name_a, fullname_a, gender, dob_hijri, marital_status, blood_type, tahseli, qudorat, secondary, aggregation, address1, address2, address_state, address_city, phone_mobile, email, contact_name, contact_relationship, contact_mobile, placement_test_score, institute from v_si_applicant";


$conditionText = " institute = '" . jra_get_institute() . "' and semester = '" . $semester->semester . "' and deleted = 0 and status = 11 and admit_status = '1' $status_filter $city_filter " . $like;

$sort = "aggregation desc";

$records = $DB->get_records_sql($sql . ' where ' . $conditionText . ' order by ' . $sort, $search_params);

$marital_status = jra_lookup_marital_status();
$approval_status_list = jra_lookup_admission_status(get_string('all', 'local_jra'));

//preprocess it into make it in human readable format
$data = array();
foreach($records as $r)
{
	$r->aggregation = number_format($r->aggregation, 2);
	$data[] = $r;
}
//$data = jra_student_operation_master_detail_status($records);
//print_object($data);
//die;
if(count($data) > 0)
{
	//construct the file name
	$filename = jra_system_report_name('applicant_list_' . $semester_text);
	$objPHPExcel = new PHPExcel;
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
	//$numberFormat = '#,#0.##;[Red]-#,#0.##';
		
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");	
	
	//we use this for CSV output (and don't forget the MIME type at the bottom to set to text)
	$objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
	$objWriter->setDelimiter(',');
	$objWriter->setEnclosure('');
	$objWriter->setLineEnding("\r\n");
	$objWriter->setSheetIndex(0);
	
	
	
	$objSheet = $objPHPExcel->getActiveSheet();
	$objSheet->setTitle('Sheet1');
	$row = 1; //in phpexcel, row starts with index 1
	$count = 1;
	foreach($data as $rec) //for a row
	{
		$col = 0; //in phpexcel, column starts with index 0
		foreach($rec as $key => $value) //for column
		{
			if($row == 1) //first row, write the header
			{
				if($key == 'id')
				{
					$objSheet->setCellValueByColumnAndRow($col, $row, 'No');
					$objSheet->setCellValueByColumnAndRow($col, $row + 1, $count);
				}
				else
				{
					$objSheet->setCellValueByColumnAndRow($col, $row, $key);
					$objSheet->setCellValueByColumnAndRow($col, $row + 1, $value);
				}
			}
			else
			{
				if($key == 'id')
					$objSheet->setCellValueByColumnAndRow($col, $row, $count);
				else
					$objSheet->setCellValueByColumnAndRow($col, $row, $value);
			}
			$col++;
		}
		if($row == 1) //if first row, we jump to 3rd row because first row also includes header
			$row = 3;
		else
			$row++;
		$count++;
	}
	
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
	header('Cache-Control: max-age=0');
	header('Content-type: text/csv'); //for CSV, don't forget this line
	$objWriter->save('php://output');
	
	exit;

}
else
	jra_ui_alert('no data', 'danger');
