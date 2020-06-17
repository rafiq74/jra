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
require_once '../../lib/jra_lib.php';
require_once '../../lib/jra_ui_lib.php';
require_once '../../lib/jra_asset_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once 'lib.php'; //local library (for index we name it as locallib because there is already an official lib.php from moodle
require_once 'form.php';

require_login(); //always require login

$urlparams = $_GET;
$PAGE->set_url('/local/jra/user/asset/add_asset.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

jra_include_jquery(); //call to include jquery in the page

$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$id = optional_param('id', false, PARAM_INT);
if($id)
{
	$qs = '?id=' . $id;
	$bc = ['update', 'asset'];
}
else
{
	$qs = '';
	$bc = ['add', 'asset'];
}

$PAGE->navbar->add(get_string('dashboard', 'local_jra'), new moodle_url($CFG->wwwroot . '/local/jra/dashboard/index.php', array()));
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('add_asset.php', $urlparams));

$return_url = jra_get_session('source_page');
if($return_url == '')
	$return_url = new moodle_url('/local/jra/dashboard/index.php');
	
$isError = '';
$is_reload = false;
$mform = new asset_form();
if ($mform->is_cancelled()) 
{
	jra_set_session('jra_asset_subcategory_option', '');//kill the ajax update session for subcategory
	jra_set_session('jra_asset_area_option', '');//kill the ajax update session for subcategory
    redirect($return_url);
} 
else if ($data = $mform->get_data()) 
{		
	$now = time();
	$is_reload = true;
	$result = jra_asset_add_asset($data);
	if($result === '') //empty string for success
	{
		jra_set_session('jra_asset_subcategory_option', '');//kill the ajax update session for subcategory
		jra_set_session('jra_asset_area_option', '');//kill the ajax update session for subcategory
		redirect($return_url);
	}
	else
		$isError = $result;		
}

echo $OUTPUT->header();
//content code starts here
if($isError != '')
	jra_ui_alert(get_string($isError, 'local_jra'), 'danger');
//content code starts here
jra_ui_page_title(jra_get_string($bc));

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$toform = $DB->get_record('jra_user', array('id' => $id));
	if($toform)
		$mform->set_data($toform);
}

$mform->display();

//content code ends here
echo $OUTPUT->footer();

$PAGE->requires->js('/local/jra/user/asset/asset.js'); //global javascript
$PAGE->requires->js('/local/jra/script.js'); //global javascript

//if it is a fresh page, we make sure to reset the subcategory and area
if(!$is_reload)
{
	echo '<script>';
	echo '
	$(document).ready(function(){
	//start of code
		var category=$("#id_category").val();
		$.ajax({
			type: "post",
			url: "subcategory_action.php",
			data: "category=" + category,
			success: function(data){
				$("#id_subcategory").html(data);
			}
		});

		var location=$("#id_location").val();
		$.ajax({
			type: "post",
			url: "location_action.php",
			data: "location=" + location,
			success: function(data){
				$("#id_area").html(data);
			}
		});
	//end of code
	});
		
	   
	';	
	echo '</script>';
}
?>

