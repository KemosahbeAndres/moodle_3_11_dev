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

use block_sic\app\domain\request;
use block_sic\app\domain\response;
use block_sic\app\infraestructure\persistence\repository_context;
use block_sic\app\utils\Dates;

class module_controller extends controller {
    public function __construct(repository_context $context) {
        parent::__construct($context);
        $this->content->coursepage = true;
    }
    public function creating(request $request): response {
        $this->content->courseid = intval($request->params->courseid);
        return $this->response('course/module/create');
    }

    public function edit(request $request): response {
        $module = $this->context->modules->by_id(intval($request->params->moduleid));
        $this->content->module = (object)[
            'id' => $module->id,
            'code' => $module->code,
            'startdate' => Dates::format_iso_8601($module->startdate),
            'enddate' => Dates::format_iso_8601($module->enddate),
        ];
        $this->content->courseid = intval($request->params->courseid);
        return $this->response('course/module/edit');
    }

    public function save(request $request): response {
        $message = "No se realizo ningun cambio!";
        try{
            $courseid = intval($request->params->courseid);
            $id = 0;
            if(isset($request->params->moduleid)){
                $id = intval($request->params->moduleid);
                echo "ID encontrado => ".$id;
            }
            $code = trim(strval($request->params->code));
            $startdate = strtotime(trim(strval($request->params->startdate)));
            $enddate = strtotime(trim(strval($request->params->enddate)));

            if(empty($code) or $startdate < 0 or $enddate < 0){
                throw new \exception("Error");
            }

            $module = (object)[
                'id' => $id,
                'code' => $code,
                'startdate' => $startdate,
                'enddate' => $enddate,
            ];

            $result = $this->context->modules->attach_to($module, $courseid);
            if(!$result){
                $message = "No se pudo guardar el modulo!";
            }else{
                $message = "Modulo guardado con exito!";
            }
        }catch (\exception $e){
            return $this->redirect('course', $request->params,'Error. Datos ingresados invalidos!');
        }
        return $this->redirect('course', $request->params,$message);
    }

    public function delete(request $request): response {
        $moduleid = intval($request->params->moduleid);
        $module = $this->context->modules->by_id($moduleid);
        $this->content->module = (object)[
            'id' => $module->id,
            'code' => $module->code,
            'startdate' => Dates::format_date_time($module->startdate),
            'enddate' => Dates::format_date_time($module->enddate),
        ];
        return $this->response('course/module/delete');
    }
    public function confirmDelete(request $request): response {
        $message = 'No se hizo ninguna modificacion!';
        try{
            $moduleid = intval($request->params->moduleid);
            $sections = $this->context->sections->related_to($moduleid);
            $nsec = count($sections);
            foreach ($sections as $section){
                $this->context->sections->dettach($section->id);
            }
            $result = $this->context->modules->dettach($moduleid);
            if($result){
                $message = "Modulo eliminado con exito! {$nsec} Secciones liberadas!";
            }
        }catch (\exception $e){
            return $this->redirect('course', $request->params, $message);
        }
        return $this->redirect('course', $request->params, $message);
    }

}