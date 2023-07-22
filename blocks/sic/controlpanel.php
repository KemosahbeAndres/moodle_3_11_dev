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

require_once(__DIR__ . '/../../config.php');

use block_sic\app\application\consult_course_controller;
use block_sic\app\application\create_module_controller;
use block_sic\app\application\delete_module_controller;
use block_sic\app\application\edit_module_controller;
use block_sic\app\application\load_course_data_controller;
use block_sic\app\application\login_controller;
use block_sic\app\infraestructure\persistence\activities_repository;
use block_sic\app\infraestructure\persistence\attendances_repository;
use block_sic\app\infraestructure\persistence\completion_repository;
use block_sic\app\infraestructure\persistence\courses_repository;
use block_sic\app\infraestructure\persistence\dedications_repository;
use block_sic\app\infraestructure\persistence\grades_repository;
use block_sic\app\infraestructure\persistence\lessons_repository;
use block_sic\app\infraestructure\persistence\modules_repository;
use block_sic\app\infraestructure\persistence\roles_repository;
use block_sic\app\infraestructure\persistence\sections_repository;
use block_sic\app\infraestructure\persistence\states_repository;
use block_sic\app\infraestructure\persistence\users_repository;
use block_sic\app\infraestructure\web\controlpanel_view;
use block_sic\app\infraestructure\web\manager_panel_view;

global $PAGE, $OUTPUT, $COURSE, $USER;

$courseid = required_param("courseid", PARAM_INT);

$tab = required_param("tab", PARAM_INT);

$instance = required_param("instance", PARAM_INT);

$url = new moodle_url('/blocks/sic/controlpanel.php', array('tab' => $tab, 'courseid' => $courseid, "instance" => $instance));

$PAGE->set_url($url);

$PAGE->set_context(\context_course::instance($courseid));

$PAGE->set_pagelayout("standard");

$PAGE->set_title('Integracion API SIC');

$PAGE->set_heading('Integracion API SIC');

echo $OUTPUT->header();

//require_login($COURSE);

// MAIN.

$users = new users_repository();
$states = new states_repository();
$roles = new roles_repository();
$courses = new courses_repository();
$modules = new modules_repository();
$sections = new sections_repository();
$activities = new activities_repository();
$dedications = new dedications_repository();
$lessons = new lessons_repository();
$progress = new completion_repository();
$grades = new grades_repository();
$attendances = new attendances_repository();

$identificator = new login_controller($users, $roles);

$userdataloader = new load_course_data_controller(
    $dedications,
    $progress,
    $grades,
    $attendances
);

$courseloader = new consult_course_controller(
    $courses,
    $modules,
    $sections,
    $activities,
    $lessons
);


$usersfinder = new \block_sic\app\application\users_finder_controller($users, $states);

$modulecreator = new create_module_controller($modules);

$moduleeditor = new edit_module_controller($modules);

$changestates = new \block_sic\app\application\change_state_controller($states);

$deletemodule = new delete_module_controller($modules);

$sectionattacher = new block_sic\app\application\attach_section_controller($sections);

$sectiondettacher = new \block_sic\app\application\dettach_section_controller($sections);

$lessonattacher = new \block_sic\app\application\attach_lesson_controller($lessons);

$view = new controlpanel_view();

$params = new \block_sic\app\domain\session(
    $courseid,
    $instance,
    $tab,
    $courseloader,
    $identificator,
    $usersfinder,
    $userdataloader
);

// Actions

$view->post('change_states', $changestates, $view::$MANAGER);

$view->post('create_module', $modulecreator, $view::$MANAGER);

$view->post('modify_module', $moduleeditor, $view::$MANAGER);

$view->post('delete_module', $deletemodule, $view::$MANAGER);

$view->post('attach_section', $sectionattacher, $view::$MANAGER);

$view->post('dettach_section', $sectiondettacher, $view::$MANAGER);

$view->post('attach_lesson', $lessonattacher, $view::$MANAGER);

?>
<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="/blocks/sic/styles.css">

<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>

<?php

// Render content
echo $view->render($params);

?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

<?php

// Render Footer

echo $OUTPUT->footer();
