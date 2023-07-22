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

use block_sic\app\application\contracts\iconfig_repository;
use block_sic\app\domain\configuration;

class configuration_repository implements iconfig_repository {

    /**
     * @throws \dml_exception
     */
    public function all(): array {
        global $DB;
        $table = 'block_instances';
        $conditions = array(
            'blockname' => 'sic'
        );
        $records = $DB->get_records($table, $conditions);
        $output = array();
        foreach ($records as $record){
            if($record) {
                $decoded = base64_decode($record->configdata);
                $output[] = unserialize($decoded);
            }
        }
        return $output;
    }

    /**
     * @throws \dml_exception
     */
    public function from_global(): object {
        global $DB;
        $table = 'config_plugins';
        $conditions = array(
            'plugin' => 'block_sic'
        );
        $records = $DB->get_records($table, $conditions, 'id ASC');
        $config = new \stdClass();
        foreach ($records as $record){
            if($record){
                $config->{$record->name} = $record->value;
            }
        }
        return $config;
    }

    public function from_instance(int $instanceid): object {
        global $DB;
        $table = 'block_instances';
        $conditions = array(
            'id' => $instanceid,
            'blockname' => 'sic'
        );
        $record = $DB->get_record($table, $conditions);
        $config = new \stdClass();
        if($record and !empty(strval($record->configdata))){
            $decoded = base64_decode($record->configdata);
            $config = unserialize($decoded);
        }
        return $config;
    }

    /**
     * @throws \dml_exception
     */
    public function from_course(int $courseid): object {
        global $DB;
        $table = 'block_instances';
        $conditions = array(
            'blockname' => 'sic'
        );
        $records = $DB->get_records($table, $conditions, 'id ASC');
        $config = new \stdClass();
        foreach ($records as $record){
            if($record){
                $data = unserialize(base64_decode($record->configdata));
                if(intval($data->sic_courseid) == $courseid){
                    return $data;
                }
            }
        }
        return $config;
    }

    /**
     * @throws \dml_exception
     */
    public function save(object $config, int $courseid): bool {
        global $DB;
        $table = 'block_instances';
        $conditions = array(
            'blockname' => 'sic'
        );
        $records = $DB->get_records($table, $conditions, 'id ASC');
        $configuration = null;
        foreach ($records as $record){
            if(isset($record->configdata) && !empty($record->configdata)){
                $content = unserialize(base64_decode($record->configdata));
                $id = intval($content->sic_courseid);
                if($id == $courseid) {
                    $configuration = $record;
                    break;
                }
            }
        }
        if($configuration){
            // Guardar
            $configuration->configdata = base64_encode(serialize($config));
            $DB->update_record($table, $configuration);
            return true;
        }
        return false;
    }
}