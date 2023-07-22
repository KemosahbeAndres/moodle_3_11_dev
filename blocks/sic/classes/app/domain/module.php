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
use block_sic\app\utils\Arrays;
use block_sic\app\utils\Dates;

class module {
    private $id;
    private $codigo;
    private $fechainicio;
    private $fechafin;
    private $secciones;

    public function __construct(int $id, string $code, int $startdate, int $enddate) {
        $this->id = $id;
        $this->codigo = $code;
        $this->fechainicio = $startdate;
        $this->fechafin = $enddate;
        $this->secciones = Arrays::void();
    }

    public function equal(module $module): bool {
        return $this->id == $module->get_id();
    }

    public function __toObject(): object {
        $sections = array();
        /** @var section $section */
        foreach ($this->get_sections() as $section) {
            $s = $section->__toObject();
            $s->module = $this->basicObject();
            $sections[] = $s;
        }
        return (object) [
            'id' => $this->get_id(),
            'code' => $this->get_code(),
            'startdate' => Dates::format_date_time($this->get_startdate()),
            'enddate' => Dates::format_date_time($this->get_enddate()),
            'sync' => $this->get_sync_amount(),
            'async' => $this->get_async_amount(),
            'sections' => $sections
        ];
    }

    public function basicObject(): object {
        return (object)[
            'id' => $this->get_id(),
            'code' => $this->get_code(),
            'startdate' => Dates::format_date_time($this->get_startdate()),
            'enddate' => Dates::format_date_time($this->get_enddate()),
            'sync' => $this->get_sync_amount(),
            'async' => $this->get_async_amount(),
            ];
    }

    public function get_id(): int {
        return $this->id;
    }

    public function get_code(): string {
        return $this->codigo;
    }

    public function get_startdate(): int {
        return $this->fechainicio;
    }

    public function get_enddate(): int {
        return $this->fechafin;
    }

    public function add_section(section $section) {
        $this->secciones[] = $section;
    }

    public function get_sections(): array {
        return $this->secciones;
    }

    public function get_excluded_section(): array {
        return array_filter($this->secciones, function (section $section) {
            return !$section->assigned();
        });
    }

    public function count_lessons(): int {
        $total = 0;
        /** @var section $seccion */
        foreach ($this->secciones as $seccion){
            $total += $seccion->count_lessons();
        }
        return $total;
    }
    public function count_activities(): int {
        $total = 0;
        foreach ($this->secciones as $seccion) {
            $total += $seccion->count_activities();
        }
        return intval($total);
    }

    public function get_async_amount(): int {
        //return intval($this->asincronas);
        $activities = $this->count_activities();
        $lessons = $this->count_lessons();
        return $activities - $lessons;
    }

    public function get_sync_amount(): int {
        //return intval($this->sincronas);
        return $this->count_lessons();
    }

    public function in(section $section): bool {
        foreach ($this->secciones as $seccion) {
            if($section->equal($seccion)) {
                return true;
            }
        }
        return false;
    }

    public function get_activities(): array {
        $activities = array();
        /** @var section $section */
        foreach ($this->secciones as $section) {
            /** @var activity $activity */
            foreach ($section->get_activities() as $activity) {
                $activities[] = $activity;
            }
        }
        return $activities;
    }

    public function get_lessons(): array {
        $lessons = array();
        /** @var section $section */
        foreach ($this->secciones as $section) {
            /** @var lesson $lesson */
            foreach ($section->get_lessons() as $lesson) {
                $lessons[] = $lesson;
            }
        }
        return $lessons;
    }

    public function toObject(): object {
        return (object)[
            'id' => $this->get_id(),
            'code' => $this->get_code(),
            'startdate' => $this->get_startdate(),
            'enddate' => $this->get_enddate(),
            'sync' => $this->get_sync_amount(),
            'async' => $this->get_async_amount()
        ];
    }

}
