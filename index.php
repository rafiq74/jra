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

require_once '../../config.php';
require_once 'lib/jra_lib.php';
require_once 'lib/jra_ui_lib.php';
require_once 'locallib.php'; //local library (for index we name it as locallib because there is already an official lib.php from moodle

require_login(); //always require login

//Role checking code here
//if(!jra_is_system_admin()) //not admin, do not allow
//	throw new moodle_exception('Access denied. This module is only accessible by administrator.');

$urlparams = $_GET;
$PAGE->set_url('/local/jra/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

echo $OUTPUT->header();
//content code starts here
jra_ui_page_title(get_string('payment', 'local_jra'));


$cost = 20;
$localisedcost = format_float($cost, 2, true);
$cost = format_float($cost, 2, false);

$instance = new stdClass();
$instance->id = 1;
$instance->currency = 'MYR';
$instance->name = 'JRA Pro Package';
$instance->cost = $cost;

$message = 'This package costs';

$coursefullname  = format_string('JRA Pro Package');
$courseshortname = 'JRA_PRO';

$userfullname    = fullname($USER);
$userfirstname   = $USER->firstname;
$userlastname    = $USER->lastname;
$useraddress     = $USER->address;
$usercity        = $USER->city;
$useremail       = $USER->email;

$publishablekey = jra_decrypt(jra_get_config('stripe_publishable_key'));
$billingaddress = jra_get_config('stripe_validate_billing');
$validatezipcode = jra_get_config('stripe_validate_postcode');


//build the content
ob_start();
include($CFG->dirroot.'/local/jra/stripe/payment.php');
$content = ob_get_clean();

$data = array();
//one row of data
$obj = new stdClass();
$obj->column = 3;
$obj->left_content = '';
$obj->center_content = jra_ui_box($content, jra_get_string(['payment']), '', true);
$obj->right_content = '';
$data[] = $obj;
//end of data row

$str = jra_ui_multi_column($data, 3);
echo $str;

//content code ends here
echo $OUTPUT->footer();

$PAGE->requires->js('/local/jra/script.js'); //global javascript
