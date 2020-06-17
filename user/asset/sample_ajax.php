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
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../lib/jra_lib.php';
require_once '../../lib/jra_ui_lib.php';
require_once 'lib.php'; //local library (for index we name it as locallib because there is already an official lib.php from moodle
require_once 'form.php';

require_login(); //always require login

$urlparams = $_GET;
$PAGE->set_url('/local/jra/user/asset/add_asset.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

jra_include_jquery(); //call to include jquery in the page

$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$id = optional_param('id', false, PARAM_INT);
if($id)
{
	$qs = '?id=' . $id;
	$bc = ['update', 'asset'];
}
else
{
	$qs = '';
	$bc = ['add', 'asset'];
}

$PAGE->navbar->add(get_string('dashboard', 'local_jra'), new moodle_url($CFG->wwwroot . '/local/jra/dashboard/index.php', array()));
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('add_asset.php', $urlparams));

echo $OUTPUT->header();
//content code starts here

jra_ui_page_title(jra_get_string($bc));


	$str = '<form name="form1" action="">';
	$str = $str . '<div class="row">';
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('effective_date', 'local_jra') . '</div>';
	$str = $str . '	<div class="col-md-3 pt-3">' . '
		<div class="input-group mb-3">
		  <input type="text" id="datepicker" name="eff_date" class="form-control" readonly>
		  <div class="input-group-append">
			<span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
		  </div>
		</div>' . jra_ui_checkbox('default_date', false) . '&nbsp;&nbsp;&nbsp;' . get_string('set_to_course_eff_date', 'local_jra') . '
	' . '</div>';
	$str = $str . '	<div class="col-md-6 pt-3"></div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('tags', 'local_jra') . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . jra_ui_input('tags', 30) . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('birds', 'local_jra') . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . jra_ui_input('birds', 30) . '</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('output', 'local_jra') . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">
		<div class="ui-widget" style="margin-top:2em; font-family:Arial">
		  Result:
		  <div id="log" style="height: 200px; width: 300px; overflow: auto;" class="ui-widget-content"></div>
		</div>	
	</div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3"><button id="button">Get External Content</button></div>';
	$str = $str . '	<div class="col-md-9 pt-3"><div id="div1"><h2>Let jQuery AJAX Change This Text</h2></div></div>';
	//end of one row
	//one row
	$str = $str . '	<div class="col-md-3 pt-3">' . get_string('probation_if_fail', 'local_jra') . '</div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . jra_ui_select('probation_fail', $yesno, 'N') . '</div>';
	//end of one row
	//one row
	$add_url = "javascript:add_plan_course()";
	$str = $str . '	<div class="col-md-3 pt-3"></div>';
	$str = $str . '	<div class="col-md-9 pt-3">' . jra_ui_button_link($add_url, get_string('add'), 'primary') . '</div>';
	//end of one row
	$str = $str . '</div>';
	$str = $str . '</form>';
	
	$str = $str . '<script>';
	$str = $str . '
	  $( function() {
		$("#datepicker").datepicker();
		$("#datepicker").datepicker("option", "dateFormat", "dd-MM-yy");
	  } );
	  
	  $( function() {
		var availableTags = [
		  "ActionScript",
		  "AppleScript",
		  "Asp",
		  "BASIC",
		  "C",
		  "C++",
		  "Clojure",
		  "COBOL",
		  "ColdFusion",
		  "Erlang",
		  "Fortran",
		  "Groovy",
		  "Haskell",
		  "Java",
		  "JavaScript",
		  "Lisp",
		  "Perl",
		  "PHP",
		  "Python",
		  "Ruby",
		  "Scala",
		  "Scheme"
		];
		$("#tags").autocomplete({
		  source: availableTags
		});
	  } );	  
	  
	  $( function() {
		function log( message ) {
		  $( "<div>" ).text( message ).prependTo( "#log" );
		  $( "#log" ).scrollTop( 0 );
		}
/*	 
		$("#birds").autocomplete({
		  source: "search.php",
		  minLength: 1,
		  select: function( event, ui ) {
			log( "Selected: " + ui.item.value + " aka " + ui.item.id );
		  }
		});
*/

		$( "#birds" ).autocomplete({
		  source: function( request, response ) {
			$.ajax( {
			  url: "search.php",
			  dataType: "json",
			  data: {
				term: request.term
			  },
			  success: function( data ) {
				response( data );
			  }
			} );
		  },
		  minLength: 1,
		  select: function( event, ui ) {
			log( "Selected Item: " + ui.item.value   );
		  }
		});

	  } );	  
	  
	  $("#button").click(function(){
		$.ajax({
			url: "search.php", 
			success: function(result){
				alert("success" + result);
				$("#div1").html(result);
			},
			error: function(result){
				alert("error");
				$("#div1").html(result);
			},
		}); //ajax
		return false;
	  }); //#button

	  
	';
	$str = $str . '</script>';

echo $str;

//content code ends here
echo $OUTPUT->footer();

$PAGE->requires->js('/local/jra/user/asset/asset.js'); //global javascript
$PAGE->requires->js('/local/jra/script.js'); //global javascript
