<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Dynamic form for adding a new greeting message (used in modal via AJAX).
 *
 * @package     block_greetings
 * @copyright   2022 Your name <your@email>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_greetings\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Greeting message form (dynamic_form for modal/AJAX).
 *
 * @package     block_greetings
 * @copyright   2022 Your name <your@email>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message_form extends \core_form\dynamic_form {

    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('textarea', 'message', get_string('yourmessage', 'block_greetings'));
        $mform->setType('message', PARAM_TEXT);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
    }

    /**
     * Returns context where this form is used.
     *
     * @return \context
     */
    protected function get_context_for_dynamic_submission(): \context {
        return \context_system::instance();
    }

    /**
     * Checks if current user has access to this form.
     */
    protected function check_access_for_dynamic_submission(): void {
        require_capability('block/greetings:postmessages', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission (save message and return data for JS).
     *
     * @return \stdClass Data for the new message card (id, message, formatteddate, authorname, etc.)
     */
    public function process_dynamic_submission() {
        global $DB, $USER, $CFG;

        $data = $this->get_data();
        $message = trim($data->message ?? '');
        if ($message === '') {
            return (object) ['error' => get_string('required')];
        }

        $record = new \stdClass();
        $record->message = $message;
        $record->timecreated = time();
        $record->userid = $USER->id;
        $record->id = $DB->insert_record('block_greetings_messages', $record);

        $context = $this->get_context_for_dynamic_submission();
        $deleteanypost = has_capability('block/greetings:deleteanymessage', $context);
        $deletepost = has_capability('block/greetings:deleteownmessage', $context);
        $candelete = $deleteanypost || ($deletepost && $record->userid == $USER->id);

        $user = $DB->get_record('user', ['id' => $record->userid]);
        $authorname = $user ? fullname($user) : get_string('unknownuser', 'block_greetings');

        $cardbackgroundcolor = get_config('block_greetings', 'messagecardbgcolor');
        if (empty($cardbackgroundcolor)) {
            $cardbackgroundcolor = '#fff';
        }

        $deleteurl = $CFG->wwwroot . '/my/?action=del&id=' . $record->id . '&sesskey=' . sesskey();
        $editurl = $CFG->wwwroot . '/blocks/greetings/edit.php?id=' . $record->id;

        return (object) [
            'id' => $record->id,
            'message' => format_string($record->message, true),
            'timecreated' => $record->timecreated,
            'formatteddate' => userdate($record->timecreated, get_string('strftimedaydatetime', 'core_langconfig')),
            'authorname' => $authorname,
            'candelete' => $candelete,
            'deleteurl' => $deleteurl,
            'editurl' => $editurl,
            'cardbackgroundcolor' => $cardbackgroundcolor,
        ];
    }

    /**
     * Load in existing data as form defaults (for new message form, nothing to load).
     */
    public function set_data_for_dynamic_submission(): void {
        $id = $this->optional_param('id', 0, PARAM_INT);
        if ($id > 0) {
            global $DB;
            $record = $DB->get_record('block_greetings_messages', ['id' => $id]);
            if ($record) {
                $this->set_data(['id' => $id, 'message' => $record->message]);
            }
        }
    }

    /**
     * Returns URL to set in $PAGE->set_url() when form is rendered or submitted via AJAX.
     *
     * @return \moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): \moodle_url {
        return new \moodle_url('/my/');
    }
}
