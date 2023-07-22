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

use block_sic\app\application\login_controller;
use stdClass;

class request {
    public $action;
    public $cookies;
    public $params;
    /**
     * @var user
     */
    public $user;
    /**
     * @var configuration|stdClass
     */
    public $config;
    public function __construct(){
        $this->action = "";
        $this->cookies = new stdClass();
        $this->params = new stdClass();
        $this->user = new stdClass();
        $this->config = new stdClass();
    }

    public function get_string(string $name): string {
        $param = trim($name);
        if($this->exists($param)) {
            return trim(strval($this->params->{$param}));
        }
        return "";
    }
    public function get_int(string $name): int {
        $param = trim($name);
        if($this->exists($param)) {
            return intval($this->params->{$param});
        }
        return -1;
    }

    public function exists(string $name): bool {
        $param = trim($name);
        return isset($this->params->{$param});
    }

}