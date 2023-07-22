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

use block_sic\app\application\contracts\idedication_repository;
use block_sic\app\utils\Arrays;
use stdClass;

class dedications_repository implements idedication_repository {
    public $repository;
    private static $activities;

    public function __construct() {
        $this->repository = new activities_repository();
        self::$activities = Arrays::void();

    }

    private static $logstores = array('logstore_standard', 'logstore_legacy');

    /**
     * @throws \dml_exception
     */
    public function between(int $userid, object $section): object {
        global $DB;
        $record = $DB->get_record('course_sections', ['id' => $section->id], '*', MUST_EXISTS);
        $course = $DB->get_record('course', ['id' => $record->course], '*', MUST_EXISTS);
        $where = 'courseid = :courseid AND userid = :userid AND timecreated >= :mintime AND timecreated <= :maxtime AND
        (action = :action_v OR action = :action_u) AND (target = :target_c OR target = :target_cm OR target = :target_cmc OR target = :target_cp)';
        $params = array(
            'courseid' => $course->id,
            'userid' => $userid,
            'mintime' => $course->startdate,
            'maxtime' => $course->enddate,
            'action_v' => 'viewed',
            'action_u' => 'updated',
            'target_c' => 'course',
            'target_cm' => 'course_module',
            'target_cmc' => 'course_module_completion',
            'target_cp' => 'content_page'
        );
        $events = $this->get_events_select($where, $params);

        self::$activities = $this->repository->related_to($section);

        $total = 0;

        if (!empty($events)) {
            $lastevent = array_shift($events);
        }

        foreach ($events as $event) {
            if ($this->section_clicked($event) || $this->activity_clicked($event)) {
                if (!is_null($lastevent)) {
                    $resultado = intval($event->time - $lastevent->time);
                    $maxtime = intval(60 * 45);

                    if ($resultado > $maxtime) {
                        $total += $maxtime;
                    } else {
                        $total += $resultado;
                    }
                }

                $lastevent = $event;
            }

        }
        $output = new stdClass();
        $output->time = $total;
        return $output;

    }


    private function section_clicked($event): bool {
        if (is_null($event->other) || !isset($event->section)) {
            return false;
        }
        return true;

    }

    private function activity_clicked($event): bool {
        foreach (self::$activities as $activity) {
            if ($activity->instance == $event->activity && $activity->type == $event->table) {
                return true;
            }
        }
        return false;
    }

    /**

     * Return formatted events from logstores.

     * @param string $selectwhere

     * @param array $params

     * @return array

     */
    private static function get_events_select($selectwhere, array $params) {
        $return = array();

        static $allreaders = null;

        if (is_null($allreaders)) {

            $allreaders = get_log_manager()->get_readers();

        }

        // var_dump($allreaders);

        $processedreaders = 0;

        foreach (self::$logstores as $name) {

            if (isset($allreaders[$name])) {

                $reader = $allreaders[$name];

                $events = $reader->get_events_select($selectwhere, $params, 'timecreated ASC', 0, 0);

                // var_dump($reader);

                foreach ($events as $event) {

                    // Note: see \core\event\base to view base class of event.

                    $obj = new stdClass();

                    $obj->time = $event->timecreated;

                    $obj->ip = $event->get_logextra()['ip'];

                    $obj->other = $event->other;

                    $obj->section = $event->other['coursesectionnumber'];

                    $obj->action = $event->action;

                    $obj->target = $event->target;

                    $obj->table = $event->objecttable;

                    $obj->activity = $event->objectid;

                    $obj->course = $event->courseid;

                    $obj->event = $event;

                    $return[] = $obj;

                }

                if (!empty($events)) {

                    $processedreaders++;

                }

            }

        }

        // Sort mixed array by time ascending again only when more of a reader has added events to return array.

        if ($processedreaders > 1) {

            usort($return, function($a, $b) {

                return $a->time > $b->time;

            });

        }

        return $return;

    }

    /**

     * Return formatted events from logstores.

     * @param string $selectwhere

     * @param array $params

     * @return array

     */

    private static function get_events_from_table($where, $params, $table = 'logstore_standard_log'){

        global $DB;

        $params['table'] = $table;

        $sql = "SELECT * FROM $table WHERE 'courseid' = {$params['courseid']} AND 'userid' = {$params['userid']} AND timecreated >= {$params['mintime']} AND timecreated <= {$params['maxtime']}";

        $events = $DB->get_records_sql($sql);

        // echo 'N Events: '.count($events);

        return $events;

    }

}
