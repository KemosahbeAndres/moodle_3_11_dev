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

use block_sic\app\application\consult_course_controller;
use block_sic\app\application\load_course_data_controller;
use block_sic\app\application\login_controller;
use block_sic\app\application\users_finder_controller;

class session {
    private $user;
    private $course;
    private $block;
    private $tab;
    private $post;
    private $get;
    // USERS
    private $students;
    private $manager;
    private $moderators;
    private $teachers;

    private $userfinder;
    private $dataloader;
    private $courseloader;
    private $login;
    private $courseid;

    /**
     * @param int $courseid
     * @param int $block
     * @param int $tab
     * @param consult_course_controller $courseloader
     * @param login_controller $userlogin
     */
    public function __construct(
        int $courseid,
        int $block,
        int $tab = 1,
        consult_course_controller $courseloader,
        login_controller $userlogin,
        users_finder_controller $userfinder,
        load_course_data_controller $dataloader
    ) {
        $this->courseid = $courseid;
        $this->courseloader = $courseloader;
        $this->login = $userlogin;
        $this->block = $block;
        $this->tab = $tab;
        $this->post = (object) filter_input_array(INPUT_POST);
        $this->get = (object) filter_input_array(INPUT_GET);
        if(!is_null($this->get->tab)) {
            $t = intval($this->get->tab);
            $this->tab = $t > 0 ? $t : 1;
        }
        $this->students = array();
        $this->manager = null;
        $this->moderators = array();
        $this->teachers = array();
        $this->userfinder = $userfinder;
        $this->dataloader = $dataloader;
        $this->load();

    }

    public function load() {
        global $USER;
        $this->user = $this->login->execute($USER->id, $this->courseid);
        $this->course = $this->courseloader->execute($this->courseid);
        $this->user->set_course($this->course);
        $this->manager = $this->userfinder->get_manager($this->course);
        $this->moderators = $this->userfinder->get_moderators($this->course);
        $this->teachers = $this->userfinder->get_teachers($this->course);
        $this->students = $this->userfinder->get_students($this->course);
        $this->students = $this->userfinder->get_students($this->course);
        foreach ($this->students as $student) {
            $this->dataloader->execute($student);
        }
    }

    /**
     * @return manager|null
     */
    public function get_manager(): ?manager {
        return $this->manager;
    }

    /**
     * @return array
     */
    public function get_students(): array {
        return $this->students;
    }

    public function find_student(int $id): ?student {
        foreach ($this->students as $student) {
            if($student->get_id() == $id) {
                return $student;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function get_moderators(): array {
        return $this->moderators;
    }

    /**
     * @return array
     */
    public function get_teachers(): array {
        return $this->teachers;
    }

    /**
     * @return user
     */
    public function get_user(): user {
        return $this->user;
    }

    /**
     * @param user $user
     */
    public function set_user(user $user): void {
        $this->user = $user;
    }

    /**
     * @return course
     */
    public function get_course(): course {
        return $this->course;
    }

    /**
     * @param course $course
     */
    public function set_course(course $course): void {
        $this->course = $course;
    }

    /**
     * @return int
     */
    public function get_block(): int {
        return $this->block;
    }

    /**
     * @param int $block
     */
    public function set_block(int $block): void {
        $this->block = $block;
    }

    /**
     * @return int
     */
    public function get_tab(): int {
        return $this->tab;
    }

    /**
     * @param int $tab
     */
    public function set_tab(int $tab): void {
        $this->tab = $tab;
    }

    /**
     * @return object
     */
    public function get_get(): object {
        return $this->get;
    }

    /**
     * @return object
     */
    public function get_post(): object {
        return $this->post;
    }

}