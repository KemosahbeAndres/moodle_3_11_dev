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

use block_sic\app\application\contracts\istates_repository;
use block_sic\app\utils\Arrays;
use coding_exception;
use dml_exception;
use stdClass;

class states_repository implements istates_repository {

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function __construct() {
        self::init();
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function init() {
        global $DB;
        if ($DB->count_records('sic_estados') <= 0) {
            $dataobjects = array();
            $dataobjects[] = (object)[
                'codigo' => 1,
                'estado' => "cursando"
            ];
            $dataobjects[] = (object)[
                'codigo' => 2,
                'estado' => "aprobado"
            ];
            $dataobjects[] = (object)[
                'codigo' => 3,
                'estado' => "reprobado"
            ];
            $DB->insert_records('sic_estados', $dataobjects);
        }
    }

    /**
     * @throws dml_exception
     */
    public function by_id (int $id): object {
        global $DB;
        $record = $DB->get_record('sic_estados', ['id' => $id], '*', MUST_EXIST);
        $output = new stdClass();
        $output->id = $record->id;
        $output->codigo = $record->codigo;
        $output->estado = $record->estado;
        return $output;

    }

    /**
     * @throws dml_exception
     */
    public function all (): array {
        global $DB;
        $output = Arrays::void();
        $records = $DB->get_records('sic_estados', [], 'id ASC');
        foreach ($records as $record) {
            $output[] = $this->by_id($record->id);
        }
        return $output;
    }

    /**
     * @throws dml_exception
     */
    public function by_code(int $code): ?object {
        global $DB;
        $record = $DB->get_record('sic_estados', ['codigo' => $code]);
        if (is_bool($record) && !$record) {
            return null;
        }
        return $this->by_id($record->id);
    }

    /**
     * @throws dml_exception
     */
    public function by_state(string $state): ?object {
        $records = $this->all();
        foreach ($records as $record) {
            if(strtolower(trim($state)) == strtolower(trim($record->estado)) ) {
                return $this->by_id($record->id);
            }
        }
        return null;
    }

    /**
     * @throws dml_exception
     */
    public function between (int $userid, int $courseid): object {
        global $DB;
        $matricula = $DB->get_record('sic_matriculas', ['user_id' => $userid, 'course_id' => $courseid]);
        if (is_bool($matricula) && !$matricula) {
            $estado = $this->by_code(1);
            $this->attach_to($estado, $userid, $courseid);
            return $estado;
        }
        return $this->by_id($matricula->id_estado);
    }

    /**
     * @throws dml_exception
     */
    public function attach_to(object $state, int $userid, int $courseid) {
        global $DB;
        $dataobject = new stdClass();
        $dataobject->course_id = $courseid;
        $dataobject->user_id = $userid;
        if ($state->id > 0) {
            $record = $this->by_id($state->id);
        } else if (intval($state->codigo) > 0) {
            $record = $this->by_code($state->codigo);
        } else {
            $record = $this->by_code(1);
        }
        $dataobject->id_estado = $record->id;
        $dataobject->vigente = true;
        if ($DB->record_exists('sic_matriculas', ['course_id' => $courseid, 'user_id' => $userid])) {
            $matricula = $DB->get_record('sic_matriculas', ['course_id' => $courseid, 'user_id' => $userid], '*', MUST_EXIST);
            $dataobject->id = $matricula->id;
            $dataobject->created = $matricula->created;
            $DB->update_record('sic_matriculas', $dataobject);
        } else {
            $dataobject->created = time();
            $DB->insert_record('sic_matriculas', $dataobject);
        }

    }

}
