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

use block_sic\app\domain\activity;
use block_sic\app\domain\lesson;
use block_sic\app\domain\section;
use block_sic\app\infraestructure\persistence\repository_context;

class section_finder {

    private $context;

    public function __construct(repository_context $context){
        $this->context = $context;
    }

    /**
     * @throws \dml_exception
     */
    public function execute(int $id): ?section {
        $section = $this->context->sections->by_id($id);
        $module = $this->context->modules->from($id);
        $seccion = new section(
            $section->id,
            $section->name,
            $section->assigned,
            $module
        );
        $activitylist = $this->context->activities->related_to($section);
        //echo "FOUND: ".count($activitylist);
        foreach ($activitylist as $activity) {
            $actividad = new activity(
                $activity->id,
                $activity->code,
                $activity->mandatory,
                $activity->instance,
                $activity->type
            );
            //echo "<br>FOUND: ".$actividad->get_code() ;
            $seccion->add_activity($actividad);
        }
        // Clases
        $lessonlist = $this->context->lessons->related_to($seccion->get_id());
        foreach ($lessonlist as $lesson) {
            $act = null;
            foreach ($seccion->get_activities() as $activity) {
                if ($activity->get_id() == $lesson->activity) {
                    $act = $activity;
                }
            }
            if (!is_null($act)) {
                $clase = new lesson(
                    $lesson->id,
                    $act,
                    $lesson->date,
                    $lesson->duration,
                    $seccion->get_id()
                );
                $seccion->add_lesson($clase);
            }

        }
        return $seccion;
    }

}