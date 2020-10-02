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
require_once '../../../jra/lib/jra_lib.php'; 
require_once '../../../jra/lib/jra_file_lib.php'; 
require_once 'lib.php'; //local library
require_once $CFG->libdir.'/filelib.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/file.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login

//2nd level data in tabs
//Check if a tab has to be active by default
//Breadcrumb

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

//make sure only accessible if login
//make sure only accessible if login

$aPath = required_param('path', PARAM_TEXT);
$aFile = required_param('file', PARAM_TEXT);

$filename = $aPath . $aFile;

$ext = jra_file_get_extension($aFile);

$filetype = get_mimetypes_array();
$mime_type = $filetype[$ext]['type'];

$content = file_get_contents($filename);

header('Content-Type: ' . $mime_type);
header('Content-Length: ' . strlen($content));
header('Content-Disposition: inline; filename="' . $aFile . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
ini_set('zlib.output_compression','0');

die($content);
