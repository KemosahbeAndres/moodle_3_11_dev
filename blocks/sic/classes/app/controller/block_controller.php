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

namespace block_sic\app\controller;

use block_sic\app\application\student_finder;
use block_sic\app\domain\request;
use block_sic\app\domain\response;
use block_sic\app\domain\student;
use block_sic\app\infraestructure\persistence\repository_context;
use moodle_url;

class block_controller extends controller {
    /**
     * @var student_finder
     */
    private $studentFinder;

    public function __construct(repository_context $context) {
        parent::__construct($context);
        $this->studentFinder = new student_finder($this->context);
    }

    /**
     * @throws \moodle_exception
     */
    public function index(request $request): response {
        global $instanceid;
        if($request->exists('course')){
            $courseid = $request->get_int('course');
        } else if($request->exists('courseid')){
            $courseid = $request->get_int('courseid');
        } else{
            $courseid = $request->get_int('id');
        }
        $userid = $request->user->get_id();
        $role = $request->user->get_role();

        switch ($role) {
            case 'student':
                $this->content->student = $this->studentFinder->execute($userid, $courseid)->__toObject();
                $this->content->link = $this->get_url('/blocks/sic/dashboard.php', array('courseid' => $courseid, 'instance' => $instanceid));
                break;
            case 'manager':
            case 'coursecreator':
            case 'editingteacher':
            case 'teacher':
                $students = $this->studentFinder->all($courseid);
                $alumnos = array();
                /** @var student $student */
                foreach ($students as $student) {
                    $alumnos[] = $student->__toObject();
                }
                $this->content->students = $alumnos;
                $this->content->link = $this->get_url('/blocks/sic/dashboard.php', array('courseid' => $courseid, 'instance' => $instanceid));
                break;
        }

        if($this->is_admin($userid)){
            $this->content->link = $this->get_url('/blocks/sic/dashboard.php', array('courseid' => $courseid, 'instance' => $instanceid));
        }

        return $this->response('block/index');
    }
    protected function get_url(string $path, array $params){
        $url = new moodle_url(trim($path), $params);
        return str_replace('&amp;', '&', $url);
    }

    protected function is_admin(int $userid): bool {
        foreach (get_admins() as $user) {
            if($user->id == $userid) return true;
        }
        return false;
    }
}