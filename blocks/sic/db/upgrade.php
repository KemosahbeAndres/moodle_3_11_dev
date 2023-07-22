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

defined("MOODLE_INTERNAL") || die;

/**
 * @throws ddl_exception
 * @throws upgrade_exception
 * @throws downgrade_exception
 * @throws coding_exception
 * @throws dml_exception
 */
function xmldb_block_sic_upgrade($oldversion) {

    global $DB;

    $dbman = $DB->get_manager();

    $version = 2023071102;

    if ($oldversion < $version) {

        // Tabla Modulos
        $table_modulos = new xmldb_table('sic_modulos');

        // Adding fields to table sic_modulos.
        $table_modulos->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_modulos->add_field('course_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_modulos->add_field('codigo', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table_modulos->add_field('fecha_inicio', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table_modulos->add_field('fecha_fin', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $sync_field = new xmldb_field('act_sincronas', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        //$table->addField($sync_field);
        $async_field = new xmldb_field('act_asincronas', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        //$table->addField($async_field);
        $table_modulos->add_field('created', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table sic_modulos.
        $table_modulos->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table_modulos->add_key('fk_module_course', XMLDB_KEY_FOREIGN, ['course_id'], 'course', ['id']);

        // Conditionally launch create table for sic_modulos.
        if (!$dbman->table_exists($table_modulos)) {
            $dbman->create_table($table_modulos);
        }
        if($dbman->field_exists($table_modulos, 'act_sincronas') and $dbman->field_exists($table_modulos, 'act_asincronas')){
            $dbman->drop_field($table_modulos, $sync_field);
            $dbman->drop_field($table_modulos, $async_field);
        }

        // Tabla Asignaciones
        $table_asignaciones = new xmldb_table('sic_asignaciones');

        // Adding fields to table sic_asignaciones.
        $table_asignaciones->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_asignaciones->add_field('id_modulo', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_asignaciones->add_field('section_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_asignaciones->add_field('created', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table sic_asignaciones.
        $table_asignaciones->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table_asignaciones->add_key('fk_assigment_module', XMLDB_KEY_FOREIGN, ['id_modulo'], 'sic_modulos', ['id']);
        $table_asignaciones->add_key('fk_assigment_section', XMLDB_KEY_FOREIGN, ['section_id'], 'course_sections', ['id']);

        // Conditionally launch create table for sic_asignaciones.
        if (!$dbman->table_exists($table_asignaciones)) {
            $dbman->create_table($table_asignaciones);
        }

        // Tabla Clases
        $table_clases = new xmldb_table('sic_clases');

        // Adding fields to table sic_clases.
        $table_clases->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_clases->add_field('section_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_clases->add_field('activity_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_clases->add_field('fecha', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_clases->add_field('duracion', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '7200');

        // Adding keys to table sic_clases.
        $table_clases->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table_clases->add_key('fk_lesson_section', XMLDB_KEY_FOREIGN_UNIQUE, ['section_id'], 'course_sections', ['id']);
        $table_clases->add_key('fk_lesson_activity', XMLDB_KEY_FOREIGN_UNIQUE, ['activity_id'], 'course_modules', ['id']);

        // Conditionally launch create table for sic_clases.
        if (!$dbman->table_exists($table_clases)) {
            $dbman->create_table($table_clases);
        }

        // Tabla Asistencia
        $table_asistencia = new xmldb_table('sic_asistencia');

        // Adding fields to table sic_asistencia.
        $table_asistencia->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_asistencia->add_field('id_clase', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_asistencia->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_asistencia->add_field('asistio', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table sic_asistencia.
        $table_asistencia->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table_asistencia->add_key('fk_asistencia_clase', XMLDB_KEY_FOREIGN, ['id_clase'], 'sic_clases', ['id']);
        $table_asistencia->add_key('fk_asistencia_usuario', XMLDB_KEY_FOREIGN, ['user_id'], 'user', ['id']);

        // Conditionally launch create table for sic_asistencia.
        if (!$dbman->table_exists($table_asistencia)) {
            $dbman->create_table($table_asistencia);
        }

        // Tabla Estados
        $table_estados = new xmldb_table('sic_estados');

        // Adding fields to table sic_estados.
        $table_estados->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_estados->add_field('codigo', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null);
        $table_estados->add_field('estado', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);

        // Adding keys to table sic_estados.
        $table_estados->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for sic_estados.
        if (!$dbman->table_exists($table_estados)) {
            $dbman->create_table($table_estados);

            $records = array(
                (object)[
                    'codigo' => 1,
                    'estado' => 'cursando'
                ],
                (object)[
                    'codigo' => 2,
                    'estado' => 'aprobado'
                ],
                (object)[
                    'codigo' => 3,
                    'estado' => 'reprobado'
                ],
            );

            $DB->insert_records('sic_estados', $records);
        }

        // Tabla Matriculas
        $table_matriculas = new xmldb_table('sic_matriculas');

        // Adding fields to table sic_matriculas.
        $table_matriculas->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_matriculas->add_field('course_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_matriculas->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_matriculas->add_field('id_estado', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_matriculas->add_field('vigente', XMLDB_TYPE_INTEGER, '1', null, null, null, '1');
        $table_matriculas->add_field('created', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table sic_matriculas.
        $table_matriculas->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table_matriculas->add_key('fk_matricula_curso', XMLDB_KEY_FOREIGN, ['course_id'], 'course', ['id']);
        $table_matriculas->add_key('fk_matricula_usuario', XMLDB_KEY_FOREIGN, ['user_id'], 'user', ['id']);
        $table_matriculas->add_key('fk_matricula_estado', XMLDB_KEY_FOREIGN, ['id_estado'], 'sic_estados', ['id']);

        // Conditionally launch create table for sic_matriculas.
        if (!$dbman->table_exists($table_matriculas)) {
            $dbman->create_table($table_matriculas);
        }

        // Tabla Respuetas
        $table_respuestas = new xmldb_table('sic_respuestas');

        // Adding fields to table sic_respuestas.
        $table_respuestas->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_respuestas->add_field('course_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_respuestas->add_field('id_proceso', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table_respuestas->add_field('contenido', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table_respuestas->add_field('errores', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table_respuestas->add_field('respuesta', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table_respuestas->add_field('created', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table sic_respuestas.
        $table_respuestas->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table_respuestas->add_key('fk_response_course', XMLDB_KEY_FOREIGN, ['course_id'], 'course', ['id']);

        // Conditionally launch create table for sic_respuestas.
        if (!$dbman->table_exists($table_respuestas)) {
            $dbman->create_table($table_respuestas);
        }

        // Sic savepoint reached.
        upgrade_block_savepoint(true, $version, 'sic');

    }

}
