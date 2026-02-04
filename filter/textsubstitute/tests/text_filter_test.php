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
 * Unit tests for the Text substitute filter.
 *
 * @package    filter_textsubstitute
 * @category   test
 * @copyright  2026 Intensiti Elemen Sdn Bhd <Info@iesb.com.my>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \filter_textsubstitute\text_filter
 */
final class text_filter_test extends \advanced_testcase {

    /**
     * Check that search terms are substituted with another given term when filtered.
     *
     * @param string $searchterm Configured search term
     * @param string $substituteterm Configured replacement text
     * @param string $formats Comma-separated list of format IDs the filter applies to
     * @param int $originalformat The format of the input text
     * @param string $inputtext Original text
     * @param string $expectedtext Expected text after filtering
     * @dataProvider filter_textsubstitute_provider
     * @covers \filter_textsubstitute\text_filter::filter
     */
    public function test_filter_textsubstitute(
        string $searchterm,
        string $substituteterm,
        string $formats,
        int $originalformat,
        string $inputtext,
        string $expectedtext
    ): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Set the plugin config (plugin uses 'replacewith', not 'substituteterm').
        set_config('searchterm', $searchterm, 'filter_textsubstitute');
        set_config('replacewith', $substituteterm, 'filter_textsubstitute');
        set_config('formats', $formats, 'filter_textsubstitute');

        $filter = new \filter_textsubstitute\text_filter(\core\context\system::instance(), []);

        $filteredtext = $filter->filter($inputtext, ['originalformat' => $originalformat]);

        $this->assertEquals($expectedtext, $filteredtext);
    }

    /**
     * Data provider for {@see test_filter_textsubstitute}
     *
     * @return array[]
     */
    public static function filter_textsubstitute_provider(): array {
        return [
            'All formats allowed - html' => [
                'searchterm' => 'Moodle',
                'substituteterm' => 'Workplace',
                'formats' => FORMAT_HTML . ',' . FORMAT_MARKDOWN . ',' . FORMAT_MOODLE . ',' . FORMAT_PLAIN,
                'originalformat' => FORMAT_HTML,
                'inputtext' => 'Moodle is a popular LMS. You can download Moodle for free. MOODLE 4.2 is out now.',
                'expectedtext' => 'Workplace is a popular LMS. You can download Workplace for free. MOODLE 4.2 is out now.',
            ],
            'FORMAT_HTML is allowed' => [
                'searchterm' => 'Moodle',
                'substituteterm' => 'Workplace',
                'formats' => (string) FORMAT_HTML,
                'originalformat' => FORMAT_HTML,
                'inputtext' => '<em>Moodle</em> is a popular LMS. You can download Moodle for free. MOODLE 4.2 is here.',
                'expectedtext' => '<em>Workplace</em> is a popular LMS. You can download Workplace for free. MOODLE 4.2 is here.',
            ],
            'Format not in list - filter not applied' => [
                'searchterm' => 'Moodle',
                'substituteterm' => 'Workplace',
                'formats' => (string) FORMAT_HTML,
                'originalformat' => FORMAT_PLAIN,
                'inputtext' => 'Moodle is a popular LMS.',
                'expectedtext' => 'Moodle is a popular LMS.',
            ],
        ];
    }

    /**
     * When originalformat is not passed, filter returns text unchanged.
     *
     * @covers \filter_textsubstitute\text_filter::filter
     */
    public function test_filter_skipped_when_no_originalformat(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        set_config('searchterm', 'Moodle', 'filter_textsubstitute');
        set_config('replacewith', 'Workplace', 'filter_textsubstitute');
        set_config('formats', (string) FORMAT_HTML, 'filter_textsubstitute');

        $filter = new \filter_textsubstitute\text_filter(\core\context\system::instance(), []);

        $input = 'Moodle is great.';
        $result = $filter->filter($input, []);

        $this->assertEquals($input, $result);
    }
}
