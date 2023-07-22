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

use block_sic\app\application\contracts\iusers_repository;
use block_sic\app\utils\Arrays;

class users_repository implements iusers_repository {

    public function by_id(int $id): object {
        global $DB;
        $record = $DB->get_record('user', ['id' => $id], '*', MUST_EXIST);
        $name = strval($record->firstname . " " . $record->lastname);
        $splitted = preg_split("/-/", $record->idnumber);
        $rut = intval($splitted[0]);
        $dv = strval($splitted[1]);
        $output = new \stdClass();
        $output->id = $id;
        $output->name = $name;
        $output->rut = $rut;
        $output->dv = $dv;
        return $output;
    }

    /**
     * @throws \dml_exception
     */
    protected function related_to(int $courseid, int $rolecode = 5): array {
        global $DB;
        $output = Arrays::void();
        $sql = "SELECT id FROM {user} WHERE id IN
            (SELECT userid FROM {role_assignments} WHERE roleid = :rolecode AND contextid IN
            (SELECT id FROM {context} WHERE contextlevel = 50 and instanceid = :courseid))";
        $records = $DB->get_records_sql($sql, ['courseid' => $courseid, 'rolecode' => $rolecode]);
        foreach ($records as $record) {
            $user = $this->by_id($record->id);
            try{
                $table = "sic_matriculas";
                $condition = [ 'user_id' => $user->id, 'course_id' => $courseid ];
                if($DB->record_exists($table, $condition)){
                    $enrol = $DB->get_record($table, $condition, '*', MUST_EXIST);
                    $user->active = $enrol->vigente == 1;
                }else{
                    $estado = $DB->get_record('sic_estados', [ 'codigo' => 1 ]);
                    if($estado) {
                        $matricula = new \stdClass();
                        $matricula->course_id = $courseid;
                        $matricula->user_id = $user->id;
                        $matricula->id_estado = intval($estado->id);
                        $matricula->vigente = true;
                        $matricula->created = time();
                        $DB->insert_record($table, $matricula);
                        $user->active = true;
                    }
                }
            }catch(\exception $e){}
            $output[] = $user;
        }
        return $output;
    }

    /**
     * @throws \dml_exception
     */
    public function set_validity(int $userid, int $courseid, bool $valid = true){
        global $DB;
        $table = "sic_matriculas";
        $condition = [ 'user_id' => $userid, 'course_id' => $courseid ];
        if($DB->record_exists($table, $condition)){
            $enrol = $DB->get_record($table, $condition, '*', MUST_EXIST);
            $enrol->vigente = $valid ? 1 : 0;
            $DB->update_record($table, $enrol);
        }
    }

    /**
     * @throws \dml_exception
     */
    public function students_of(int $courseid): array {
        return $this->related_to($courseid, 5);
    }

    /**
     * @throws \dml_exception
     */
    public function moderators_of(int $courseid): array {
        return $this->related_to($courseid, 4);
    }

    /**
     * @throws \dml_exception
     */
    public function teachers_of(int $courseid): array {
        return $this->related_to($courseid, 3);
    }

    /**
     * @throws \dml_exception
     */
    public function manager_of(int $courseid): ?object {
        return $this->related_to($courseid, 1)[0];
    }

}
