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

namespace block_sic\app\utils;

final class Arrays {
    public static function convert($data): array {
        if (is_null($data)) {
            return self::void();
        }
        if (is_array($data)) {
            return $data;
        } else {
            $response = array();
            foreach ($data as $key => $value) {
                $response[$key] = $value;
            }
            return $response;
        }
    }
    public static function join($first, $second): array {
        $response = array();
        if (!is_null($first) && !is_null($second)) {
            foreach (self::convert($first) as $key => $value) {
                $response[$key] = $value;
            }
            foreach (self::convert($second) as $key => $value) {
                $response[$key] = $value;
            }
        }
        return $response;
    }

    public static function void(): array {
        return [];
    }

    public static function search(array $array, object $object): bool {
        return in_array($object, $array, true);
    }
}
