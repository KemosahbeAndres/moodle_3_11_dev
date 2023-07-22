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

class activity_completion {
    private $activity;
    private $completed;

    public function __construct(activity $activity, bool $completed) {
        $this->activity = $activity;
        $this->completed = $completed;
    }

    public function equal(activity_completion $completion): bool {
        return $completion->get_activity()->get_id() == $this->activity->get_id();
    }

    /**
     * @return activity
     */
    public function get_activity(): activity {
        return $this->activity;
    }

    /**
     * @return bool
     */
    public function completed(): bool {
        return $this->completed;
    }

    /**
     * @param bool $completed
     */
    public function set_completed(bool $completed) {
        $this->completed = $completed;
    }



}
