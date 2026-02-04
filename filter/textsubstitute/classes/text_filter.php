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

namespace filter_textsubstitute;

/**
 * Text substitute filter.
 *
 * Substitutes a configurable search term with replacement text. Search and replacement
 * come from the plugin settings. The filter runs only for content in the formats
 * selected in "Apply to formats" (included for the exercise; for plain text substitution
 * you could apply to all formats).
 *
 * @package    filter_textsubstitute
 * @copyright  2026 Intensiti Elemen Sdn Bhd <Info@iesb.com.my>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends \core_filters\text_filter {

    /**
     * Apply the filter: replace the configured search term with the substitute text.
     *
     * Uses plugin settings for search term, replacement text, and allowed formats.
     * Only runs when the content's original format is in the selected "Apply to formats".
     *
     * @param string $text The text to filter.
     * @param array $options Options with at least 'originalformat' (content format).
     * @return string The filtered text.
     */
    public function filter($text, array $options = []) {
        // Do not filter when format is unknown (e.g. from format_string()).
        if (!isset($options['originalformat'])) {
            return $text;
        }
        // Only apply when content is in one of the selected formats.
        $formats = get_config('filter_textsubstitute', 'formats');
        if ($formats === false || $formats === '') {
            return $text;
        }
        if (!in_array($options['originalformat'], explode(',', $formats))) {
            return $text;
        }
        // Get search and substitute from plugin settings.
        $searchterm = get_config('filter_textsubstitute', 'searchterm');
        $replacewith = get_config('filter_textsubstitute', 'replacewith');
        if ($searchterm !== '' && $searchterm !== false) {
            $text = str_replace($searchterm, (string) $replacewith, $text);
        }
        return $text;
    }
}
