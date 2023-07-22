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

namespace block_sic\app\domain;

use block_sic\app\utils\Arrays;

class section_dedication {
    private $section;
    private $time;
    public function __construct(section $section, int $time) {
        $this->section = $section;
        $this->time = $time;

    }

    public function equal(section_dedication $dedication): bool {
        return $dedication->get_section()->get_id() == $this->section->get_id();
    }

    /**
     * @return section
     */
    public function get_section(): section {
        return $this->section;
    }

    /**
     * @return int
     */
    public function get_time(): int {
        return $this->time;
    }
}
