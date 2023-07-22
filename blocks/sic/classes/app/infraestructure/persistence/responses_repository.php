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

class responses_repository implements \block_sic\app\application\contracts\iresponses_repository {

    public function by_id(int $id): object {
        global $DB;
        $table = 'sic_respuestas';
        $condition = [ 'id' => $id ];
        $record = $DB->get_record($table, $condition);
        $object = new \stdClass();
        $object->id = intval($record->id);
        $object->id_proceso = intval($record->id_proceso);
        $object->payloads = array_merge(json_decode(base64_decode($record->contenido), false));
        $object->errors = array_merge(json_decode(base64_decode($record->errores), false));
        $object->respuesta_SIC = trim(strval($record->respuesta));
        $object->fecha = $record->created;
        return $object;
    }

    /**
     * @throws \dml_exception
     */
    public function related_to(int $courseid, int $page = 1, int $count = 20): array {
        global $DB;
        $table = 'sic_respuestas';
        $condition = [ 'course_id' => $courseid ];
        $from = intval($page * $count) - $count;
        if($from < 0) $from = 0;
        if($count < 0) $count = 20;
        $records = $DB->get_records($table, $condition, 'id ASC', 'id', $from, $count);
        $output = array();
        foreach($records as $record) {
            $output[] = $this->by_id($record->id);
        }
        return $output;
    }

    /**
     * @throws \dml_exception
     */
    public function save_to(object $response, int $courseid) {
        global $DB;
        $table = 'sic_respuestas';
        $dataobject = new \stdClass();
        $dataobject->course_id = $courseid;
        $dataobject->id_proceso = intval($response->id_proceso);
        $dataobject->contenido = base64_encode(json_encode($response->payloads, JSON_UNESCAPED_UNICODE));
        $dataobject->errores = base64_encode(json_encode($response->errores, JSON_UNESCAPED_UNICODE));
        $dataobject->respuesta = strval($response->respuesta_SIC);
        if($DB->record_exists($table, ['id' => intval($response->id)] )) {
            $record = $DB->get_record($table, [ 'id' => $response->id]);
            $dataobject->id = $response->id;
            $dataobject->created = $record->created;
            return $DB->update_record($table, $dataobject);
        }else{
            $dataobject->created = time();
            unset($response->id);
            unset($dataobject->id);
            return $DB->insert_record($table, $dataobject, false);
        }
    }

}