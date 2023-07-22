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

use block_sic\app\application\contracts\iactivities_repository;
use block_sic\app\utils\Arrays;

final class activities_repository implements iactivities_repository {

    /**
     * @throws \dml_exception
     */
    public function by_id(int $id): object {
        global $DB;
        $module = $DB->get_record("course_modules", ['id' => $id], '*', MUST_EXIST);
        $table = $DB->get_record("modules", ['id' => $module->module], '*', MUST_EXIST);
        $activity = $DB->get_record($table->name, ['id' => $module->instance], '*', MUST_EXIST);
        $mandatory = intval($module->completion) == 2;
        $output = new \stdClass();
        $output->id = $id;
        $output->code = $activity->name;
        $output->instance = $activity->id;
        $output->type = $table->name;
        $output->mandatory = $mandatory;
        return $output;
    }

    /**
     * @throws \dml_exception
     */
    public function related_to(object $section): array {
        global $DB;
        $activities = Arrays::void();
        $modules = $DB->get_records("course_modules", ['section' => $section->id], 'id ASC', '*');
        foreach ($modules as $module) {
            $activities[] = $this->by_id($module->id);
        };
        return Arrays::convert($activities);
    }

}
