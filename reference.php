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
require_once 'lib/sis_lib.php';
require_once 'lib.php'; //local library

require_login(); //always require login

//Role checking code here
if(!sis_is_system_admin()) //not admin, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');

$urlparams = $_GET;
$PAGE->set_url('/local/sis/reference.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

$PAGE->set_pagelayout('sis');
$PAGE->set_title(sis_site_fullname());
$PAGE->set_heading(sis_site_fullname());

//set up breadcrumb
$PAGE->navbar->add(get_string('sis', 'local_sis'), new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add(get_string('preview'), new moodle_url('/a/link/if/you/want/one.php'));
//end of breadcrumb

echo $OUTPUT->header();

//This page is for storing code snippet as reference
/* Defining multi-roles

//when defining more than one role, we nest the access rule in the role. In otherword, the role is an array of access rule
$roles = array();
$roles[] = array(
	'role' => 'academic',
	'subrole' => 'all',
);
$roles[] = array(
	'role' => 'student',
	'subrole' => 'all',
);
$access_rules = array(
	'role' => $roles,
);

//Note: You can also nest a subrole in array.

*/


//content code ends here
echo $OUTPUT->footer();
