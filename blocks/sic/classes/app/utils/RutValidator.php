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

namespace block_sic\app\utils;

final class RutValidator {
    public static function valido(string $payload): bool {
        try {
            $clean = str_replace('.', '', trim($payload));
            $splitted = explode("-", $clean);
            $rut = array_reverse(str_split($splitted[0]));
            $dv = strval($splitted[1]);
            $numeros = array(2, 3, 4, 5, 6, 7);
            $i = 0;
            $resultados = array();
            foreach ($rut as $n) {
                $res = intval($n) * $numeros[$i];
                $resultados[] = $res;
                $i = ($i < 5) ? $i + 1 : 0;
            }
            $suma = 0;
            foreach ($resultados as $numero) {
                $suma += intval($numero);
            }
            $cociente = intval($suma / 11);
            $resto = $suma - ($cociente * 11);
            $modulo = intval(11 - $resto);
        }catch (\exception $e){
            return false;
        }
        return self::get_dv($modulo) == $dv;
    }
    private static function get_dv(int $module): string {
        switch($module) {
            case 11:
                return '0';
            case 10:
                return 'K';
            default:
                return "{$module}";
        }
    }
}