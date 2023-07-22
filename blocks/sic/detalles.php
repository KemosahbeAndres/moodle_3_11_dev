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

require_once(__DIR__ . '/api_lib.php');

require_once("{$CFG->libdir}/formslib.php");

require_once(__DIR__ . '/block_sic_lib.php');



class myform extends moodleform{

    function definition(){

        $mform =& $this->_form;

        $mform->addElement('header','displayinfo', "Filtrar");

        $mform->addElement('date_selector', 'date_from', "Desde");

        $mform->setDefault('date_from', date('U', strtotime("-1 days")));

        $mform->addElement('date_selector', 'date_to', "Hasta");

        $mform->addElement('text', 'limit', "Cantidad de resultados");

        $mform->setType("limit", PARAM_INT);

        $mform->setDefault("limit", 5);

        $mform->addElement('submit', 'search_button', "Buscar");

    }

}



function desencriptar($valor) {

    global $CFG;

    $clave  = $CFG->dbpass;

    $method = 'aes-256-cbc';

    $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");

    $encrypted_data = base64_decode($valor);

    return openssl_decrypt($valor, $method, $clave, 0, $iv);

}



global $DB, $COURSE, $CFG, $PAGE;





// $courseid = $_GET['courseid'];

$courseid = required_param("courseid", PARAM_INT);

$blockid = optional_param("blockid", $_GET['blockid'], PARAM_INT);

$url = new moodle_url('/blocks/sic/detalles.php', array('blockid'=>$blockid, 'courseid'=>$courseid));

$PAGE->set_url($url);

$PAGE->set_context(\context_course::instance($courseid));

$PAGE->set_title('Historial API SIC');



require_login($COURSE);



echo $OUTPUT->header();

echo $OUTPUT->heading("Historial de envíos");



$form = new myform($url);



$post = $form->get_data();



if(is_null($post)){

    $filter = null;

    $limit = 5;

}else{

    $filter = (object)[

        'from' => $post->date_from,

        'to' => $post->date_to,

    ];

    $limit = $post->limit;

}

// var_dump($filter);



$rut = desencriptar($CFG->sence['rutotec']);

$token = desencriptar($CFG->sence['token']);

//$response = sic_api::get_history($rut, $token);

//$local = sic_api::get_response_history($courseid, $filter, $limit);

// echo 'HISTORY';

// var_dump($response);

$datafields = array();

// require_once(__DIR__.'/FileRepository.php');

// $repos = new FileRepository(\context_course::instance($courseid));

$errores = array();
/*
foreach($local as $id=>$row){

    $errores[] = $row->errors;

    $req = (object)[

        'date' => $row->datetime,

        'request' => json_decode(base64_decode($row->data)),

        'response' => json_decode(json_decode($row->errors)->response),

    ];

    $datafields[] = $req;

    echo '<br>ID: '.$id.' => ';

    var_dump(json_decode($row->errors));

    // $repos->save_data($req);

}

var_dump($local[433]->errors);

$arr = array();

$arr[] = (object)[

    'id_proceso'=>25,

    'id_lms'=>0,

    'codigo_externo'=>'qa-f-2',

    'fecha' => "2021-07-27 20:37:06",

    'horario' => null,

    'observaciones' => "El proceso 30 a finalizado correctamente.",

    'rut' => "19149514-4",

    "listaregistros" => [

        (object) [

            'id_registro' => '44',

            'rut_otec' => "19149514-4",

            'etc' => 'xD'

        ],

        (object) [

            'id_registro' => '45',

            'rut_otec' => "19149514-4",

            'etc' => 'xD'

        ],

        (object) [

            'id_registro' => '46',

            'rut_otec' => "19149514-4",

            'etc' => 'xD'

        ],

    ]

];

$test = json_encode($arr);

// var_dump($test);

// var_dump(json_encode($local));
*/


$students = array();

$sql = "SELECT id, username, idnumber, firstname, lastname FROM {user} WHERE id IN
    (SELECT userid FROM {role_assignments} WHERE roleid = 5 and contextid IN
        (SELECT id FROM {context} WHERE contextlevel = 50 and instanceid = :courseid))";

try{
    $data = $DB->get_records_sql($sql, ['courseid' => $courseid]);
}catch(\moodle_exception $e){
    $data = array();
}
$course = $DB->get_record("course", [ 'id' => $courseid ], "*");
$mintime = intval($course->startdate);
$manager = new block_sic_manager($courseid, $mintime, time());

//$sections = $manager->get_sections($courseid);

//var_dump($sections);

$count = 0;
foreach($data as $student){

    $time = $manager->get_user_connection_time($student);

    $progress = $manager->get_user_progress($student);

    $segundos = intval($time);

    $students[] = (object)[
        'id' => $student->id,
        'name' => $student->firstname." ".$student->lastname,
        'rut' => $student->username,
        'progress' => $progress,
        'time' => $time,
    ];
}

$context_template = (object) [

    'title' => "Titulo",

    'dir' => $CFG->wwwroot,

    'course' => $COURSE->id,

    'local' => "",

    'history' => "",

    // 'filter' => $post['date_from'],

    'students' => $students,
    'data' => "Cantidad: ". sizeof($students). " estudiantes",
    'startdate' => $mintime,

];
$json = json_encode($students, JSON_UNESCAPED_UNICODE);

//$form->display();
$url = new moodle_url('/blocks/sic/outputfile.php', array('students' => base64_encode($json)) );

echo html_writer::link($url, "Descargar archivo JSON", [ 'target' => '_blank' , 'class' => 'btn btn-primary']);

echo $OUTPUT->render_from_template('block_sic/detalles', $context_template);

echo $OUTPUT->footer();


//<div id="history_view" local='{{local}}' history='{{history}}'></div>

//<script type="module" src="{{dir}}/blocks/sic/js/lib.js"></script>
