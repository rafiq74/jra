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
require_once '../../lib/jra_lookup_lib.php'; 
require_once '../../lib/jra_query_lib.php'; 
require_once '../../lib/jra_output_lib.php'; 
require_once 'lib.php';

require_once 'form.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/plan/edit_plan.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$id = required_param('id', PARAM_INT);
$dataid = optional_param('dataid', false, PARAM_INT);
$operation = optional_param('op', '', PARAM_TEXT);

if($dataid)
{
	$qs = '?id=' . $id.  '&dataid=' . $dataid;
}
else
{
	$qs = '?id=' . $id;
}

$bc = ['update', 'plan'];

//make sure the plan is valid
$co = $DB->get_record('jra_plan', array('id' => $id));
if(!$co)
	throw new moodle_exception(get_string('invalid_parameter', 'local_jra'));	

if(isset($_POST['delete_id'])) //need to delete plan
{
	$delete_id = $_POST['delete_id'];
	$DB->delete_records('jra_plan', array('id' => $delete_id));
	$max_plan = jra_app_get_plan($co, false); //get the max record
	if(!$max_plan) //no more plan left, we redict user back to the index page
	{
		$return_params = jra_get_session('jra_plan_return_params'); //make sure we get the index session information
		if($return_params == '')
			$return_params = array();
		$return_url = new moodle_url('index.php', $return_params);
		redirect($return_url->out(false));	
	}
	if($delete_id == $id) //the id used to display the plan is delete, we have to get the next id)
	{
		$id = $max_plan->id;
	}
	//finally, we redirect it again to refresh the page after delete. This will avoid the resubmit warning if user refresh and also
	//if the selected record is deleted, we can show the page with the next max eff_date record.
	$return_url = new moodle_url('edit_plan.php', array('id' => $id));
	redirect($return_url->out(false));	
}


//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());

$PAGE->navbar->add('JRA ' . strtolower(get_string('administration')), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['subscription', 'plan']), new moodle_url('index.php'));
$PAGE->navbar->add(jra_get_string($bc), new moodle_url('add_plan.php', $urlparams));

$return_params = jra_get_session('jra_plan_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);

$same_effective_date = false;
//put before header so we can redirect
$isDuplicate = false;
$same_effective_date = false;
$mform = new plan_form();
if ($mform->is_cancelled()) 
{
	//not sue
} 
else if ($data = $mform->get_data()) 
{	
	if(isset($data->cancel))
	{
		unset($params['op']);
		$return_url = new moodle_url('edit_plan.php', array('id' => $data->id));
	    redirect($return_url->out(false));
		die;
	}
	//for effective date, we allow it to go freely. No restriction except that we do not allow 2 records with same effective date
	$duplicate_condition = array(
		'plan_code' => $data->plan_code,
		'eff_date' => $data->eff_date,
		'country' => $data->country,
	);
	$isDuplicate = jra_query_is_duplicate('jra_plan', $duplicate_condition, $data->id);
	if(!$isDuplicate) //no duplicate, update it
	{
		$updated = false;
		$existing_id = $data->id; //we remember it so when we call insert we don't forget the id
		if(isset($data->saveasbutton)) //save as, so always create new
		{
			//get the current record
			$cur = $DB->get_record('jra_plan', array('id' => $data->id));
			if($cur->eff_date != $data->eff_date) //make sure no duplicate effective date
			{
				//initialize the new record with missing field
				$data->id = '';
				$DB->insert_record('jra_plan', $data);	
				$updated = true;
			}
			else
				$same_effective_date = true;
		}
		else
		{
			$DB->update_record('jra_plan', $data);
			$updated = true;
		}			
		if($updated)
		{
			unset($params['op']);
			$return_url = new moodle_url('edit_plan.php', array('id' => $existing_id));
			redirect($return_url->out(false));
		}
	}
	else
		$same_effective_date = true;
}

echo $OUTPUT->header();
if($isDuplicate)
{
//	jra_ui_alert(get_string('less_effective_date', 'local_jra'), 'danger');
//	$operation = 'edit';
}
if($same_effective_date)
{
	jra_ui_alert(get_string('same_effective_date', 'local_jra'), 'danger');
	$operation = 'edit';
}

$view = optional_param('view', '', PARAM_TEXT);
if($view == '') //try to get from session
{
	$view = jra_get_session('plan_edit_view');
	if($view == '')
		$view = 'active';
}
if($view == 'all')
{
	$view_flip = 'active';
}
else
{
	$view_flip = 'all';
}
jra_set_session('plan_edit_view', $view);

//content code starts here
jra_ui_page_title(jra_get_string($bc));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, jra_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_jra'), array('title' => get_string('back', 'local_jra'))) . '</div>';

if($operation == 'edit')
{
	if($dataid)
	{
		//get the program data (false if not created)
		$toform = $DB->get_record('jra_plan', array('id' => $dataid));
		$toform->plan_code_temp = $toform->plan_code;
		$mform->set_data($toform);
	}
	$mform->display();
}
else
{
	jra_admin_plan_edit_plan($id, $view);
}

$PAGE->requires->js('/local/jra/admin/plan/plan.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();
?>

<!-- The Modal -->
<div class="modal fade" id="deleteModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title"><?= jra_get_string(['confirm', 'delete', 'plan']); ?></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <form id="form_delete" name="form_delete" method="post" action="">
	      	<div id="modal-delete-content">
	        </div>
        </form>	  
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
			<?= get_string('cancel'); ?>
        </button>
		<?php
			$btn_url = "javascript:delete_plan()";
			echo jra_ui_button(get_string('delete', 'local_jra'), $btn_url, 'primary');		
		?>
      </div>

    </div>
  </div>
</div>

