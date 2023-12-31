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

namespace block_sic\task;

use block_sic\app\infraestructure\persistence\repository_context;

class task_for_testing extends \core\task\scheduled_task {

    /**
     * @inheritDoc
     */
    public function get_name() {
        return "Task for Testing SIC Plugin";
    }

    /**
     * @inheritDoc
     */
    public function execute() {
        $context = new repository_context();

        try {
            $config = $context->config->all()[0];
            $config->sic_status = $config->sic_status ? 0 : 1;
            $courseid = intval($config->sic_courseid);
            $context->config->save($config, $courseid);
        } catch (\exception $e) {
            $trace = str_replace('#', '<br>#', $e->getTraceAsString());
            echo "<br>Error: {$e->getMessage()}<br>{$trace}";
        }
    }
}