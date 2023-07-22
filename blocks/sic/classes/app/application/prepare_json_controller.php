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

namespace block_sic\app\application;

use block_sic\app\application\sic\activities_handler;
use block_sic\app\application\sic\course_handler;
use block_sic\app\application\sic\modules_handler;
use block_sic\app\application\sic\users_handler;
use block_sic\app\domain\configuration;
use block_sic\app\infraestructure\persistence\repository_context;

class prepare_json_controller {
    /**
     * @var consult_course_controller
     */
    private $courseLoader;
    /**
     * @var student_finder
     */
    private $studentsFinder;
    private $coursehandler;

    /**
     * @param consult_course_controller $courseloader
     * @param student_finder $studentsFinder
     */
    public function __construct(consult_course_controller $courseloader, student_finder $studentsFinder) {
        $this->courseLoader = $courseloader;
        $this->studentsFinder = $studentsFinder;
        $this->coursehandler = null;
    }

    public function execute(int $courseid, configuration $config): ?object {
        $course = $this->courseLoader->execute($courseid);
        $students = $this->studentsFinder->all($courseid);

        $this->coursehandler = new course_handler($course, $config);
        $this->coursehandler->set_next(new users_handler($course, $students));

        $object = new \stdClass();
        return $this->coursehandler->handle($object);
    }

}