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
 * Plugin upgrade steps are defined here.
 *
 * @package     local_dbapis
 * @category    upgrade
 * @copyright   2023 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute local_dbapis upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_dbapis_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026020300) {

        $table = new xmldb_table('local_dbapis_history');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('messageid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Copy existing records from local_dbapis into local_dbapis_history.
        $records = $DB->get_records('local_dbapis');
        foreach ($records as $record) {
            $historyrecord = (object) [
                'messageid' => $record->id,
                'message' => $record->message,
                'userid' => $record->userid,
                'timecreated' => $record->timecreated,
            ];
            $DB->insert_record('local_dbapis_history', $historyrecord);
        }

        upgrade_plugin_savepoint(true, 2026020300, 'local', 'dbapis');
    }

    if ($oldversion < 2026020302) {
        // Sync capabilities from db/access.php into the database.
        update_capabilities('local_dbapis');
        upgrade_plugin_savepoint(true, 2026020302, 'local', 'dbapis');
    }

    if ($oldversion < 2026020303) {
        // Ensure capabilities are synced (in case 2026020302 was skipped or capabilities not yet installed).
        update_capabilities('local_dbapis');
        upgrade_plugin_savepoint(true, 2026020303, 'local', 'dbapis');
    }

    return true;
}
