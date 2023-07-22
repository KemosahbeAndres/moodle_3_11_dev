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

use block_sic\app\application\contracts\iroles_repository;
use block_sic\app\application\contracts\iusers_repository;
use block_sic\app\domain\state;
use block_sic\app\domain\visitor;
use block_sic\app\domain\manager;
use block_sic\app\domain\moderator;
use block_sic\app\domain\rol;
use block_sic\app\domain\student;
use block_sic\app\domain\teacher;
use block_sic\app\domain\user;
use block_sic\app\infraestructure\persistence\repository_context;
use block_sic\app\infraestructure\persistence\states_repository;

class login_controller {

    private $context;

    public function __construct(repository_context $context) {
        $this->context = $context;
    }

    /**
     * @throws \dml_exception
     */
    public function execute(int $userid, int $courseid): user {

        $rol = $this->context->roles->between($userid, $courseid);

        $user = $this->context->users->by_id($userid);

        switch($rol->get_role()) {
            case rol::$manager:
                return new manager($user->id, $user->name, $user->rut, $user->dv, $rol->get_rolename());
            case rol::$moderator:
                return new moderator($user->id, $user->name, $user->rut, $user->dv, $rol->get_rolename());
            case rol::$teacher:
                return new teacher($user->id, $user->name, $user->rut, $user->dv, $rol->get_rolename());
            case rol::$student:
                $student = new student($user->id, $user->name, $user->rut, $user->dv, $rol->get_rolename());
                $student->set_state(new state($this->context->states->between($userid, $courseid)));
                return $student;
        }
        if($this->is_admin($userid)){
            return new manager($user->id, $user->name, $user->rut, $user->dv, 'manager');
        }
        return new visitor($user->id, $user->name, $user->rut, $user->dv, "guest");
        //return new visitor($user->id, $user->name, $user->rut, $user->dv);
    }

    protected function is_admin(int $userid): bool {
        foreach (get_admins() as $user) {
            if($user->id == $userid) return true;
        }
        return false;
    }

}
