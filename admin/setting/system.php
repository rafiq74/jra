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
require_once '../../lib/jra_lookup_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once '../../lib/jra_system_lib.php';
require_once '../../lib/jra_exdb_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/setting/system.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'system',
);
jra_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('../index.php', array()));
$PAGE->navbar->add(get_string('system', 'local_jra') . ' ' , new moodle_url('system.php'));

echo $OUTPUT->header();

$test_connection = false;
if(isset($_POST['button_save']))
{
	foreach($_POST as $key => $value)
	{
		if($key != 'button_save')
		{
			$subfield = '';
			//if need to have further processing
			if($key == 'sis_dbpassword') //password, encrypt it
			{
				$value = jra_encrypt($value);
			}
			if($key != 'sis_testconnection')
				jra_update_config($key, $subfield, $value);
		}
	}
}

if(isset($_POST['sis_testconnection']))
	$test_connection = true;
//content code starts here
jra_ui_page_title(get_string('system','local_jra') .  ' ' . get_string('administrator') . ' ' . get_string('settings'));
$currenttab = 'system'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('jra_tabbox');


echo '<form id="mform1" name="form1" class="mform" action="" method="post">';

$data = array();
//one row of data
$obj = new stdClass();
$obj->column = 2;
$obj->left_content = jra_admin_setting_sis_config($test_connection);
$obj->right_content = ''; //jra_admin_setting_system_eula();
$data[] = $obj;
//end of data row

$str = jra_ui_multi_column($data);
echo $str;

echo '<hr />';
echo '<div class="text-center">';
echo '<input name="button_save" type="submit" value="'.get_string('save').'" class="btn btn-primary" />';
echo '</div>';
echo '</form>';

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/admin/setting/setting.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();