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

namespace onOffice\WPlugin\Controller;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierFactory;

/**
 *
 */

class EstateTitleBuilder
{
	/** @var ViewFieldModifierFactory */
	private $_pViewFieldModifierFactory = null;

	/** @var DataDetailViewHandler */
	private $_pDataDetailViewHandler = null;

	/** @var EstateListBase */
	private $_pEstateDetail = null;

	/** @var DefaultFilterBuilderDetailView */
	private $_pDefaultFilterBuilder = null;


	/**
	 *
	 * @param ViewFieldModifierFactory $pViewFieldModifierFactory
	 * @param DataDetailViewHandler $pDataDetailViewHandler
	 * @param EstateDetail $pEstateDetail
	 * @param DefaultFilterBuilderDetailView $pDefaultFilterBuilder
	 *
	 */

	public function __construct(
		ViewFieldModifierFactory $pViewFieldModifierFactory = null,
		DataDetailViewHandler $pDataDetailViewHandler = null,
		EstateDetail $pEstateDetail = null,
		DefaultFilterBuilderDetailView $pDefaultFilterBuilder = null)
	{
		$this->_pViewFieldModifierFactory = $pViewFieldModifierFactory ??
			new ViewFieldModifierFactory(onOfficeSDK::MODULE_ESTATE);
		$this->_pDataDetailViewHandler = $pDataDetailViewHandler ?? new DataDetailViewHandler();
		$this->_pDefaultFilterBuilder = $pDefaultFilterBuilder ?? new DefaultFilterBuilderDetailView();

		if ($pEstateDetail === null) {
			$pEstateDetail = new EstateDetail($this->_pDataDetailViewHandler->getDetailView());
			$pEstateDetail->setDefaultFilterBuilder($this->_pDefaultFilterBuilder);
		}
		$this->_pEstateDetail = $pEstateDetail;
	}


	/**
	 *
	 * @param int $estateId
	 * @param string $format
	 *
	 * The Format consists of:
	 * %1$s: 'objekttitel',
	 * %2$s: 'objektart',
	 * %3$s: 'vermarktungsart',
	 * %4$s: 'ort',
	 * %5$s: 'objektnr_extern',
	 *
	 */

	public function buildTitle(int $estateId, string $format): string
	{
		$this->_pDefaultFilterBuilder->setEstateId($estateId);
		$this->_pEstateDetail->loadSingleEstate($estateId);
		$modifier = EstateViewFieldModifierTypes::MODIFIER_TYPE_TITLE;
		$pEstateIterator = $this->_pEstateDetail->estateIterator($modifier);
		$pEstateFieldModifier = $this->_pViewFieldModifierFactory->create($modifier);
		$fieldsForTitle = $pEstateFieldModifier->getVisibleFields();

		if ($pEstateIterator) {
			$fetchedValues = array_map([$pEstateIterator, 'getValueRaw'], $fieldsForTitle);
			$values = array_combine($fieldsForTitle, $fetchedValues);
			$this->_pEstateDetail->resetEstateIterator();
			return $this->buildEstateTitle($format, $values);
		}
		return '';
	}


	/**
	 *
	 * @param string $format
	 * @param array $values
	 * @return string
	 *
	 */

	private function buildEstateTitle(string $format, array $values): string
	{
		return sprintf($format, $values['objekttitel'], $values['objektart'],
			$values['vermarktungsart'], $values['ort'], $values['objektnr_extern']);
	}


	/**
	 *
	 * @return ViewFieldModifierFactory
	 *
	 */

	public function getViewFieldModifierFactory(): ViewFieldModifierFactory
	{
		return $this->_pViewFieldModifierFactory;
	}


	/**
	 *
	 * @return DataDetailViewHandler
	 *
	 */

	public function getDataDetailViewHandler(): DataDetailViewHandler
	{
		return $this->_pDataDetailViewHandler;
	}


	/**
	 *
	 * @return EstateDetail
	 *
	 */

	public function getEstateDetail(): EstateDetail
	{
		return $this->_pEstateDetail;
	}


	/**
	 *
	 * @return DefaultFilterBuilderDetailView
	 *
	 */

	public function getDefaultFilterBuilder(): DefaultFilterBuilderDetailView
	{
		return $this->_pDefaultFilterBuilder;
	}
}
