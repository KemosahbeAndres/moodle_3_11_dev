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

namespace block_sic\app\controller;

use block_sic\app\application\consult_course_controller;
use block_sic\app\application\prepare_json_controller;
use block_sic\app\application\student_finder;
use block_sic\app\domain\configuration;
use block_sic\app\domain\request;
use block_sic\app\domain\response;
use block_sic\app\domain\sic\sic_response;
use block_sic\app\domain\student;
use block_sic\app\infraestructure\api\connection_manager;
use block_sic\app\infraestructure\persistence\repository_context;
use block_sic\app\utils\Dates;

class sic_controller extends controller {
    /**
     * @var consult_course_controller
     */
    private $courseLoader;
    /**
     * @var prepare_json_controller
     */
    private $jsonPrepare;
    public function __construct(repository_context $context) {
        parent::__construct($context);
        $this->content->sicpanelpage = true;
        $this->courseLoader = new consult_course_controller($context);
        $this->jsonPrepare = new prepare_json_controller($this->courseLoader, new student_finder($context));
    }

    public function sicpanel(request $request): response {
        if(false === $request->config instanceof configuration)
            return $this->redirect('course', $request->params, "Debes configurar el complemento!");
        $courseid = $request->get_int('courseid');
        $course = $this->courseLoader->execute($courseid);
        $students = $this->context->students->execute($courseid);
        $nstudents = 0;
        /** @var student $student */
        foreach ($students as $student){
            if($student->is_active()) {
                $nstudents += 1;
            }
        }
        $this->content->course = $course->__toObject();
        $rut = $request->config->get_string('config_rutotec');
        $token = $request->config->get_string('config_token');

        $data = json_encode($this->jsonPrepare->execute($courseid, $request->config), JSON_UNESCAPED_UNICODE);
        $url = new \moodle_url('/blocks/sic/outputfile.php', array( 'data' => $data ));
        $this->content->downloadurl = str_replace('&amp;', '&', $url->__toString());

        $this->content->course->nmodules = count($course->get_modules());
        $this->content->course->nstudents = $nstudents;
        $this->content->course->rutotec = $rut;
        $this->content->tokenvalid = $this->tokenValid($request->config);
        $this->content->course->codigo_oferta = $request->config->get_string('sic_codigo_oferta');
        $this->content->course->codigo_grupo = $request->config->get_string('sic_codigo_grupo');
        $json = json_encode($this->content, JSON_UNESCAPED_UNICODE);
        $this->content->json = $json;
        $this->content->messages = connection_manager::get_messages();

        try{
            $responses = array_reverse($this->context->responses->related_to($courseid));
            $objects = array();
            foreach (array_slice($responses, 0, 6) as $response) {
                $response->respuestas = count($response->payloads);
                $response->errores = count($response->errors);
                $objects[] = $response;
            }
            $this->content->responses = $objects;
        } catch (\dml_exception|\exception $e) {}

        $response = connection_manager::history($rut, $token);
        $this->content->history = array_slice($response->get_payloads(), 0, 5);

        return $this->response('sic/sicpanel');
    }
    public function reg_detail(request $request): response {
        if(false === $request->config instanceof configuration)
            return $this->redirect('course', $request->params, "Debes configurar el complemento!");
        $id = $request->get_int('id');
        $response = $this->context->responses->by_id($id);
        $response->have_data = !empty($response->payloads);
        $response->errores = !empty($response->errors) ? "SI" : "NO";
        $this->content->response = $response;
        return $this->response('sic/reg_detail');
    }

    public function details(request $request): response {
        if(false === $request->config instanceof configuration)
            return $this->redirect('course', $request->params, "Debes configurar el complemento!");
        $processid = $request->get_int('processid');
        $rut = $request->config->get_string('config_rutotec');
        $token = $request->config->get_string('config_token');
        $response = connection_manager::history($rut, $token, $processid);
        $this->content->history = $response->get_payloads()[0];
        $this->content->history->cantidad = count($response->get_payloads()[0]->listaregistros);
        $this->content->round = function ($valor, \Mustache_LambdaHelper $helper) {
            $avance = $helper->render($valor);
            return intval($avance);
        };
        //print_r($response->get_payloads()[0]);
        return $this->response('sic/details');
    }

    /**
     * @throws \moodle_exception
     */
    public function resume(request $request): response {
        if(false === $request->config instanceof configuration)
            return $this->redirect('course', $request->params, "Debes configurar el complemento!");
        $courseid = intval($request->params->courseid);
        $json = json_encode($this->jsonPrepare->execute($courseid, $request->config), JSON_UNESCAPED_UNICODE);
        $this->content->json = $json;
        $url = new \moodle_url('/blocks/sic/outputfile.php', array( 'data' => $json ));
        $this->content->downloadurl = str_replace('&amp;', '&', $url->__toString());
        $this->content->tokenvalid = $this->tokenValid($request->config);
        return $this->response('sic/resume');
    }

    public function send(request $request): response {
        $message = "No se hizo ningun cambio!";
        try{
            $courseid = $request->get_int('courseid');
            if(false === $request->config instanceof configuration)
                return $this->redirect('course', $request->params, "Debes configurar el complemento!");
            //$json = json_encode($this->jsonPrepare->execute($courseid, $request->config), JSON_UNESCAPED_UNICODE);
            $dataobject = $this->jsonPrepare->execute($courseid, $request->config);
            if($this->tokenValid($request->config)){
                $response = connection_manager::send($dataobject);
                $object = $response->__toObject();
                $this->context->responses->save_to($object, $courseid);
                $message = "Datos enviados con exito!";
                //print_r($response);
                //return $this->response('sic/resume');
            }else{
                $message = "No se hizo ningun cambio! TOKEN invalido!";
            }
        }catch (\exception $e) {
            $message = $e->getMessage();
        }
        return $this->redirect('resume', $request->params, $message);
    }

    protected function connection_info(object $config): sic_response {
        $token = trim(strval($config->token));
        $rut = trim(strval($config->rut_otec));
        return connection_manager::history($rut, $token);
    }

    protected function tokenValid(configuration $config): bool {
        $token = $config->get_string('config_token');
        $rut = $config->get_string('config_rutotec');
        $oferta = $config->get_string('sic_codigo_oferta');
        $grupo = $config->get_string('sic_codigo_grupo');
        if(
            !empty($token) and
            !empty($rut) and
            !empty($oferta) and
            !empty($grupo)  and
            connection_manager::alive($rut, $token)
        ){
            return true;
        }
        return false;
    }
}