<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Certificate module internal API,
 * this is in separate file to reduce memory use on non-certificate pages.
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//call this function to initialize system lookup values
function jra_admin_bulk_action_lookup()
{
	jra_admin_bulk_action_update_lookup('Islam', 'personal_info', 'religion', 1);
	jra_admin_bulk_action_update_lookup('Others', 'personal_info', 'religion', 1);
	
	jra_admin_bulk_action_update_lookup('primary', 'personal_info', 'address_type', 1);
	jra_admin_bulk_action_update_lookup('secondary', 'personal_info', 'address_type', 2);

}

function jra_admin_bulk_action_update_lookup($value, $category, $subcategory, $sort_order = 1, $description = '')
{		
	$country = jra_get_country();
	$lang = 'en';
	//one row
	if(!jra_lookup_duplicate($value, $lang, $category, $subcategory, $country)) //not exist, add it
	{
		jra_lookup_insert($value, $lang, $category, $subcategory, $country, $sort_order, $description);
		print_object("INSERTED $value into category ($category) and subcategory ($subcategory)");
	}
	//end of one row
}

