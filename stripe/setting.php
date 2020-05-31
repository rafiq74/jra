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

require_once '../../../config.php';
require_once '../lib/jra_lib.php'; 
require_once '../lib/jra_ui_lib.php'; 
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/stripe/setting.php', $urlparams);
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

$PAGE->navbar->add('JRA ' . strtolower(get_string('administration')), new moodle_url('/local/jra/admin/index.php', array()));
$PAGE->navbar->add(jra_get_string(['payment', 'settings']), new moodle_url('setting.php'));

$return_url = new moodle_url('/local/jra/admin/index.php', array());
//put before header so we can redirect
$mform = new stripe_form();
$saved = false;
if ($mform->is_cancelled()) 
{
    redirect($return_url);
} 
 
else if ($data = $mform->get_data()) 
{		
	$en = jra_encrypt($data->stripe_secret_key);
	$de = jra_decrypt($en);
	foreach($data as $key => $value)
	{
		if($key != 'submitbutton') //not the submit button
		{
			if($key == 'stripe_secret_key' || $key == 'stripe_publishable_key') //for key, we encrypt it
				$value = jra_encrypt($value);
			jra_update_config($key, '', $value);
		}
	}
	$saved = true;
}

echo $OUTPUT->header();

if($saved)
	jra_ui_alert('Setting saved', 'success');
//content code starts here
jra_ui_page_title(jra_get_string(['payment', 'settings']));

//try to get the existing saved value
$obj = new stdClass();
$var_name = 'stripe_secret_key';
$obj->stripe_secret_key = jra_decrypt(jra_get_config($var_name));

$var_name = 'stripe_publishable_key';
$obj->stripe_publishable_key = jra_decrypt(jra_get_config($var_name));

$var_name = 'stripe_validate_billing';
$obj->stripe_validate_billing = jra_get_config($var_name);

$var_name = 'stripe_validate_postcode';
$obj->stripe_validate_postcode = jra_get_config($var_name);

$mform->set_data($obj);

$mform->display();

echo $OUTPUT->footer();