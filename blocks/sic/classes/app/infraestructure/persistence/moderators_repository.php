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

namespace block_sic\app\infraestructure\persistence;

use block_sic\app\application\contracts\iroles_repository;
use block_sic\app\application\contracts\iusers_repository;
use block_sic\app\domain\moderator;

class moderators_repository {
    private $users;
    private $roles;
    public function __construct(iusers_repository $users, iroles_repository $roles){
        $this->users = $users;
        $this->roles = $roles;
    }

    public function execute(int $courseid): array {
        $users = $this->users->moderators_of($courseid);
        $moderators = array();
        foreach($users as $user) {
            $role = $this->roles->between($user->id, $courseid);
            $moderators[] = new moderator($user->id, $user->name, $user->rut, $user->dv, $role->get_rolename());
        }
        return $moderators;
    }

}