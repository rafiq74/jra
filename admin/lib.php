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

//get list of active student
function jra_admin_menu()
{
	$padding = 2;
	$str = '';
	$str = $str . '<div class="row text-center">';
	//one icon
	$str = $str . '<div class="col-md-2 pt-' . $padding . '">';
	$icon_url = new moodle_url('/local/jra/admin/user/index.php');
	$url_text = '<div>' . jra_ui_icon('group', '3', true) . '</div>';
	$url_text = $url_text . '<div class="pt-2 pb-3"><strong>' . jra_get_string(['user', 'management']) . '</strong></div>';
	$str = $str . html_writer::link($icon_url, $url_text, array());
	$str = $str . '</div>';
	//end of one icon
	//one icon
	$str = $str . '<div class="col-md-2 pt-' . $padding . '">';
	$icon_url = new moodle_url('plan/index.php');
	$url_text = '<div>' . jra_ui_icon('ticket', '3', true) . '</div>';
	$url_text = $url_text . '<div class="pt-2 pb-3"><strong>' . jra_get_string(['subscription', 'plan']) . '</strong></div>';
	$str = $str . html_writer::link($icon_url, $url_text, array());
	$str = $str . '</div>';
	//end of one icon
	//one icon
	$str = $str . '<div class="col-md-2 pt-' . $padding . '">';
	$icon_url = new moodle_url('setting/index.php');
	$url_text = '<div>' . jra_ui_icon('cogs', '3', true) . '</div>';
	$url_text = $url_text . '<div class="pt-2 pb-3"><strong>' . jra_get_string(['system', 'settings']) . '</strong></div>';
	$str = $str . html_writer::link($icon_url, $url_text, array());
	$str = $str . '</div>';
	//end of one icon
	//one icon
	$str = $str . '<div class="col-md-2 pt-' . $padding . '">';
	$icon_url = new moodle_url('/local/jra/stripe/setting.php');
	$url_text = '<div>' . jra_ui_icon('cc-stripe', '3', true) . '</div>';
	$url_text = $url_text . '<div class="pt-2 pb-3"><strong>' . jra_get_string(['payment', 'settings']) . '</strong></div>';
	$str = $str . html_writer::link($icon_url, $url_text, array());
	$str = $str . '</div>';
	//end of one icon

	$access_rules = array(
		'system' => ''
	); //super admin role only
	$is_system = jra_access_control($access_rules, false);
	if($is_system) //only for system administrator
	{
		//one icon
		$str = $str . '<div class="col-md-2 pt-' . $padding . '">';
		$icon_url = new moodle_url('bulk_action/index.php');
		$url_text = '<div>' . jra_ui_icon('cubes', '3', true) . '</div>';
		$url_text = $url_text . '<div class="pt-2 pb-3"><strong>' . jra_get_string(['bulk', 'action']) . '</strong></div>';
		$str = $str . html_writer::link($icon_url, $url_text, array());
		$str = $str . '</div>';
		//end of one icon
	}
	$str = $str . '</div>';
	return $str;	
}

