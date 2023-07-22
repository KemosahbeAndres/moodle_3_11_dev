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

class activity_grade {
    private $activity;
    private $grade;

    /**
     * @param activity $activity
     * @param int $grade
     */
    public function __construct(activity $activity, int $grade) {
        $this->activity = $activity;
        $this->grade = $grade;
    }

    public function equal(activity_grade $grade): bool {
        return $this->activity->equal($grade->get_activity());
    }

    /**
     * @return activity
     */
    public function get_activity(): activity {
        return $this->activity;
    }

    /**
     * @return int
     */
    public function get_grade(): int {
        return $this->grade;
    }

    /**
     * @param int $grade
     */
    public function set_grade(int $grade): void {
        $this->grade = $grade;
    }



}
