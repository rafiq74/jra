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
 * Change password form definition.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../lib/jra_lib.php';
require_once '../../lib/jra_lookup_lib.php';

$location = $_POST['location'];

if($location != '')
	$arr = jra_lookup_city($location, get_string('select_area', 'local_jra'));
else
{
	$arr = array(
		'' => get_string('select_area', 'local_jra'),
	);	
}
//because the way moodle form works, if the form is refreshed and the select has no item, the selection will be lost. 
//an initialization by ajax will not work as it is the new item and moodle will lost the selection. So we keep a session of the items
//and if there is a reload, the item will be taken from this session.
//Important!!! - do kill the session once the form is cancel or done (i.e. saved).
jra_set_session('jra_asset_area_option', $arr);

foreach($arr as $id => $value)
	echo "<option value=$id>$value</option>";
