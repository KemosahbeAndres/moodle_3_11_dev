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

use block_sic\app\application\contracts\icourses_repository;
use block_sic\app\application\contracts\imodules_repository;
use block_sic\app\domain\course;
use block_sic\app\domain\module;
use block_sic\app\domain\session;
use stdClass;

final class create_module_controller {
    private $modules;

    public function __construct(imodules_repository $repo) {
        $this->modules = $repo;
    }

    public function execute(session $params) {

        $post = $params->get_post();

        if ($post->action != "create_module") {
            return;
        }

        $mdata = json_decode($post->data);

        $course = $params->get_course();

        $module = new stdClass();
        $module->id = 0;
        $module->code = $mdata->code;
        $module->startdate = intval($mdata->startdate) + 86400;
        $module->enddate = intval($mdata->enddate) + 86400;
        $module->sync = intval($mdata->sync);
        $module->async = intval($mdata->async);

        $this->modules->attach_to($module, $course->get_id());
    }

}
