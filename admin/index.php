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

require_once '../../../config.php';
require_once '../lib/jra_lib.php'; 
require_once '../lib/jra_ui_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_pagelayout('jra');
$PAGE->set_url('/local/jra/admin/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
//frontpage - for 2 columns with standard menu on the right
// - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('index.php', array()));
//$PAGE->navbar->add(jra_get_string(['main', 'menu']), new moodle_url('index.php', array()));

echo $OUTPUT->header();
//content code starts here

jra_ui_page_title(get_string('administration') . ' ' . jra_get_string(['menu']));

$menu = jra_admin_menu();
echo jra_ui_box($menu);



$PAGE->requires->js('/local/jra/admin/script.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();