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

final class Router {

    private static $queue = array();

    public static function post(string $action, $callback) {
        if (empty($action) || !is_callable($callback) || !method_exists($callback, 'execute')) {
            return;
        }
        if (!self::exists($action)) {
            $data = array(
                'action' => $action,
                'callback' => $callback,
                'params' => filter_var_array(INPUT_POST)
            );
            self::$queue[] = $data;
        }
    }

    public static function run() {
        if (empty(self::$queue)) {
            return;
        }
        $params = filter_var_array(INPUT_POST);
        foreach (self::$queue as $request) {
            if ($params['action'] == $request['action']) {
                if (is_callable($request['callback'])) {
                    $request['callback']((object) $request['params']);
                } else if(is_object($request['callback'])) {
                    $request['callback']->execute((object) $request['params']);
                }
                break;
            }
        }
    }

    private static function exists(string $action): bool {
        if (empty($action) || empty(self::$queue)) {
            return true;
        }
        foreach (self::$queue as $key => $controller) {
            if ($action == $key) {
                return true;
            }
        }
        return false;
    }

}
