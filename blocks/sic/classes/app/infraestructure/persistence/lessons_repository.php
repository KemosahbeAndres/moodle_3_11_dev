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

use block_sic\app\application\contracts\ilessons_repository;
use block_sic\app\utils\Arrays;
use dml_exception;
use stdClass;

class lessons_repository implements ilessons_repository {
    /**
     * @throws dml_exception
     */
    public function by_id(int $id): object {
        global $DB;
        $lesson = $DB->get_record('sic_clases', ['id' => $id], '*', 'MUST_EXISTS');
        $output = new stdClass();
        $output->id = $id;
        $output->activity = intval($lesson->activity_id);
        //$output->name = strval($lesson->nombre);
        $output->duration = intval($lesson->duracion);
        $output->date = intval($lesson->fecha);
        return $output;
    }

    /**
     * @throws dml_exception
     */
    public function related_to(int $sectionid): array {
        global $DB;
        $output = Arrays::void();
        $records = $DB->get_records('sic_clases', ['section_id' => $sectionid], 'id ASC');
        foreach ($records as $record) {
            $output[] = $this->by_id($record->id);
        }
        return $output;
    }

    /**
     * @throws dml_exception
     */
    public function attach_to(object $lesson, int $sectionid, int $activityid) {
        global $DB;
        $table = 'sic_clases';
        $condition = ['activity_id' => $activityid, 'section_id' => $sectionid];
        if($DB->record_exists($table, $condition)) {
            $object = $DB->get_record($table, $condition);
        }else {
            $object = new stdClass();
            $object->id = intval($lesson->id);
        }
        $object->section_id = $sectionid;
        $object->activity_id = $activityid;
        $object->duracion = intval($lesson->duration);
        $object->fecha = intval($lesson->date);
        if ($DB->record_exists($table, ['id' => $object->id])) {
            $DB->update_record($table, $object);
        } else {
            unset($object->id);
            $DB->insert_record($table, $object);
        }
    }

    /**
     * @throws dml_exception
     */
    public function dettach(int $lessonid) {
        global $DB;
        $table = 'sic_clases';
        $conditions = ['id' => $lessonid];
        if ($DB->record_exists($table, $conditions)) {
            $DB->delete_records($table, $conditions);
        }
    }

    public function have(int $activityid): bool{
        global $DB;
        $table = 'sic_clases';
        try {
            return $DB->record_exists($table, ['activity_id' => $activityid]);
        } catch (dml_exception $e) {}
        return false;
    }
}
