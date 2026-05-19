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

namespace local_aigrade\hook\output;

/**
 * Hook callback for before_footer_html_generation (replaces legacy before_footer callback).
 *
 * Injects AI grading buttons into assignment grading pages via AMD modules.
 *
 * @package    local_aigrade
 * @copyright  2025 Brian A. Pool, National Trail Local Schools
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_footer {

    /**
     * Add AI Grade button(s) on assignment grading pages.
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     */
    public static function inject_aigrade_buttons(\core\hook\output\before_footer_html_generation $hook): void {
        global $DB;

        $page = $hook->renderer->get_page();

        // Only on assignment module pages.
        if ($page->pagetype !== 'mod-assign-view' &&
            $page->pagetype !== 'mod-assign-grader' &&
            $page->pagetype !== 'mod-assign-grading' &&
            strpos($page->pagetype, 'mod-assign') === false) {
            return;
        }

        $cm = $page->cm;
        if (!$cm || $cm->modname !== 'assign') {
            return;
        }

        $config = $DB->get_record('local_aigrade_config', ['assignmentid' => $cm->instance]);
        if (!$config || !$config->enabled) {
            return;
        }

        $context = \context_module::instance($cm->id);
        if (!has_capability('local/aigrade:grade', $context)) {
            return;
        }

        $action = optional_param('action', '', PARAM_ALPHA);
        $userid = optional_param('userid', 0, PARAM_INT);
        $is_individual = ($action === 'grader');

        $ai_name = get_config('local_aigrade', 'ai_name');
        if (empty($ai_name)) {
            $ai_name = 'AI';
        }

        $sesskey = sesskey();

        if ($is_individual) {
            $url = new \moodle_url('/local/aigrade/grade_single.php', ['id' => $cm->id, 'userid' => $userid]);
            $button_text = get_string('button_grade_single', 'local_aigrade', $ai_name);
            $page->requires->js_call_amd('local_aigrade/grade_single', 'init', [
                $url->out(false),
                $button_text,
                $sesskey,
            ]);
        } else {
            $url = new \moodle_url('/local/aigrade/grade.php', ['id' => $cm->id]);
            $button_text = get_string('button_grade_bulk', 'local_aigrade', $ai_name);
            $page->requires->js_call_amd('local_aigrade/grade_bulk', 'init', [
                $url->out(false),
                $button_text,
                $sesskey,
            ]);
        }
    }
}
