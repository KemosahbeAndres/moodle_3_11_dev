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

namespace block_sic\app;

use block_sic\app\application\login_controller;
use block_sic\app\domain\configuration;
use block_sic\app\domain\redirect_response;
use block_sic\app\domain\request;
use block_sic\app\domain\response;
use block_sic\app\domain\route;
use block_sic\app\infraestructure\persistence\repository_context;
use block_sic\app\infraestructure\persistence\roles_repository;
use block_sic\app\infraestructure\persistence\users_repository;
use phpDocumentor\Reflection\Types\ClassString;

class SicApplication {
    /**
     * @var request
     */
    public $request;
    protected $routes;
    /**
     * @var route
     */
    protected $default;
    protected $context;

    public function __construct(){
        $this->request = new request();
        $this->routes = array();
        $this->default = new \stdClass();
        $this->context = new repository_context();
    }

    /**
     * @throws \dml_exception
     */
    private function __init() {
        global $USER, $COURSE;

        $this->request->cookies = filter_input_array(INPUT_COOKIE);
        $post = filter_input_array(INPUT_POST);
        $get = filter_input_array(INPUT_GET);
        if ($post && $get) {
            $this->request->params = (object)array_merge($get, $post);
        } elseif ($get) {
            $this->request->params = (object)array_merge($get);
        } elseif ($post) {
            $this->request->params = (object)array_merge($post);
        }
        $this->request->action = $this->request->get_string('action');
        if ($this->request->exists('courseid')) {
            $courseid = $this->request->get_int('courseid');
        }else if($this->request->exists('course')){
            $courseid = $this->request->get_int('course');
        }else{
            $courseid = $COURSE->id;
        }
        $this->request->user = (new login_controller($this->context))->execute($USER->id, $courseid);

        try{
            // CONFIGURATION
            $global_config = $this->context->config->from_global();
            if($this->request->exists('instance')){
                $local_config = $this->context->config->from_instance($this->request->get_int('instance'));
            }else{
                global $instanceid;
                $local_config = $this->context->config->from_instance(intval($instanceid));
            }

            $this->request->config = new configuration($global_config, $local_config);

            /*
            $this->request->config->rut_otec = strval($global_config->config_rutotec);
            $this->request->config->token = strval($global_config->config_token);
            $this->request->config->codigo_oferta = strval($local_config->sic_codigo_oferta);
            $this->request->config->codigo_grupo = strval($local_config->sic_codigo_grupo);
            $this->request->config->courseid = intval($local_config->sic_courseid);
            $this->request->config->rol = intval($local_config->sic_rol);
            */
        }catch (\exception $e){}
    }

    public function get(string $action, string $controller, string $function) {
        $this->routes[] = new route('get', $action, new $controller($this->context), $function);
    }

    public function post(string $action, string $controller, string $function) {
        $this->routes[] = new route('post', $action, new $controller($this->context), $function, true);
    }

    public function default(string $action, string $controller, string $function){
        if($this->default instanceof route) return;
        $this->default = new route('get', $action, new $controller($this->context), $function);
        $this->routes[] = $this->default;
    }

    /**
     * @throws \moodle_exception
     */
    public function run() {
        global $PAGE;
        $this->__init();
        if ($this->request->user->get_role() == 'guest' && !$this->is_admin($this->request->user->get_id())) {
            return "<div class='alert alert-danger'>No puedes ver el contenido porque no estas matriculado en este curso!</div>";
            /*redirect(
                new \moodle_url('/my'),
                'No puedes ver el contenido porque no estas matriculado en este curso!',
                4
            );*/
        }
        $is_manager = $this->request->user->get_role() == "manager" || $this->is_admin($this->request->user->get_id());
        $callback = [$this->default->controller, $this->default->callback];
        /** @var route $route */
        foreach ($this->routes as $route) {
            if($route->action == $this->request->action){
                if(!$is_manager and $route->exclusive) break;
                $callback = [$route->controller, $route->callback];
                break;
            }
        }
        /** @var response $response */
        $response = call_user_func($callback, $this->request);
        $this->registerRoutes($response);
        $response->content->valid_local_config = $this->request->config->valid_local();
        $response->content->valid_global_config = $this->request->config->valid_global();
        $response->content->global_configurl = "#";
        $response->content->local_configurl = "#";

        if($this->request->exists('courseid')){
            $courseid = $this->request->get_int('courseid');
        }else{
            $courseid = $this->request->get_int('id');
        }
        if($this->request->exists('instance')){
            $blockid = $this->request->get_int('instance');
        }else{
            global $instanceid;
            $blockid = intval($instanceid);
        }
        if($is_manager){
            $response->content->local_configurl = response::link('/course/view.php', array( 'id' => $courseid, 'bui_editid' => $blockid ));
            $response->content->global_configurl = response::link('/admin/settings.php', array( 'section' => 'blocksettingsic' ));
        }
        $response->content->is_manager = $is_manager;

        /*
        $courseurl = $response->content->courseurl;
        $course_node = \navigation_node::create('Curso', $courseurl);
        $participantsurl = $response->content->participantsurl;
        $participants_node = \navigation_node::create('Participantes', $participantsurl);
        $sicpanelurl = $response->content->sicpanelurl;
        $sic_node = \navigation_node::create('SIC', $sicpanelurl);

        $navigation = $PAGE->navigation;

        $main_node = $navigation->find('gestion_sic', \navigation_node::TYPE_CUSTOM);
        if($main_node){
            echo "Found";
            $main_node->add_node($course_node);
            $main_node->add_node($participants_node);
            $main_node->add_node($sic_node);
            $main_node->make_active();
        }
        */
        return $response->render();
    }

    private function registerRoutes(response $response){
        /** @var route $route */
        foreach ($this->routes as $route){
            $response->registerRoute($route, $this->request);
        }
    }

    protected function is_admin(int $userid): bool {
        foreach (get_admins() as $user) {
            if($user->id == $userid) return true;
        }
        return false;
    }

}