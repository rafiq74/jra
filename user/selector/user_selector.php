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
 * Potential admin user selector.
 *
 * @package    core_role
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/jra/user/selector/lib.php');
require_once($CFG->dirroot.'/local/jra/lib/jra_lib.php');

/////////////////////////////////////////////////////////////////////
///////CLASSES FOR USER ROLE////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
//for list of user who are eligible to be assigned a role
class jra_user_role_available_selector extends jra_user_selector_base {
    /**
     * Create instance.
     *
     * @param string $name control name
     * @param array $options should have two elements with keys groupid and courseid.
     */
	var $section;
	var $not_in;
	
    public function __construct($name = null, $options = array()) {
        global $CFG;
        if (is_null($name)) {
            $name = 'addselect';
        }
        $options['multiselect'] = true;
        $options['exclude'] = explode(',', $CFG->siteadmins);
        parent::__construct($name, $options);
    }

    public function find_users($search) {
        global $CFG, $DB;
		$this->extrafields = array();
		$not_in = $_SESSION['ajax_user_role_not_in'];
		$params = array(); //leave it empty
		$sortparams = array(); //leave it empty
        $wherecondition = "user_type = 'employee' and deleted = 0 and active_status = 'A' and institute = '" . jra_get_institute() . "'";
		if($not_in != '')
			$wherecondition = $wherecondition . " and id not in($not_in)";
		if($search != '')
			$wherecondition = $wherecondition . " and (username like '%$search%' or first_name like '%$search%' or family_name like '%$search%')";

        $fields      = "select id, username, first_name as firstname, family_name as lastname";
        $countfields = 'SELECT COUNT(1)';

        $sql = " from v_jra_user
                WHERE $wherecondition";

        $order = ' ORDER BY first_name, family_name';

        // Check to see if there are too many to show sensibly.
        if (!$this->is_validating()) {
            $potentialcount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialcount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialcount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potential_users', 'local_jra', $search);
        } else {
            $groupname = get_string('potential_users', 'local_jra');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = $CFG->admin . '/roles/lib.php';
        return $options;
    }
}

//for list of user who already been assigned a role
class jra_user_assigned_role_selector extends jra_user_selector_base {
    /**
     * Create instance.
     *
     * @param string $name control name
     * @param array $options should have two elements with keys groupid and courseid.
     */
    public function __construct($name = null, $options = array()) {
        global $CFG;
        if (is_null($name)) {
            $name = 'existingselect';
        }
        $options['multiselect'] = true;
        $options['exclude'] = explode(',', $CFG->siteadmins);
        parent::__construct($name, $options);
    }

    public function find_users($search) {
        global $CFG, $DB;
		$role = $_SESSION['ajax_user_role_role'];
		$subrole = $_SESSION['ajax_user_role_subrole'];
		$this->extrafields = array('role_value');
		$params = array(); //leave it empty
		$sortparams = array(); //leave it empty
		$institute = jra_get_institute();
        $wherecondition = "a.role = '$role' and a.subrole = '$subrole' and a.institute = '$institute'";
		if($search != '')
			$wherecondition = $wherecondition . " and (b.username like '%$search%' or b.first_name like '%$search%' or b.family_name like '%$search%')";

        $fields      = "select a.id, b.username, b.first_name as firstname, b.family_name as lastname, a.role_value";
        $countfields = 'SELECT COUNT(1)';

        $sql = " from {jra_user_role} a inner join {jra_user} b on a.user_id = b.id
                WHERE $wherecondition";

        $order = ' ORDER BY b.username';

        // Check to see if there are too many to show sensibly.
        if (!$this->is_validating()) {
            $potentialcount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialcount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialcount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('assigned_users', 'local_jra', $search);
        } else {
            $groupname = get_string('assigned_users', 'local_jra');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = $CFG->admin . '/roles/lib.php';
        return $options;
    }
	
}


/////////////////////////////////////////////////////////////////////
///////CLASSES FOR PLAN USER////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
//for list of user who are eligible to be assigned a role
class jra_user_plan_available_selector extends jra_user_selector_base {
    /**
     * Create instance.
     *
     * @param string $name control name
     * @param array $options should have two elements with keys groupid and courseid.
     */
	var $section;
	var $not_in;
	
    public function __construct($name = null, $options = array()) {
        global $CFG;
        if (is_null($name)) {
            $name = 'addselect';
        }
        $options['multiselect'] = true;
        $options['exclude'] = explode(',', $CFG->siteadmins);
        parent::__construct($name, $options);
    }

    public function find_users($search) {
        global $CFG, $DB;
		$this->extrafields = array();
		$not_in = $_SESSION['ajax_user_plan_not_in'];
		$params = array(); //leave it empty
		$sortparams = array(); //leave it empty
        $wherecondition = "user_type = 'public' and deleted = 0 and institute = '" . jra_get_institute() . "'";
		if($not_in != '')
			$wherecondition = $wherecondition . " and id not in($not_in)";
		if($search != '')
			$wherecondition = $wherecondition . " and (username like '%$search%' or first_name like '%$search%' or family_name like '%$search%')";

        $fields      = "select id, username, first_name as firstname, family_name as lastname";
        $countfields = 'SELECT COUNT(1)';

        $sql = " from v_jra_user
                WHERE $wherecondition";

        $order = ' ORDER BY first_name, family_name';

        // Check to see if there are too many to show sensibly.
        if (!$this->is_validating()) {
            $potentialcount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialcount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialcount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potential_users', 'local_jra', $search);
        } else {
            $groupname = get_string('potential_users', 'local_jra');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = $CFG->admin . '/roles/lib.php';
        return $options;
    }
}

//for list of user who already been assigned a plan
class jra_user_assigned_plan_selector extends jra_user_selector_base {
    /**
     * Create instance.
     *
     * @param string $name control name
     * @param array $options should have two elements with keys groupid and courseid.
     */
    public function __construct($name = null, $options = array()) {
        global $CFG;
        if (is_null($name)) {
            $name = 'existingselect';
        }
        $options['multiselect'] = true;
        $options['exclude'] = explode(',', $CFG->siteadmins);
        parent::__construct($name, $options);
    }

    public function find_users($search) {
        global $CFG, $DB;
		$plan = $_SESSION['ajax_user_plan_plan'];
		$this->extrafields = array($plan->plan_code);
		$params = array(); //leave it empty
		$sortparams = array(); //leave it empty
		$institute = jra_get_institute();
        $wherecondition = "a.plan_code = '$plan->plan_code' and a.institute = '$institute'";
		if($search != '')
			$wherecondition = $wherecondition . " and (b.username like '%$search%' or b.first_name like '%$search%' or b.family_name like '%$search%')";

        $fields      = "select a.id, b.username, b.first_name as firstname, b.family_name as lastname, a.plan_code";
        $countfields = 'SELECT COUNT(1)';

        $sql = " from {jra_plan_user} a inner join {jra_user} b on a.user_id = b.id
                WHERE $wherecondition";

        $order = ' ORDER BY b.first_name, b.family_name';

        // Check to see if there are too many to show sensibly.
        if (!$this->is_validating()) {
            $potentialcount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialcount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialcount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('assigned_users', 'local_jra', $search);
        } else {
            $groupname = get_string('assigned_users', 'local_jra');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = $CFG->admin . '/roles/lib.php';
        return $options;
    }
	
}