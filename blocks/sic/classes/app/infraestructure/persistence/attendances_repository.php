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

use block_sic\app\application\contracts\iattendance_repository;
use stdClass;

class attendances_repository implements iattendance_repository {

    public function by_id(int $id): object {
        global $DB;
        $asistio = false;
        $record = $DB->get_record('sic_asistencia', ['id' => $id], '*', MUST_EXISTS);
        if (is_numeric($record->asistio)) {
            $asistio = $record->asistio > 0 ? true : false;
        } else if (is_bool($record->asistio)) {
            $asistio = $record->asistio ? true : false;
        }
        $output = new stdClass();
        $output->id = $id;
        $output->assist = $asistio;
        return $output;
    }
    public function between(int $userid, int $lessonid): object {
        global $DB;
        if (!$DB->record_exists('sic_asistencia', ['id_clase' => $lessonid, 'user_id' => $userid])) {
            $id = $this->attach_to(
                (object) [
                    'id' => 0,
                    'assist' => false
                    ],
                $userid,
                $lessonid
            );
            return $this->by_id($id);
        }
        $record = $DB->get_record('sic_asistencia', ['id_clase' => $lessonid, 'user_id' => $userid], '*', MUST_EXISTS);
        return $this->by_id($record->id);
    }
    public function attach_to(object $attendance, int $userid, int $lessonid): int {
        global $DB;
        $table = 'sic_asistencia';
        $object = new  stdClass();
        $object->id_clase = $lessonid;
        $object->user_id = $userid;
        $object->asistio = false;
        if ($DB->record_exists($table, ['id' => $attendance->id])) {
            $object->id = $attendance->id;
            $object->asistio = $attendance->assist;
            $DB->update_record($table, $object);
        } else {
            $object->id = $DB->insert_record($table, $object);
        }
        return $object->id;
    }

}
