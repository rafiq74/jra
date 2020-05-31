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
 * This file keeps track of upgrades to the settings block
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @since Moodle 2.0
 * @package local_jra
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * As of the implementation of this block and the general navigation code
 * in Moodle 2.0 the body of immediate upgrade work for this block and
 * settings is done in core upgrade {@see lib/db/upgrade.php}
 *
 * There were several reasons that they were put there and not here, both becuase
 * the process for the two blocks was very similar and because the upgrade process
 * was complex due to us wanting to remvoe the outmoded blocks that this
 * block was going to replace.
 *
 * @param int $oldversion
 * @param object $block
 */
function xmldb_local_jra_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); //this is new in moodle 3.0
    
    // Put any upgrade step following this.
    $newversion = 2016062460; //put the new version number here
    if ($oldversion < $newversion) {
		//Upgrade code starts here
		
/*		
        // Define field suspended to be added to si_user.
        $table = new xmldb_table('si_user');
        $field = new xmldb_field('suspended', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'institute');

        // Conditionally launch add field suspended.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field deleted to be added to si_user.
        $table = new xmldb_table('si_user');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'enable_login');

        // Conditionally launch add field deleted.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
        // Define field temp_grade_num to be added to si_section_student.
        $table = new xmldb_table('si_section_student');
        $field = new xmldb_field('temp_grade_num', XMLDB_TYPE_NUMBER, '20, 3', null, null, null, null, 'temp_grade');

        // Conditionally launch add field temp_grade_num.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
        // Changing precision of field description on table si_lookup to (255).
        $table = new xmldb_table('si_course');
        $field = new xmldb_field('course_code', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'course_num');

        // Launch change of precision for field description.
        $dbman->change_field_precision($table, $field);
		

        // Rename field sort_order on table si_plan to NEWNAMEGOESHERE.
        $table = new xmldb_table('si_plan');
        $field = new xmldb_field('sort_order', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'plan_type');

        // Launch rename field sort_order.
        $dbman->rename_field($table, $field, 'NEWNAMEGOESHERE');


        // Define field program to be dropped from si_payroll_user_summary.
        $table = new xmldb_table('si_payroll_user_summary');
        $field = new xmldb_field('program');

        // Conditionally launch drop field program.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
*/

        // Define table jra_plan to be created.
        $table = new xmldb_table('jra_plan');

        $field = new xmldb_field('eff_status', XMLDB_TYPE_CHAR, '1', null, null, null, null, 'filename');

        // Conditionally launch add field suspended.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('sort_order', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'eff_seq');

        // Conditionally launch add field deleted.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }



		// upgrade code ends here
        // jra savepoint reached.
        upgrade_plugin_savepoint(true, $newversion, 'local', 'jra');
    }


    return true;
}
