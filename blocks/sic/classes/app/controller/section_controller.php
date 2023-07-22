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
use block_sic\app\application\section_finder;
use block_sic\app\domain\request;
use block_sic\app\domain\response;
use block_sic\app\domain\section;
use block_sic\app\infraestructure\persistence\repository_context;

class section_controller extends controller {

    private $sectionFinder;
    private $courseLoader;

    public function __construct(repository_context $context) {
        parent::__construct($context);
        $this->content->coursepage = true;
        $this->sectionFinder = new section_finder($context);
        $this->courseLoader = new consult_course_controller($context);
    }
    public function details(request $request): response {
        try {
            $sid = intval($request->params->id);
            $this->content->section = $this->sectionFinder->execute($sid)->__toObject();
        } catch (\Exception $e) {}
        return $this->response('course/sections/details');
    }

    public function edit(request $request): response {
        $courseid = intval($request->params->courseid);
        $sectionid = intval($request->params->id);
        try {
            $section = $this->sectionFinder->execute($sectionid);
            $modules = $this->context->modules->related_to($courseid);
            $this->content->section = $section->__toObject();
            $this->content->module_list = $modules;
        } catch (\exception $e) {}
        return $this->response('course/sections/edit');
    }

    public function reassign(request $request): response {
        $message = "No se hizo ningun cambio!";
        try{
            $courseid = intval($request->params->courseid);
            $moduleid = intval($request->params->moduleid);
            $sectionid = intval($request->params->sectionid);

            if($courseid <= 0 or $sectionid <= 0) throw new \exception($message);

            if($moduleid > 0){
                $this->context->sections->attach_to($sectionid, $moduleid);
                $message = "Seccion asignada con exito!";
            }else{
                $this->context->sections->dettach($sectionid);
                $message = "Seccion liberada con exito!";
            }
        }catch (\exception $e){
            return $this->redirect('course', $request->params, $message);
        }
        return $this->redirect('course', $request->params, $message);
    }

    public function massive(request $request): response {
            $courseid = intval($request->params->courseid);
            $moduleid = intval($request->params->moduleid);

            $course = $this->courseLoader->execute($courseid);

            $module = $course->get_module($moduleid);

            $sections = array();
            /** @var section $section */
            foreach($course->get_excluded_sections() as $section){
                $sections[] = $section->__toObject();
            }

            $this->content->sections = $sections;
            $this->content->module = $module->__toObject();

        return $this->response('course/sections/massive');
    }

    public function massive_reassign(request $request): response {
        $message = "No se hizo ningun cambio!";
        try{
            $courseid = intval($request->params->courseid);
            $moduleid = intval($request->params->moduleid);

            if($moduleid < 0) throw new \exception($message);

            $course = $this->courseLoader->execute($courseid);
            $sections = $course->get_excluded_sections();

            $asignadas = "";

            /** @var section $section */
            foreach ($sections as $section){
                if(isset( $request->params->{"section_".$section->get_id()} )){
                    $this->context->sections->attach_to($section->get_id(), $moduleid);
                    $asignadas .= "{$section->get_id()}, ";
                }
            }
            if(!empty($asignadas)){
                $message = "Secciones asignadas => ".$asignadas;
            }
        }catch(\exception $e){}
        return $this->redirect('course', $request->params, $message);
    }
}
