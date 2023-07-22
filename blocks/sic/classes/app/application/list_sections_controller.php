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

namespace block_sic\app\application;

use block_sic\app\application\contracts\iactivities_repository;
use block_sic\app\application\contracts\ilessons_repository;
use block_sic\app\domain\activity;
use block_sic\app\domain\lesson;
use block_sic\app\domain\section;
use block_sic\app\infraestructure\persistence\repository_context;

class list_sections_controller {
    private $sectionFinder;

    public function __construct(repository_context $context) {
        $this->sectionFinder = new section_finder($context);
    }

    public function execute(array $sections): array {
        $output = array();
        foreach ($sections as $section) {
            $output[] = $this->sectionFinder->execute($section->id);
        }
        return $output;
    }

}