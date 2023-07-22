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

abstract class user {
    protected $id;
    protected $name;
    protected $rut;
    protected $dv;
    protected $role;
    protected $course;

    /**
     * @param int $id
     * @param string $name
     * @param int $rut
     * @param string $dv
     * @param string $role
     */
    public function __construct(int $id, string $name, int $rut, string $dv, string $role) {
        $this->id = $id;
        $this->name = $name;
        $this->rut = $rut;
        $this->dv = $dv;
        $this->role = $role;
        $this->course = null;
    }

    public function __toObject(): object {
        return (object) [
            'id' => $this->get_id(),
            'name' => $this->get_name(),
            'rut' => $this->get_full_rut(),
            'role' => $this->get_role()
        ];
    }

    /**
     * @param course $course
     */
    public function set_course(course $course) {
        $this->course = $course;
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
     * @return int
     */
    public function get_rut(): int {
        return $this->rut;
    }

    /**
     * @return string
     */
    public function get_dv(): string {
        return $this->dv;
    }

    /**
     * @return string
     */
    public function get_full_rut(): string {
        return strval("{$this->rut}-{$this->dv}");
    }

    public function get_role(): string {
        return trim(strval($this->role));
    }

    /**
     * @return course
     */
    public function get_course(): ?course {
        return $this->course;
    }



}
