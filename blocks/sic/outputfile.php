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

/*
require_once(__DIR__ . '/../../config.php');

require_once("{$CFG->libdir}/formslib.php");

function desencriptar($valor) {

    global $CFG;

    $clave  = $CFG->dbpass;

    $method = 'aes-256-cbc';

    $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");

    $encrypted_data = base64_decode($valor);

    return openssl_decrypt($valor, $method, $clave, 0, $iv);

}

function get_config($crypted){

    $config = new stdClass();

    $config = unserialize(base64_decode($crypted));

    return $config;

}

function get_course_config($courseid){

    global $CFG, $DB;

    require_once("{$CFG->dirroot}/config.php");

    $config = new stdClass();

    $registros = $DB->get_records('block_instances', ['blockname' => 'sic'], '', '*');

    foreach($registros as $registro){
        if($registro->sic_courseid){
            $config = get_config($registro->configdata);
            break;
        }
    }

    $config->rutOtec = desencriptar($CFG->sence['rutotec']);

    $config->token = desencriptar($CFG->sence['token']);

    return $config;

}*/

require_once(__DIR__ . '/../../config.php');

//$courseid = required_param("courseid", );
$data = required_param("data", PARAM_TEXT);
/*
$context = new \block_sic\app\infraestructure\persistence\repository_context();

$courseLoader = new \block_sic\app\application\consult_course_controller($context);
$jsonPrepare = new \block_sic\app\application\prepare_json_controller(
    $courseLoader,
    new \block_sic\app\application\student_finder($context)
);
*/
//$data = json_encode($jsonPrepare->execute($courseid), JSON_UNESCAPED_UNICODE);

//$data = base64_decode($_GET['students']);
//$config = get_course_config($id);
//$json = array('config' => $config);
//$data = json_encode($json, JSON_UNESCAPED_UNICODE);

$curso = json_decode($data, false);

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename='.$curso->codigoOferta.'_'.time().'.json');
header('Expires: 0'); //No caching allowed
header('Cache-Control: must-revalidate');
header('Content-Length: ' . strlen($data));

file_put_contents('php://output', $data);
