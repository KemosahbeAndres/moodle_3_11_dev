<?php /** @noinspection PhpArrayUsedOnlyForWriteInspection */
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
 * Task for send information of dedication and completion to API SIC
 *
 * @package   block_sic
 * @author    Andres Cubillos <andrestj1996@gmail.com>
 * @copyright Andres Cubillos 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_sic\task;

use block_sic\app\application\consult_course_controller;
use block_sic\app\application\sic\course_handler;
use block_sic\app\application\sic\users_handler;
use block_sic\app\application\student_finder;
use block_sic\app\domain\configuration;
use block_sic\app\infraestructure\api\connection_manager;
use block_sic\app\infraestructure\persistence\repository_context;

defined('MOODLE_INTERNAL') || die();

class send_to_sic_api extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('apitask', 'block_sic');
    }

    public function execute() {
        /*
         * 1. Obtener las configuraciones disponibles para procesar y la configuracion global.
         * 2. Revisar validez de configuracion de cada curso.
         * 3. Revisar en la configuracion del curso si esta activado el procesamiento.
         * 4. Obtener la estructura de los cursos y la lista de alumnos con su informacion.
         * TODO 5. Revisar la fecha de termino de cada curso para decidir si desactivar el procesamiento automatico del curso.
         * 6. Procesar la informacion del curso y los alumnos segun configuracion.
         * 7. Enviar los datos procesados al Gestor Intermedio del SIC y guardar Respuesta.
         * */

        $errores = array();

        try{
            // CARGANDO CONFIGURACION
            $context = new repository_context();
            $coursesFinder = new consult_course_controller($context);
            $studentFinder = new student_finder($context);

            $global = $context->config->from_global();
            $configurations = array();

            /** @var object $config */
            foreach ($context->config->all() as $config){
                $configurations[] = new configuration($global, $config);
            }

            /** @var configuration $config */
            foreach($configurations as $config) {
                if($config->valid_global()) {

                    if($config->valid_local() && $config->exists('sic_courseid')) {

                        // Configuraciones validas.
                        $courseid = $config->get_int('sic_courseid');

                        if($courseid <= 0){
                            $errores[] = new error("Curso no encontrado ID: {$courseid}", "COURSEID {$courseid}");
                            continue;
                        }

                        $status = false;
                        if($config->exists('sic_status')){
                            $status = $config->get_bool('sic_status');
                        }

                        $rut = $config->get_string('config_rutotec');
                        $token = $config->get_string('config_token');

                        $curso = $coursesFinder->execute($courseid);
                        $name = $curso->get_code();

                        if(!$status) {
                            $errores[] = new error("La tarea esta desactivada en el curso {$name}", "COURSEID {$courseid}");
                            continue;
                        }

                        // Revision desactivacion de tarea en ese curso por expiracion de fechas.
                        if($curso->get_enddate() < time()){
                            $object = $config->get_local_config();
                            $object->sic_status = 0;
                            $context->config->save($object, $courseid);
                            // Tarea desactivada para este curso.
                            $errores[] = new error("Se desactivo la tarea para el curso {$name}", "COURSEID {$courseid}");
                        }

                        $students = $studentFinder->all($courseid);

                        $course_handler = new course_handler($curso, $config);
                        $users_hanlder = new users_handler($curso, $students);
                        $course_handler->set_next($users_hanlder);

                        $dataobject = $course_handler->handle();

                        if($dataobject){
                            if(connection_manager::alive($rut, $token)){
                                $response = connection_manager::send($dataobject);
                                $object = $response->__toObject();
                                $context->responses->save_to($object, $courseid);
                            }else{
                                $errores[] = new error("Conexion perdida con API de Gestor Intermedio!", "COURSEID {$courseid}");
                            }
                        }else{
                            $errores[] = new error("No se logro procesar el curso {$name}", "COURSEID {$courseid}");
                        }
                    }else{
                        $errores[] = new error("Configuracion LOCAL invalida!");
                    }
                }else{
                    throw new \exception('Configuracion GLOBAL invalida! No se podra hacer envios de los datos!');
                }
            }


        }catch(\exception $e) {
            echo "Error al procesar la tarea.\n". $e->getMessage(). "\n" . str_replace('#', '\n#', $e->getTraceAsString());
        }
        $count = count($errores);
        echo "\nMensaje encontrados ({$count})\n";
        /** @var error $error */
        foreach ($errores as $error){
            echo "\n{$error}\n";
        }
        echo "\n\n";
    }

}
