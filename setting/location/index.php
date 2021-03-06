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
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/sis/setting/location/index.php', $urlparams);
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
$PAGE->navbar->add(get_string('city', 'local_sis'), new moodle_url('index.php'));

//Any form processing code
if(isset($_POST['delete_id'])) //delete
{
	$DB->delete_records('si_city', array('id' => $_POST['delete_id']));
}

if(isset($_POST['delete_all'])) //delete all
{
//	$sql = "delete from {si_state} where moodle_courseid = '$course->id'";
//	$DB->execute($sql);
}

echo $OUTPUT->header();
//content code starts here
sis_ui_page_title(get_string('city','local_sis'));
$currenttab = 'city'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('sis_tabbox');

//initialize the country
$qs = $_GET;
if(isset($_GET['country'])) //if there is aountry from query string, get it
{
	$city_country = $_GET['country'];	
	if(sis_lookup_countries($city_country) == '') //validate that it is a valid country
		$city_country = '';
	else
		sis_set_session('location_city_country', $city_country);	
}
else //if not, try to get it from the session
{
	$city_country = sis_get_session('location_city_country');
}
if($city_country == '') //no country, initialize it
{
	$city_country = sis_location_init_city_country();
}

if(!$city_country)
{
	sis_ui_alert(get_string('must_define_city', 'local_sis'), 'info', $title = 'Note', $close = false);
}
$add_url = new moodle_url('/local/sis/setting/location/add_city.php', array('action' => '1'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' ' . get_string('add_city', 'local_sis'), array('title' => get_string('add_city', 'local_sis'))) . '</div>';

//create the master filter of countries
unset($qs['country']); //have to remove existing country query string
$a_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
$country_url = "javascript:refresh_country('".$a_url->out(false)."')";
$city_country_list = sis_location_city_country_list();
$master_filter = '<span class="pull-right"><strong>' . get_string('country', 'local_sis') . '</strong>&nbsp;&nbsp;&nbsp;' . sis_ui_select('country', $city_country_list, $city_country, $country_url) . '</span>';

$condition = array('institute' => sis_get_institute(), 'country' => $city_country);
//setup the table options
$options = array(
	'sql' => '', //incase if we need to use sql
	'condition' => $condition, //and sql condition
	'table_class' => 'generaltable', //table class is either generaltable (moodle standard table), or table for plain
	'responsive' => true, //responsive table
	'border-table' => false, //make the table bordered
	'condensed-table' => false, //compact table (not applicable under generaltable)
	'hover-table' => false, //make the table hover (not applicable under generaltable)
	'action' => true, //automatic add form and javascript for action edit and delete
	'sortable' => true, //enable clicking of heading to sort
	'default_sort_field' => 'city',
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'city', //default field choose for search
	'view_page' => '',
	'edit_page' => 'add_state.php',
	'perpage' => sis_global_var('PER_PAGE'), //use large number to remove pagination
	'master_filter' => $master_filter, //primary master filter for master-child relation
	'delete_admin' => false, //only allow to delete if it is siteadmin
//	'debug' => true,
);

$state_list = sis_lookup_state($city_country);			
//setup the table fields
$fields = array(
	'#' => array(), //# for numbering
	'city_code' => array(
		'header'=>get_string('code', 'local_sis'), //for custom header
		'align' => 'left',
		'size' => '10%',
//		'format' => 'date',
//		'disable_search' => true,
	),
	'city' => array(
//		'header'=>'Wow', //for custom header
		'align' => 'left',
		'size' => '25%',
//		'format' => 'date',
//		'disable_search' => true,
	),
	'city_a' => array(
		'header'=>get_string('city', 'local_sis') . ' (' . get_string('arabic', 'local_sis') . ')', //for custom header
		'align' => 'left',
		'size' => '20%',
	),
	'state_id' => array(
		'header'=>get_string('state', 'local_sis'), //for custom header
		'align' => 'left',
		'size' => '15%',
		'format' => 'lookup', //indicate that the output comes from an associative array
		'lookup_list' => $state_list, //the associative array
	),
	'country' => array(
		'align' => 'left',
		'size' => '15%',
		'format' => 'country',
	),
	'*' => array(), //action
);
//output the table
if($city_country)
	echo sis_ui_dump_table('si_city', $options, $fields, 'local_sis');

echo $OUTPUT->box_end();
$PAGE->requires->js('/local/sis/setting/location/location.js');
$PAGE->requires->js('/local/sis/script.js'); //global javascript

echo $OUTPUT->footer();