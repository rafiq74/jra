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
require_once '../../lib/jra_app_lib.php'; 
require_once 'lib.php'; //local library
require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/confirmation.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
jra_allow_application(); //make sure it is permissable

$return_url = new moodle_url($CFG->wwwroot, $return_params);

$jra_user = $USER->jra_user;
$applicant = jra_app_get_applicant();
$semester = jra_get_semester();

if(!$applicant || $applicant->status < 4)
    redirect($return_url);

if(jra_is_closed(true))
    redirect($return_url);

$id = $applicant->id;

$bc = ['confirmation', 'and', 'acknowledgement'];

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('confirmation.php', $urlparams));

if(isset($_POST['confirm']))
{
	$confirm = $_POST['confirm'];
	$now = time();	
	$obj = new stdClass();
	$obj->id = $applicant->id;
	$obj->date_updated = $now;
	
	if($confirm == 4) //confirming
	{
		$obj->status = 5;
		$text = jra_ui_alert(get_string('confirm_successful', 'local_jra', $a), 'success', '', true, true);
	}
	else
	{
		$obj->status = 4;
		$text = jra_ui_alert(get_string('unconfirm_successful', 'local_jra', $a), 'success', '', true, true);
	}
		
	$DB->update_record('si_applicant', $obj);	
		
	jra_ui_set_flash_message($text, 'jra_information_updated');
	redirect($return_url); //we redirect to kill the post session
}

echo $OUTPUT->header();

if($applicant->status == 4) //for confirmation
{
	$alert_type = 'warning';
	$alert_message = 'confirmation_message';
	$btn_text = jra_get_string(['confirm_and_acknowledge']);
	$btn_color = 'primary';
	$dialog_text = 'confirm_application';
	$dialog_button = 'confirm';
}
else //for unconfirmation
{
	$alert_type = 'danger';
	$alert_message = 'unconfirmation_message';
	$btn_text = jra_get_string(['unconfirm_and_unacknowledge']);
	$btn_color = 'warning';
	$dialog_text = 'unconfirm_application';
	$dialog_button = 'unconfirm';
}
//content code starts here
$title = '<div class="text-center"><h3>' . jra_get_string($bc) . '</h3></div>';

$content = jra_ui_alert(get_string($alert_message, 'local_jra') . '<br /><br /><strong>' . get_string('confirm_warning_message', 'local_jra') . '</strong>', $alert_type, '', false, true);

$confirm_url = "javascript:show_confirm_modal()";			
$url = new moodle_url($CFG->wwwroot);
$btn = '<div class="text-center">';
$btn = $btn . '<button type="button" class="btn btn-'.$btn_color.' mw-100" style="text-overflow: ellipjra;overflow: hidden;" data-toggle="modal" data-target="#myModal">' . $btn_text . '</button>';
$btn = $btn . jra_ui_space(2);
$btn = $btn . '<a href="' . $url . '"><button type="button" class="btn btn-secondary mw-100" style="text-overflow: ellipjra;overflow: hidden;">' . get_string('cancel') . '</button></a>';
$btn = $btn . '</div>';

echo jra_ui_box($content, $title, $btn, true);
;
	
//$mform->display();


?>

<!-- The Modal -->
<div class="modal fade" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title"><?= $btn_text; ?></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
      	<div id="modal-content">
		<form name="form_confirm" method="post">
        	<input type="hidden" name="confirm" value="<?= $applicant->status; ?>" />
            <?php echo get_string($dialog_text, 'local_jra'); ?>
        </form>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
			<?= get_string('cancel'); ?>
        </button>
		<?php
			$btn_url = "javascript:confirm_application()";
			echo jra_ui_button(get_string($dialog_button, 'local_jra'), $btn_url, $btn_color);		
		?>
      </div>

    </div>
  </div>
</div>


<?php
$PAGE->requires->js('/local/jra/application/applicant/applicant.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript
echo $OUTPUT->footer();
