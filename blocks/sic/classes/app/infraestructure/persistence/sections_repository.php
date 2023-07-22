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

use block_sic\app\application\contracts\isections_repository;
use block_sic\app\utils\Arrays;
use stdClass;

class sections_repository implements isections_repository {
    public function by_id(int $id): object {
        global $DB;
        $section = $DB->get_record('course_sections', ['id' => $id], '*', MUST_EXIST);
        $extras = $DB->get_record('sic_asignaciones', ['section_id' => $id], '*', IGNORE_MISSING);
        $name = "Sin nombre";
        if (!is_null($section->name)) {
            $name = $section->name;
        }
        $assigned = is_bool($extras) && $extras == false ? false : true;
        $output = new stdClass();
        $output->id = $id;
        $output->name = $name;
        $output->assigned = $assigned;
        return $output;

    }
    public function from(int $courseid): array {
        global $DB;
        $output = Arrays::void();
        $sections = $DB->get_records('course_sections', ['course' => $courseid], 'id ASC', '*');
        foreach ($sections as $section) {
            $output[] = $this->by_id($section->id);
        }
        return $output;
    }
    public function related_to(int $moduleid): array {
        global $DB;
        $output = Arrays::void();
        $sections = $DB->get_records('sic_asignaciones', ['id_modulo' => $moduleid], 'id ASC', '*');
        foreach ($sections as $section) {
            $output[] = $this->by_id($section->section_id);
        }
        return $output;
    }

    /**
     * @throws \dml_exception
     */
    public function attach_to(int $sectionid, int $moduleid) {
        global $DB;
        $object = new stdClass();
        $object->id_modulo = $moduleid;
        $object->section_id = $sectionid;
        if ($DB->record_exists('sic_asignaciones', ['section_id' => $sectionid])) {
            $record = $DB->get_record('sic_asignaciones', ['section_id' => $sectionid], '*', MUST_EXIST);
            $object->id = $record->id;
            $object->created = $record->created;
            $DB->update_record('sic_asignaciones', $object);
        } else {
            $object->created = time();
            $DB->insert_record('sic_asignaciones', $object);
        }

    }

    /**
     * @throws \dml_exception
     */
    public function dettach(int $sectionid) {
        global $DB;
        if ($DB->record_exists('sic_asignaciones', ['section_id' => $sectionid])) {
            $DB->delete_records('sic_asignaciones', ['section_id' => $sectionid]);
        }

    }

}
