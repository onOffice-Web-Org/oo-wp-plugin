<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

namespace onOffice\WPlugin;

use onOffice\WPlugin\DataView\DataAddressDetailView;
use onOffice\WPlugin\DataView\DataViewAddress;
use onOffice\WPlugin\Types\ImageTypes;

class AddressDetail
	extends AddressList {


	/** @var int */
	private $_addressId = null;

	/**
	 *
	 * @return int
	 *
	 */

	protected function getNumAddressPages()
	{
		return 1;
	}


	/**
	 *
	 * @param int $id
	 *
	 */

	public function loadSingleAddress($id)
	{
		$this->_addressId = $id;
		$fields = $this->getDataViewAddress()->getFields();

		if ($this->getDataViewAddress() instanceof DataAddressDetailView) {
			$pictureTypes = $this->getDataViewAddress()->getPictureTypes();

			if (in_array(ImageTypes::PASSPORTPHOTO, $pictureTypes)) {
				array_push($fields, "imageUrl");
			}

			if (in_array(ImageTypes::BILDWEBSEITE, $pictureTypes)) {
				array_push($fields, ImageTypes::BILDWEBSEITE);
			}
		}

		$this->loadAddressesById([$id], $fields);
	}


	/**
	 *
	 * @return int
	 *
	 */

	protected function getRecordsPerPage()
	{
		return 1;
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function addExtraParams(): array
	{
		return [];
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getAddressId(): int
	{
		return $this->_addressId;
	}


	/**
	 *
	 * @param int $addressId
	 *
	 */

	public function setAddressId(int $addressId)
	{
		$this->_addressId = $addressId;
	}

	/**
	 *
	 * @return string
	 *
	 */

	 public function getShortCodeForm(): string
	 {
		$view = $this->getDataViewAddress();

		if (! $view instanceof DataAddressDetailView || $view->getShortCodeForm() == '') {
			return '';
		}

		$result = $view->getShortCodeForm();

		return  '[oo_form form="' . $result . '"]';
	 }

	/**
	 *
	 * @return string
	 *
	 */

	 public function getShortCodeReferenceEstates(): string
	 {
		 $view = $this->getDataViewAddress();

		 if (! $view instanceof DataAddressDetailView || $view->getShortCodeReferenceEstate() == '') {
			 return '';
		 }

		 $result = $view->getShortCodeReferenceEstate();

		 return '[oo_estate view="' . $result . '" address="'.$this->_addressId.'"]';

	 }

	/**
	 *
	 * @return string
	 *
	 */

	 public function getShortCodeActiveEstates(): string
	 {
		 $view = $this->getDataViewAddress();

		 if (! $view instanceof DataAddressDetailView || $view->getShortCodeActiveEstate() == '') {
			 return '';
		 }

		 $result = $view->getShortCodeActiveEstate();

		 return '[oo_estate view="' . $result . '" address="'.$this->_addressId.'"]';

	 }

}
