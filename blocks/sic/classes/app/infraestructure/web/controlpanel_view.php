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

use block_sic\app\domain\manager;
use block_sic\app\domain\moderator;
use block_sic\app\domain\session;
use block_sic\app\domain\student;
use block_sic\app\domain\teacher;

class controlpanel_view extends view {
    private $view;
    public function __construct() {
        parent::__construct();
        $this->view = new empty_view();
    }

    public function render(session $params) {

        $userlogged = $params->get_user();
        $level = self::$STUDENT;

        if ($userlogged instanceof manager) {
            $this->view = new manager_view();
            $level = self::$MANAGER;
            //redirect(new \moodle_url("/course/view.php", array('id' => $params->get_course()->get_id())), "USUARIO ROL MANAGER");
        } else if ($userlogged instanceof moderator) {
            $this->view = new moderator_view();
            $level = self::$MODERATOR;
        } else if ($userlogged instanceof teacher) {
            $this->view = new teacher_view();
            $level = self::$TEACHER;
        } else if ($userlogged instanceof student) {
            $this->view = new student_view();
            $level = self::$STUDENT;
        } else {
            redirect(new \moodle_url("/course/view.php", array('id' => $params->get_course()->get_id())), "Complemento en desarrollo!!");
        }

        if ($this->view instanceof empty_view) {
            //echo get_user_roles_in_course($USER->id, $courseid);
            redirect(new \moodle_url("/course/view.php", array('id' => $params->get_course()->get_id())), "Complemento en desarrollo!!");
        }

        $this->run_request($params, $level);

        $params->load();

        return $this->view->render($params);

        //$this->echo_render($data);

        //return $OUTPUT->render_from_template($this->view->get_source(), $this->view->get_template());
    }

}
