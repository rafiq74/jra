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

$category = $_POST['category'];

if($category == 'property')
{
	$arr = array(
		'1' => 'Apartment',
		'2' => 'House',
		'3' => 'Room',
		'4' => 'Commercial Properties',
		'5' => 'Land',
		'6' => 'Others',
	);
}
else if($category == 'vehicle')
{
	$arr = array(
		'1' => 'Car',
		'2' => 'Motocycles',
		'3' => 'Bicycle',
		'4' => 'Commercial Vehicles',
		'5' => 'Industrial Vehicles',
		'6' => 'Boats',
		'7' => 'Others',
	);
}
else if($category == 'service')
{
	$arr = array(
		'1' => 'Cleaning',
		'2' => 'Childcare',
		'3' => 'Tuition',
		'4' => 'Training/Seminar',
		'5' => 'Maid',
		'6' => 'Gardeming',
		'7' => 'Others',
	);
}
else if($category == 'facility')
{
	$arr = array(
		'1' => 'Badminton Court',
		'2' => 'Tennis Court',
		'3' => 'Futsal Court',
		'4' => 'Others',
	);
}
else if($category == 'others')
{
	$arr = array(
		'1' => 'Anything for Rent',
		'4' => 'Others',
	);
}
else
{
	$arr = array(
		'' => get_string('select_subcategory', 'local_jra'),
	);	
}
//because the way moodle form works, if the form is refreshed and the select has no item, the selection will be lost. 
//an initialization by ajax will not work as it is the new item and moodle will lost the selection. So we keep a session of the items
//and if there is a reload, the item will be taken from this session.
//Important!!! - do kill the session once the form is cancel or done (i.e. saved).
jra_set_session('jra_asset_subcategory_option', $arr);

foreach($arr as $id => $value)
	echo "<option value=$id>$value</option>";
