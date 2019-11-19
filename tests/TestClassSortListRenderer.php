<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

use onOffice\WPlugin\Controller\SortList\SortListDataModel;
use onOffice\WPlugin\Renderer\SortListRenderer;
use WP_UnitTestCase;

class TestClassSortListRenderer
	extends WP_UnitTestCase
{
	/** @var SortListDataModel */
	private $_pSortListModelAdjustable = null;

	/** @var SortListDataModel */
	private $_pSortListModelNotAdjustable = null;

	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSortListModelAdjustable = new SortListDataModel;
		$this->_pSortListModelAdjustable->setSortbyUserDirection(1);
		$this->_pSortListModelAdjustable->setAdjustableSorting(true);
		$this->_pSortListModelAdjustable->setSortByUserValues([
			'kaltmiete' => 'Kaltmiete',
			'kaufpreis' => 'Kaufpreis']);
		$this->_pSortListModelAdjustable->setSelectedSortorder('ASC');
		$this->_pSortListModelAdjustable->setSortbyDefaultValue('kaufpreis');
		$this->_pSortListModelAdjustable->setSelectedSortby('kaufpreis');

		$this->_pSortListModelNotAdjustable = new SortListDataModel;
		$this->_pSortListModelNotAdjustable->setSortbyUserDirection(1);
		$this->_pSortListModelNotAdjustable->setAdjustableSorting(false);
		$this->_pSortListModelNotAdjustable->setSortByUserValues([
			'kaltmiete' => 'Kaltmiete',
			'kaufpreis' => 'Kaufpreis']);
		$this->_pSortListModelNotAdjustable->setSelectedSortorder('ASC');
		$this->_pSortListModelNotAdjustable->setSortbyDefaultValue('kaufpreis');
		$this->_pSortListModelNotAdjustable->setSelectedSortby('kaufpreis');
	}

	/**
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createHtmlSelector
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::estimateDirectionLabelBySortorder
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createOptionLabel
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createOptionValue
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createSelectedValue
	 *  @covers onOffice\WPlugin\Renderer\SortListRenderer::createHtml
	 */
	public function testAdjustableWithDefault()
	{
		$expected = '<select name="userDefinedSelection" id="onofficeSortListSelector"><option value="kaltmiete#ASC" >Kaltmiete ascending</option><option value="kaltmiete#DESC" >Kaltmiete descending</option><option value="kaufpreis#ASC"  selected>Kaufpreis ascending</option><option value="kaufpreis#DESC" >Kaufpreis descending</option></select>';

		$pSortListRenderer = new SortListRenderer;
		$this->assertEquals($expected, $pSortListRenderer->createHtmlSelector($this->_pSortListModelAdjustable));
	}

	/**
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createHtmlSelector
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::estimateDirectionLabelBySortorder
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createOptionLabel
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createOptionValue
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createSelectedValue
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createHtml
	 */
	public function testNotAdjustable()
	{
		$pSortListRenderer = new SortListRenderer;
		$this->assertEquals('', $pSortListRenderer->createHtmlSelector($this->_pSortListModelNotAdjustable));
	}

	/**
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createHtmlSelector
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::estimateDirectionLabelBySortorder
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createOptionLabel
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createOptionValue
	 * @covers onOffice\WPlugin\Renderer\SortListRenderer::createSelectedValue
	 *  @covers onOffice\WPlugin\Renderer\SortListRenderer::createHtml
	 */
	public function testAdjustableFromRequestVars()
	{
		$_GET = ['sortby' => 'kaltmiete', 'sortorder' => 'DESC'];
		$expected = '<select name="userDefinedSelection" id="onofficeSortListSelector"><option value="kaltmiete#ASC" >Kaltmiete ascending</option><option value="kaltmiete#DESC" >Kaltmiete descending</option><option value="kaufpreis#ASC"  selected>Kaufpreis ascending</option><option value="kaufpreis#DESC" >Kaufpreis descending</option></select>';

		$pSortListRenderer = new SortListRenderer;
		$this->assertEquals($expected, $pSortListRenderer->createHtmlSelector($this->_pSortListModelAdjustable));
	}

}