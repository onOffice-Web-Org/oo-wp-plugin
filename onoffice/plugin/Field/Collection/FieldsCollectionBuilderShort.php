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

namespace onOffice\WPlugin\Field\Collection;

use DI\Container;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorFormContact;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionBackend;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorInternalAnnotations;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorSearchcriteria;
use onOffice\WPlugin\Types\FieldsCollection;
use function __;


/**
 *
 */

class FieldsCollectionBuilderShort
{
	/** @var Container */
	private $_pContainer = null;


	/**
	 *
	 * @param Container $pContainer
	 *
	 */

	public function __construct(Container $pContainer)
	{
		$this->_pContainer = $pContainer;
	}


	/**
	 *
	 * @param FieldsCollection $pFieldsCollection
	 * @return $this
	 *
	 */

	public function addFieldsAddressEstate(FieldsCollection $pFieldsCollection): self
	{
		$pFieldLoader = $this->_pContainer->get(FieldLoaderGeneric::class);
		$pFieldCollectionAddressEstate = $this->_pContainer->get(FieldsCollectionBuilder::class)
			->buildFieldsCollection($pFieldLoader);
		$pFieldsCollection->merge($pFieldCollectionAddressEstate);
		return $this;
	}


	/**
	 *
	 * @param FieldsCollection $pFieldsCollection
	 * @return $this
	 *
	 */

	public function addFieldsSearchCriteria(FieldsCollection $pFieldsCollection): self
	{
		$pFieldLoader = $this->_pContainer->get(FieldLoaderSearchCriteria::class);
		$pFieldsCollectionSearchCriteria = $this->_pContainer->get(FieldsCollectionBuilder::class)
			->buildFieldsCollection($pFieldLoader);
		$pFieldsCollection->merge($pFieldsCollectionSearchCriteria);
		return $this;
	}


	/**
	 *
	 * @param FieldsCollection $pFieldsCollection
	 * @return $this
	 *
	 */

	public function addFieldsFormBackend(FieldsCollection $pFieldsCollection): self
	{
		$pFieldsCollectionTmp = new FieldModuleCollectionDecoratorInternalAnnotations
			(new FieldModuleCollectionDecoratorSearchcriteria
				(new FieldModuleCollectionDecoratorFormContact
					(new FieldModuleCollectionDecoratorGeoPositionBackend(new FieldsCollection))));
		$pFieldsCollection->merge($pFieldsCollectionTmp);
		return $this;
	}


	/**
	 *
	 * @param FieldsCollection $pFieldsCollection
	 * @return $this
	 *
	 */

	public function addFieldsFormFrontend(FieldsCollection $pFieldsCollection): self
	{
		$pFieldsCollectionTmp = new FieldModuleCollectionDecoratorFormContact
			(new FieldModuleCollectionDecoratorSearchcriteria
				(new FieldModuleCollectionDecoratorGeoPositionFrontend(new FieldsCollection)));
		$pFieldsCollection->merge($pFieldsCollectionTmp);
		return $this;
	}


	/**
	 *
	 * @param FieldsCollection $pFieldsCollection
	 * @return $this
	 *
	 */

	public function addFieldsSearchCriteriaSpecificBackend(FieldsCollection $pFieldsCollection): self
	{
		$pFieldsCollectionTmp = new FieldModuleCollectionDecoratorGeoPositionBackend
				(new FieldModuleCollectionDecoratorInternalAnnotations
					(new FieldModuleCollectionDecoratorSearchcriteria(new FieldsCollection)));
		$pFieldsCollection->merge($pFieldsCollectionTmp, __('Form Specific Fields', 'onoffice'));
		return $this;
	}
}