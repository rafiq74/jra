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

define("MAX_USERS_TO_LIST_PER_ROLE", 20);
require_once '../../../../config.php';
require_once '../../lib/jra_lib.php'; 
require_once '../../lib/jra_ui_lib.php';
require_once '../../lib/jra_lookup_lib.php';
require_once '../../lib/jra_output_lib.php';
require_once '../../lib/jra_query_lib.php';
require_once 'lib.php'; //local library
require_once($CFG->dirroot . '/local/jra/user/selector/user_selector.php');

$urlparams = $_GET;
$PAGE->set_url('/local/jra/admin/user/role.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$access_rules = array(
	'role' => 'admin',
	'subrole' => 'all',
);
jra_access_control($access_rules);

//initialize the role
$role = optional_param('role', '', PARAM_TEXT);
$subrole = '';
$prevRole = jra_get_session('ajax_user_role_role'); //try to get if there is any previous role

if($role == '')
{
	//see if there is any session
	$role = jra_get_session('ajax_user_role_role');
	if($role == '') //no session
	{
		$role = jra_admin_user_init_role(); //initialize it
		$subrole = 'all';
	}
}
if($prevRole != $role) //role has changed, subrole reset
	$subrole = 'all';
else
{
	//initialize the subrole
	$subrole = optional_param('subrole', '', PARAM_TEXT);
	if($subrole == '') //subrole not initialized yet
	{
		$subrole = jra_get_session('ajax_user_role_subrole');
		if($subrole == '')
			$subrole = 'all';
	}
}

jra_set_session('ajax_user_role_role', $role);
jra_set_session('ajax_user_role_subrole', $subrole);

//also set the session for the listing
$list_role = jra_get_session('user_account_role');
if($list_role != '') //if it is not all
	jra_set_session('user_account_role', $role);	

//frontpage - for 2 columns with standard menu on the right
//jra - 1 column
$PAGE->set_pagelayout('jra');
$PAGE->set_title(jra_site_fullname());
$PAGE->set_heading(jra_site_fullname());
$PAGE->navbar->add(get_string('system', 'local_jra') . ' '  . get_string('administration'), new moodle_url('../index.php', array()));
$PAGE->navbar->add(jra_get_string(['user', 'management']), new moodle_url('index.php'));
$PAGE->navbar->add(get_string('roles'), new moodle_url('role.php'));

echo $OUTPUT->header();
//content code starts here
jra_ui_page_title(get_string('roles', 'local_jra') . ' ' . get_string('management', 'local_jra'));
$currenttab = 'role'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('jra_tabbox');
jra_set_session('jra_admin_user_role_page', 'role');

$add_url = new moodle_url('/local/jra/admin/user/role_list.php', array());
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, jra_ui_icon('navicon', '1', true) . ' ' . get_string('list_view', 'local_jra'), array('title' => get_string('list_view', 'local_jra'))) . '</div>';


$existing = $DB->get_records_menu('jra_user_role', array('role' => $role, 'subrole' => $subrole), '', 'id, user_id');
$not_in = implode(',', $existing);
jra_set_session('ajax_user_role_not_in', $not_in);

$available_selector = new jra_user_role_available_selector('addselect');
$assigned_selector = new jra_user_assigned_role_selector('existingselect');

if (optional_param('add', false, PARAM_BOOL) and confirm_sesskey()) 
{
    if ($to_add = $available_selector->get_selected_users()) 
	{
		$post_role = $_POST['role'];
		$post_subrole = $_POST['subrole'];
		$post_role_value = jra_admin_user_format_role_value($_POST['role_value']);
		jra_admin_user_assign_role($to_add, $post_role, $post_subrole, $post_role_value);
		//need to reinitialize
		$existing = $DB->get_records_menu('jra_user_role', array('role' => $post_role, 'subrole' => $post_subrole), '', 'id, user_id');
		$not_in = implode(',', $existing);
		jra_set_session('ajax_user_role_not_in', $not_in);
		$available_selector = new jra_user_role_available_selector('addselect');
		$assigned_selector = new jra_user_assigned_role_selector('existingselect');
    }
} 
else if (optional_param('remove', false, PARAM_BOOL) and confirm_sesskey()) 
{
    if ($to_remove = $assigned_selector->get_selected_users()) 
	{
		$post_role = $_POST['role'];
		$post_subrole = $_POST['subrole'];
		$post_role_value = jra_admin_user_format_role_value($_POST['role_value']);
		jra_admin_user_remove_role($to_remove);
		//need to reinitialize
		$existing = $DB->get_records_menu('jra_user_role', array('role' => $post_role, 'subrole' => $post_subrole), '', 'id, user_id');
		$not_in = implode(',', $existing);
		jra_set_session('ajax_user_role_not_in', $not_in);
		$available_selector = new jra_user_role_available_selector('addselect');
		$assigned_selector = new jra_user_assigned_role_selector('existingselect');
    }

}

if(isset($post_role_value))
	$role_value = $post_role_value;
else
	$role_value = '';
$form = jra_admin_user_role_search_form($role, $subrole, 'role', $role_value);

?>

<form id="assignform" name="form1" method="post" action="<?php echo $PAGE->url ?>">
<div class="card">
	<div class="card-header">
		<?php echo $form; ?>
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
echo $OUTPUT->box_end();

$PAGE->requires->js('/local/jra/admin/user/user.js');
$PAGE->requires->js('/local/jra/script.js'); //global javascript

echo $OUTPUT->footer();
