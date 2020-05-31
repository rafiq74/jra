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
require_once '../../lib/sis_output_lib.php';
require_once 'lib.php'; //local library


$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/organization/section.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);

sis_access_control($access_rules);

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());
sis_set_session('sis_home_tab', 'setup');
$PAGE->navbar->add(get_string('setup', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'setup')));
$PAGE->navbar->add(get_string('organization', 'local_sis'), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('section', 'local_sis'), new moodle_url('section.php'));
echo $OUTPUT->header();
//content code starts here


sis_ui_page_title(get_string('section','local_sis'));
$currenttab = 'section'; //change this according to tab
include('tabs.php');

if($id = $_GET['id'])
{
	$DB->delete_records('si_organization', array('id' => $id));
	 redirect('section.php');
}

$add_url = new moodle_url('/local/sis/setting/organization/add_section.php', array('action' => '1'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' Add Section', array('title' => 'Add Section')) . '</div>';


$sql = "select * from {si_organization} $where order by id desc";
echo $OUTPUT->box_start('sis_tabbox');

$templates = $DB->get_records_sql($sql, array(), $start, $perpage);

$table = new html_table();
$table->attributes['class'] = 'table table-bordered table-striped';
$table->width = "100%";
$table->head[] = 'Parent';
$table->size[] = '35%';
$table->align[] = 'center';
$table->head[] = 'Section';
$table->size[] = '35%';
$table->align[] = 'center';
$table->head[] = 'Action';
$table->size[] = '30%';	
$table->align[] = 'center';
foreach($templates as $template)
{
	
	$data[] = $template->organization_name;
	$data[] = $template->organization_type;
	$delete_url = "javascript:delete_record_section('$template->id')";
	$update_url = new moodle_url('/local/sis/setting/organization/add_section.php', array('id'=>$template->id));		
	$data[] = html_writer::link($delete_url, sis_ui_icon('trash', '1', true), array('title' => 'Delete Section')) . '&nbsp;' . 
			  html_writer::link($update_url, sis_ui_icon('pencil', '1', true), array('title' => 'Update Section'));
	$table->data[] = $data;
	unset($data);				
}

sis_ui_print_table($table);
echo $OUTPUT->box_end();

$PAGE->requires->js('/local/sis/setting/organization/organization.js');

echo $OUTPUT->footer();