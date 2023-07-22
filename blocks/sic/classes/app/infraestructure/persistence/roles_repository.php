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
 * Provides meta-data about the plugin.
 *
 * @package     block_sic
 * @author      {2023} {Andres Cubillos Salazar}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_sic\app\infraestructure\persistence;

use block_sic\app\application\contracts\iroles_repository;
use block_sic\app\domain\rol;

class roles_repository implements iroles_repository {
    /**
     * @throws \dml_exception
     */
    public function by_id (int $id): ?rol {
        global $DB;
        $record = $DB->get_record('role', ['id' => $id], '*', IGNORE_MISSING);
        if(!$record){
            return null;
        }
        $output = new \stdClass();
        $output->role = intval($record->id);
        $output->name = strval($record->shortname);
        return new rol($output);
    }

    /**
     * @throws \dml_exception
     */
    public function all (): array {
        global $DB;
        $records = $DB->get_records('role');
        $output = array();
        foreach ($records as $record){
            $output[] = $this->by_id($record->id);
        }
        return $output;
    }
    public function between(int $userid, int $courseid): ?rol {
        global $DB;
        $sql = "SELECT a.roleid, r.shortname as rolename FROM {role_assignments} a LEFT JOIN {context} c
        ON c.id = a.contextid LEFT JOIN {role} r ON r.id = a.roleid WHERE (a.roleid = 5 OR a.roleid = 4 OR a.roleid = 3 OR a.roleid = 1)
        AND a.userid = :userid AND c.contextlevel = 50 AND c.instanceid = :courseid;";
        $record = $DB->get_record_sql($sql, ['userid' => $userid, 'courseid' => $courseid]);
        $output = new \stdClass();
        $output->role = intval($record->roleid);
        $output->name = strval($record->rolename);
        return new rol($output);
    }
}
