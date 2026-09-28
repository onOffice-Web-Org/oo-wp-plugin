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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use WP_UnitTestCase;
use const ONOFFICE_PLUGIN_DIR;

/**
 * js/onoffice-custom-select.js initialises Tom Select on `.custom-single-select-tom` and
 * `.custom-multiple-select-tom` only. The classes without the `-tom` suffix are handled by
 * the jQuery Select2 branch of that file, and Select2 is no longer loaded in the frontend -
 * a select that carries them stays an unstyled native select nobody wired up.
 */
class TestClassTemplatesCustomSelectClass
	extends WP_UnitTestCase
{
	/** matches the legacy classes, but not their `-tom` successors */
	const PATTERN_LEGACY_SELECT_CLASS = '/custom-(single|multiple)-select(?!-tom)/';

	/**
	 * @return array
	 */
	private function getTemplateFiles(): array
	{
		$pIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator
			(ONOFFICE_PLUGIN_DIR . '/templates.dist'));
		$files = [];

		foreach ($pIterator as $pFile) {
			/* @var $pFile SplFileInfo */
			if ($pFile->isFile() && $pFile->getExtension() === 'php') {
				$files []= $pFile->getPathname();
			}
		}

		return $files;
	}

	public function testTemplatesUseTomSelectClassesOnly()
	{
		$filesWithLegacyClass = [];

		foreach ($this->getTemplateFiles() as $file) {
			if (preg_match(self::PATTERN_LEGACY_SELECT_CLASS, (string) file_get_contents($file))) {
				$filesWithLegacyClass []= str_replace(ONOFFICE_PLUGIN_DIR . '/', '', $file);
			}
		}

		$this->assertSame([], $filesWithLegacyClass, 'Select fields must use the '
			. '"-tom" classes, otherwise onoffice-custom-select.js does not pick them up');
	}

	/**
	 * Guards the test above against a typo in its own pattern.
	 */
	public function testPatternMatchesTheLegacyClassesOnly()
	{
		$this->assertSame(1, preg_match(self::PATTERN_LEGACY_SELECT_CLASS,
			'<select class="custom-single-select" size="1">'));
		$this->assertSame(1, preg_match(self::PATTERN_LEGACY_SELECT_CLASS,
			'<select class="custom-multiple-select form-control">'));
		$this->assertSame(0, preg_match(self::PATTERN_LEGACY_SELECT_CLASS,
			'<select class="custom-single-select-tom oo-regions">'));
		$this->assertSame(0, preg_match(self::PATTERN_LEGACY_SELECT_CLASS,
			'<select class="custom-multiple-select-tom form-control">'));
	}
}
