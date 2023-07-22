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

class lesson_attendance {
    private $id;
    private $lesson;
    private $present;

    /**
     * @param int $id
     * @param lesson $lesson
     * @param bool $present
     */
    public function __construct(int $id, lesson $lesson, bool $present) {
        $this->id = $id;
        $this->lesson = $lesson;
        $this->present = $present;
    }

    /**
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * @return lesson
     */
    public function get_lesson(): lesson {
        return $this->lesson;
    }

    /**
     * @return bool
     */
    public function is_present(): bool {
        return $this->present;
    }

}
