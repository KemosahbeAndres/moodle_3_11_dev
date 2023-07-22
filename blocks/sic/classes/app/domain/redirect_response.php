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

class redirect_response extends response {
    protected $action;
    protected $params;
    protected $message;
    protected $delay;
    public function __construct(string $actionname, string $message, object $params, int $seconds = 4 ) {
        parent::__construct();
        $this->action = $actionname;
        $this->params = $params;
        $this->message = $message;
        $this->delay = $seconds;
    }

    /**
     * @throws \moodle_exception
     */
    public function render(){
        parent::render();
        redirect(
            self::url($this->action, $this->params),
            $this->message,
            $this->delay
        );
    }
}