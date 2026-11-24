<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use WP_UnitTestCase;

/**
 * The units template must show the marketing status (sold, rented, reserved, ...) of each unit,
 * so visitors see right away which units are no longer available.
 *
 * @covers \onOffice\WPlugin\Template::render
 */
class TestTemplateEstateDefaultUnits
	extends WP_UnitTestCase
{
	use HtmlNormalizerTrait;

	public function testRenderShowsMarketingStatusOfUnits()
	{
		$output = $this->renderUnits([
			151 => ['objekttitel' => 'Unit A', 'objektnr_extern' => 'W1', 'vermarktungsstatus' => ''],
			153 => ['objekttitel' => 'Unit B', 'objektnr_extern' => 'W2', 'vermarktungsstatus' => 'vermietet'],
		]);

		$this->assertStringContainsString('<th class="oo-units__data">Status</th>', $output);
		$this->assertStringContainsString(
			'<td class="oo-units__data" data-label="Status">vermietet</td>', $output);
		$this->assertStringContainsString(
			'<td class="oo-units__data --empty" data-label="Status">-</td>', $output);
		// The computed field has no enterprise label, so its raw key must not leak into the table.
		$this->assertStringNotContainsString('label-vermarktungsstatus', $output);
	}

	public function testRenderHidesStatusColumnIfNoUnitHasAStatus()
	{
		$output = $this->renderUnits([
			151 => ['objekttitel' => 'Unit A', 'objektnr_extern' => 'W1', 'vermarktungsstatus' => ''],
			153 => ['objekttitel' => 'Unit B', 'objektnr_extern' => 'W2', 'vermarktungsstatus' => ''],
		]);

		$this->assertStringContainsString('<th class="oo-units__data">label-objektnr_extern</th>', $output);
		$this->assertStringNotContainsString('data-label="Status"', $output);
	}

	/**
	 * @param array $units unit id => record elements
	 * @return string the normalized HTML
	 */
	private function renderUnits(array $units): string
	{
		$pTemplate = (new TemplateMocker(getcwd()))
			->withTemplateName('templates.dist/estate/default_units.php')
			->withEstateList($this->createEstateListDouble($units));

		return $this->normalizeHtml($pTemplate->render());
	}

	/**
	 * The template clones the list and iterates the clone and the original separately, which a
	 * mock with consecutive return values can't do, so this double keeps its own position.
	 *
	 * @param array $units unit id => record elements
	 * @return EstateList
	 */
	private function createEstateListDouble(array $units): EstateList
	{
		$pEstateList = new class(new DataListView(1, 'units')) extends EstateList {
			/** @var array */
			private $_units = [];

			/** @var int[] */
			private $_unitIds = [];

			/** @var int */
			private $_position = 0;

			/** @var int */
			private $_currentUnitId = 0;

			/**
			 * The constructor signature is fixed by EstateListBase, hence a setter.
			 *
			 * @param array $units unit id => record elements
			 */
			public function setUnits(array $units)
			{
				$this->_units = $units;
				$this->_unitIds = array_keys($units);
			}

			public function estateIterator($modifier = EstateViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT,
				$checkEstateIdRequestGuard = false)
			{
				if (!isset($this->_unitIds[$this->_position])) {
					return false;
				}
				$this->_currentUnitId = $this->_unitIds[$this->_position++];
				return new ArrayContainerEscape($this->_units[$this->_currentUnitId]);
			}

			public function resetEstateIterator()
			{
				$this->_position = 0;
			}

			public function getCurrentEstateId(): int
			{
				return $this->_currentUnitId;
			}

			public function getRawValues(): ArrayContainerEscape
			{
				$recordsRaw = [];
				foreach ($this->_unitIds as $unitId) {
					$recordsRaw[$unitId] = ['id' => $unitId, 'type' => 'estate', 'elements' => []];
				}
				return new ArrayContainerEscape($recordsRaw);
			}

			public function getFieldLabel($field): string
			{
				return 'label-'.$field;
			}

			public function getEstateLink(): string
			{
				return 'https://example.com/unit/'.$this->_currentUnitId;
			}
		};
		$pEstateList->setUnits($units);

		return $pEstateList;
	}
}
