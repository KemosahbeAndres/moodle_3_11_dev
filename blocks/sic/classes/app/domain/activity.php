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


class activity {
    private $id;
    private $code;
    private $mandatory;
    private $instance;
    private $type;

    /**
     * @param $id
     * @param $code
     * @param $mandatory
     * @param $instance
     * @param $type
     */
    public function __construct(int $id, string $code, bool $mandatory, int $instance, string $type) {
        $this->id = $id;
        $this->code = $code;
        $this->mandatory = $mandatory;
        $this->instance = $instance;
        $this->type = $type;
    }

    public function equal (activity $activity): bool {
        return $this->id == $activity->get_id();
    }

    public function __toObject(): object {
        return (object) [
            'id' => $this->id,
            'code' => $this->code,
            'mandatory' => $this->mandatory ? "SI" : "NO",
            'instance' => $this->instance,
            'type' => $this->type
        ];
    }
    public function toObject(): object {
        return (object) [
            'id' => $this->id,
            'code' => $this->code,
            'mandatory' => $this->mandatory,
            'instance' => $this->instance,
            'type' => $this->type
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
    public function get_code(): string {
        return $this->code;
    }

    /**
     * @return bool
     */
    public function is_mandatory(): bool {
        return $this->mandatory;
    }

    /**
     * @return int
     */
    public function get_instance(): int {
        return $this->instance;
    }

    /**
     * @return string
     */
    public function get_type(): string {
        return $this->type;
    }




}
