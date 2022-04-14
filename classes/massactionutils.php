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
 * massactionutils class: Utility class providing methods for generating data used by the massaction block.
 *
 * @package    block_massaction
 * @copyright  2021 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_massaction;

use dml_exception;
use moodle_exception;
use stdClass;

/**
 * Mass action utility functions class.
 *
 * @copyright  2021 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class massactionutils {

    /**
     * Method to extract the modules from the request JSON which is sent by the block_massaction JS module to the backend.
     *
     * @param string $massactionrequest the json string containing the module ids to be handled as well as the action
     *  which should be applied to them
     * @return stdClass the data structure converted from the json
     * @throws dml_exception if the database lookup fails
     * @throws moodle_exception if the json is of a wrong format
     */
    public static function extract_modules_from_json(string $massactionrequest): stdClass {
        global $DB;
        // Parse the submitted data.
        $data = json_decode($massactionrequest);

        // Verify that the submitted module IDs do belong to the course.
        if (!property_exists($data, 'moduleIds') || !is_array($data->moduleIds) || count($data->moduleIds) == 0) {
            throw new moodle_exception('jsonerror', 'block_massaction');
        }

        $modulerecords = $DB->get_records_select('course_modules',
            'ID IN (' . implode(',', array_fill(0, count($data->moduleIds), '?')) . ')',
            $data->moduleIds);

        foreach ($data->moduleIds as $modid) {
            if (!isset($modulerecords[$modid])) {
                throw new moodle_exception('invalidmoduleid', 'block_massaction', $modid);
            }
        }

        if (!isset($data->action)) {
            throw new moodle_exception('noaction', 'block_massaction');
        }
        $data->modulerecords = $modulerecords;
        return $data;
    }
}
