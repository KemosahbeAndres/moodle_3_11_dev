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

namespace block_sic\task;

class error {
    protected $message;
    protected $subject;
    public function __construct(string $message,string $subject = '0') {
        $this->subject = trim($subject);
        $this->message = trim($message);
    }
    public function __toString(): string {
        return "[PROCESO {$this->subject}] '{$this->message}'";
    }
    public function get_message(): string {
        return $this->message;
    }
    public function get_subject(): string {
        return $this->subject;
    }
}