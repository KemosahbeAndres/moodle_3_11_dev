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

use block_sic\app\controller\lesson_controller;
use block_sic\app\controller\module_controller;
use block_sic\app\controller\section_controller;
use block_sic\app\controller\sic_controller;
use block_sic\app\controller\student_controller;
use block_sic\app\SicApplication;
use block_sic\app\controller\course_controller;

global $PAGE, $OUTPUT, $COURSE, $USER;

$courseid = required_param("courseid", PARAM_INT);

$instance = required_param("instance", PARAM_INT);

if(!is_numeric($courseid) or $courseid <= 0 or !is_numeric($instance) or $instance <= 0){
    $url = new moodle_url('/my/', array());
    redirect($url, "Parametros incorrectos", 0);
    die();
}

$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));

$url = new moodle_url('/blocks/sic/dashboard.php', array('courseid' => $courseid, 'instance' => $instance, 'action' => 'course'));

$PAGE->set_url($url);

$PAGE->set_context(\context_course::instance($courseid));
$course = get_course($courseid);
$PAGE->set_course($course);

$settingsnav = $PAGE->settingsnav;
$course_node = $settingsnav->add($course->shortname, $courseurl);
$plugin_node = $course_node->add('Gestion SIC', $url);
$plugin_node->make_active();

$navigation = $PAGE->navigation;

//$icon = $OUTPUT->pix_icon('core:e/document_properties', '');

$flat_node = navigation_node::create(
    get_string('pluginname', 'block_sic'),
    $url,
    navigation_node::TYPE_CUSTOM,
    get_string('pluginname', 'block_sic')
);
$flat_node->showinflatnavigation = true;
$navigation->add_node($flat_node);
/*
$nav =$PAGE->navigation;
$course_node = $nav->add('Volver al curso', $courseurl);
$course_thing_node = $course_node->add('Volver al curso', $courseurl);
$course_thing_node->make_active();
*/
/*
$node = $nav->add('Gestion SIC', $url);
$node->make_active();
*/

/*
$settingsnode = $PAGE->settingsnav->add("{$accion}");
$editnode = $settingsnode->add("{$accion}", $url);
$editnode->make_active();
 * */


require_login($courseid);

$PAGE->set_pagelayout("standard");

$PAGE->set_title('Integracion API SIC');

$PAGE->set_heading('Integracion API SIC');

echo $OUTPUT->header();

try{
    $app = new SicApplication();

    $app->default('course', course_controller::class, 'index');

    $app->get('freesections', course_controller::class, 'free_sections');

    $app->get('participants', course_controller::class, 'participants');

    $app->get('sic', sic_controller::class, 'sicpanel');

    $app->get('lessons', course_controller::class, 'lessons');

    $app->get('studentdetail', student_controller::class, 'details');

    // SIC
    $app->get('resume', sic_controller::class, 'resume');

    $app->get('history_details', sic_controller::class, 'details');

    $app->post('send_register', sic_controller::class, 'send');

    $app->get('reg_detail', sic_controller::class, 'reg_detail');

    // Modulos
    $app->get('create_module', module_controller::class, 'creating');

    $app->get('edit_module', module_controller::class, 'edit');

    $app->post('save_module', module_controller::class, 'save');

    $app->get('delete_module', module_controller::class, 'delete');

    $app->post('confirm_delete_module', module_controller::class, 'confirmDelete');

    // Secciones
    $app->get('sectiondetail', section_controller::class, 'details');

    $app->get('edit_section', section_controller::class, 'edit');

    $app->post('reassign_section', section_controller::class, 'reassign');

    $app->get('massive_assign_section', section_controller::class, 'massive');

    $app->post('massive_reassign', section_controller::class, 'massive_reassign');

    // Alumnos
    $app->get('change_state', student_controller::class, 'change_state');

    $app->post('confirm_change_state', student_controller::class, 'confirm_change_state');

    $app->post('exclude_include', student_controller::class, 'exclude_include');

    $app->get('attendance', student_controller::class, 'attendance');

    $app->post('massive_attendance', student_controller::class, 'save_attendance');

    // Clases
    $app->get('create_lesson', lesson_controller::class, 'create');

    $app->get('edit_lesson', lesson_controller::class, 'edit');

    $app->post('save_lesson', lesson_controller::class, 'save');

    $app->get('delete_lesson', lesson_controller::class, 'delete');

    $app->post('confirm_delete_lesson', lesson_controller::class, 'confirm_delete');

    // Lanzar aplicacion
    echo $app->run();
}catch(\exception $e) {
    echo "<div class='alert alert-danger'>{$e->getMessage()}</div>";
    $filtered = str_replace('#', '<br>#', $e->getTraceAsString());
    echo "<div class='alert alert-danger'>{$filtered}</div>";
    echo "<div class='alert alert-danger'>Comunicate con un administrador!</div>";
}

echo $OUTPUT->footer();
