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

//this is the function that will collect all the output to the frontpage and output it. The function is called in the theme body_frontpage
//and then output it to the mustache page
function jra_theme_frontpage()
{
	global $USER;
	$str = '';
	if (isloggedin() and !isguestuser()) //log in user
	{
		$str = '';
		if(jra_get_user_type() == 'student')
		{
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
	$str = '';
	return $str;
}

function jra_theme_admin_preface()
{
	global $DB;
	$str = '';
	return $str;
}

function jra_theme_public_preface()
{
	global $DB;
	$str = '';
	return $str;
}

//course code in moodle has the format
//course_code (section) : id
function jra_theme_marketing()
{
	global $DB;
	$str = '';
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
	$str = '';
	$str = $str . '
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
	$url = new moodle_url('/local/jra/admin/university/index.php');
	$str = $str . '<div class="col-lg-3 py-10 ">
				<div>
					<a href="'.$url->out(false).'">
						<p>
							<i class="fa fa-graduation-cap fa-3x" aria-hidden="true" style="color:#4f1ebe;"></i>
						</p>
					</a>
						<h7 style="color: #555555;"><strong>' . jra_get_string(['university']) . '</strong></h7>
				</div>
			</div>';
			
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
	}
	$str = $str . '</div>'; //end of row
	$str = $str .'</div>';
	//2nd row
	$str = $str . '
	<div class="row text-center justify-content-center m-auto hidden-md-down">';
	
	$access_rules = array(
		'role' => 'admin',
		'subrole' => 'all',
	);
	if(jra_access_control($access_rules, false))
	{
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
	$str = $str .'</div>'; //end of span
	$str = $str .'</div>'; //end of mb-5
	
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
