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

use block_sic\app\application\contracts\istates_repository;
use block_sic\app\domain\session;

class change_state_controller {
    private $states;

    public function __construct(istates_repository $repo) {
        $this->states = $repo;
    }

    public function execute(session $params): bool {
        if ($params->get_post()->action != 'change_states') {
            return false;
        }
        $students = json_decode($params->get_post()->data);
        $courseid = $params->get_course()->get_id();

        $cambiados = 0;

        foreach ($students as $student) {
            $found = $params->find_student($student->id);

            if (!is_null($found)) {
                if($found->get_state()->get_state() != trim($student->state)) {
                    $state = $this->states->by_state(trim($student->state));
                    if(is_null($state)) {
                        $state = $this->states->by_code(1);
                    }
                    $this->states->attach_to($state, $student->id, $courseid);
                    $cambiados += 1;
                }
            }
        }
        return $cambiados > 0;
    }


}
