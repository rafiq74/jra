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
 * This file contains main functions for RCYCI Module
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
   This file contain all the global functions for RCYCI module
*/

// This is the library for global RCYCI functions 
defined('MOODLE_INTERNAL') || die();

//this is a custom mail function to send email to user not in moodle account. As moodle email_to_user function requires a valid moodel
//account, unregistered user do not have the account. So this function use the guest user and replaces the email address for email sending
function jra_mail_user($to, $subject, $message)
{
	global $DB;
	$user = $DB->get_record('user', array('id' => 1)); //get the guess user as dummy user for sending email
	$user->email = $to;

	$supportuser = core_user::get_support_user();

	return email_to_user($user, $supportuser, $subject, $message);
}

function jra_mail_send_activation_email($jra_user) 
{
    global $CFG, $DB;

	//generate and save the token
	$now = time();
	$token_end_time = strtotime(date('d-M-Y', $now) . ' + 200 days'); //2 days for token expiry
	$token = md5(uniqid($jra_user->username . $token_end_time, true));
	$jra_user->token = $token;
	$jra_user->token_date = $token_end_time;
	$jra_user->active_status = 'P'; //if we send the activation email, it must be pending
	$DB->update_record('jra_user', $jra_user);
	//end of token generation
	
    $site = get_site();
    $supportuser = core_user::get_support_user();
    $systemcontext = context_system::instance();

    $data = new stdClass();
    $data->firstname = $jra_user->first_name;
    $data->lastname  = $jra_user->family_name;
    $data->username  = $jra_user->username;
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

	$url = new moodle_url($CFG->wwwroot . '/local/jra/user/activation.php', array('token' => $token));
	$data->link = $url->out(false);

	$to = $jra_user->email;
//	$subject = get_string('emailactivationsubject', 'local_jra', format_string($site->fullname));
	$subject = format_string($site->fullname) . ' : تفعيل حساب قبول HIEI';
//	$message = get_string('emailactivationcontent', 'local_jra', $data);
	$message = "
		مرحبا $data->firstname,
		
		لقد أنشأت حساب مستخدم جديدًا تحت عنوان البريد الإلكتروني  '$data->username' على '$data->sitename'.
		
		لتفعيل حسابك ، يرجى الذهاب إلى عنوان الويب التالي :
		
		$data->link
		
		في معظم برامج البريد الإلكتروني، يجب أن يظهر هذا كارتباط أزرق
		والتي يمكنك فقط النقر فوقها. إذا لم يعمل ذلك ،
		قم بنسخ ولصق العنوان في العنوان
		أعلى نافذة متصفح الويب.
		
		إذا كنت بحاجة إلى مساعدة ، يرجى الاتصال بمسؤول الموقع ،
		$data->admin	
	";
	
	//for testing
//	echo '<p>' . $subject . '</p>';
//	echo '<p>' . $message . '</p>';
	//for real
	return jra_mail_user($to, $subject, $message);
	
    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
//    return email_to_user($user, $supportuser, $subject, $message);

}

function jra_mail_send_password_change_info($jra_user) {
    global $CFG, $DB;

	//generate and save the token
	$now = time();
	$token_end_time = strtotime(date('d-M-Y', $now) . ' + 2 days'); //2 days for token expiry
	$token = md5(uniqid($jra_user->username . $token_end_time, true));
	$jra_user->token = $token;
	$jra_user->token_date = $token_end_time;
	$DB->update_record('jra_user', $jra_user);
	//end of token generation
	
    $site = get_site();
    $supportuser = core_user::get_support_user();
    $systemcontext = context_system::instance();

    $data = new stdClass();
    $data->firstname = $jra_user->first_name;
    $data->lastname  = $jra_user->family_name;
    $data->username  = $jra_user->username;
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

	$url = new moodle_url($CFG->wwwroot . '/local/jra/user/reset_password.php', array('token' => $token));
	$data->link = $url->out(false);

	$to = $jra_user->email;
	$message = get_string('emailpasswordchangeinfo', '', $data);
	$subject = get_string('emailpasswordchangeinfosubject', '', format_string($site->fullname));

	return jra_mail_user($to, $subject, $message);
    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
//    return email_to_user($user, $supportuser, $subject, $message);

}

function jra_mail_send_notification_email($applicant) 
{
    global $CFG, $DB;
	
    $data = new stdClass();
    $data->fullname = $applicant->fullname_a;
    $data->admin     = generate_email_signoff();

	$to = $applicant->email;
//	$subject = get_string('emailactivationsubject', 'local_jra', format_string($site->fullname));
	$subject = 'إعلان بشأن حالة الطلب للقبول في المعهد العالي للصناعات المطاطية';
//	$message = get_string('emailactivationcontent', 'local_jra', $data);
	$message = "<p dir=\"rtl\">
		مرحبا $data->fullname,<br /><br />
		
		يرجى زيارة صفحة القبول على https: //www.estudentprogram/admission وتسجيل الدخول إلى حسابك للتحقق من حالة طلبك.		
		<br /><br />
		إذا كنت بحاجة إلى مساعدة ، يرجى الاتصال بمسؤول الموقع ،
		$data->admin	
	</p>";
	
	//for testing
//	echo '<p>' . $subject . '</p>';
//	echo '<p>' . $message . '</p>';
	//for real
	return jra_mail_user($to, $subject, $message);
	
    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
//    return email_to_user($user, $supportuser, $subject, $message);

}
