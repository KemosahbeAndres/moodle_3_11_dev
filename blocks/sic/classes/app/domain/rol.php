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

final class rol {

    public static $manager = 1;
    public static $teacher = 3;
    public static $moderator = 4;
    public static $student = 5;
    public static $user = 7;

    private $role;
    private $name;

    public function __construct($object) {
        $data = Arrays::convert($object);
        $rol = $data['role'];
        switch($rol) {
            case 1:
            case 3:
            case 4:
            case 5:
                $this->role = $rol;
                break;
            default:
                $this->role = self::$user;
        }
        $this->name = $data['name'];

    }

    public function get_role() {
        return $this->role;
    }

    public function get_rolename() {
        return $this->name;
    }

    public function equal(int $role): bool {
        return $role == $this->role;
    }

}
