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

use block_sic\app\domain\redirect_response;
use block_sic\app\domain\view_response;
use block_sic\app\infraestructure\persistence\repository_context;
use block_sic\app\utils\Dates;

class controller {
    protected $context;
    protected $content;

    protected function __construct(repository_context $context){
        $this->context = $context;
        $this->content = new \stdClass();
        $this->content->format = function ($valor, \Mustache_LambdaHelper $helper) {
            $fecha = $helper->render($valor);
            return Dates::format_date_time(intval($fecha));
        };
        $this->content->time_format = function ($time, \Mustache_LambdaHelper $helper) {
            $timestamp = $helper->render($time);
            if($timestamp <= 0) return "0 horas y 0 minutos";
            return Dates::format_time($timestamp);
        };
        $this->content->real_state = function ($value, \Mustache_LambdaHelper $helper){
            $state = $helper->render($value);
            switch($state){
                case 1:
                    return "cursando";
                case 2:
                    return "aprobado";
                case 3:
                    return "reprobado";
            }
            return "cursando";
        };
    }

    protected function response(string $viewname): view_response {
        //$path = preg_split('/(\/)/', $viewname);
        $path = explode('/', $viewname);
        $name = $viewname;
        if(is_array($path)){
            $name = strval($path[array_key_last($path)]);
        }
        $this->content->{trim($name)."page"} = true;
        return new view_response(trim($viewname), $this->content);
    }

    protected function redirect(string $action, object $params, string $message = "No se realizo ningun cambio!"): redirect_response {
        return new redirect_response($action, $message, $params);
    }

}