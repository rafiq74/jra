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
 * This file contains main class for the course format Weeks
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns navigation controls (tabtree) to be displayed on cohort management pages
 *
 * @param context $context system or category context where cohorts controls are about to be displayed
 * @param moodle_url $currenturl
 * @return null|renderable
 */

function jra_user_update_password($m_user, $password)
{
	global $DB;
	$user = $DB->get_record('jra_user', array('id' => $m_user->idnumber));
	if($user)
	{
		$user->password = jra_user_password_hash($password);
		$user->password_change = 'N';
		$user->token = null;
		$user->token_date = null;
		$DB->update_record('jra_user', $user);
		return true;
	}
	return false;
}

//use function to hash so in future, easy to change
function jra_user_password_hash($pswd)
{
	return md5($pswd);
}

//reset the jra_user password to the default system password
function jra_user_reset_password($id)
{
	global $DB;
	$var_name = 'system_default_password';
	$reset_option = jra_get_config($var_name);
	$pswd = '';
	if($reset_option == 'custom')
	{
		$var_name = 'system_default_password_custom';
		$pswd = jra_get_config($var_name);
	}
	if($pswd != '')
	{
		$user = $DB->get_record('jra_user', array('id' => $id));
		if($user)
		{
			 //check if user allow to change password
			$m_user = $DB->get_record('user', array('idnumber' => $user->id));
			if($m_user)
			{
				if(jra_allow_password_change($m_user))
				{
					$var_name = 'system_force_password_change';
					$force_change = jra_get_config($var_name);
				}
				else
					$force_change = 'N';
			}
			else
			{
				$var_name = 'system_force_password_change';
				$force_change = jra_get_config($var_name);
			}
			$user->password = jra_user_password_hash($pswd);
			$user->password_change = $force_change;
			$DB->update_record('jra_user', $user);
			return true;
		}
	}
	return false;
}

function jra_user_send_password_change_info($user, $jra_user) {
    global $CFG, $DB;

	//generate and save the token
	$now = time();
	$token_end_time = strtotime(date('d-M-Y', $now) . ' + 2 days'); //2 days for token expiry
	$token = md5(uniqid($user->username . $token_end_time, true));
	$jra_user->token = $token;
	$jra_user->token_date = $token_end_time;
	$DB->update_record('jra_user', $jra_user);
	//end of token generation
	
    $site = get_site();
    $supportuser = core_user::get_support_user();
    $systemcontext = context_system::instance();

    $data = new stdClass();
    $data->firstname = $user->firstname;
    $data->lastname  = $user->lastname;
    $data->username  = $user->username;
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

	$url = new moodle_url($CFG->wwwroot . '/local/jra/user/reset_password.php', array('token' => $token));
	$data->link = $url->out(false);

	$message = get_string('emailpasswordchangeinfo', '', $data);
	$subject = get_string('emailpasswordchangeinfosubject', '', format_string($site->fullname));


    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
    return email_to_user($user, $supportuser, $subject, $message);

}
