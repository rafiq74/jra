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
$PAGE->set_url('/local/jra/application/applicant/index.php', $urlparams);
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
		if($del_applicant->transcript_file != '')
		{
			$file_path = jra_file_supporting_document_path(jra_get_semester()) . $del_applicant->transcript_file;
			jra_file_delete_file($file_path);
		}
		if($del_applicant->uni_approval_file != '')
		{
			$file_path = jra_file_supporting_document_path(jra_get_semester()) . $del_applicant->uni_approval_file;
			jra_file_delete_file($file_path);
		}

		//delete from moodle
		$DB->delete_records('user', array('idnumber' => $del_applicant->user_id));
		//delete from jra_user
		$DB->delete_records('jra_user', array('id' => $del_applicant->user_id));
		$cascade = array(
			'si_applicant_contact' => 'applicant_id',
		);
		jra_query_delete_cascade('si_applicant', $_POST['delete_id'], $cascade);
	}
}

echo $OUTPUT->header();
//content code starts here
jra_ui_page_title(jra_get_string(['applicant', 'management']));

$currenttab = 'complete'; //change this according to tab
include('tabs.php');
jra_set_session('jra_applicant_tab', 'index');
echo $OUTPUT->box_start('jra_tabbox');

$semester = $DB->get_record('si_semester', array('semester' => jra_get_semester()));

if(!$semester)
	throw new moodle_exception('Error!!! No semester defined');

$status_list = jra_lookup_admission_status(get_string('all', 'local_jra'));
$university_list = jra_lookup_university();
$major_list = jra_lookup_major();
$per_page_list = jra_lookup_per_page();
//$city_list = jra_lookup_city_applicant(get_string('all', 'local_jra'));

$qs = $_GET;
//create the master filter of program
unset($qs['status']); //have to remove existing status query string
unset($qs['city']); //have to remove existing status query string
unset($qs['per_page']); //have to remove existing user type query string
$a_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
$index_url = "javascript:refresh_index('".$a_url->out(false)."')";

//$aggregate = jra_ui_filter_value('aggregate', '', 'applicantlist', $aggregate_list, true);
$aggregate = '';
$per_page = jra_ui_filter_value('per_page', jra_global_var('PER_PAGE'), 'applicantlist', $per_page_list, false);

$is_closed = jra_is_closed();
if($is_closed != '')
{
	$action_item = array();
	$action_item[] = array(
		'title' => jra_get_string(['pending', 'all', 'in_the_list']), // - for divider
		'url' => "javascript:update_status_all(5, '" . $PAGE->url->out(false) . "')",
		'target' => '', //_blank
		'icon' => 'minus-circle',
	);
	$action_item[] = array(
		'title' => jra_get_string(['approve', 'all', 'in_the_list']), // - for divider
		'url' => "javascript:update_status_all(11, '" . $PAGE->url->out(false) . "')",
		'target' => '', //_blank
		'icon' => 'check-circle',
	);
	$action_item[] = array(
		'title' => jra_get_string(['waiting', 'list', 'all', 'in_the_list']), // - for divider
		'url' => "javascript:update_status_all(12, '" . $PAGE->url->out(false) . "')",
		'target' => '', //_blank
		'icon' => 'exclamation-circle',
	);
	$action_item[] = array(
		'title' => jra_get_string(['reject', 'all', 'in_the_list']), // - for divider
		'url' => "javascript:update_status_all(13, '" . $PAGE->url->out(false) . "')",
		'target' => '', //_blank
		'icon' => 'times-circle',
	);
	$action_item[] = array(
		'title' => '-', // - for divider
		'url' => "",
		'target' => '', //_blank
		'icon' => '',
	);
	$action_item[] = array(
		'title' => jra_get_string(['notify_all_in_list']), // - for divider
		'url' => "javascript:update_status_all(20, '" . $PAGE->url->out(false) . "')",
		'target' => '', //_blank
		'icon' => 'send',
	);
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
	//only show the per page
	$action_menu = '<div class="row pull-right pr-3">';
	$action_menu = $action_menu . jra_get_string(['records_per_page']) . '</strong>&nbsp;&nbsp;&nbsp;' . jra_ui_select('per_page', $per_page_list, $per_page, $index_url);
	$action_menu = $action_menu . '</div><br /><br />';
	echo $action_menu;
}
$status = jra_ui_filter_value('status', '5', 'applicantlist', $status_list, true);
//$city = jra_ui_filter_value('city', '', 'applicantlist', $city_list, true);

$master_filter = '';
$master_filter = $master_filter . jra_ui_space(3);
$filter_url = "javascript:show_apply_filter()";
$master_filter = $master_filter . jra_ui_button(jra_get_string(['applicant', 'filter']), $filter_url, 'dark', '', 'btn-sm');
$master_filter = $master_filter . '<span class="pull-right"><strong>';
//$master_filter = $master_filter . get_string('city', 'local_jra') . '</strong>&nbsp;&nbsp;&nbsp;' . jra_ui_select('city', $city_list, $city, $index_url);
//$master_filter = $master_filter . jra_ui_space(3);
$master_filter = $master_filter . jra_ui_hidden('city', '');
$master_filter = $master_filter . get_string('status', 'local_jra') . '</strong>&nbsp;&nbsp;&nbsp;' . jra_ui_select('status', $status_list, $status, $index_url);
$master_filter = $master_filter . jra_ui_space(3);
$excel_url = new moodle_url('index_excel.php');
$master_filter = $master_filter . html_writer::link($excel_url, jra_ui_icon('download', 1, true), array('title' => get_string('download_excel_report', 'local_jra'), 'target' => '_blank'));
$master_filter = $master_filter . '</span>';

$status_filter = '';
if($status != '')
	$status_filter = " and status = '$status'";
else
	$status_filter = " and status >= " . jra_app_read_only_stage(); //if all, take those that is completed

//$city_filter = '';
//if($city != '')
//	$city_filter = " and address_city = '$city'";

$aggregate_filter = '';
$city_filter = '';

if($semester->admission_type == "regular"){
		if($semester->min_aggregate != '')
			$aggregate_filter = " and aggregation >= '$semester->min_aggregate'";

		if($semester->city_filter != '')
			$city_filter = " and address_city >= '$semester->city_filter'";
		}

if($semester->num_applicant != '' && $semester->num_applicant != 0)
	$limit = $semester->num_applicant;
else
	$limit = '';


$sql = "select * from v_si_applicant";
$conditionWhere = " institute = '" . jra_get_institute() . "' and semester = '" . $semester->semester . "' and deleted = 0 and acceptance is null $status_filter $aggregate_filter $city_filter";


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
	'limit' => $limit,
//	'debug' => true,
);


if($semester->admission_type =='regular'){
	$academic = array(
		'tahseli' => array(
		'header'=>jra_get_string(['tahseli']), //for custom header
		'align' => 'center',
		'size' => '5%',
		'sortable' => false,
	),
	'qudorat' => array(
		'header'=>jra_get_string(['qudorat']), //for custom header
		'align' => 'center',
		'size' => '5%',
		'sortable' => false,
	),
	'secondary' => array(
		'header'=>jra_get_string(['secondary']), //for custom header
		'align' => 'center',
		'size' => '5%',
		'sortable' => false,
	),
	'aggregation' => array(
		'header'=>jra_get_string(['aggregate']), //for custom header
		'align' => 'center',
		'size' => '5%',
		'sortable' => false,
	),
	'status' => array(
		'header'=>get_string('status', 'local_jra'), //for custom header
		'align' => 'center',
		'size' => '10%',
		'format' => 'lookup',
		'lookup_list' => $status_list,
		'show_reference' => true,
		'sortable' => false,
	),
	'*' => array(), //action
);
}
else{
	$academic = array(
		'graduated_from' => array(
		'header'=>jra_get_string(['graduate_from']), //for custom header
		'align' => 'center',
		'size' => '13%',
		'format' => 'lookup',
		'lookup_list' => $university_list,
		'sortable' => false,
	),

	'graduated_major' => array(
		'header'=>jra_get_string(['major']), //for custom header
		'align' => 'center',
		'size' => '7%',
		'format' => 'lookup',
		'lookup_list' => $major_list,
		'sortable' => false,
	),
	'graduated_year' => array(
		'header'=>jra_get_string(['year_graduation']), //for custom header
		'align' => 'center',
		'size' => '7%',
		'sortable' => false,
	),
	'graduated_gpa' => array(
		'header'=>jra_get_string(['cgpa']), //for custom header
		'align' => 'center',
		'size' => '5%',
		'sortable' => false,
	),
	'status' => array(
		'header'=>get_string('status', 'local_jra'), //for custom header
		'align' => 'center',
		'size' => '10%',
		'format' => 'lookup',
		'lookup_list' => $status_list,
		'show_reference' => true,
		'sortable' => false,
	),
	'*' => array(), //action
);
}


//setup the table fields
$fields = array(
	'#' => array(), //# for numbering
	'appid' => array(
		'header'=>get_string('app_ref', 'local_jra') . ' #', //for custom header
		'align' => 'left',
		'size' => '10%',
		'sort' => '', //indicates that these must be sorted together
		'format' => 'app_ref',
		'sortable' => false,
	),
	'fullname' => array(
		'header'=>get_string('name', 'local_jra') . ' (' . get_string('english', 'local_jra') . ')', //for custom header
		'align' => 'left',
		'size' => '15%',
		'sortable' => false,
	),
	'fullname_a' => array(
		'header'=>get_string('name', 'local_jra') . ' (' . get_string('arabic', 'local_jra') . ')', //for custom header
		'align' => 'right',
		'size' => '15%',
		'sortable' => false,
	),
	'national_id' => array(
		'header'=>jra_get_string(['national_id']), //for custom header
		'align' => 'left',
		'size' => '10%',
		'sortable' => false,
	),
	'phone_mobile' => array(
		'header'=>jra_get_string(['mobile']), //for custom header
		'align' => 'left',
		'size' => '5%',
		'sortable' => false,
	),
	'address_city' => array(
		'header'=>jra_get_string(['city']), //for custom header
		'align' => 'left',
		'size' => '10%',
		'sortable' => false,
	),
);


$mergearr = array_merge($fields, $academic);


//output the table
echo jra_ui_dump_table('si_applicant', $options, $mergearr, 'local_jra');

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
