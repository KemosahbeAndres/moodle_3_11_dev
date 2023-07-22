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

namespace block_sic\app\domain\sic;

abstract class sic_response {
    private $id;
    private $id_proceso;
    protected $payloads;
    protected $errors;
    private $message;
    public function __construct(object $response) {
        try{
            if(!isset($response->id)) throw new \exception();
            $this->id = intval($response->id);
            $this->id_proceso = intval($response->id_proceso);
            $this->message = trim(strval($response->respuesta_SIC));
        }catch (\exception $e) {
            $this->id = 0;
            $this->id_proceso = 0;
            $this->message = "";
        }
        $this->payloads = array();
        $this->errors = array();
    }
    public function __toObject(): object {
        return (object)[
            'id' => $this->id,
            'id_proceso' => $this->id_proceso,
            'payloads' => array_merge($this->payloads),
            'errores' => array_merge($this->errors),
            'respuesta_SIC' => trim(strval($this->message))
        ];
    }
    public function get_process_id(): int {
        return $this->id;
    }
    public function get_message(): string {
        return $this->message;
    }
    public function get_payloads(): array {
        return $this->payloads;
    }
    public function get_errors(): array {
        return $this->errors;
    }
    public function has_errors(): bool {
        return count($this->errors) > 0;
    }
    public function toJson(): string {
        return json_encode($this, JSON_UNESCAPED_UNICODE);
    }
    public static function fromJson(string $json): default_response {
        $response = json_decode(trim($json), false);
        return new default_response((object)[
            'id_proceso' => $response->id,
            'payloads' => $response->payloads,
            'errors' => $response->errors,
            'respuesta_SIC' => $response->message
        ]);
    }
}