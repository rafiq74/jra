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
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php';
require_once '../../lib/sis_lookup_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/admin/setting/replicate_institute_data.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'system' => ''
); //super admin role only
sis_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

sis_set_session('sis_home_tab', 'system');
$PAGE->navbar->add(get_string('system', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'system')));
$PAGE->navbar->add(get_string('bulk_action', 'local_sis'), new moodle_url('index.php'));


if($_POST['confirmadd'])
{
	$qs = $_POST;	
	unset($qs['confirmadd']);
	unset($qs['sesskey']);
	if(isset($qs['institute']) && isset($qs['institute_target']))
	{
		set_time_limit(0);
		$institute = $qs['institute'];
		$institute_target = $qs['institute_target'];
		if($institute != $institute_target) //must be not the same institute
		{
			//perform the replication
			$tables = sis_query_get_sis_tables();
			foreach($tables as $table_name => $table)
			{
				$records = $DB->get_records($table_name, array('institute' => $institute)); //get the records from source table
				$arr = array();
				foreach($records as $r)
				{
					$r->id = ''; //remove the id
					$r->institute = $institute_target; //change the institute
					$arr[] = $r;
				}
//				$DB->insert_records($table_name, $arr); //bulk insert it as new institute data
			}
			
			$msg = sis_ui_alert('Data for the institute has been replicated to the target institute', 'success', '', true, true);
			sis_ui_set_flash_message($msg, 'replicate_institute_data');
		}
		else
		{
			$msg = sis_ui_alert('The source and target institute must be different', 'danger', '', true, true);
			sis_ui_set_flash_message($msg, 'replicate_institute_data');
		}
	}
	else
	{
		$msg = sis_ui_alert('No institute has been selected', 'danger', '', true, true);
		sis_ui_set_flash_message($msg, 'replicate_institute_data');
	}
	$redirect_url = new moodle_url('/local/sis/admin/bulk_action/replication.php', $qs);	
	redirect($redirect_url);
}

echo $OUTPUT->header();
//content code starts here
sis_ui_page_title('Replicate Data from One Institute to Another Institute');
$currenttab = 'replication'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

$cancel_url = new moodle_url('/local/sis/admin/bulk_action/replication.php', $urlparams);	
$confirm_url = array_merge(array('confirmadd'=>'yes', 'sesskey'=>sesskey()), $urlparams);
$yesurl = new moodle_url('/local/sis/admin/bulk_action/replicate_institute_data.php', $confirm_url);
echo $OUTPUT->confirm('Are you sure you would like to replicate all the data for the selected institute to the target institute?', $yesurl, $cancel_url);


echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/admin/bulk_action/bulk_action.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();