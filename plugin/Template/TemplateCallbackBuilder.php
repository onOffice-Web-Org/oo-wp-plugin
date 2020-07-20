<?php
/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

namespace onOffice\WPlugin\Template;

use Closure;
use DI\Container;
use onOffice\WPlugin\Controller\SortList\SortListDropDownGenerator;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\Form;

class TemplateCallbackBuilder
{
	/** @var Container */
	private $_pContainer;

	/**
	 * @param Container $pContainer
	 */
	public function __construct(Container $pContainer)
	{
		$this->_pContainer = $pContainer;
	}

	/**
	 * @param EstateList|null $pEstateList
	 * @return Closure
	 */
	public function buildCallbackListSortDropDown($pEstateList): Closure
	{
		$pContainer = $this->_pContainer;
		return static function() use ($pEstateList, $pContainer): string {
			if ($pEstateList !== null) {
				return $pContainer->get(SortListDropDownGenerator::class)
					->generate($pEstateList->getDataView()->getName());
			}
			return '';
		};
	}

	/**
	 * @param EstateList|null $pEstateList
	 * @return Closure
	 */
	public function buildCallbackEstateListName($pEstateList): Closure
	{
		return static function() use ($pEstateList): string {
			return ($pEstateList !== null) ? $pEstateList->getDataView()->getName() : '';
		};
	}
}