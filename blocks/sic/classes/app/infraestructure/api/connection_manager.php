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

namespace block_sic\app\infraestructure\api;

use block_sic\app\domain\sic\empty_response;
use block_sic\app\domain\sic\error_response;
use block_sic\app\domain\sic\history_response;
use block_sic\app\domain\sic\sic_response;
use block_sic\app\domain\sic\success_response;

class connection_manager {
    /**
     * @var string
     */
    private static $HISTORY_ENDPOINT = "https://auladigital.sence.cl/gestor/API/avance-sic/historialEnvios";
    /**
     * @var string
     */
    private static $POST_ENDPOINT = "https://auladigital.sence.cl/gestor/API/avance-sic/enviarAvance";

    private static $mensajes = array();
    private static function get_format_query(string $url, $params = null): string {
        if(is_null($params) or empty($params)) return $url;
        $url = trim($url) . "?";
        $cantidad = 0;
        foreach ($params as $key => $param) {
            if($cantidad <= 0) {
                $url .= $key . '=' . trim(strval($param));
            }else{
                $url .= '&' . $key . '=' . trim(strval($param));
            }
            $cantidad += 1;
        }
        return $url;
    }
    private static function prepare_post(string $url, string $data){
        self::clear_messages();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Accept:application/json'
        ));
        return $curl;
    }
    private static function prepare_get(string $url, array $params){
        self::clear_messages();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        $cleanurl = self::get_format_query($url, $params);
        curl_setopt($curl, CURLOPT_URL, $cleanurl);
        return $curl;
    }
    public static function clear_messages() {
        self::$mensajes = array();
    }
    public static function get_messages(): array {
        return self::$mensajes;
    }
    private static function get_response($payload): sic_response {
        if(is_bool($payload)){
            $message = "Error. No hay respuesta por parte del servidor!";
            self::$mensajes[] = $message;
        }else if(is_string($payload) && !empty($payload)){
            $response = json_decode(trim($payload), false);
            if(isset($response->error) and is_array($response->error)){
                foreach ($response->error as $error){
                    self::$mensajes[] = strval($error->mensaje);
                }
            }
            if(isset($response->envio)){
                return new success_response($response);
            } else if(isset($response->datosError)){
                return new error_response($response);
            } else if(is_array($response)){
                return new history_response($response);
            }
        }
        return new empty_response('', self::get_messages());
    }

    public static function send(object $payload): sic_response {
        $curl = self::prepare_post(
            self::$POST_ENDPOINT,
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );
        $data = curl_exec($curl);
        $response = self::get_response($data);
        curl_close($curl);
        return $response;
    }

    public static function history(string $rut, string $token, int $processid = 0, string $date = '', int $systemid = 1350): sic_response {
        $params = array(
            'rutOtec' => $rut,
            'idSistema' => $systemid,
            'token' => $token,
        );
        if(!empty($date)) $params['fechaDesde'] = $date;
        if($processid > 0) $params['id_proceso'] = $processid;
        $curl = self::prepare_get(self::$HISTORY_ENDPOINT, $params);
        $response = self::get_response(curl_exec($curl));
        return $response;
    }

    public static function alive(string $rut, string $token, int $systemid = 1350): bool {
        $params = array(
            'rutOtec' => $rut,
            'idSistema' => $systemid,
            'token' => $token
        );
        $curl = self::prepare_get(self::$HISTORY_ENDPOINT, $params);
        curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        return is_numeric($code) and $code == 200;
    }

}