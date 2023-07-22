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

class section {
    private $id;
    private $name;
    private $assigned;
    private $activities;
    private $lessons;
    private $module;

    /**
     * @param $id
     * @param $name
     */
    public function __construct(int $id, string $name, bool $assigned, $module = null) {
        $this->id = $id;
        $this->name = trim($name);
        $this->assigned = $assigned;
        $this->activities = array();
        $this->lessons = array();
        $this->module = $module;
    }

    public function equal(section $section): bool {
        return $this->id == $section->get_id();
    }

    public function __toObject(): object {
        $activities = array();
        /** @var activity $activity */
        foreach ($this->activities as $activity){
            $activities[] = $activity->__toObject();
        }
        $lessons = array();
        /** @var lesson $lesson */
        foreach ($this->lessons as $lesson){
            $lessons[] = $lesson->__toObject();
        }
        return (object) [
            'id' => $this->id,
            'name' => $this->name,
            'assigned' => $this->assigned,
            'activities' => $activities,
            'nactivities' => count($activities),
            'lessons' => $lessons,
            'nlessons' => count($lessons),
            'module' => $this->module
        ];
    }
    public function object(): object {
        return (object) [
            'id' => $this->id,
            'name' => $this->name,
            'assigned' => $this->assigned
        ];
    }

    /**
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * @param activity $activity
     * @return false|void
     */
    public function add_activity(activity $activity) {
        if (in_array($activity, $this->activities, true)) {
            return false;
        }
        $this->activities[] = $activity;
    }

    public function count_activities(): int {
        return count($this->activities);
    }

    /**
     * @return array
     */
    public function get_activities(): array {
        return $this->activities;
    }

    /**
     * @param lesson $lesson
     * @return false|void
     */
    public function add_lesson(lesson $lesson) {
        if (in_array($lesson, $this->lessons, true)) {
            return false;
        }
        $this->lessons[] = $lesson;
    }

    /**
     * @return array
     */
    public function get_lessons(): array {
        return $this->lessons;
    }

    public function count_lessons(): int {
        return count($this->lessons);
    }

    public function assigned(): bool {
        return $this->assigned;
    }
    public function exists(activity $activity): bool {
        return in_array($activity, $this->activities, true);
    }

    public function have(int $activityid): bool {
        /** @var activity $activity */
        foreach($this->get_activities() as $activity){
            if($activity->get_id() == $activityid){
                return true;
            }
        }
        return false;
    }
}
