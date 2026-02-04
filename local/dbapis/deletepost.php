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
 * Delete a message from local_dbapis.
 *
 * @package    local_dbapis
 * @copyright  2023 Your Name <you@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_login();

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/dbapis/deletepost.php', ['id' => $id]));

require_capability('local/dbapis:deletemessage', $context);

$record = $DB->get_record('local_dbapis', ['id' => $id]);
if (!$record) {
    throw new moodle_exception('invalidrecord', 'local_dbapis');
}

$DB->delete_records('local_dbapis', ['id' => $id]);

if ($returnurl) {
    redirect($returnurl);
} else {
    redirect(new moodle_url('/local/dbapis/search.php'));
}
