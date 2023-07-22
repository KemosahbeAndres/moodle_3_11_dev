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

final class Dates {
    public static function format(int $timestamp): string {
        return date("Y-m-d", $timestamp);
    }
    public static function format_date_time(int $timestamp): string {
        return date("Y-m-d H:i:s", $timestamp);
    }

    public static function format_iso_8601(int $timestamp): string {
        return substr(date("c", $timestamp), 0, 16);
    }

    public static function totime(string $time): int {
        return strtotime($time);
    }

    public static function format_time(string $timestamp) {
        return date("H", $timestamp) . " horas y " . date("i", $timestamp) . " minutos";
    }
}
