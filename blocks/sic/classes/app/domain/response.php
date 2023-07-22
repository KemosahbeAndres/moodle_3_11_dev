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

use moodle_exception;

abstract class response {
    public $urls;
    public $content;
    public $template;

    public function __construct(){
        $this->urls = array();
    }
    public function render(){
        $this->renderUrls();
    }

    protected function renderUrls(){
        foreach ($this->urls as $name=>$url){
            $this->content->{$name} = str_replace('&amp;', '&', $url->__toString());
        }
    }
    /**
     * @throws moodle_exception
     */
    public function registerRoute(route $route, request $request){
        $this->urls[$route->action.'url'] = self::url($route->action, $request->params);
        //echo 'URL Registradas: '.count($this->urls).'<br>';
    }

    /**
     * @throws moodle_exception
     */
    public static function url(string $actionname, object $params): \moodle_url {
        return new \moodle_url('/blocks/sic/dashboard.php', [
            'courseid' => $params->courseid,
            'instance' => $params->instance,
            'action' => trim($actionname),
        ]);
    }

    /**
     * @throws moodle_exception
     */
    public static function link(string $path, array $params): string {
        $url = new \moodle_url(trim($path), $params);
        return str_replace('&amp;', '&', $url->__toString());
    }

}