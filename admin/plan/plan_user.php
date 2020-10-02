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
require_once '../../lib/jra_lookup_lib.php'; 
require_once '../../lib/jra_output_lib.php'; 
require_once '../../lib/jra_query_lib.php'; 
require_once($CFG->dirroot . '/local/jra/user/selector/user_selector.php');
require_once 'lib.php';

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/plan/plan_user.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
jra_access_control($access_rules);

$id = required_param('id', PARAM_INT);

//make sure the plan is valid
$plan = $DB->get_record('jra_plan', array('id' => $id));
if(!$plan)
	throw new moodle_exception(get_string('invalid_parameter', 'local_jra'));	

jra_set_session('ajax_user_plan_plan', $plan);

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['subscription', 'plan']), new moodle_url('index.php'));
$PAGE->navbar->add(jra_get_string(['plan', 'users']), new moodle_url('add_plan.php', $urlparams));

$return_params = jra_get_session('jra_plan_return_params');
if($return_params == '')
	$return_params = array();
$return_url = new moodle_url('index.php', $return_params);

//Show the user selection box
//for existing, it is any user with a plan because we don't want a user to have 2 plans
$existing = $DB->get_records_menu('jra_plan_user', array(), '', 'id, user_id');
$not_in = implode(',', $existing);
jra_set_session('ajax_user_plan_not_in', $not_in);

$available_selector = new jra_user_plan_available_selector('addselect');
$assigned_selector = new jra_user_assigned_plan_selector('existingselect');

if (optional_param('add', false, PARAM_BOOL) and confirm_sesskey()) 
{
    if ($to_add = $available_selector->get_selected_users()) 
	{
		jra_admin_plan_add_plan_user($to_add, $plan);
		//need to reinitialize
		//for existing, it is any user with a plan because we don't want a user to have 2 plans
		$existing = $DB->get_records_menu('jra_plan_user', array(), '', 'id, user_id');
		$not_in = implode(',', $existing);
		jra_set_session('ajax_user_plan_not_in', $not_in);
		$available_selector = new jra_user_plan_available_selector('addselect');
		$assigned_selector = new jra_user_assigned_plan_selector('existingselect');
    }
} 
else if (optional_param('remove', false, PARAM_BOOL) and confirm_sesskey()) 
{
    if ($to_remove = $assigned_selector->get_selected_users()) 
	{
		jra_admin_plan_remove_plan_user($to_remove);
		//need to reinitialize
		//for existing, it is any user with a plan because we don't want a user to have 2 plans
		$existing = $DB->get_records_menu('jra_plan_user', array(), '', 'id, user_id');
		$not_in = implode(',', $existing);
		jra_set_session('ajax_user_plan_not_in', $not_in);
		$available_selector = new jra_user_plan_available_selector('addselect');
		$assigned_selector = new jra_user_assigned_plan_selector('existingselect');
    }

}

echo $OUTPUT->header();

//content code starts here
jra_ui_page_title(jra_get_string(['plan', 'users']));

echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, jra_ui_icon('arrow-circle-left', '1', true) . ' ' . get_string('back', 'local_jra'), array('title' => get_string('back', 'local_jra'))) . '</div>';

$detail_data = array();
//one row of data
$obj = new stdClass();
$obj->column = 2;
$obj->left_content = '<strong>' . jra_get_string(['plan', 'id']) . '</strong>';
$obj->right_content = $plan->id;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->column = 2;
$obj->left_content = '<strong>' . jra_get_string(['plan', 'code']) . '</strong>';
$obj->right_content = $plan->plan_code;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->column = 2;
$obj->left_content = '<strong>' . jra_get_string(['plan', 'title']) . '</strong>';
$obj->right_content = $plan->title;
$detail_data[] = $obj;
//end of data row
//one row of data
$obj = new stdClass();
$obj->column = 2;
$obj->left_content = '<strong>' . jra_get_string(['total', 'subscribers']) . '</strong>';
$obj->right_content = jra_admin_plan_total_subscriber($plan);
$detail_data[] = $obj;
//end of data row

$plan_title = jra_ui_multi_column($detail_data, 2);

?>

<form id="assignform" name="form1" method="post" action="<?php echo $PAGE->url ?>">
<div class="card">
	<div class="card-header">
		<?php echo $plan_title; ?>
    </div>
    <div class="card-body">
    
    <div id="addadmisform">
        <div>
        <input type="hidden" name="sesskey" value="<?php p(sesskey()); ?>" />
    
        <table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
        <tr>
          <td id='existingcell'>
              <p>
                <label for="removeselect"><?php print_string('assigned_users', 'local_jra'); ?></label>
              </p>
              <?php $assigned_selector->display(); ?>
              </td>
          <td id="buttonscell">
            <p class="arrow_button">
                <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add'); ?>"
                       title="<?php print_string('add'); ?>" class="btn btn-secondary"/><br />
                <input name="remove" id="remove" type="submit" value="<?php echo get_string('remove').'&nbsp;'.$OUTPUT->rarrow(); ?>"
                       title="<?php print_string('remove'); ?>" class="btn btn-secondary"/><br />
            </p>
          </td>
          <td id="potentialcell">
              <p>
                <label for="addselect"><?php print_string('available_users', 'local_jra'); ?></label>
              </p>
              <?php $available_selector->display(); ?>
          </td>
        </tr>
        </table>
        </div>
    </div>
    
    </div>  <!-- end of card-body -->
</div>  <!-- end of card -->
</form>

<?php
$PAGE->requires->js('/local/jra/admin/plan/plan.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();