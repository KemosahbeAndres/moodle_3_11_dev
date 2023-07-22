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



defined('MOODLE_INTERNAL') || die();



// Default session time limit in seconds.

define('BLOCK_SIC_DEFAULT_SESSION_LIMIT', 60 * 60);

// Ignore sessions with a duration less than defined value in seconds.

define('BLOCK_SIC_IGNORE_SESSION_TIME', 59);

// Default regeneration time in seconds.

define('BLOCK_SIC_DEFAULT_REGEN_TIME', 60 * 15);

class block_sic_manager{



    protected $course;

    protected $mintime;

    protected $maxtime;

    protected $limit;

    protected $course_id;


    public function __construct($course, $mintime, $maxtime, $limit = BLOCK_SIC_DEFAULT_SESSION_LIMIT) {

        $this->course = $course;

        $this->course_id = intval(isset($course->id) ? $course->id : $course);

        $this->mintime = $mintime;

        $this->maxtime = $maxtime;

        $this->limit = $limit;

    }



    public function get_user_dedication($user, $simple = false) {

        global $DB;

        $courseid = $this->course_id;

        $userid = isset($user->id) ? $user->id : $user;

        $where = 'courseid = :courseid AND userid = :userid AND timecreated >= :mintime AND timecreated <= :maxtime AND
        (action = :action_v OR action = :action_u) AND (target = :target_c OR target = :target_cm OR target = :target_cmc OR target = :target_cp)';

        $params = array(

            'courseid' => $courseid,

            'userid' => $userid,

            'mintime' => $this->mintime,

            'maxtime' => $this->maxtime,

            'action_v' => 'viewed',

            'action_u' => 'updated',

            'target_c' => 'course',

            'target_cm' => 'course_module',

            'target_cmc' => 'course_module_completion',

            'target_cp' => 'content_page'

        );

        $logs = block_sic_utils::get_events_select($where, $params);

        // echo '<br>First check: ';

        // var_dump($logs);



        // if(count($logs) <= 0){

        //     $logs = block_sic_utils::get_events_from_table($where, $params);

        // }

        // echo '<br>Second check: ';

        // var_dump($logs);



        if ($simple) {

            // Return total dedication time in seconds.

            $total = 0;



            $connection = array();

            if ($logs) {

                $previouslog = array_shift($logs); //12924

                $sectionid = 0;

                $sections = array();

                $allsections = block_sic_utils::get_all_sections($courseid);
                //echo "COURSE ID: ". $courseid;

                //var_dump($allsections);

                foreach ($allsections as $sec) {

                    $sections[$sec->id] = 0;

                    $connection[$sec->id] = new stdClass();

                    $connection[$sec->id]->first = 0;

                    $connection[$sec->id]->last = 0;

                }



                if($previouslog->other != null && isset($previouslog->other['coursesectionnumber'])){

                    $sectionid = block_sic_utils::get_actual_section_id(

                        $courseid,

                        $previouslog->other->coursesectionnumber

                    );

                }

                $lastsection = $sectionid;

                $previouslogtime = $previouslog->time;

                $sessionstart = $previouslogtime;





                $events = array();



                foreach ($logs as $log) {

                    if (!is_null($sectionid)) {

                        foreach ($connection as $key => $value) {

                            if ($key == $lastsection) {

                                if ($connection[$key]->first == '') {

                                    $connection[$key]->first = $log->time;

                                }

                                if ($lastsection == $sectionid) {

                                    $connection[$key]->last = $log->time;

                                }

                            }

                        }

                    }



                    if (($log->time - $previouslogtime) > $this->limit) {

                        $dedication = $previouslogtime - $sessionstart;

                        $total += $dedication;

                        // Adding time to section selected.

                        if ($sectionid != null && $sectionid != 0) {

                            $sections[$sectionid] += $dedication;

                            $lastsection = $sectionid;

                        }

                        $sessionstart = $log->time;

                    } else {

                        $total += $log->time - $previouslogtime;

                        $sections[$sectionid] += $log->time - $previouslogtime;

                    }

                    // Echo '| ID: '.$sectionid.' suma: '.$sections[$sectionid].' |';.

                    $previouslogtime = $log->time;

                    $events[] = $log->event;

                    if ($log->other != null && isset($log->other['coursesectionnumber'])) {

                        // echo 'Section number: '.$log->other['coursesectionnumber'].' :::|';

                        $sectionid = block_sic_utils::get_actual_section_id(

                            $courseid,

                            $log->other['coursesectionnumber']

                        );

                        // echo 'ID: '.$sectionid.' |';

                    }

                }

                $dedication = $previouslogtime - $sessionstart;

                $sections[$lastsection] += $dedication;

                $total += $dedication;

            }

            //var_dump($allsections);

            $cleansections = array();

            foreach ($allsections as $sec) {

                if ($sec->section != 0) {

                    $cleansections[$sec->id] = $sections[$sec->id];

                }

            }

            return array($total, $cleansections, $connection);

        } else {

            // Return user sessions with details.

            $rows = array();

            if ($logs) {

                $previouslog = array_shift($logs);

                $previouslogtime = $previouslog->time;

                $sessionstart = $previouslogtime;

                $ips = array($previouslog->ip => true);

                foreach ($logs as $log) {

                    if (($log->time - $previouslogtime) > $this->limit) {

                        $dedication = $previouslogtime - $sessionstart;

                        // Ignore sessions with a really short duration.

                        if ($dedication > BLOCK_SIC_IGNORE_SESSION_TIME) {

                            $rows[] = (object) array('start_date' => $sessionstart, 'dedicationtime' => $dedication, 'ips' => array_keys($ips));

                            $ips = array();

                        }

                        $sessionstart = $log->time;

                    }

                    $previouslogtime = $log->time;

                    $ips[$log->ip] = true;

                }



                $dedication = $previouslogtime - $sessionstart;



                // Ignore sessions with a really short duration.

                if ($dedication > BLOCK_SIC_IGNORE_SESSION_TIME) {

                    $rows[] = (object) array('start_date' => $sessionstart, 'dedicationtime' => $dedication, 'ips' => array_keys($ips));

                }

            }

            return $rows;

        }

    }

    public function get_user_completion($user) {

        return block_sic_utils::get_completion_course(intval($this->course_id), isset($user->id) ? $user->id : $user);

    }

    public function get_course_completion(){

        return block_sic_utils::get_completion_course(intval($this->course_id));

    }

    public function get_sections($courseid){
        return block_sic_utils::get_all_sections($courseid);
    }

    public function get_user_progress($user){

        $completion = $this->get_user_completion($user);

        $course = array();

        //echo " |COMPLETION| ";

        foreach($completion as $student){
            if(intval($student->userid) == intval(isset($user->id) ? $user->id : $user)){
                //echo " |ENCONTRADO| ";
                $course = $student->course;
                break;
            }
        }

        //var_dump($course);
        //echo " |COMPLETION| ";
        return intval(block_sic_utils::get_formated_completion($course));


        //return block_sic_utils::get_formated_completion();

        /*
        $completion = $this->get_user_completion($user);
        $completadas = 0;

        $total = 0;

        $completion = $progress[0];
        $sections = $completion->course;
        foreach($sections as $section){
            echo "<br> <h2>Seccion:</h2>";
            foreach($section->activitys as $activity){
                echo "<br> <h3>Actividad:</h3>";
                var_dump($activity);
                $total++;
                if($activity->completed){
                    $completadas++;
                }
            }
        }

        $porcentaje = intval(($completadas/$total)*100);
        */
    }

    public function get_user_connection_time($user){
        return intval($this->get_user_dedication($user, true)[0]);
    }

    private function get_startdate($courseid){
        global $DB;
        $course = $DB->get_record("course", [ 'id' => $courseid ], "*");
        if(!is_null($course)){
            return intval($course->startdate);
        }
        return null;
    }

}


/**

 * Utils functions used by block sic.

 */

class block_sic_utils {



    public static $logstores = array('logstore_standard', 'logstore_legacy');

    private static $sections = array();

    private static $last_course_id = -1;

    private static $REQUIRED = 2;

    private static $OPTIONAL = 1;

    private static $USELESS = 0;



    public static function zoom_dedication($completion, $all = true, $sectionid = null){

        global $DB;

        $total = 0;

        $sections = array();

        // Logic

        foreach($completion as $section){

            $dedication = 0;

            foreach($section->activitys as $activity){

                if($activity->type == 'zoom'){

                    $dedication += $activity['duration'];

                }

            }

            $total += $dedication;

            $sections[$section->id] = $dedication;

            if(!is_null($sectionid) && $section->id == $sectionid){

                return $dedication;

            }

        }

        // End Logic

        if($all){

            return $total;

        }else{

            return $sections;

        }

    }



    public static function get_fdate($date){
        //return date('Y-m-d H:i:s', $date);
        return date('Y-m-d', $date);
    }



    public static function get_formated_completion($completion, $sectionid = null){

        $response = new stdClass();
        $response->total = 0;
        $response->completed = 0;

        $count = 0;

        $completed = 0;
        //Parametro $completion es un array() con las secciones.

        foreach($completion as $section){

            if(!is_null($sectionid) && $section->id == $sectionid){

                $completed = 0;

                $count = 0;

            }

            $count += count($section->activitys);

            foreach($section->activitys as $activity){

                if($activity->completed){

                    $completed += 1;

                }

            }

            if(!is_null($sectionid) && $section->id == $sectionid){

                $response->total = $count;

                $response->completed = $completed;

                break;

            }

        }

        $response->total = $count;

        $response->completed = $completed;

        return intval( ($completed / $count) *100 );

        //return $response;

    }

    private static function module_exists($modules, $code){

        $found = false;

        if(count($modules) <= 0){ return $found; }

        foreach($modules as $module){

            if($module->code == $code){

                $found = true;

                break;

            }

        }

        return $found;

    }

    public static function get_modules_from_course($courseid){

        global $DB;

        $sections = (object) $DB->get_records('course_sections', ['course' => $courseid], 'section ASC', '*');

        $count = 0;

        $secciones = 0;

        $modules = array();

        foreach($sections as $section){

            if($section->section == 0){ continue; }

            $name = explode('/', $section->name);

            $module = new stdClass();

            $module->sequence = '';

            $module->code = '';

            if(count($name) > 1 && $name[1] != ''){

                $module->code = trim($name[1]);

                $module->sequence .= $section->id.',';

                if(!self::module_exists($modules, $module->code)){

                    $modules[] = $module;

                    $count += 1;

                    $secciones += 1;

                }else{

                    foreach($modules as $index=>$mod){

                        if($mod->code == $module->code){

                            $modules[$index]->sequence .= $module->sequence;

                            $secciones += 1;

                            break;

                        }

                    }

                }

            }else{

                return array('error' => "El nombre de las section '{$section->section}' tiene un formato incorrecto.");

            }

        }

        // echo '|||| N Modulos: '.$count.' | N Secciones: '.$secciones.' ||||';

        return $modules;

    }

    public static function desencriptar($valor) {

        global $CFG;

        $clave  = $CFG->dbpass;

        $method = 'aes-256-cbc';

        $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");

        $encrypted_data = base64_decode($valor);

        return openssl_decrypt($valor, $method, $clave, 0, $iv);

    }

    public static function get_user_rut($userid, $option = null){

        global $DB;

        $info = (object) $DB->get_record('user', ['id' => $userid], 'username, idnumber');

        $rut = explode('-', $info->username);

        if(is_null($option)){

            $rut = explode('-', $info->idnumber);

        }

        if(is_null($rut) && count($rut) <= 1 && count($rut) > 2){

            // echo 'Trying again...';

            if(!is_null($option)){ return (object) ['rut' => $rut[0]]; }

            return self::get_user_rut($userid, true);

        }

        return (object) [ 'rut' => $rut[0], 'dv' => $rut[1] ];

    }

    public static function get_actual_section_id($courseid, $sectionnumber){

        global $DB;

        $section = $sectionnumber;

        // echo '| Section N:'.$section. ' |';

        if(block_sic_utils::$last_course_id != $courseid){

            block_sic_utils::$sections = $DB->get_records('course_sections', ['course' => $courseid], '', '*');

        }

        block_sic_utils::$last_course_id = $courseid;

        foreach(block_sic_utils::$sections as $fields){

            if($fields->section == $section){

                // echo 'get_actual_section_id: '.$fields->id;

                return $fields->id;

            }

        }

        //second method to find section id

        return null;

    }

    public static function get_config($crypted){

        $config = new stdClass();

        $config = unserialize(base64_decode($crypted));

        return $config;

    }

    public static function get_all_sections($courseid){

        global $DB;

        return $DB->get_records('course_sections', ['course' => $courseid], 'id ASC', '*');

    }

    public static function save_course_data(){}

    private static function activity_exists($array, $item){

        $found = false;

        foreach($array as $object){

            if($object->id == $item->id && $object->name == $item->name){

                $found = true;

            }

        }

        return $found;

    }

    public static function get_completion_course($courseid, $userid = null){

        global $DB;

        // echo 'Course ID: ';

        // echo($courseid);

        $users = array();

        $secciones = (object) $DB->get_records('course_sections', ['course' => $courseid], '', '*');

        // echo count($secciones). '<br>';

        // var_dump($secciones);

        $modules = (object) $DB->get_records('course_modules', ['course' => $courseid], 'section ASC', '*');

        // echo count($modules). '<br>';

        // var_dump($modules);

        $moduleTypes = (object) $DB->get_records('modules', null, 'id ASC', '*');

        // echo 'types: '. count($moduleTypes). '<br>';

        // var_dump($moduleTypes);

        $completion = array();

        if(!is_null($userid)){

            $user = array(

                'userid' => $userid

            );

            $users[] = (object) $user;

        }else{

            $sql = "SELECT id, username, idnumber, firstname, lastname FROM {user} WHERE id IN
                (SELECT userid FROM {role_assignments} WHERE roleid = 5 and contextid IN
                    (SELECT id FROM {context} WHERE contextlevel = 50 and instanceid = :courseid))";

            try{
                $usr = $DB->get_records_sql($sql, ['courseid' => $courseid]);
            }catch(\moodle_exception $e){
                $usr = array();
            }
            //$usr = (object) $DB->get_records('course_completions', ['course' => $courseid],'', '*');

            foreach($usr as $u){

                $user = new stdClass();

                $user->userid = intval($u->id);

                $users[] = $user;

            }

        }

        //echo '::::::::::::::::::::::: USERS ARRAY ::::::::::::::::::::::';

        //var_dump($users);

        //echo '::::::::::::::::::::: END USERS ARRAY ::::::::::::::::::::';

        if(empty($users)){ return null; }

        foreach($users as $user){

            $user->course = array();

            $completions = $DB->get_records('course_modules_completion', ['userid' => $user->userid], 'id ASC', '*');

            // echo 'Registro de avance (ID '.$user->userid.'): ';

            // var_dump($completions);

            // echo '<br>';

            foreach($secciones as $section){

                if($section->section == 0){ continue; }

                // $section = new stdClass();

                // $section->section = $section;

                $section->activitys = array();



                // $completion[$section->id] = array();

                // echo 'Seccion: '.$section->id.' '.$section->name.'<br>';

                foreach(explode(',', $section->sequence) as $id){ // Actividades en section

                    foreach($modules as $module){

                        if($module->id == $id){ // Actividad encontrada

                            $table = "";

                            foreach($moduleTypes as $type){

                                if($module->module == $type->id){

                                    $table = $type->name;

                                    break;

                                }

                            }

                            if($module->completion == self::$OPTIONAL || $module->completion == self::$USELESS){ break; }

                            // echo 'table '.$table;

                            if($table == "label" || $table == 'attendance'){ break; }

                            // echo '<br>';

                            $activity = new stdClass();

                            $activity->completed = false;

                            $activity->type = $table;

                            $activity->duration = 0 ;



                            if($activity->type != 'zoom'){

                                $act = (object) $DB->get_record($table, ['course' => $courseid, 'id' => $module->instance], 'id, name', MUST_EXIST);

                                // echo ' |||||||||||| VARIABLE $ACT |||||||||||| ';

                                // var_dump($act);

                                // echo ' |||||||||||| VARIABLE $ACT |||||||||||| ';

                                $activity->id = $act->id;

                                $activity->name = $act->name;

                                // echo ' | '.$activity->name.' |';

                                // echo 'found: ';

                                // var_dump($activity);

                                // echo '<br>';

                                // var_dump($activity);

                                foreach($completions as $completed){

                                    if($module->id == $completed->coursemoduleid){

                                        $activity->completed = $completed->completionstate == "1" ? true : false;

                                    }

                                }

                            }else{

                                $meeting = (object) $DB->get_record($table, ['course' => $courseid, 'id' => $module->instance], 'id, meeting_id, name', MUST_EXIST);

                                $details = (object) $DB->get_record($table.'_meeting_details', ['meeting_id' => $meeting->meeting_id, 'zoomid' => $meeting->id], 'id');

                                $participant = (object) $DB->get_record($table.'_meeting_participants', ['detailsid' => $details->id, 'userid' => $user->id], 'duration');

                                $activity->id = $meeting->id;

                                $activity->name = $meeting->name;

                                if(!isset($participant->duration) && is_null($participant->duration)){

                                    $activity->duration = 0;

                                }else{

                                    $activity->duration = $participant->duration;

                                }

                                if($activity->duration > 0){

                                    $activity->completed = true;

                                }

                            } // Zoom Activity Check

                            if(!self::activity_exists($section->activitys, $activity)){

                                $section->activitys[] = $activity;

                                // echo 'Actividad Agregada Al Final | ';

                                // echo 'Nombre: '.$activity->name.' ID: '.$activity->id.' | ';

                                break;

                            }

                        } // Activity Found Condition

                    } // Modules Loop

                } // Section Sequence Loop

                $user->course[] = $section;

            } // Sections Loop

            $completion[] = $user;

        } // Users Loop

        return $completion;

    }



    /**

     * Return formatted events from logstores.

     * @param string $selectwhere

     * @param array $params

     * @return array

     */

    public static function get_events_select($selectwhere, array $params) {

        $return = array();



        static $allreaders = null;



        if (is_null($allreaders)) {

            $allreaders = get_log_manager()->get_readers();

        }

        // var_dump($allreaders);



        $processedreaders = 0;



        foreach (self::$logstores as $name) {

            if (isset($allreaders[$name])) {

                $reader = $allreaders[$name];

                $events = $reader->get_events_select($selectwhere, $params, 'timecreated ASC', 0, 0);

                // var_dump($reader);

                foreach ($events as $event) {

                    // Note: see \core\event\base to view base class of event.

                    $obj = new stdClass();

                    $obj->time = $event->timecreated;

                    $obj->ip = $event->get_logextra()['ip'];

                    $obj->other = $event->other;

                    $obj->action = $event->action;

                    $obj->target = $event->target;

                    $obj->table = $event->objecttable;

                    $obj->activity = $event->objectid;

                    $obj->course = $event->courseid;

                    $obj->event = $event;

                    $return[] = $obj;

                }

                if (!empty($events)) {

                    $processedreaders++;

                }

            }

        }

        // Sort mixed array by time ascending again only when more of a reader has added events to return array.

        if ($processedreaders > 1) {

            usort($return, function($a, $b) {

                return $a->time > $b->time;

            });

        }

        return $return;

    }

    /**

     * Return formatted events from logstores.

     * @param string $selectwhere

     * @param array $params

     * @return array

     */

    public static function get_events_from_table($where, $params, $table = 'logstore_standard_log'){

        global $DB;

        $params['table'] = $table;

        $sql = "SELECT * FROM $table WHERE 'courseid' = {$params['courseid']} AND 'userid' = {$params['userid']} AND timecreated >= {$params['mintime']} AND timecreated <= {$params['maxtime']}";

        $events = $DB->get_records_sql($sql);

        // echo 'N Events: '.count($events);

        return $events;

    }



    /**

     * Formats time based in Moodle function format_time($totalsecs).

     * @param int $totalsecs

     * @return string

     */

    public static function format_dedication($totalsecs) {

        $totalsecs = abs($totalsecs);



        $str = new stdClass();

        $str->hour = get_string('hour');

        $str->hours = get_string('hours');

        $str->min = get_string('min');

        $str->mins = get_string('mins');

        $str->sec = get_string('sec');

        $str->secs = get_string('secs');



        $hours = floor($totalsecs / HOURSECS);

        $remainder = $totalsecs - ($hours * HOURSECS);

        $mins = floor($remainder / MINSECS);

        $secs = round($remainder - ($mins * MINSECS), 2);



        $ss = ($secs == 1) ? $str->sec : $str->secs;

        $sm = ($mins == 1) ? $str->min : $str->mins;

        $sh = ($hours == 1) ? $str->hour : $str->hours;



        $ohours = '';

        $omins = '';

        $osecs = '';



        if ($hours) {

            $ohours = $hours . ' ' . $sh;

        }

        if ($mins) {

            $omins = $mins . ' ' . $sm;

        }

        if ($secs) {

            $osecs = $secs . ' ' . $ss;

        }



        if ($hours) {

            return trim($ohours . ' ' . $omins);

        }

        if ($mins) {

            return trim($omins . ' ' . $osecs);

        }

        if ($secs) {

            return $osecs;

        }

        return get_string('none');

    }

}





// public function get_students_dedication($students) {

//     global $DB;



//     $rows = array();



//     $where = 'courseid = :courseid AND userid = :userid AND timecreated >= :mintime AND timecreated <= :maxtime';

//     $params = array(

//         'courseid' => $this->course->id,

//         'userid' => 0,

//         'mintime' => $this->mintime,

//         'maxtime' => $this->maxtime

//     );



//     $perioddays = ($this->maxtime - $this->mintime) / DAYSECS;



//     foreach ($students as $user) {

//         $daysconnected = array();

//         $params['userid'] = $user->id;

//         $logs = block_sic_utils::get_events_select($where, $params);



//         if ($logs) {

//             $previouslog = array_shift($logs);

//             $previouslogtime = $previouslog->time;

//             $sessionstart = $previouslog->time;

//             $dedication = 0;

//             $daysconnected[date('Y-m-d', $previouslog->time)] = 1;



//             foreach ($logs as $log) {

//                 if (($log->time - $previouslogtime) > $this->limit) {

//                     $dedication += $previouslogtime - $sessionstart;

//                     $sessionstart = $log->time;

//                 }

//                 $previouslogtime = $log->time;

//                 $daysconnected[date('Y-m-d', $log->time)] = 1;

//             }

//             $dedication += $previouslogtime - $sessionstart;

//         } else {

//             $dedication = 0;

//         }

//         $groups = groups_get_user_groups($this->course->id, $user->id);

//         $group = !empty($groups) && !empty($groups[0]) ? $groups[0][0] : 0;

//         $rows[] = (object) array(

//             'user' => $user,

//             'groupid' => $group,

//             'dedicationtime' => $dedication,

//             'connectionratio' => round(count($daysconnected) / $perioddays, 2),

//         );

//     }



//     return $rows;

// }



?>



