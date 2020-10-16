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
require_once '../../lib/jra_output_lib.php';
require_once '../../lib/jra_file_lib.php';
require_once '../../lib/jra_app_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../user/lib.php'; //user library
require_once 'lib.php'; //local library

$post_data = $_POST;
if(isset($post_data['uid']))
	$id = $post_data['uid'];
else
	$id = required_param('id', PARAM_INT);

$urlparams = array('id' => $id);
$PAGE->set_url('/local/jra/application/applicant/view_applicant.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admission',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$semester = $DB->get_record('si_semester', array('semester' => jra_get_semester()));

//$applicant = $DB->get_record('si_applicant', array('id' => $id, 'semester' => $semester->semester));
$sql = "select * from v_si_applicant where id = '$id' and semester = '$semester->semester'";
$applicant = $DB->get_record_sql($sql);
if(!$applicant)
	throw new moodle_exception('Invalid applicant id.');

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$PAGE->navbar->add(jra_get_string(['applicant', 'management']), new moodle_url('index.php'));
$PAGE->navbar->add(jra_get_string(['view', 'applicant']), new moodle_url('view_applicant.php', $urlparams));
$PAGE->navbar->add(jra_output_show_user_name($applicant));

echo $OUTPUT->header();
//content code starts here
jra_ui_page_title(jra_get_string(['applicant','details']));

$return_page = jra_get_session('jra_applicant_tab');

if($return_page == 'index')
	$return_params = jra_get_session('si_applicant_return_params');
else if($return_page == 'confirmed')
	$return_params = jra_get_session('si_applicant_confirmed_return_params');
else if($return_page == 'admitted')
	$return_params = jra_get_session('si_applicant_admitted_return_params');

if($return_params == '')
	$return_params = array();

$return_url = new moodle_url($return_page . '.php', $return_params);

echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, jra_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_jra'), array('title' => get_string('back', 'local_jra'))) . '</div>';

$is_closed = jra_is_closed();
if($is_closed != '')
{
	//BUTTONS
	if($return_page == 'index') //from index
	{
		$btn_pending = $applicant->status == 5 ? 'primary' : 'light';
		$btn_approve = $applicant->status == 11 ? 'primary' : 'light';
		$btn_waiting = $applicant->status == 12 ? 'primary' : 'light';
		$btn_reject = $applicant->status == 13 ? 'primary' : 'light';
		$btn = '';
		$url = "javascript:update_status('" . $applicant->id . "', 5, '" . $PAGE->url->out(false) . "')";
		$btn = $btn . jra_ui_button(get_string('pending', 'local_jra'), $url, $btn_pending, '', '', true);
		$url = "javascript:update_status('" . $applicant->id . "', 11, '" . $PAGE->url->out(false) . "')";
		$btn = $btn . jra_ui_space(3);
		$btn = $btn . jra_ui_button(get_string('approved', 'local_jra'), $url, $btn_approve, '', '', true);
		$url = "javascript:update_status('" . $applicant->id . "', 12, '" . $PAGE->url->out(false) . "')";
		$btn = $btn . jra_ui_space(3);
		$btn = $btn . jra_ui_button(jra_get_string(['waiting', 'list']), $url, $btn_waiting, '', '', true);
		$url = "javascript:update_status('" . $applicant->id . "', 13, '" . $PAGE->url->out(false) . "')";
		$btn = $btn . jra_ui_space(3);
		$btn = $btn . jra_ui_button(get_string('rejected', 'local_jra'), $url, $btn_reject, '', '', true);

		echo '<div class="row mb-3">';
			echo '<div class="col-md-12">';
				echo '<div class="pull-right">';
					echo $btn;
				echo '</div>';
			echo '</div>';
		echo '</div>';
	}
	else if($return_page == 'confirmed' || $return_page == 'admitted')//from confirmede
	{
		if($applicant->acceptance == '' || $applicant->acceptance == 3) //unconfirmed or suspended
		{
			$btn_unconfirmed = $applicant->acceptance == '' ? 'primary' : 'light';
			$btn_confirmed = $applicant->acceptance == 1 ? 'primary' : 'light';
			$btn_suspended = $applicant->acceptance == 3 ? 'primary' : 'light';
			$btn = '';
			$url = "javascript:update_confirm_status('" . $applicant->id . "', 4, '" . $PAGE->url->out(false) . "')";
			$btn = $btn . jra_ui_button(get_string('unconfirmed', 'local_jra'), $url, $btn_unconfirmed, '', '', true);
			$btn = $btn . jra_ui_space(3);
			$url = "javascript:update_confirm_status('" . $applicant->id . "', 6, '" . $PAGE->url->out(false) . "')";
			$btn = $btn . jra_ui_button(get_string('confirmed', 'local_jra'), $url, $btn_confirmed, '', '', true);
			$btn = $btn . jra_ui_space(3);
			$url = "javascript:update_confirm_status('" . $applicant->id . "', 3, '" . $PAGE->url->out(false) . "')";
			$btn = $btn . jra_ui_button(get_string('suspended', 'local_jra'), $url, $btn_suspended, '', '', true);

			echo '<div class="row mb-3">';
				echo '<div class="col-md-12">';
					echo '<div class="pull-right">';
						echo $btn;
					echo '</div>';
				echo '</div>';
			echo '</div>';
		}
		if($applicant->acceptance == 1 || $applicant->acceptance == 5) //unconfirmed or suspended
		{
			$btn_unlocked = $applicant->acceptance == '1' ? 'primary' : 'light';
			$btn_locked = $applicant->acceptance == 5 ? 'primary' : 'light';
			$btn = '';
			$url = "javascript:update_confirm_status('" . $applicant->id . "', 4, '" . $PAGE->url->out(false) . "')";
			$btn = $btn . jra_ui_button(get_string('unconfirmed', 'local_jra'), $url, $btn_unconfirmed, '', '', true);
			$btn = $btn . jra_ui_space(3);
			$url = "javascript:update_confirm_status('" . $applicant->id . "', 1, '" . $PAGE->url->out(false) . "')";
			$btn = $btn . jra_ui_button(get_string('unlocked', 'local_jra'), $url, $btn_unlocked, '', '', true);
			$btn = $btn . jra_ui_space(3);
			$url = "javascript:update_confirm_status('" . $applicant->id . "', 5, '" . $PAGE->url->out(false) . "')";
			$btn = $btn . jra_ui_button(get_string('locked', 'local_jra'), $url, $btn_locked, '', '', true);
			$btn = $btn . jra_ui_space(3) . '|' . jra_ui_space(3);
			//for final admission
			$url = "javascript:show_final_admission('$applicant->id')";
			$btn = $btn . jra_ui_button(jra_get_string(['final_admission']), $url, 'warning', '', '', true);

			echo '<div class="row mb-3">';
				echo '<div class="col-md-12">';
					echo '<div class="pull-right">';
						echo $btn;
					echo '</div>';
				echo '</div>';
			echo '</div>';
		}
	}
}

if($applicant->admit_status == '1') //admitted
	jra_ui_alert(get_string('applicant_final_admitted', 'local_jra'), 'success', '', false);

//PERSONAL INFORMATION
$id_type = jra_lookup_get_list('personal_info', 'id_type', '', true);
$countries = jra_lookup_countries();

$detail_data = array();
//one row of data
$obj = new stdClass();
$obj->title = get_string('app_ref', 'local_jra') . ' #';
$obj->content = jra_app_ref_number($applicant);
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('name', 'local_jra') . ' (' . get_string('english', 'local_jra') . ')';
$obj->content = $applicant->fullname;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')';
$obj->content = $applicant->fullname_a;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = jra_get_string(['national_id']);
$obj->content = $applicant->national_id == '' ? '-' : $applicant->national_id . ' (' . $id_type[$applicant->id_type] . ')';
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('nationality', 'local_jra');
$obj->content = $applicant->nationality == '' ? '-' : $countries[$applicant->nationality];
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('date_of_birth', 'local_jra');
//$obj->content = $applicant->dob == 0 ? '-' : jra_to_hijrah(date('d-M-Y', $applicant->dob));
$obj->content = $applicant->dob_hijri == '' ? '-' : $applicant->dob_hijri;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('age', 'local_jra');
$obj->content = $applicant->dob_hijri == '' ? '-' : jra_app_get_age_hijri($applicant->dob_hijri);
$detail_data[] = $obj;
//end of data row
//one row of data
$marital_status = jra_lookup_marital_status();
$obj = new stdClass();
$obj->title = get_string('marital_status', 'local_jra');
$obj->content = $applicant->marital_status == '' ? '-' : $marital_status[$applicant->marital_status];
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('blood_group', 'local_jra');
$obj->content = $applicant->blood_type == '' ? '-' : $applicant->blood_type;
$detail_data[] = $obj;
//end of data row

$str = jra_ui_data_detail($detail_data, 2);
jra_ui_box($str, '<strong>' . jra_get_string(['personal_information']) . '</strong>');


//ACADEMIC INFORMATION

$detail_data = array();

if($semester->admission_type == "regular"){
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('secondary_school_result', 'local_jra');
	$obj->content = $applicant->secondary . ' (' . get_string('weight', 'local_jra') . ': ' . $semester->secondary_weight. ')';
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('tahseli', 'local_jra');
	$obj->content = $applicant->tahseli . ' (' . get_string('weight', 'local_jra') . ': ' . $semester->tahseli_weight. ')';
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('qudorat', 'local_jra');
	$obj->content = $applicant->qudorat . ' (' . get_string('weight', 'local_jra') . ': ' . $semester->qudorat_weight. ')';
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = jra_get_string(['aggregation', 'points']);
	$obj->content = $applicant->aggregation;
	$detail_data[] = $obj;
	//end of data row
}
else {

	$graduated_from = jra_lookup_university();
	$obj = new stdClass();
	$obj->title = get_string('graduate_from', 'local_jra');
	$obj->content = $applicant->graduated_from == '' ? '-' : $graduated_from[$applicant->graduated_from];
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$graduated_major = jra_lookup_major();
	$obj = new stdClass();
	$obj->title = get_string('major', 'local_jra');
	$obj->content = $applicant->graduated_major == '' ? '-' : $graduated_major[$applicant->graduated_major];
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = get_string('year_graduation', 'local_jra');
	$obj->content = $applicant->graduated_year;
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$graduated_maxgpa = jra_lookup_maxgpa();
	$obj = new stdClass();
	$obj->title = get_string('max_cgpa', 'local_jra');
	$obj->content = $applicant->graduated_max_gpa == '' ? '-' : $graduated_maxgpa[$applicant->graduated_max_gpa];
	$detail_data[] = $obj;
	//end of data row
	//one row of data
	$obj = new stdClass();
	$obj->title = jra_get_string(['cgpa', 'points']);
	$obj->content = $applicant->graduated_gpa;
	$detail_data[] = $obj;
}

$str = jra_ui_data_detail($detail_data, 2);
echo '<br />';
jra_ui_box($str, '<strong>' . jra_get_string(['academic_information']) . '</strong>');

//CONTACT INFORMATION

$contact = $DB->get_record('si_applicant_contact', array('applicant_id' => $applicant->id));
$kindship = jra_lookup_kindship();

$detail_data = array();
//one row of data
$obj = new stdClass();
$obj->title = get_string('address', 'local_jra');
$obj->content = jra_application_applicant_format_contact($contact);
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = jra_get_string(['phone_mobile']);
$obj->content = $contact->phone_mobile == '' ? '-' : $contact->phone_mobile;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('phone_home', 'local_jra');
$obj->content = $contact->phone_home == '' ? '-' : $contact->phone_home;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = get_string('email', 'local_jra');
$obj->content = $contact->email_primary == '' ? '-' : $contact->email_primary;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = jra_get_string(['guardian_name']);
$obj->content = $contact->contact_name == '' ? '-' : $contact->contact_name;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = jra_get_string(['guardian_relationship']);
$obj->content = $contact->contact_relationship == '' ? '-' : $kindship[$contact->contact_relationship];
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->title = jra_get_string(['guardian_mobile']);
$obj->content = $contact->contact_mobile == '' ? '-' : $contact->contact_mobile;
$detail_data[] = $obj;
//end of data row

$str = jra_ui_data_detail($detail_data, 2);
echo '<br />';
jra_ui_box($str, '<strong>' . jra_get_string(['contact_information']) . '</strong>');

//SUPPORTING DOCUMENT

$str = jra_application_list_supporting_document($applicant, true, true);
echo '<br />';
jra_ui_box($str, '<strong>' . jra_get_string(['uploaded', 'supporting', 'documents']) . '</strong>');
?>

<!-- The Modal -->
<div class="modal fade" id="myModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title"><?= jra_get_string(['applicant', 'filter']); ?></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
		<form name="form_filter" method="post">
	       	<input type="hidden" name="confirm" value="1" />
            <div id="modal-content">
                <?php echo get_string($dialog_text, 'local_jra'); ?>
            </div>
        </form>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
			<?= get_string('cancel'); ?>
        </button>
		<?php
			$btn_url = "javascript:update_admit('" . $PAGE->url->out(false) . "')";
			echo jra_ui_button(jra_ui_space(5) . get_string('ok') . jra_ui_space(5), $btn_url);
		?>
      </div>

    </div>
  </div>
</div>

<?php
$PAGE->requires->js('/local/jra/application/applicant/applicant.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript
echo $OUTPUT->footer();
