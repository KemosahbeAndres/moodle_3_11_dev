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


use block_sic\app\application\login_controller;
use block_sic\app\application\consult_course_controller;
use block_sic\app\application\load_course_data_controller;
use block_sic\app\controller\block_controller;
use block_sic\app\infraestructure\persistence\activities_repository;
use block_sic\app\infraestructure\persistence\attendances_repository;
use block_sic\app\infraestructure\persistence\courses_repository;
use block_sic\app\infraestructure\persistence\dedications_repository;
use block_sic\app\infraestructure\persistence\grades_repository;
use block_sic\app\infraestructure\persistence\lessons_repository;
use block_sic\app\infraestructure\persistence\modules_repository;
use block_sic\app\infraestructure\persistence\completion_repository;
use block_sic\app\infraestructure\persistence\roles_repository;
use block_sic\app\infraestructure\persistence\sections_repository;
use block_sic\app\infraestructure\persistence\states_repository;
use block_sic\app\infraestructure\persistence\users_repository;
use block_sic\app\domain\student;
use block_sic\app\domain\manager;
use block_sic\app\domain\teacher;
use block_sic\app\domain\moderator;
use block_sic\app\infraestructure\web\block_view;
use block_sic\app\infraestructure\web\student_block_view;
use block_sic\app\infraestructure\web\user_block_view;
use block_sic\app\SicApplication;

defined('MOODLE_INTERNAL') or die();


class block_sic extends block_base{

    public function init() {
        $this->title = get_string('pluginname', 'block_sic');
    }

    public function has_config() {
        return true;
    }

    /**
     * Core function, specifies where the block can be used.
     * @return array
     */
    public function applicable_formats() {
        return array('course-view' => true, 'mod' => true);
    }

    private function build() {
        global $PAGE;
        if ($this->content !== null) {
            return $this->content;
        }

        /*
        $this->content = new stdClass();

        $second = new moodle_url('/blocks/sic/dashboard.php', array('courseid' => $COURSE->id, 'instance' => $this->instance->id));
        $this->content->text = "<a href='{$second}' class='btn btn-primary btn-block'>GESTION SIC</a>";


        return $this->content;*/
        try{

            $GLOBALS['instanceid'] = intval($this->instance->id);
                $courseid = intval(optional_param('id', 0, PARAM_INT));

            $navigation = $PAGE->navigation;

            $url = new moodle_url('/blocks/sic/dashboard.php', array('courseid' => $courseid, 'instance' => $this->instance->id, 'action' => 'course'));

            $flat_node = navigation_node::create(
                get_string('pluginname', 'block_sic'),
                $url,
                navigation_node::TYPE_CUSTOM,
                get_string('pluginname', 'block_sic')
            );
            $flat_node->showinflatnavigation = true;
            $navigation->add_node($flat_node);


            $app = new SicApplication();

            $app->default('index', block_controller::class, 'index');

            $this->content->text = $app->run();

        }catch(\exception $e) {
            $this->content->text .= "<div class='alert alert-danger'>{$e->getMessage()}</div>";
            $filtered = str_replace('#', '<br>#', $e->getTraceAsString());
            $this->content->text .= "<div class='alert alert-danger'>{$filtered}</div>";
            $this->content->text .= "<div class='alert alert-danger'>Comunicate con un administrador!</div>";
        }

        return $this->content;
    }

    public function get_content() {
        return $this->build();

        $this->content = new stdClass();

        $id = optional_param('id', 0, PARAM_INT);



        $_SESSION['section_selected'] = isset($_GET['section']) ? $_GET['section'] : 0;



        require_once('block_sic_lib.php');

        $mintime = $this->page->course->startdate;

        $maxtime = $this->page->course->enddate;

        // echo '<br>'.$maxtime.'<br>';

        $maxtime = time();

        $sm = new block_sic_manager($this->page->course, $mintime, $maxtime, BLOCK_SIC_DEFAULT_SESSION_LIMIT);

        $response = $sm->get_user_dedication($USER, true);

        $dedication = $response[0];

        $sectionsdedication = $response[1];

        $userid = $USER->id;

        $rut = $USER->idnumber;

        $codcurso = $this->config->sic_codigo_grupo;

        $completion = $sm->get_user_completion($USER);

        // $info_usuario = user_get_user_details($USER, $PAGE->course);

        // $info_roles = $info_usuario['roles'];

        // foreach($info_roles as $role){

        //     if($role['roleid'] == $this->config->sic_rol){

        //         $userrol = $this->config->sic_rol;

        //     }else{

        //         $userrol = $role['roleid'];

        //     }

        // }



        // $html = 'ID: '.optional_param('id', 0, PARAM_INT).'<br>';

        $html = 'Course ID: '.$COURSE->id.'<br>';

        $html .= 'ID Usuario: '.$userid.'<br>';

        $html .= 'RUT: '.$rut.'<br>';

        $context = context_course::instance($COURSE->id);

        $roles = get_user_roles($context, $USER->id, true);

        $role = key($roles);

        $html .= 'User Role ID: '. $roles[$role]->roleid.'<br>';

        $html .= 'Codigo Curso: '.$codcurso.'<br>';

        $html .= 'Section ID: '.$_GET['section'].'<br>';

        $html .= 'Dedication: '.round(($dedication /60 /60), 2).' horas<br>';

        $checksum = 0;

        $num = 0;

        foreach($sectionsdedication as $section=>$time){

            $html .= 'Section ID '.$section.': '.round(($time /60 /60), 2).' horas<br>';

            $checksum += round(($time /60 /60), 2);

            $num += 1;

        }

        $html .= 'Checksum: '.$checksum.' from '.$num.' sections<br>';

        $html .= 'Avance: <br>';

        foreach($completion as $key=>$user){

            foreach($user->course as $sectionid=>$section){

                $html .= 'Seccion '.$sectionid.' = ';

                foreach($section->activitys as $activity){

                    $html .= $activity->name. ' ' . ($activity->completed == 0 ? "incompleto" : "completado") . ' | ';

                }

            }

            $html .= '<br>';

        }

        // var_dump(get_config('sic', 'courseid'));



        $crono = false;

        $start = 0;

        if(isset($_SESSION['swlogueado'.$id]) && $_SESSION['swlogueado'.$id] == "SI"){

            $crono = true;

            if(!isset($_SESSION['cronometro'])){

                $_SESSION['cronometro'] = time();

            }

            $start = $_SESSION['cronometro'];

        }

        //$html = "<h3 id='cronometro' status='{$crono}' starttime='{$start}'>00:00:00</h3>";

        //$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/blocks/sic/js/lib.js'));
        $conected_time = round(($dedication /60 /60), 2);

        $html = "<h5>Tiempo total:</h5> <p>{$conected_time} horas</p>";
        $html += "<p>mintime: {$mintime}</p>";

        // $html .= $id.'<br>';

        $this->content->text = $html;

        $url = new moodle_url('/blocks/sic/detalles.php', array('blockid'=>$this->instance->id, 'courseid'=>$COURSE->id));

        $this->content->footer = html_writer::link($url, "Ver historial");

        // $task = new \block_sic\task\send_to_sic_api();

        // $task->execute();

        return $this->content;

    }

    /**
     * Allows the block to be added multiple times to a single page
     * @return boolean
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     * @since Moodle 3.8
     */
    public function get_config_for_external() {
        // Return all settings for all users since it is safe (no private keys, etc..).
        $instanceconfigs = !empty($this->config) ? $this->config : new stdClass();
        $pluginconfigs = get_config('block_activity_results');

        return (object) [
            'instance' => $instanceconfigs,
            'plugin' => $pluginconfigs,
        ];
    }

}

