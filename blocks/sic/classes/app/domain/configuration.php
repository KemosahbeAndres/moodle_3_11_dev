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

final class configuration {
    /**
     * @var object
     */
    protected $global;
    /**
     * @var object
     */
    protected $local;
    public function __construct(object $global_conf, object $local_conf){
        $this->global = $global_conf;
        $this->local = $local_conf;
    }
    public function valid_local(): bool {
        return !empty(get_object_vars($this->local));
    }
    public function valid_global(): bool {
        return !empty(get_object_vars($this->global));
    }
    public function exists(string $item): bool {
        foreach ($this->global as $key => $value) {
            if($key == trim($item)) return true;
        }
        foreach ($this->local as $key => $value) {
            if($key == trim($item)) return true;
        }
        return false;
    }
    public function get_int(string $item): int {
        foreach ($this->global as $key => $value) {
            if($key == trim($item)) return intval($value);
        }
        foreach ($this->local as $key => $value) {
            if($key == trim($item)) return intval($value);
        }
        return 0;
    }
    public function get_string(string $item): string {
        foreach ($this->global as $key => $value) {
            if($key == trim($item)) return trim(strval($value));
        }
        foreach ($this->local as $key => $value) {
            if($key == trim($item)) return trim(strval($value));
        }
        return "";
    }
    public function get_bool(string $item): bool {
        foreach ($this->global as $key => $value) {
            if($key == trim($item)) return boolval($value);
        }
        foreach ($this->local as $key => $value) {
            if($key == trim($item)) return boolval($value);
        }
        return false;
    }
    public function get_local_config(): object {
        return $this->local;
    }
}