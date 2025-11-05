<?php

/**
 *
 *    Copyright (C) 2025 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare (strict_types=1);

namespace onOffice\tests;

/**
 * Trait for normalizing HTML output in tests
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2025, onOffice(R) GmbH
 */
trait HtmlNormalizerTrait
{
    /**
     * Normalize HTML by removing all whitespace between tags
     *
     * @param string $html
     * @return string
     */
    protected function normalizeHtml(string $html): string
    {
        // Decode HTML entities (&gt; -> >, &lt; -> <, etc.)
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);
        // Trim leading/trailing whitespace
        $html = trim($html);
        // Normalize multiple spaces to single space
        $html = preg_replace('/\s+/', ' ', $html);

        return $html;
    }

    /**
     * Assert that two HTML strings are equal after normalization
     *
     * @param string $expected
     * @param string $actual
     * @param string $message
     * @return void
     */
    protected function assertHtmlEquals(string $expected, string $actual, string $message = ''): void
    {
        $this->assertEquals(
            $this->normalizeHtml($expected),
            $this->normalizeHtml($actual),
            $message
        );
    }

    /**
     * Assert that HTML string equals content of file after normalization
     *
     * @param string $expectedFile Path to file with expected content
     * @param string $actual Actual HTML string
     * @param string $message Optional message
     * @return void
     */
    protected function assertHtmlEqualsFile(string $expectedFile, string $actual, string $message = ''): void
    {
        $this->assertFileExists($expectedFile, "Expected file does not exist: {$expectedFile}");
        
        $expected = file_get_contents($expectedFile);
        
        $this->assertEquals(
            $this->normalizeHtml($expected),
            $this->normalizeHtml($actual),
            $message
        );
    }
}