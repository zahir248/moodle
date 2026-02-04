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
 * Search.
 *
 * @package    local_dbapis
 * @copyright  2023 Your Name <you@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/dbapis/search.php'));
$PAGE->set_pagelayout('standard');

$strtitle = get_string('pluginname', 'local_dbapis');
$strheading = get_string('searchposts', 'local_dbapis');

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

// Add breadcrumbs.
$navbar = $PAGE->navbar;
$navbar->add($strtitle, new moodle_url('/local/dbapis/'));
$navbar->add($strheading)->make_active();

require_login();

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$searchform = new \local_dbapis\form\search_form();

if ($data = $searchform->get_data()) {

    // Sanitize search term: plain text only, trimmed.
    $searchterm = trim(clean_param($data->searchterm, PARAM_TEXT));

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading, 2);
    $searchform->display();

    if ($searchterm === '') {
        echo $OUTPUT->notification('Please enter a search term.', 'warning');
        echo $OUTPUT->footer();
        exit;
    }

    // Escape LIKE wildcards so % and _ in user input are matched literally.
    $likesearch = str_replace(['%', '_'], ['\%', '\_'], $searchterm);

    // Single query with JOIN: fetch posts and author names, use named parameters and recordset.
    $sql = "SELECT p.id, p.message, p.userid, p.timecreated, u.firstname, u.lastname
              FROM {local_dbapis} p
              LEFT JOIN {user} u ON u.id = p.userid
             WHERE " . $DB->sql_like('p.message', ':searchpattern', false);
    $params = ['searchpattern' => '%' . $likesearch . '%'];
    $recordset = $DB->get_recordset_sql($sql, $params);

    $candelete = has_capability('local/dbapis:deletemessage', $context);
    $results = [];
    foreach ($recordset as $record) {
        $authorname = ($record->firstname !== null && $record->lastname !== null)
            ? trim($record->firstname . ' ' . $record->lastname)
            : get_string('unknownuser', 'local_dbapis');

        $row = (object) [
            'id' => $record->id,
            'message' => format_string($record->message, true),
            'authorname' => s($authorname),
        ];
        if ($candelete) {
            $row->deletebutton = $OUTPUT->single_button(
                new moodle_url('/local/dbapis/deletepost.php', [
                    'id' => $record->id,
                    'returnurl' => $PAGE->url->out(false),
                ]),
                get_string('delete'),
                'get'
            );
        } else {
            $row->deletebutton = '';
        }
        $results[] = $row;
    }
    $recordset->close();

    $templatedata = [
        'results' => $results,
        'continueurl' => $PAGE->url->out(false),
        'continueurltext' => get_string('continue'),
    ];
    echo $OUTPUT->render_from_template('local_dbapis/search_results', $templatedata);

    echo $OUTPUT->footer();

    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading, 2);

$searchform->display();

echo $OUTPUT->footer();
