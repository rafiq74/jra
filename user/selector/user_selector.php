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

require_once($CFG->dirroot.'/local/sis/user/selector/lib.php');
require_once($CFG->dirroot.'/local/sis/lib/sis_lib.php');

/////////////////////////////////////////////////////////////////////
///////CLASSES FOR ENROLLMENT////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
//for students who are enrolled in a section
class sis_user_enrolled_student_selector extends sis_user_selector_base {
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
		$section_id = $_SESSION['section_id'];
		$this->extrafields = array('program');
		$params = array(); //leave it empty
		$sortparams = array(); //leave it empty
        $wherecondition = "a.section_id = '$section_id'";
		if($search != '')
			$wherecondition = $wherecondition . " and (b.appid like '%$search%' or b.first_name like '%$search%' or b.family_name like '%$search%')";

        $fields      = "select a.id, b.appid, CONCAT(IFNULL(b.first_name, ''), ' ', IFNULL(b.father_name, ''), ' ', IFNULL(b.grandfather_name, '')) as firstname, b.family_name as lastname, a.program";
        $countfields = 'SELECT COUNT(1)';

        $sql = " from {si_section_student} a inner join v_si_active_student b on a.user_id = b.user_id
                WHERE $wherecondition";

        $order = ' ORDER BY b.appid';

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
            $groupname = get_string('existing_students', 'local_sis', $search);
        } else {
            $groupname = get_string('existing_students', 'local_sis');
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

//for students who are term activated in the provided semester
class sis_user_term_student_selector extends sis_user_selector_base {
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
		$this->section = $sec;
		$this->not_in = $notin;
        $options['multiselect'] = true;
        $options['exclude'] = explode(',', $CFG->siteadmins);
        parent::__construct($name, $options);
    }

    public function find_users($search) {
        global $CFG, $DB;
		$this->extrafields = array('program');
		$institute = sis_get_institute();
		$semester = $_SESSION['ajax_semester'];		
		$not_in = $_SESSION['ajax_not_in'];
		$params = array(); //leave it empty
		$sortparams = array(); //leave it empty
        $wherecondition = "a.semester = '$semester' and a.institute = '$institute'";
		if($not_in != '')
			$wherecondition = $wherecondition . " and a.user_id not in($not_in)";
		if($search != '')
			$wherecondition = $wherecondition . " and (b.appid like '%$search%' or b.first_name like '%$search%' or b.family_name like '%$search%')";

        $fields      = "select b.user_id as id, b.appid, b.fullname as firstname, b.fullname_a as lastname, a.program";
        $countfields = 'SELECT COUNT(1)';

        $sql = " from {si_student_term} a inner join v_si_active_student b on a.user_id = b.user_id
                WHERE $wherecondition";

        $order = ' ORDER BY b.appid';

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
            $groupname = get_string('potential_students', 'local_sis', $search);
        } else {
            $groupname = get_string('potential_students', 'local_sis');
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
///////CLASSES FOR USER ROLE////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
//for list of user who are eligible to be assigned a role
class sis_user_role_available_selector extends sis_user_selector_base {
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
        $wherecondition = "user_type = 'employee' and institute = '" . sis_get_institute() . "'";
		if($not_in != '')
			$wherecondition = $wherecondition . " and user_id not in($not_in)";
		if($search != '')
			$wherecondition = $wherecondition . " and (appid like '%$search%' or first_name like '%$search%' or family_name like '%$search%')";

        $fields      = "select user_id as id, appid, first_name as firstname, family_name as lastname";
        $countfields = 'SELECT COUNT(1)';

        $sql = " from v_si_userlogin
                WHERE $wherecondition";

        $order = ' ORDER BY appid';

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
            $groupname = get_string('potential_users', 'local_sis', $search);
        } else {
            $groupname = get_string('potential_users', 'local_sis');
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
class sis_user_assigned_role_selector extends sis_user_selector_base {
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
		$institute = sis_get_institute();
        $wherecondition = "a.role = '$role' and a.subrole = '$subrole' and a.institute = '$institute'";
		if($search != '')
			$wherecondition = $wherecondition . " and (b.appid like '%$search%' or b.first_name like '%$search%' or b.family_name like '%$search%')";

        $fields      = "select a.id, b.appid, b.first_name as firstname, b.family_name as lastname, a.role_value";
        $countfields = 'SELECT COUNT(1)';

        $sql = " from {si_role_user} a inner join {si_user} b on a.user_id = b.id
                WHERE $wherecondition";

        $order = ' ORDER BY b.appid';

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
            $groupname = get_string('assigned_users', 'local_sis', $search);
        } else {
            $groupname = get_string('assigned_users', 'local_sis');
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
///////CLASSES FOR DORMITORY SWAP////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
//for list of user who are eligible to be assigned a role
class sis_user_room2 extends sis_user_selector_base {
    /**
     * Create instance.
     *
     * @param string $name control name
     * @param array $options should have two elements with keys groupid and courseid.
     */
	var $section;
	var $not_in2;
	
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
		$room2 = $_SESSION['ajax_student_room2'];
		$this->extrafields = array();
		$not_in2 = $_SESSION['ajax_student_room2_not_in'];
		$params = array(); //leave it empty
		$sortparams = array(); //leave it empty
        $wherecondition = "a.room ='$room2' and a.status = 'CI' and a.id=(select max(id) from {si_student_dormitory} q where a.user_id = q.user_id) and a.institute = '" . sis_get_institute() . "'";
		
        $fields      = "select a.id as id, a.appid,a.user_id,a.building,a.status, b.first_name as firstname, b.family_name as lastname";
        $countfields = 'SELECT COUNT(1)';

        $sql = " from {si_student_dormitory} a inner join v_si_user b on a.user_id = b.id
                WHERE $wherecondition";

        $order = ' ORDER BY a.appid';

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
            $groupname = get_string('potential_students', 'local_sis', $search);
        } else {
            $groupname = get_string('potential_students', 'local_sis');
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


class sis_user_room1 extends sis_user_selector_base {
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
		$room1 = $_SESSION['ajax_student_room1'];
		$this->extrafields = array('role_value');
		$params = array(); //leave it empty
		$sortparams = array(); //leave it empty
		$institute = sis_get_institute();
        $wherecondition = "a.room ='$room1' and a.status = 'CI' and a.id=(select max(id) from {si_student_dormitory} q where a.user_id = q.user_id) and a.institute = '" . sis_get_institute() . "'";
		
        $fields      = "select a.id as id, a.appid,a.user_id,a.building,a.status, b.first_name as firstname, b.family_name as lastname";
        $countfields = 'SELECT COUNT(1)';

        $sql = " from {si_student_dormitory} a inner join v_si_user b on a.user_id = b.id
                WHERE $wherecondition";

        $order = ' ORDER BY a.appid';

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
            $groupname = get_string('potential_students', 'local_sis', $search);
        } else {
            $groupname = get_string('potential_students', 'local_sis');
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
