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

use block_sic\app\application\contracts\icourses_repository;
use dml_exception;

/** Return false if record not found, show debug warning if multiple records found */
//define('IGNORE_MISSING', 0);
/** Similar to IGNORE_MISSING but does not show debug warning if multiple records found, not recommended to be used */
//define('IGNORE_MULTIPLE', 1);
/** Indicates exactly one record must exist */
//define('MUST_EXIST', 2);

final class courses_repository implements icourses_repository {
    /**
     * @throws dml_exception
     */
    public function by_id(int $id): ?object {
        global $DB;
        $course = $DB->get_record('course', ['id' => $id], '*', 'IGNORE_MISSING');
        if (is_null($course->startdate) || $course->startdate <= 0) {
            $startdate = 1640995200;
        } else {
            $startdate = $course->startdate;
        }
        if (is_null($course->enddate) || $course->enddate <= 0) {
            $enddate = time();
        } else {
            $enddate = $course->enddate;
        }
        if($course){
            $output = new \stdClass();
            $output->id = intval($course->id);
            $output->code = $course->shortname;
            $output->startdate = $startdate;
            $output->enddate = $enddate;
            return $output;
        }
        return null;
        //echo "REPOSITORY ## {$id} ## {$output->code} ## {$startdate} ## {$enddate} ## <br>";
    }

    public function related_to (int $userid): array {
        return array();
    }


}
