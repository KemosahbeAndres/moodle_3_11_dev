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
use block_sic\app\utils\Dates;

class lesson {
    private $id;
    /**
     * @var activity
     */
    private $activity;
    //private $name;
    private $date;
    private $duration;
    private $sectionid;

    /**
     * @param $id
     * @param $activity
     * @param $date
     * @param $duration
     */
    public function __construct(int $id, activity $activity, int $date, int $duration, int $sectionid) {
        $this->id = $id;
        $this->activity = $activity;
        $this->date = $date;
        $this->duration = $duration;
        $this->sectionid = $sectionid;
    }

    public function __toObject(): object {
        return (object) [
            'id' => $this->get_id(),
            'code' => $this->get_code(),
            'date' => Dates::format_date_time($this->get_date()),
            'duration' => $this->get_duration(),
            'activity' => $this->get_activity()->__toObject(),
            'sectionid' => $this->sectionid
        ];
    }

    public function basicObject(): object {
        return (object)[
            'id' => $this->get_id(),
            'code' => $this->get_code(),
            'date' => Dates::format_iso_8601($this->get_date()),
            'duration' => $this->get_duration(),
            'activityid' => $this->get_activity()->get_id(),
            'sectionid' => $this->sectionid
        ];
    }

    public function equal(lesson $lesson): bool {
        return $lesson->get_id() == $this->get_id();
    }

    public function get_id(): int {
        return $this->id;
    }

    public function get_code(): string {
        return $this->activity->get_code();
    }

    public function get_date(): int {
        return $this->date;
    }

    public function get_duration(): int {
        return intval($this->duration);
    }

    public function get_activity(): activity {
        return $this->activity;
    }

}
