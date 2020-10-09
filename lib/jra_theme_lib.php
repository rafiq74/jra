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
   This file contains Query helper functions.
*/

// This is the library for custom user interface
defined('MOODLE_INTERNAL') || die();

require_once 'jra_app_lib.php'; 
require_once 'jra_output_lib.php'; 
require_once 'jra_ui_lib.php'; 

function jra_theme_registration_message()
{
	global $CFG;
	require_once $CFG->dirroot . '/local/jra/lib/jra_public_lib.php'; 	
	$is_closed = jra_is_closed();
	if($is_closed != '')
		return '<br />' . $is_closed;
	else
	{
		$url = new moodle_url('/local/jra/user/self_registration.php');
		$str = '
			<hr />
			<div class=""><h5>' . get_string('first_time_here', 'local_jra') . '</h5>
				' . get_string('self_registration_message', 'local_jra'). '
				<div class="mt-3"><a href="' . $url->out(false) . '"><button type="button" class="btn btn-secondary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . get_string('create_new_account', 'local_jra') . '</button></a></div>
			</div>
		';
		return $str;
	}
}

//this is the function that will collect all the output to the frontpage and output it. The function is called in the theme body_frontpage
//and then output it to the mustache page
function jra_theme_frontpage()
{
	global $USER;
	if (isloggedin() and !isguestuser()) //log in user
	{
		$applicant = jra_app_get_applicant();
		$str = '';
		if(jra_get_user_type() == 'public')
		{
			$str = $str . jra_theme_preface();
			if($USER->jra_user->active_status == 'A')
			{
				if(!jra_app_applicant_admitted($applicant))
					$str = $str . jra_theme_marketing($applicant);
				else
					$str = $str . jra_theme_admitted($applicant);
			}
		}
		else
		{
			$str = $str . jra_theme_admin_preface();
			$str = $str . jra_theme_icon_grid();
		}
	}
	else
	{
		$str = $str . jra_theme_public_preface();
	}
//	$str = $str . jra_theme_about();
//	$str = $str . jra_theme_listing();
	return $str;	
}

function jra_theme_preface()
{
	global $DB, $USER;
	$semester = jra_get_semester();
	$sem = $DB->get_record('si_semester', array('semester' => $semester));
	$str = jra_ui_show_flash_message('jra_information_updated', true);
	$applicant = jra_app_get_applicant();
	if($applicant)
	{
		$ref_number = '<h4>' . jra_get_string(['your_application_reference_number']) . ' : <span class="badge badge-dark">' . jra_app_ref_number($applicant) . '</span></h4>';
	}
	else
		$ref_number = '';
	$str = $str . '
	<div class="mt-2 mb-3">
		<div class="row text-center">
			<div class="col-lg-12 pb-50 px-50 pt-0" style="background-image: url();background-repeat: no-repeat; background-position: center;">
				<div>
					<h2>' . get_string('welcome_preface', 'local_jra', jra_output_show_field_language($sem, 'description')) . '</h2>';
	if($USER->jra_user->active_status == 'A') //for active user
	{
		if(!jra_app_applicant_admitted($applicant))
		{
			$str = $str . ' <p class="" style="color:#908b8b;">
								' . get_string('preface_introduction_login', 'local_jra') . '
							</p>
							' . $ref_number;
		}
		else
		{
			$str = $str . $ref_number;
		}
	}
	else //if user pending, ask him to activate the account first
	{
		$x = jra_get_session('jra_resend_email');
		if($x == '') //never resend yet
		{
			$resend_url = new moodle_url('/local/jra/user/resend_email.php');
			$resend_btn = '<a href="' . $resend_url->out(false) . '"><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['resend', 'activation', 'email']) . '</button></a>';
		}
		else
		{
			$resend_btn = '<button type="button" class="btn btn-primary mw-100 disabled" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['resend', 'activation', 'email']) . '</button><br /><br />' . jra_ui_alert(get_string('activation_resend', 'local_jra'), 'warning', '', false, true);
		}
		$str = $str . '<p class="" style="color:#908b8b;">
							' . get_string('account_not_verified', 'local_jra') . '
						</p>
						' . $resend_btn . '	
						';
	}
	$str = $str . '</div>
			</div>
		</div>
	</div>
	';	
	return $str;
}

function jra_theme_admin_preface()
{
	global $DB;
	$semester = jra_get_semester();
	$sem = $DB->get_record('si_semester', array('semester' => $semester));
	$str = jra_ui_show_flash_message('jra_information_updated', true);
	$str = $str . '
	<div class="mt-2 mb-5">
		<div class="row text-center">
			<div class="col-lg-12 pb-50 px-50 pt-0" style="background-image: url();background-repeat: no-repeat; background-position: center;">
				<div>
					<h2>' . get_string('welcome_preface', 'local_jra', jra_output_show_field_language($sem, 'description')) . '</h2>
					<p class="" style="color:#908b8b;">
						' . get_string('preface_introduction_login', 'local_jra') . '
					</p>
					<!--
					<a href=""><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">Click Here</button></a>	
					-->
				</div>
			</div>
		</div>
	</div>
	';	
	return $str;
}

function jra_theme_public_preface()
{
	global $DB;
	$semester = jra_get_semester();
	$sem = $DB->get_record('si_semester', array('semester' => $semester));
	$is_closed = jra_is_closed(false, true);
	$str = jra_ui_show_flash_message('jra_information_updated', true);
	$str = $str . '
	<div class="mb-1 icon-header">
		<div class="row text-center">
			<div class="col-lg-12 pb-50 px-50 pt-0">
				<div>
					<h2>' . get_string('welcome_preface', 'local_jra', jra_output_show_field_language($sem, 'description')) . '</h2>';
	$str = $str . $is_closed;
	$str = $str . '<p class="" style="color:#908b8b;">';
	$is_closed = jra_is_closed(true); //get the truth value of close
	if(!$is_closed) //open
	{
		$str = $str . get_string('preface_introduction_public', 'local_jra');
		$str = $str . '<div class="row">';
		$str = $str . '<div class="col-md-1"></div>'; //blank column
		$url = new moodle_url('/local/jra/user/self_registration.php');
		$str = $str . '<div class="col-md-5">';
		$step1 = '<p>' . get_string('create_account_message', 'local_jra') . '</p>' . jra_ui_button(jra_get_string(['create_account']), $url);
		$step1_title = '<strong>' . get_string('step_1_create_account', 'local_jra') . '</strong>';
		$str = $str . jra_ui_box($step1, $step1_title, '', true);
		$str = $str . '</div>';		
		$url = new moodle_url('/login/index.php');
		$str = $str . '<div class="col-md-5">';
		$step2 = '<p>' . get_string('login_message', 'local_jra') . '</p>' . jra_ui_button(jra_get_string(['login']), $url);
		$step2_title = '<strong>' . get_string('step_2_login', 'local_jra') . '</strong>';
		$str = $str . jra_ui_box($step2, $step2_title, '', true);
		$str = $str . '</div>';		
		$str = $str . '<div class="col-md-1"></div>'; //blank column
		$str = $str . '</div>';
	}
	$str = $str . '</p>';
	$str = $str . '</div>
			</div>
		</div>
	</div>
	';	
	return $str;
}

//course code in moodle has the format
//course_code (section) : id
function jra_theme_marketing($applicant)
{
	global $DB;
	$str = '';
	$is_closed = jra_is_closed();
	if($is_closed != '')
		$str = $str . $is_closed;
	else
		$str = $str . jra_is_closed(false, true);
	
	$read_only = false;
	$stage = jra_app_get_applicant_stage($applicant);
	if($stage >= jra_app_read_only_stage()) //user has confirmed the application. It has reached the read only stage, show message
	{
		$str = $str . jra_ui_alert(get_string('finalized_application_message', 'local_jra', $a), 'warning', '', false, true);
		$read_only = true;
	}
	if($is_closed != '') //already closed, always read only
		$read_only = true;
	/******************* 
	**** STAGE 1 *******
	********************/
	$url = new moodle_url('/local/jra/application/applicant/personal_info.php');
	$url_view = new moodle_url('/local/jra/application/applicant/personal_info_view.php');
	//show edit and view button
	$stage = jra_app_get_applicant_stage($applicant);
	$btn = '<div class="text-center">';
	if(!$read_only)
	{
		$btn = $btn . '
		<a href="' . $url . '"><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['update_personal_information']) . '</button></a>
		';
	}
	if($stage > 0) //completed stage 1
	{
		$btn = $btn . jra_ui_space(3) . '
		<a href="' . $url_view . '"><button type="button" class="btn btn-secondary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['view', 'personal_information']) . '</button></a>	
		';
		$complete_status = jra_theme_get_complete_status(true);
	}
	else
		$complete_status = jra_theme_get_complete_status(false);
	$btn = $btn . '</div>';
	$str = $str . '
	<div class="pt-5">
		<div class="col-lg-12 mt-10">
			<div class="row col-lg-12 m-0">
				<!-- ./start column -->
				<div class="col-lg-12 col-md-12 my-15">
					<div class="h-100 mb-10" style="box-shadow: 0 2px 5px #35868c!important; border-radius: 0px;">
						<div style="background-color:white; border-radius: 0px;" class="h-100">
							<div class="text-center icon-holder">
								<!-- Create an icon wrapped by the fa-stack class -->
								<span class="fa-stack fa-3x" style="color:#35868c;">
									<!-- The icon that will wrap the number -->
									<span class="fa fa-circle fa-stack-2x"></span>
									<!-- a strong element with the custom content, in this case a number -->
									<strong class="fa-stack-1x" style="color:white;">
										1
									</strong>
								</span>
							</div>
							<div class="card-body content-holder">
								<h5 class="card-title text-center" style="color:#555555;">' . get_string('personal_information', 'local_jra'). '</h5>
								<p class="card-desc" style="color:#908b8b;">' . get_string('personal_information_description', 'local_jra') . '</p>
								' . $btn . '
							</div>
							<div class="card-footer text-center">
								' . $complete_status . '
							</div>
						</div>
					</div>
				</div>
				<!-- ./end column -->
			</div>
		</div>		
	</div>
	';

	/******************* 
	**** STAGE 2 *******
	********************/
	$url = new moodle_url('/local/jra/application/applicant/contact_info.php');
	$url_view = new moodle_url('/local/jra/application/applicant/contact_info_view.php');
	//show edit and view button
	$stage = jra_app_get_applicant_stage($applicant);
	$btn = '<div class="text-center">';
	if(!$read_only)
	{	
		if($stage < 1)
		{
			$btn = $btn . '
				<button type="button" class="btn btn-primary mw-100 disabled" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['update_contact_information']) . '</button>
			';
		}
		else
		{
			$btn = $btn . '
				<a href="' . $url . '"><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['update_contact_information']) . '</button></a>
				';
		}
	}
	if($stage > 1) //completed stage 2
	{
		$btn = $btn . jra_ui_space(3) . '
		<a href="' . $url_view . '"><button type="button" class="btn btn-secondary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['view', 'contact_information']) . '</button></a>	
		';
		$complete_status = jra_theme_get_complete_status(true);
	}
	else
		$complete_status = jra_theme_get_complete_status(false);
	$btn = $btn . '</div>';
	$str = $str . '
	<br />
	<div class="pt-5">
		<div class="col-lg-12 mt-10">
			<div class="row col-lg-12 m-0">
				<!-- ./start column -->
				<div class="col-lg-12 col-md-12 my-15">
					<div class="h-100" style="
								box-shadow: 0 2px 5px #de0f4f!important;
						border-radius: 0px;
						">
						<div style="background-color:white; border-radius: 0px;" class="h-100 aboutus-backimg">
							<div class="text-center icon-holder">
								<!-- Create an icon wrapped by the fa-stack class -->
								<span class="fa-stack fa-3x" style="color:#de0f4f">
									<!-- The icon that will wrap the number -->
									<span class="fa fa-circle fa-stack-2x"></span>
									<!-- a strong element with the custom content, in this case a number -->
									<strong class="fa-stack-1x" style="color:white;">
										2
									</strong>
								</span>
							</div>
							<div class="card-body content-holder">
								<h5 class="card-title text-center" style="color:#555555;">' . jra_get_string(['contact_information']) . '</h5>
								<p class="card-desc" style="color:#908b8b;">' . get_string('contact_information_description', 'local_jra') . '</p>
								' . $btn . '	
							</div>
							<div class="card-footer text-center">
								' . $complete_status . '
							</div>
						</div>
					</div>
				</div>
				<!-- ./end column -->
			</div>
		</div>		
	</div>
	';

	/******************* 
	**** STAGE 3 *******
	********************/
	$url = new moodle_url('/local/jra/application/applicant/academic_info.php');
	$url_view = new moodle_url('/local/jra/application/applicant/academic_info_view.php');
	//show edit and view button
	$stage = jra_app_get_applicant_stage($applicant);
	$btn = '<div class="text-center">';
	if(!$read_only)
	{
		if($stage < 2)
		{
			$btn = $btn . '
				<button type="button" class="btn btn-primary mw-100 disabled" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['update_academic_information']) . '</button>
			';
		}
		else
		{
			$btn = $btn . '
				<a href="' . $url . '"><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['update_academic_information']) . '</button></a>
				';
		}
	}
	if($stage > 2) //completed stage 3
	{
		$btn = $btn . jra_ui_space(3) . '
		<a href="' . $url_view . '"><button type="button" class="btn btn-secondary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['view', 'academic_information']) . '</button></a>	
		';
		$complete_status = jra_theme_get_complete_status(true);
	}
	else
		$complete_status = jra_theme_get_complete_status(false);
	$btn = $btn . '</div>';
	$str = $str . '
	<br />
	<div class="pt-5">
		<div class="col-lg-12 mt-10">
			<div class="row col-lg-12 m-0">
				<!-- ./start column -->
				<div class="col-lg-12 col-md-12 my-15">
					<div class="h-100" style="
								box-shadow: 0 2px 5px #1d5ed7!important;
						border-radius: 0px;
						">
						<div style="background-color:white; border-radius: 0px;" class="h-100 aboutus-backimg">
							<div class="text-center icon-holder">
								<!-- Create an icon wrapped by the fa-stack class -->
								<span class="fa-stack fa-3x" style="color:#1d5ed7;">
									<!-- The icon that will wrap the number -->
									<span class="fa fa-circle fa-stack-2x"></span>
									<!-- a strong element with the custom content, in this case a number -->
									<strong class="fa-stack-1x" style="color:white;">
										3
									</strong>
								</span>
							</div>
							<div class="card-body content-holder">
								<h5 class="card-title text-center" style="color:#555555;">' . jra_get_string(['academic_information']) . '</h5>
								<p class="card-desc" style="color:#908b8b;">' . get_string('academic_information_description', 'local_jra') . '</p>
								' . $btn . '
							</div>
							<div class="card-footer text-center">
								' . $complete_status . '
							</div>
						</div>
					</div>
				</div>
				<!-- ./end column -->
			</div>
		</div>		
	</div>
	';

	/******************* 
	**** STAGE 4 *******
	********************/
	$url = new moodle_url('/local/jra/application/applicant/upload_document.php');
	$url_view = new moodle_url('/local/jra/application/applicant/upload_document.php');
	//show edit and view button
	$stage = jra_app_get_applicant_stage($applicant);
	$btn = '<div class="text-center">';
	if(!$read_only)
	{
		if($stage < 3)
		{
			$btn = $btn . '
				<button type="button" class="btn btn-primary mw-100 disabled" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['upload', 'documents']) . '</button>
			';
		}
		else
		{
			$btn = $btn . '
				<a href="' . $url . '"><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['upload', 'documents']) . '</button></a>
				';
		}
	}
	else
	{
		$btn = $btn . jra_ui_space(3) . '
		<a href="' . $url_view . '"><button type="button" class="btn btn-secondary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['view', 'uploaded', 'documents']) . '</button></a>	
		';
	}
	if($stage > 3) //completed stage 3
	{
		$complete_status = jra_theme_get_complete_status(true);
	}
	else
		$complete_status = jra_theme_get_complete_status(false);
	$btn = $btn . '</div>';
	$str = $str . '
	<br />
	<div class="pt-5">
		<div class="col-lg-12 mt-10">
			<div class="row col-lg-12 m-0">
				<!-- ./start column -->
				<div class="col-lg-12 col-md-12 my-15">
					<div class="h-100" style="
								box-shadow: 0 2px 5px #718d51!important;
						border-radius: px;
						">
						<div style="background-color:white; border-radius: px;" class="h-100 aboutus-backimg">
							<div class="text-center icon-holder">
								<!-- Create an icon wrapped by the fa-stack class -->
								<span class="fa-stack fa-3x" style="color:#718d51;">
									<!-- The icon that will wrap the number -->
									<span class="fa fa-circle fa-stack-2x"></span>
									<!-- a strong element with the custom content, in this case a number -->
									<strong class="fa-stack-1x" style="color:white;">
										4
									</strong>
								</span>
							</div>
							<div class="card-body content-holder">
								<h5 class="card-title text-center" style="color:#555555;">' . jra_get_string(['upload', 'supporting_documents']) . '</h5>
								<p class="card-desc" style="color:#908b8b;">' . get_string('supporting_document_description', 'local_jra') . '</p>
								' . $btn . '
							</div>
							<div class="card-footer text-center">
								' . $complete_status . '
							</div>
						</div>
					</div>
				</div>
				<!-- ./end column -->
			</div>
		</div>		
	</div>
	';
	
	/******************* 
	**** STAGE 5 *******
	********************/
	$url = new moodle_url('/local/jra/application/applicant/confirmation.php');
	$url_view = new moodle_url('/local/jra/application/applicant/confirmation_view.php');
	//show edit and view button
	$stage = jra_app_get_applicant_stage($applicant);
	$btn = '<div class="text-center">';
	if($stage < 4)
	{
		$btn = $btn . '
			<button type="button" class="btn btn-primary mw-100 disabled" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['confirm_and_acknowledge']) . '</button>
		';
	}
	else
	{
		if($is_closed == '') //not closed
		{
			if($stage < 5)
			{
				$btn = $btn . '<a href="' . $url . '"><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['confirm_and_acknowledge']) . '</button></a>';
			}
			else
			{
				$btn = $btn . '<a href="' . $url . '"><button type="button" class="btn btn-secondary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['unconfirm_and_unacknowledge']) . '</button></a>';
			}
		}
		else
		{			
			if($stage < 5)
			{
				$a = jra_get_string(['application_not_confirmed']);
				$c = 'secondary';
			}
			else
			{
				$a = jra_get_string(['application_confirmed']);
				$c = 'success';
			}
			
			$btn = $btn . '<h4><span class="badge badge-' . $c . ' p-2">' . $a . '</span></h4>' . get_string('application_closed_disable_confirmation', 'local_jra');
		}
	}
	if($stage > 4) //completed stage 3
	{
		/* for upload, no need to have view as the view page is with the upload document as well
		$btn = $btn . jra_ui_space(3) . '
		<a href="' . $url_view . '"><button type="button" class="btn btn-secondary mw-100" style="text-overflow: ellipsis;overflow: hidden;">' . jra_get_string(['view', 'uploaded', 'documents']) . '</button></a>	
		';
		*/
		$complete_status = jra_theme_get_complete_status(true);
	}
	else
		$complete_status = jra_theme_get_complete_status(false);
	$btn = $btn . '</div>';
	$str = $str . '
	<br />
	<div class="pt-5">
		<div class="col-lg-12 mt-10">
			<div class="row col-lg-12 m-0">
				<!-- ./start column -->
				<div class="col-lg-12 col-md-12 my-15">
					<div class="h-100" style="
								box-shadow: 0 2px 5px #39557d!important;
						border-radius: px;
						">
						<div style="background-color:white; border-radius: px;" class="h-100 aboutus-backimg">
							<div class="text-center icon-holder">
								<!-- Create an icon wrapped by the fa-stack class -->
								<span class="fa-stack fa-3x" style="color:#39557d;">
									<!-- The icon that will wrap the number -->
									<span class="fa fa-circle fa-stack-2x"></span>
									<!-- a strong element with the custom content, in this case a number -->
									<strong class="fa-stack-1x" style="color:white;">
										5
									</strong>
								</span>
							</div>
							<div class="card-body content-holder">
								<h5 class="card-title text-center" style="color:#555555;">' . jra_get_string(['confirmation', 'and', 'acknowledgement']) . '</h5>
								<p class="card-desc" style="color:#908b8b;">' . get_string('confirmation_description', 'local_jra') . '</p>
								' . $btn . '
							</div>
							<div class="card-footer text-center">
								' . $complete_status . '
							</div>
						</div>
					</div>
				</div>
				<!-- ./end column -->
			</div>
		</div>		
	</div>
	';
		
	/******************* 
	**** STAGE 6 *******
	********************/
	$semester = $DB->get_record('si_semester', array('semester' => jra_get_semester()));
	$btn = '';
	$instruction = '';
	if($stage > 5 && $semester->display == 'Y') //completed stage 3
	{
		//$complete_status = jra_theme_get_complete_status(true);
		if($stage == 11) //approved
		{
			$confirm_date = '';
			$is_confirm_closed = false;
			if($semester->confirm_end_date != '' && $semester->confirm_end_date != 0)
			{
				$confirm_date = '<div class="text-center"><strong>(' . get_string('must_confirm_date', 'local_jra', jra_output_formal_datetime_12(jra_app_get_end_date($semester->confirm_end_date))) . ')</strong></div>';
				$is_confirm_closed = jra_app_is_end_date($semester->confirm_end_date);
			}
			$complete_status = '<div id="stage_six_result">' . jra_theme_acceptance_status($applicant) . '</div>';
			if($applicant->acceptance == 3 || ($is_confirm_closed && $applicant->acceptance == '')) //application closed or applican is suspended or not yet confirmed
			{
				$msg = '<h4 class="text-center">' . get_string('offer_suspended', 'local_jra') . '</h4>';
				$msg = $msg . '<div class="text-center">' . get_string('confirm_date_expired', 'local_jra', date('d/m/Y', $semester->confirm_end_date)) . '</div>';
				$result_message = jra_ui_alert($msg, 'warning', '', false, true);
			}
			else
			{
				if(!$is_confirm_closed && $applicant->acceptance != 5) //applicant must be unlocked
				{
					$btn = '<div class="text-center mt-4 mb-3"><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;" data-toggle="modal" data-target="#acceptanceModal">' . jra_get_string(['confirm_acceptance_of_offer']) . '</button></div>' . $confirm_date;
					$msg = '<h4 class="text-center">' . get_string('congratulation', 'local_jra') . '</h4>';
					$msg = $msg . '<div class="text-center">' . get_string('approve_message', 'local_jra') . '</div>';
					$msg = $msg . $btn;
				}
				else
				{
					$msg = '<h4 class="text-center">' . get_string('congratulation', 'local_jra') . '</h4>';
				}
				
				$result_message = jra_ui_alert($msg, 'success', '', false, true);
				if($semester->placement_test_date != '' && $semester->placement_test_date != 0)
				{
					$a = new stdClass();
					$a->date = date('d-M-Y', $semester->placement_test_date);
					$a->venue = $semester->placement_test_venue;
					$instruction_msg = '<h5>' . get_string('note', 'local_jra') . '</h5>' . get_string('placement_test_instruction_date', 'local_jra', $a);
					$instruction = jra_ui_box($instruction_msg, '', '', true);
				}
			}
		}
		else if($stage == 12) //waiting list
		{
			$msg = '<h4 class="text-center">' . jra_get_string(['waiting', 'list']) . '</h4>';
			$msg = $msg . '<div class="text-center">' . get_string('waiting_list_message', 'local_jra') . '</div>';
			$result_message = jra_ui_alert($msg, 'info', '', false, true);
			$complete_status = jra_theme_get_complete_status(true);
		}
		else if($stage == 13) //rejected
		{
			$msg = '<h4 class="text-center">' . jra_get_string(['application', 'declined']) . '</h4>';
			$msg = $msg . '<div class="text-center">' . get_string('reject_message', 'local_jra') . '</div>';
			$result_message = jra_ui_alert($msg, 'warning', '', false, true);
			$complete_status = jra_theme_get_complete_status(true);
		}
	}
	else
	{
		$complete_status = jra_theme_acceptance_status($applicant);
		$result_message = get_string('waiting_for_result', 'local_jra');
	}

	$str = $str . '
	<br />
	<div class="pt-5">
		<div class="col-lg-12 mt-10">
			<div class="row col-lg-12 m-0">
				<!-- ./start column -->
				<div class="col-lg-12 col-md-12 my-15">
					<div class="h-100" style="
								box-shadow: 0 2px 5px #ffc20c!important;
						border-radius: px;
						">
						<div style="background-color:white; border-radius: px;" class="h-100 aboutus-backimg">
							<div class="text-center icon-holder">
								<!-- Create an icon wrapped by the fa-stack class -->
								<span class="fa-stack fa-3x" style="color:#ffc20c;">
									<!-- The icon that will wrap the number -->
									<span class="fa fa-circle fa-stack-2x"></span>
									<!-- a strong element with the custom content, in this case a number -->
									<strong class="fa-stack-1x" style="color:white;">
										6
									</strong>
								</span>
							</div>
							<div class="card-body content-holder">
								<h5 class="card-title text-center" style="color:#555555;">' . jra_get_string(['application_result']) . '</h5>
								<p class="card-desc" style="color:#908b8b;">' . $result_message . '</p>
								' . $instruction . '
							</div>
							<div class="card-footer text-center">
								' . $complete_status . '
							</div>
						</div>
					</div>
				</div>
				<!-- ./start column -->
			</div>
		</div>		
	</div>
	';
	if($stage == 11)
		$str = $str . jra_theme_acceptance_modal();
	return $str;
}

function jra_theme_admitted($applicant)
{
	$str = '';
	$str = $str . '
	<div class="pt-3">
		<div class="col-lg-12 mt-10">
			<div class="row col-lg-12 m-0">
				<!-- ./start column -->
				<div class="col-lg-12 col-md-12 my-15">
				' . jra_ui_box(get_string('admit_student_message', 'local_jra', $applicant), '<h3 class="text-center">' . get_string('admit_student_title', 'local_jra') . '</h3>', '', true) . '
				</div>
				<!-- ./start column -->
			</div>
		</div>		
	</div>
	';
	return $str;
}

function jra_theme_get_complete_status($status)
{
	if($status) //done
	{
		$str = '
			<button type="button" class="btn btn-default border rounded-circle p-2" style="
			 background-color: #FFCC00; border-color:#FFCC00; !important; 
			">
			<i class="fa fa-check" aria-hidden="true"></i>
			</button>
			<h4 class="mt-2">' . get_string('completed', 'local_jra') . '</h4>
		';
	}
	else
	{
		$str = '
			<button type="button" class="btn btn-default border rounded-circle p-2" style="
			 background-color: #FFF; border-color:black; color:#FFF; !important; 
			">
			<i class="fa fa-circle" aria-hidden="true"></i>
			</button>
			<h4 class="mt-2">' . get_string('pending', 'local_jra') . '</h4>
		';
	}
	return $str;
}

function jra_theme_acceptance_status($applicant)
{
	if($applicant->acceptance == 1 || $applicant->acceptance == 5) //accepted
	{
		$str = '
			<button type="button" class="btn btn-default border rounded-circle p-2" style="
			 background-color: #FFCC00; border-color:#FFCC00; !important; 
			">
			<i class="fa fa-check" aria-hidden="true"></i>
			</button>
			<h4 class="mt-2">' . jra_get_string(['accepted_offer']) . '</h4>
		';
	}
	else if($applicant->acceptance == 2) //rejected
	{
		$str = '
			<button type="button" class="btn btn-default border rounded-circle p-2" style="
			 background-color: #FF0000; border-color:#FF0000; !important; 
			">
			<i class="fa fa-times" aria-hidden="true" style="color:#FFF;"></i>
			</button>
			<h4 class="mt-2">' . jra_get_string(['declined_offer']) . '</h4>
		';
	}
	else //if undefined, then pending
	{
		$str = jra_theme_get_complete_status(false);
	}
	return $str;
}


//get the moodle course given the section in jra
function jra_theme_slider()
{
	global $CFG;
	if (!isloggedin()) //not log in, show the slide
	{
		$str = '
		<div class="jumbotron text-center" style="background-image:url(' . $CFG->wwwroot . '/local/jra/images/slider/slide.jpg); background-position: center; background-size: cover; background-repeat:no-repeat; min-height:448px;">
		</div>	
		';
	}
	else
		$str = '<div class="mt-3">&nbsp;</div>';
	return $str;
	
//if want slider
	$str = '
<div class="carousel slide" id="CarouselCaptions" data-interval="5000" data-ride="carousel">
                <ol class="carousel-indicators">
                        <li data-slide-to="0" data-target="#CarouselCaptions" class="active"></li>
                        <li data-slide-to="1" data-target="#CarouselCaptions" class=""></li>
                        <li data-slide-to="2" data-target="#CarouselCaptions" class=""></li>
                        <li data-slide-to="3" data-target="#CarouselCaptions" class=""></li>
                </ol>

                <div class="carousel-inner" role="listbox">
                        <div class="carousel-item align-items-center active">
                        <!-- <div id="banner-img-slider" class="col-12" style="background-image:url(//localhost/campus/pluginfile.php/1/theme_remui/slideimage1/1593541982/slider01.jpg); background-position: center; background-size: cover; background-repeat:no-repeat; min-height:500px;"></div> -->
                            <img id="banner-img-slider" class="d-block img-fluid w-p100" alt="Image 0" src="//localhost/campus/pluginfile.php/1/theme_remui/slideimage1/1593541982/slider01.jpg" data-holder-rendered="true">
                        <div class="carousel-caption">
                            <div class="text_to_html"></div>
                        </div>
                        </div>
                        <div class="carousel-item align-items-center ">
                        <!-- <div id="banner-img-slider" class="col-12" style="background-image:url(//localhost/campus/pluginfile.php/1/theme_remui/slideimage2/1593541982/slider02.jpg); background-position: center; background-size: cover; background-repeat:no-repeat; min-height:500px;"></div> -->
                            <img id="banner-img-slider" class="d-block img-fluid w-p100" alt="Image 1" src="//localhost/campus/pluginfile.php/1/theme_remui/slideimage2/1593541982/slider02.jpg" data-holder-rendered="true">
                        <div class="carousel-caption">
                            <div class="text_to_html"></div>
                        </div>
                        </div>
                        <div class="carousel-item align-items-center ">
                        <!-- <div id="banner-img-slider" class="col-12" style="background-image:url(//localhost/campus/pluginfile.php/1/theme_remui/slideimage3/1593541982/slider03.jpg); background-position: center; background-size: cover; background-repeat:no-repeat; min-height:500px;"></div> -->
                            <img id="banner-img-slider" class="d-block img-fluid w-p100" alt="Image 2" src="//localhost/campus/pluginfile.php/1/theme_remui/slideimage3/1593541982/slider03.jpg" data-holder-rendered="true">
                        <div class="carousel-caption">
                            <div class="text_to_html"></div>
                        </div>
                        </div>
                        <div class="carousel-item align-items-center ">
                        <!-- <div id="banner-img-slider" class="col-12" style="background-image:url(//localhost/campus/pluginfile.php/1/theme_remui/slideimage4/1593541982/slider04.jpg); background-position: center; background-size: cover; background-repeat:no-repeat; min-height:500px;"></div> -->
                            <img id="banner-img-slider" class="d-block img-fluid w-p100" alt="Image 3" src="//localhost/campus/pluginfile.php/1/theme_remui/slideimage4/1593541982/slider04.jpg" data-holder-rendered="true">
                        <div class="carousel-caption">
                            <div class="text_to_html"></div>
                        </div>
                        </div>

                    <a class="carousel-control-prev" href="#CarouselCaptions" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon fa fa-chevron-left" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#CarouselCaptions" role="button" data-slide="next">
                        <span class="carousel-control-next-icon fa fa-chevron-right" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
	';
	return $str;
}


function jra_theme_icon_grid()
{
	$str = '
	<div class="mb-5">
	<div class="row text-center justify-content-center m-auto hidden-md-down">';
	
	$url = new moodle_url('/local/jra/application/applicant/index.php');
	$str = $str . '<div class="col-lg-3 py-10 ">
				<div>
					<a href="'.$url->out(false).'">
						<p>
							<i class="fa fa-street-view fa-3x" aria-hidden="true" style="color:#6670d1;"></i>
						</p>
						<h7 style="color: #555555;"><strong>' . jra_get_string(['applicants', 'management']) . '</strong></h7>
					</a>
				</div>
			</div>';
			
	$url = new moodle_url('/local/jra/admin/semester/index.php');
	$str = $str . '<div class="col-lg-3 py-10 ">
				<div>
					<a href="'.$url->out(false).'">
						<p>
							<i class="fa fa-calendar fa-3x" aria-hidden="true" style="color:#23c58e;"></i>
						</p>
						<h7 style="color: #555555;"><strong>' . get_string('semester', 'local_jra') . '</strong></h7>
					</a>
				</div>
			</div>';
	$access_rules = array(
		'role' => 'admin',
		'subrole' => 'all',
	);
	if(jra_access_control($access_rules, false))
	{
		$url = new moodle_url('/local/jra/admin/user/index.php');
		$str = $str . '<div class="col-lg-3 py-10 ">
					<div>
						<a href="'.$url->out(false).'">
							<p>
								<i class="fa fa-anchor fa-3x" aria-hidden="true" style="color:#4f1ebe;"></i>
							</p>
						</a>
							<h7 style="color: #555555;"><strong>' . jra_get_string(['user', 'management']) . '</strong></h7>
					</div>
				</div>';
				
		$url = new moodle_url('/local/jra/admin/index.php');
		$str = $str . '<div class="col-lg-3 py-10 ">
					<div>
						<a href="'.$url->out(false).'">
							<p>
								<i class="fa fa-cogs fa-3x" aria-hidden="true" style="color:#8d5c82;"></i>
							</p>
						</a>
							<h7 style="color: #555555;"><strong>' . get_string('settings', 'local_jra') . '</strong></h7>
					</div>
				</div>';
	}
	$str = $str . '</div>'; //end of row
/*			
	//2nd row
	$str = $str . '<div class="row text-center justify-content-center m-auto hidden-md-down">
				<div class="col-lg-3 py-10 ">
					<div>
							<p>
								<i class="fa fa-university fa-3x" aria-hidden="true" style="color:#92756f;"></i>
							</p>
						<h7 style="color: #555555;"><strong>Nam eget</strong></h7>
						<p class="font-size-12" style="color: #908b8b;">Holisticly harness just in timetechnologies are viarsus nunc, quis gravida magna mi a libero.</p>
					</div>
				</div>
				<div class="col-lg-3 py-10 ">
					<div>
							<p>
								<i class="fa fa-magic fa-3x" aria-hidden="true" style="color:#523773;"></i>
							</p>
						<h7 style="color: #555555;"><strong>Nam eget</strong></h7>
						<p class="font-size-12" style="color: #908b8b;">Holisticly harness just in timetechnologies are viarsus nunc, quis gravida magna mi a libero.</p>
					</div>
				</div>
				<div class="col-lg-3 py-10 ">
					<div>
							<p>
								<i class="fa fa-snowflake-o fa-3x" aria-hidden="true" style="color:#d8b008;"></i>
							</p>
						<h7 style="color: #555555;"><strong>Nam eget</strong></h7>
						<p class="font-size-12" style="color: #908b8b;">Holisticly harness just in timetechnologies are viarsus nunc, quis gravida magna mi a libero.</p>
					</div>
				</div>
				<div class="col-lg-3 py-10 ">
					<div>
							<p>
								<i class="fa fa-street-view fa-3x" aria-hidden="true" style="color:#6670d1;"></i>
							</p>
						<h7 style="color: #555555;"><strong>Nam eget</strong></h7>
						<p class="font-size-12" style="color: #908b8b;">Holisticly harness just in timetechnologies are viarsus nunc, quis gravida magna mi a libero.</p>
					</div>
				</div>';
	*/
	$str = $str .'</div>			
		</div>
	';
	return $str;
}

function jra_theme_about()
{
	$str = '
	<br />
	<div class="row m-0 text-center m-auto px-sm-0  pt-25 pr-lg-25 pb-25 pl-lg-25 container">
			<div class="col-lg-5 test  pb-50 px-50 pt-0" style="background-image: url();background-repeat: no-repeat; background-position: center;">
		<div>
		<h3>
			About us
		</h3>
		<p class="
			
			
			font-size-16
			" style="color:#908b8b;">
			
			Holisticly harness just in timetechnologies are viarsus nunc, quis gravida magna mi a libero.
		</p>
	
						<a href=""><button type="button" class="btn btn-primary mw-100" style="text-overflow: ellipsis;overflow: hidden;">Click Here</button></a>
	
				</div>
			</div>
			<div class="
				 col-lg-7 
				
				m-auto test
			
			">
				<div class="row col-lg-12 m-0">
					<div class="col-lg-6  col-md-6 my-15">
						<div class="h-100" style="
									box-shadow: 0 2px 5px #5ed0ba!important;
							border-radius: px;
							">
							<div style="background-color:white; border-radius: px;" class="h-100 aboutus-backimg">
								<div class="text-center icon-holder">
									<button type="button" class="btn btn-default  btn-floating  border rounded-circle p-4" style="
									 background-color: #5ed0ba; border-color:#5ed0ba; color:white; !important; 
									">
									<i class="fa fa-paint-brush fa-2x" aria-hidden="true"></i>
								</button>
								</div>
								<div class="card-body content-holder">
									<h5 class="card-title" style="color:#555555;">LOREM IPSUM</h5>
									<p class="card-desc" style="color:#908b8b;">Holisticly harness just in timetechnologies are viarsus nunc, quis gravida magna mi a libero.</p>
									<a class="card-link" href=""></a>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6  col-md-6 my-15">
						<div class="h-100" style="
									box-shadow: 0 2px 5px #718d51!important;
							border-radius: px;
							">
							<div style="background-color:white; border-radius: px;" class="h-100 aboutus-backimg">
								<div class="text-center icon-holder">
									<button type="button" class="btn btn-default  btn-floating  border rounded-circle p-4" style="
									 background-color: #718d51; border-color:#718d51; color:white; !important; 
									">
									<i class="fa fa-umbrella fa-2x" aria-hidden="true"></i>
								</button>
								</div>
								<div class="card-body content-holder">
									<h5 class="card-title" style="color:#555555;">LOREM IPSUM</h5>
									<p class="card-desc" style="color:#908b8b;">Holisticly harness just in timetechnologies are viarsus nunc, quis gravida magna mi a libero.</p>
									<a class="card-link" href=""></a>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6  col-md-6 my-15">
						<br /><br /><br />
						<div class="h-100" style="
									box-shadow: 0 2px 5px #39557d!important;
							border-radius: px;
							">
							<div style="background-color:white; border-radius: px;" class="h-100 aboutus-backimg">
								<div class="text-center icon-holder">
									<button type="button" class="btn btn-default  btn-floating  border rounded-circle p-4" style="
									 background-color: #39557d; border-color:#39557d; color:white; !important; 
									">
									<i class="fa fa-envira fa-2x" aria-hidden="true"></i>
								</button>
								</div>
								<div class="card-body content-holder">
									<h5 class="card-title" style="color:#555555;">LOREM IPSUM</h5>
									<p class="card-desc" style="color:#908b8b;">Holisticly harness just in timetechnologies are viarsus nunc, quis gravida magna mi a libero.</p>
									<a class="card-link" href=""></a>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6  col-md-6 my-15">
						<br /><br /><br />
						<div class="h-100" style="
									box-shadow: 0 2px 5px #ffc20c!important;
							border-radius: px;
							">
							<div style="background-color:white; border-radius: px;" class="h-100 aboutus-backimg">
								<div class="text-center icon-holder">
									<button type="button" class="btn btn-default  btn-floating  border rounded-circle p-4" style="
									 background-color: #ffc20c; border-color:#ffc20c; color:white; !important; 
									">
									<i class="fa fa-magic fa-2x" aria-hidden="true"></i>
								</button>
								</div>
								<div class="card-body content-holder">
									<h5 class="card-title" style="color:#555555;">LOREM IPSUM</h5>
									<p class="card-desc" style="color:#908b8b;">Holisticly harness just in timetechnologies are viarsus nunc, quis gravida magna mi a libero.</p>
									<a class="card-link" href=""></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	';
	return $str;
}

function jra_theme_listing()
{
	$str = '
	<div class="mt-5 mb-5">
		<div class="row">
			<div class="col-md-8 mb-12 align-items-stretch mb-3">                
				<div class="mb-3">
				<!--Card-->
				 <div class="card">
					<div class="row no-gutters">
						<div class="col-auto">
							<img src="https://mdbootstrap.com/img/Photos/Slides/img%20(122).jpg" alt="" width="200" height="133">
						</div>
						<div class="col">
							<div class="card-block p-3">
								<h4 class="card-title">Announcement 2</h4>
								<p class="card-text">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis
									  praesentium voluptatum deleniti atque corrupti quos</p>
								<a href="#" class="pull-right">Read more...</a>
							</div>
						</div>
					</div>
				  </div>
				</div>				
				<!--/. Card-->
				<div class="mb-3">
					<!--Card-->
					 <div class="card">
						<div class="row no-gutters">
							<div class="col-auto">
								<img src="https://mdbootstrap.com/img/Photos/Slides/img%20(112).jpg" alt="" width="200" height="133">
							</div>
							<div class="col">
								<div class="card-block p-3">
									<h4 class="card-title">Announcement 2</h4>
									<p class="card-text">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis
										  praesentium voluptatum deleniti atque corrupti quos</p>
									<a href="#" class="pull-right">Read more...</a>
								</div>
							</div>
						</div>
					  </div>
					<!--/. Card-->
				</div>				
			</div>	  
			<div class="col-md-4 mb-12 d-flex align-items-stretch mb-3">                
			  <div class="card">
                <div class="card-header card-header-danger">
                  <h4 class="card-title">Employees Stats</h4>
                  <p class="card-category">New employees on 15th September, 2016</p>
                </div>
                <div class="card-body">
                  <div class="stats">
                  
		            <!--/. table responsive-->
                    <div class="table-responsive">
                        <table class="table table-responsive no-margin">
                          <thead>
                          <tr>
                            <th>Pub No</th>
                            <th>Item</th>
                            <th>Popularity</th>
                          </tr>
                          </thead>
                          <tbody>
                          <tr>
                            <td><a href="pages/examples/invoice.html">ODN84952</a></td>
                            <td>Iphone 6s</td>
                            <td>
                              <span class="badge badge-primary badge-pill">10</span>
                            </td>
                          </tr>
                          <tr>
                            <td><a href="pages/examples/invoice.html">ODN84845</a></td>
                            <td>Apple TV</td>
                            <td>
                              <span class="badge badge-info badge-pill">41</span>
                            </td>
                          </tr>
                          <tr>
                            <td><a href="pages/examples/invoice.html">ODN84982</a></td>
                            <td>Samsung TV</td>
                            <td>
                              <span class="badge badge-success badge-pill">321</span>
                            </td>
                          </tr>
                          <tr>
                            <td><a href="pages/examples/invoice.html">ODN85452</a></td>
                            <td>Intex Smart Watch</td>
                            <td>
                              <span class="badge badge-danger badge-pill">56</span>
                            </td>
                          </tr>
                          <tr>
                            <td><a href="pages/examples/invoice.html">ODN94992</a></td>
                            <td>Onida AC</td>
                            <td>
                              <span class="badge badge-secondary badge-pill">31</span>
                            </td>
                          </tr>
                          <tr>
                            <td><a href="pages/examples/invoice.html">ODN98952</a></td>
                            <td>iPhone 7 Plus</td>
                            <td>
                              <span class="badge badge-success badge-pill">15</span>
                            </td>
                          </tr>
                          <tr>
                            <td><a href="pages/examples/invoice.html">ODN88989</a></td>
                            <td>Samsung LED</td>
                            <td>
                              <span class="badge badge-warning badge-pill">1</span>
                            </td>
                          </tr>
                          </tbody>
                        </table>
                      </div>
		            <!--/. table responsive-->
                  
                  </div>
                </div>
              </div>			  
              <!--/. end card-->
			</div>	  
		</div>
	</div>
	
<div class="card">
              <h3 class="card-header light-blue lighten-1 white-text text-uppercase font-weight-bold text-center py-3">Features
                List</h3>
              <div class="card-body">
                <ul class="">
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    Cras justo odio
                    <span class="badge badge-primary badge-pill">14</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    Dapibus ac facilisis in
                    <span class="badge badge-primary badge-pill">2</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    Morbi leo risus
                    <span class="badge badge-primary badge-pill">1</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    Cras justo odio
                    <span class="badge badge-primary badge-pill">14</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    Dapibus ac facilisis in
                    <span class="badge badge-primary badge-pill">2</span>
                  </li>
                </ul>
                <p class="text-small text-muted mb-0 pt-3">* At vero eos et accusamus et iusto ducimus.</p>
              </div>
            </div>
			
<div class="card mt-5">
                <div class="card-header card-header-warning">
                  <h4 class="card-title">Employees Stats</h4>
                  <p class="card-category">New employees on 15th September, 2016</p>
                </div>
                <div class="card-body table-responsive">
                  <table class="table table-hover">
                    <thead class="text-warning">
                      <tr><th>ID</th>
                      <th>Name</th>
                      <th>Salary</th>
                      <th>Country</th>
                    </tr></thead>
                    <tbody>
                      <tr>
                        <td>1</td>
                        <td>Dakota Rice</td>
                        <td>$36,738</td>
                        <td>Niger</td>
                      </tr>
                      <tr>
                        <td>2</td>
                        <td>Minerva Hooper</td>
                        <td>$23,789</td>
                        <td>Curacao</td>
                      </tr>
                      <tr>
                        <td>3</td>
                        <td>Sage Rodriguez</td>
                        <td>$56,142</td>
                        <td>Netherlands</td>
                      </tr>
                      <tr>
                        <td>4</td>
                        <td>Philip Chaney</td>
                        <td>$38,735</td>
                        <td>Korea, South</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>			
			  
	';
	return $str;
}

function jra_theme_acceptance_modal()
{
	global $CFG;
	$accept_url = "javascript:accept_offer(1)";
	$decline_url = "javascript:accept_offer(2)";
	$a = new stdClass();
	$a->placement_test_date = '';
	$a->placement_test_venue = '';
	
	$str = '
		<div class="modal fade" id="acceptanceModal">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
		
			  <!-- Modal Header -->
			  <div class="modal-header">
				<h4 class="modal-title">' . jra_get_string(['confirm', 'acceptance_of_offer']) . '</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			  </div>
		
			  <!-- Modal body -->
			  <div class="modal-body">
				<div id="modal-content">
				<form name="form_acceptance" method="post">
					' . get_string('accept_offer_text', 'local_jra', $a) . '
				</form>
				</div>
			  </div>
		
			  <!-- Modal footer -->
			  <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					' . get_string('cancel') . '
				</button>
					' . jra_ui_button(jra_get_string(['decline', 'offer']), $decline_url, 'warning') . '		
					' . jra_ui_button(jra_get_string(['accept', 'offer']), $accept_url) . '		
			  </div>
		
			</div>
		  </div>
		</div>	
		
		<script>
			function accept_offer(option)
			{
				$.ajax({
					type: "post",
					url: "' . $CFG->wwwroot . '/local/jra/application/applicant/accept_offer_action.php",
					data: {
							option : option
						  },
					success: function(data){
						$("#stage_six_result").html(data);
						$("#acceptanceModal").modal("hide");
					}
				});
			}
		</script>
	';
	
	return $str;
}

