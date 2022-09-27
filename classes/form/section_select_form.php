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
 * A form to select the target section to restore multiple course modules to.
 *
 * @package    block_massaction
 * @copyright  2022, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_massaction\form;

use core\output\notification;
use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once('../../config.php');

require_login();

/**
 * A form to select the target section to restore multiple course modules to.
 *
 * @package    block_massaction
 * @copyright  2022, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_select_form extends moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = &$this->_form;
        $mform->addElement('hidden', 'request', $this->_customdata['request']);
        $mform->setType('request', PARAM_RAW);
        $mform->addElement('hidden', 'instance_id', $this->_customdata['instance_id']);
        $mform->setType('instance_id', PARAM_INT);
        $mform->addElement('hidden', 'return_url', $this->_customdata['return_url']);
        $mform->setType('return_url', PARAM_URL);

        $targetcourseid = $this->_customdata['targetcourseid'];
        $mform->addElement('hidden', 'targetcourseid', $targetcourseid);
        $mform->setType('targetcourseid', PARAM_INT);

        $mform->addElement('header', 'choosetargetsection', get_string('choosetargetsection', 'block_massaction'));

        if (empty($targetcourseid)) {
            redirect($this->_customdata['return_url'], get_string('notargetcourseidspecified', 'block_massaction'),
                null, notification::NOTIFY_ERROR);
        }

        $targetcoursemodinfo = get_fast_modinfo($targetcourseid);
        // We create an array with the sections. If a section does not have a name, we name it 'Section $sectionnumber'.
        $targetsections = array_map(function($section) {
            $name = $section->name;
            if (empty($section->name)) {
                $name = get_string('section') . ' ' . $section->section;
            }
            return $name;
        }, $targetcoursemodinfo->get_section_info_all());

        $radioarray = [];
        // We add the default value: Restore each course module to the section number it has in the source course.
        $radioarray[] = $mform->createElement('radio', 'targetsectionnum', '',
            get_string('keepsectionnum', 'block_massaction'), -1, ['class' => 'mt-2']);
        // Now add the sections of the target course.
        foreach ($targetsections as $sectionnum => $sectionname) {
            $radioarray[] = $mform->createElement('radio', 'targetsectionnum',
                '', $sectionname, $sectionnum, ['class' => 'mt-2']);
        }
        $mform->addGroup($radioarray, 'sections', get_string('choosesectiontoduplicateto', 'block_massaction'),
            '<br/>', false);
        $mform->setDefault('targetsectionnum', -1);

        $this->add_action_buttons(true, get_string('confirmsectionselect', 'block_massaction'));
    }
}
