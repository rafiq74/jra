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
 * @license   http://www.gnu.org/copycenter/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../lib/jra_lib.php';
require_once '../../lib/jra_ui_lib.php';
require_once '../../lib/jra_output_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../lib/jra_app_lib.php';
require_once '../../lib/jra_file_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/jra/application/applicant/confirmed.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admission',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$PAGE->navbar->add(jra_get_string(['applicant', 'management']), new moodle_url('index.php'));

//Any form processing code
if(isset($_POST['delete_id'])) //only allow site admin to delete
{
	$del_applicant = $DB->get_record('si_applicant', array('id' => $_POST['delete_id']));
	if($del_applicant)
	{
		//now delete the file
		if($del_applicant->national_id_file != '')
		{
			$file_path = jra_file_supporting_document_path(jra_get_semester()) . $del_applicant->national_id_file;
			jra_file_delete_file($file_path);
		}
		if($del_applicant->secondary_file != '')
		{
			$file_path = jra_file_supporting_document_path(jra_get_semester()) . $del_applicant->secondary_file;
			jra_file_delete_file($file_path);
		}
		if($del_applicant->tahseli_file != '')
		{
			$file_path = jra_file_supporting_document_path(jra_get_semester()) . $del_applicant->tahseli_file;
			jra_file_delete_file($file_path);
		}
		if($del_applicant->qudorat_file != '')
		{
			$file_path = jra_file_supporting_document_path(jra_get_semester()) . $del_applicant->qudorat_file;
			jra_file_delete_file($file_path);
		}
		$cascade = array(
			'si_applicant_contact' => 'applicant_id',
		);
		jra_query_delete_cascade('si_applicant', $_POST['delete_id'], $cascade);
	}
}

echo $OUTPUT->header();
//content code starts here
jra_ui_page_title(jra_get_string(['applicant', 'management']));

$currenttab = 'confirmed'; //change this according to tab
include('tabs.php');
jra_set_session('jra_applicant_tab', 'confirmed');
echo $OUTPUT->box_start('jra_tabbox');

$semester = $DB->get_record('si_semester', array('semester' => jra_get_semester()));
if(!$semester)
	throw new moodle_exception('Error!!! No semester defined');

$status_list = jra_lookup_admission_confirm_status();
$per_page_list = jra_lookup_per_page();
$city_list = jra_lookup_city_applicant(get_string('all', 'local_jra'));

$qs = $_GET;
//create the master filter of program
unset($qs['status']); //have to remove existing status query string
unset($qs['city']); //have to remove existing status query string
unset($qs['per_page']); //have to remove existing user type query string

$a_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
$index_url = "javascript:refresh_index('".$a_url->out(false)."')";

//$aggregate = jra_ui_filter_value('aggregate', '', 'confirmedlist', $aggregate_list, true);
$aggregate = '';
$per_page = jra_ui_filter_value('per_page', jra_global_var('PER_PAGE'), 'confirmedlist', $per_page_list, false);

$city = jra_ui_filter_value('city', '', 'confirmedlist', $city_list, true);

$status = jra_ui_filter_value('status', '5', 'confirmedlist', $status_list, true);

$is_closed = jra_is_closed();
if($is_closed != '') //application already close, then we allow processing
{
	$action_item = array();
	if($status == '')
	{
		$action_item[] = array(
			'title' => jra_get_string(['suspend', 'all', 'in_the_list']), // - for divider
			'url' => "javascript:update_confirm_status_all(3, '" . $PAGE->url->out(false) . "')",
			'target' => '', //_blank
			'icon' => 'minus-circle',
		);
	}
	if($status == 1) //If already accepted, allow to lock
	{
		$action_item[] = array(
			'title' => jra_get_string(['lock', 'all', 'in_the_list']), // - for divider
			'url' => "javascript:update_confirm_status_all(5, '" . $PAGE->url->out(false) . "')",
			'target' => '', //_blank
			'icon' => 'check-circle',
		);
	}
	if($status == 3)
	{
		$action_item[] = array(
			'title' => jra_get_string(['unconfirm', 'all', 'in_the_list']), // - for divider
			'url' => "javascript:update_confirm_status_all(4, '" . $PAGE->url->out(false) . "')",
			'target' => '', //_blank
			'icon' => 'check-circle',
		);
	}
	if($status == 5)
	{
		$action_item[] = array(
			'title' => jra_get_string(['unlock', 'all', 'in_the_list']), // - for divider
			'url' => "javascript:update_confirm_status_all(1, '" . $PAGE->url->out(false) . "')",
			'target' => '', //_blank
			'icon' => 'check-circle',
		);
	}
	if($status == 1 || $status == 5)
	{
		$action_item[] = array(
			'title' => jra_get_string(['admit', 'all', 'in_the_list']), // - for divider
			'url' => "javascript:update_confirm_status_all(7, '" . $PAGE->url->out(false) . "')",
			'target' => '', //_blank
			'icon' => 'check-circle',
		);
	}
	$action_menu = '<div class="row pull-right pr-3">';
	$action_menu = $action_menu . jra_get_string(['records_per_page']) . '</strong>&nbsp;&nbsp;&nbsp;' . jra_ui_select('per_page', $per_page_list, $per_page, $index_url);
	$action_menu = $action_menu . jra_ui_space(3);
	$action_menu = $action_menu . jra_ui_dropdown_menu($action_item, get_string('action', 'local_jra'));
	$action_menu = $action_menu . '</div><br /><br />';
	echo $action_menu;

}
else
{
	$a = new stdClass();
	$a->start_date = date('d-M-Y, h:i A', $semester->start_date);
	$end_date = strtotime(date('d-M-Y', $semester->end_date) . '+ 1 day') - 1; //end time has to add 24 hour minus one to get the final minute
	$a->end_date = date('d-M-Y, h:i A', $end_date);
	$action_msg = get_string('admission_open_period', 'local_jra', $a) . ' ' . get_string('cannot_process_admission', 'local_jra');
	jra_ui_alert($action_msg, 'warning', '', false);
}

$master_filter = '';
$master_filter = $master_filter . '<span class="pull-right"><strong>';
$master_filter = $master_filter . get_string('city', 'local_jra') . '</strong>&nbsp;&nbsp;&nbsp;' . jra_ui_select('city', $city_list, $city, $index_url);
$master_filter = $master_filter . jra_ui_space(3);
$master_filter = $master_filter . get_string('status', 'local_jra') . '</strong>&nbsp;&nbsp;&nbsp;' . jra_ui_select('status', $status_list, $status, $index_url);
$master_filter = $master_filter . jra_ui_space(3);
$excel_url = new moodle_url('confirmed_excel.php');
$master_filter = $master_filter . html_writer::link($excel_url, jra_ui_icon('download', 1, true), array('title' => get_string('download_excel_report', 'local_jra'), 'target' => '_blank'));
$master_filter = $master_filter . '</span>';

$status_filter = '';
if($status != '')
	$status_filter = " and acceptance = '$status'";
else
	$status_filter = " and acceptance is null"; //if all, take those that is completed

$city_filter = '';
if($city != '')
	$city_filter = " and address_city = '$city'";

$sql = "select * from v_si_applicant";
$conditionWhere = " institute = '" . jra_get_institute() . "' and semester = '" . $semester->semester . "' and deleted = 0 and status = 11 and (admit_status is null or admit_status = 0) $status_filter $city_filter";

//	$condition['aggregate'] = $aggregate;
//setup the table options
$options = array(
	'sql' => $sql, //incase if we need to use sql. Skip the where part and put in under conditionText
	'condition' => array(), //and sql condition
	'conditionText' => $conditionWhere, //and sql condition in textual format
	'table_class' => 'generaltable', //table class is either generaltable (moodle standard table), or table for plain
	'responsive' => true, //responsive table
	'border-table' => false, //make the table bordered
	'condensed-table' => false, //compact table (not applicable under generaltable)
	'hover-table' => false, //make the table hover (not applicable under generaltable)
	'action' => true, //automatic add form and javascript for action edit and delete
	'sortable' => true, //enable clicking of heading to sort
	'default_sort_field' => 'aggregation',
	'desc' => true,
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //the field to pick up as the id (reference) when going for detail view
	'detail_var' => 'id', //variable name in query string for id. var=id
	'search' => true, //allow search
	'default_search_field' => 'appid', //default field choose for search
	'view_page' => 'view_applicant.php',
	'edit_page' => '',
	'perpage' => $per_page, //use large number to remove pagination
	'master_filter' => $master_filter, //primary master filter for master-child relation
	'delete_admin' => true, //only allow to delete if it is siteadmin
	'search_reference' => true,
	'limit' => '',
//	'debug' => true,
);
//setup the table fields
$fields = array(
	'#' => array(), //# for numbering
	'appid' => array(
		'header'=>get_string('app_ref', 'local_jra') . ' #', //for custom header
		'align' => 'left',
		'size' => '10%',
		'sort' => '', //indicates that these must be sorted together
		'format' => 'app_ref',
	),
	'fullname' => array(
		'header'=>get_string('name', 'local_jra') . ' (' . get_string('english', 'local_jra') . ')', //for custom header
		'align' => 'left',
		'size' => '15%',
	),
	'fullname_a' => array(
		'header'=>get_string('name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', //for custom header
		'align' => 'right',
		'size' => '15%',
	),
	'national_id' => array(
		'header'=>jra_get_string(['national_id']), //for custom header
		'align' => 'left',
		'size' => '10%',
	),
	'aggregation' => array(
		'header'=>jra_get_string(['aggregate']), //for custom header
		'align' => 'center',
		'size' => '5%',
	),
	'phone_mobile' => array(
		'header'=>jra_get_string(['mobile']), //for custom header
		'align' => 'left',
		'size' => '5%',
	),
	'address_city' => array(
		'header'=>jra_get_string(['city']), //for custom header
		'align' => 'left',
		'size' => '10%',
	),
	'acceptance' => array(
		'header'=>get_string('confirmation', 'local_jra'), //for custom header
		'align' => 'center',
		'size' => '10%',
		'format' => 'lookup',
		'lookup_list' => $status_list,
		'show_reference' => true,
	),
	'acceptance_date' => array(
		'header'=>jra_get_string(['date']), //for custom header
		'align' => 'center',
		'size' => '10%',
		'format' => 'date',
	),
	'*' => array(), //action
);

//output the table
echo jra_ui_dump_table('si_applicant_confirmed', $options, $fields, 'local_jra');

echo $OUTPUT->box_end();

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
			$btn_url = "javascript:update_filter('" . $PAGE->url->out(false) . "')";
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
