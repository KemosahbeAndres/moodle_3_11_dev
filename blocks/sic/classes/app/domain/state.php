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

namespace block_sic\app\domain;

use block_sic\app\utils\Arrays;

class state {
    private static $cursando = 1;
    private static $aprobado = 2;
    private static $reprobado = 3;

    private $id;
    private $codigo;
    private $estado;

    public function __construct($object) {
        $data = Arrays::convert($object);
        $this->id = intval($data['id']);
        $this->codigo = intval(trim($data['codigo']));
        $this->estado = strval($data['estado']);
    }

    public function get_id(): int {
        return $this->id;
    }

    public function get_code(): int {
        return $this->codigo;
    }

    public function get_state(): string {
        return $this->estado;
    }

    public function studying(): bool {
        return $this->codigo == self::$cursando;
    }
    public function approved(): bool {
        return $this->codigo == self::$aprobado;
    }
    public function reproved(): bool {
        return $this->codigo == self::$reprobado;
    }



}
