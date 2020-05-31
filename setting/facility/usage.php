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
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../lib/sis_lib.php'; 
require_once '../../lib/sis_ui_lib.php';
require_once '../../lib/sis_query_lib.php';
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/facility/usage.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);
require_login(); //always require login
/*
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
*/
$access_rules = array(
	'system' => ''
); //super admin role only

sis_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'setup');
$PAGE->navbar->add(get_string('setup', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'setup')));
$PAGE->navbar->add(get_string('facility', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('usage', 'local_sis'), new moodle_url('usage.php'));

//content code starts here
echo $OUTPUT->header();
sis_ui_page_title(get_string('usage','local_sis'));
$option = 0;
if(isset($_GET['action']))
	$option = $_GET['action'];

if(isset($_POST['search']))
{
	$post_data = $_POST;
	sis_set_session('usage_search', $post_data);
}
else
{
	$post_data = sis_get_session('usage_search');
	if($post_data == '') //if session not defined
	{		
		$post_data = array();
		$post_data['search'] = '';
		$post_data['sort'] = 1;		
	}
}
$currenttab = 'usage'; //change this according to tab
include('tabs.php');

echo $OUTPUT->box_start('sis_tabbox');

if($option == 2)
{
    $id = $_GET['id'];
	$DB->delete_records('si_lookup', array('id' => $id));
	redirect('usage.php');
	$option = 0;

}
$add_url = new moodle_url('/local/sis/setting/facility/add_usage.php', array('action' => '1'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' Add Usage', array('title' => 'Add Usage Type')) . '</div>';

$form = sis_lookup_search_form($post_data);
sis_ui_box('', $form);

$where = sis_query_institute_where() . " and category = 'facility' && subcategory = 'usage'"; //initialize a where clause with institute
if($post_data['search'] != '')
{
	$where = $where . " and (value like '%" . $post_data['search'] . "%' or value_a like '%" . $post_data['search'] . "%')";
}

$sql = "select * from {si_lookup} $where order by value";

$templates = $DB->get_records_sql($sql);

$table = new html_table();
$table->attributes['class'] = '';
$table->width = "100%";
$table->head[] = get_string('no', 'local_sis');
$table->size[] = '5%';
$table->align[] = 'center';
$table->head[] = get_string('usage', 'local_sis');
$table->size[] = '70%';
$table->align[] = 'left';
$table->head[] = get_string('language', 'local_sis');
$table->size[] = '10%';
$table->align[] = 'center';
$table->head[] = 'Action';
$table->size[] = '15%';	
$table->align[] = 'center';

$count = 1;
foreach($templates as $template)
{
	$data[] = $count;
	$data[] = get_string($template->value, 'local_sis');
	$data[] = $template->lang;
	$delete_url = "javascript:delete_record_usage('$template->id')";
	$update_url = new moodle_url('/local/sis/setting/facility/add_usage.php', array('id'=>$template->id));		
	
	$data[] = html_writer::link($delete_url, sis_ui_icon('trash', '1.5', true), array('title' => 'Delete Usage')) . '&nbsp;' . 
			  html_writer::link($update_url, sis_ui_icon('pencil', '1.5', true), array('title' => 'Update Usage'));
	$table->data[] = $data;
	unset($data);
	$count++;	
}
sis_ui_print_table($table);
echo $OUTPUT->box_end();
$PAGE->requires->js('/local/sis/setting/facility/facility.js');

echo $OUTPUT->footer();