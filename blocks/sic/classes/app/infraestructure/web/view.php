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

namespace block_sic\app\infraestructure\web;

use block_sic\app\domain\session;

abstract class view {
    public static $MANAGER = 1;
    public static $MODERATOR = 2;
    public static $TEACHER = 3;
    public static $STUDENT = 4;
    protected static $source = "block_sic/";
    protected static $template = null;

    protected $queue;

    public function __construct() {
        $this->queue = array();
    }

    public function get_source(): string {
        return self::$source;
    }
    public function get_template(): ?object {
        return self::$template;
    }

    public function run_request(session $params, int $level = 4) {
        $post = $params->get_post();
        if (!isset($post->action)) {
            return;
        }
        foreach ($this->queue as $request) {
            if ($request->action == $post->action && $request->level == $level) {
                if (is_callable($request->callback)) {
                    $request['callback']($params);
                } else if (is_object($request->callback)) {
                    $request->callback->execute($params);
                }
                break;
            }
        }
    }

    public function post(string $action, $callback, int $level = 4) {
        if (is_null($this->queue)) {
            $this->queue = array();
        }
        if (empty($action) || (!is_callable($callback) && !method_exists($callback, 'execute')) ) {
            return;
        }
        if (!$this->exists($action)) {
            $this->queue[] = (object) array(
                'action' => $action,
                'callback' => $callback,
                'level' => $level
            );
        }
    }

    private function exists(string $action): bool {
        if (empty($this->queue)) {
            return false;
        }
        return array_key_exists($action, $this->queue);
    }

    public abstract function render(session $params);
}
