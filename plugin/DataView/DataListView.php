<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\DataView;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\ViewProperty;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataListView
	implements DataView, DataViewFilterableFields, ViewProperty
{
	/** */
	const PICTURES = 'pictures';

	/** */
	const FIELDS = 'fields';

	/** */
	const CONTACT_PERSON = 'contactPerson';

	/** */
	const LISTVIEW_TYPE_DEFAULT = 'default';

	/** */
	const LISTVIEW_TYPE_REFERENCE = 'reference';

	/** */
	const LISTVIEW_TYPE_FAVORITES = 'favorites';

	/** */
	const LISTVIEW_TYPE_UNITS = 'units';

	/** */
	const SORT_BY_USER_VALUES = 'sortbyuservalues';

	/** @var int */
	private $_id = null;

	/** @var string */
	private $_name = null;

	/** @var string[] */
	private $_fields = [];

	/** @var string[] */
	private $_filterableFields = [];

	/** @var string[] */
	private $_hiddenFields = [];

	/** @var string[] */
	private $_pictureTypes = [];

	/** @var string[] */
	private $_addressFields = [];

	/** @var int */
	private $_filterId = 0;

	/** @var string */
	private $_sortorder = '';

	/** @var string */
	private $_sortby = '';

	/** @var int */
	private $_recordsPerPage = 5;

	/** @var bool */
	private $_showStatus = false;

	/** @var string */
	private $_listType = '';

	/** @var string */
	private $_template = '';

	/** @var string */
	private $_expose = '';

	/** @var bool */
	private $_random = false;

	/** @var string[] */
	private $_availableOptions = [];

	/** @var array */
	private $_geoFields = [];

	/** @var int */
	private $_sortBySetting = 0;

	/** @var array */
	private $_sortbyuservalues = [];

	/** @var string */
	private $_sortByUserDefinedDefault = '';

	/** @var int */
	private $_sortByUserDefinedDirection = 0;

	/** @var bool */
	private $_adjustableSorting = false;

		/** @var bool */
	private $_showReferenceStatus = false;


	/**
	 *
	 * @param int $id
	 * @param string $name
	 *
	 */

	public function __construct(int $id, string $name)
	{
		$this->_id = $id;
		$this->_name = $name;
	}


	/** @return int */
	public function getId(): int
		{ return $this->_id; }

	/** @return string */
	public function getName(): string
		{ return $this->_name; }

	/** @return array */
	public function getFields(): array
		{ return $this->_fields; }

	/** @return array */
	public function getPictureTypes(): array
		{ return $this->_pictureTypes; }

	/** @return int */
	public function getFilterId(): int
		{ return $this->_filterId; }

	/** @return string */
	public function getSortorder(): string
		{ return $this->_sortorder; }

	/** @return string */
	public function getSortby(): string
		{ return $this->_sortby; }

	/** @return bool */
	public function getShowStatus(): bool
		{ return $this->_showStatus; }

	/** @return string */
	public function getListType(): string
		{ return $this->_listType; }

	/** @return int */
	public function getRecordsPerPage(): int
		{ return $this->_recordsPerPage; }

	/** @return string */
	public function getTemplate(): string
		{ return $this->_template; }

	/** @return string */
	public function getExpose(): string
		{ return $this->_expose; }

	/** @return array */
	public function getAddressFields(): array
		{ return $this->_addressFields; }

	/** @param bool $random */
	public function setRandom(bool $random)
		{ $this->_random = $random; }

	/** @return bool */
	public function getRandom(): bool
		{ return $this->_random; }

	/** @param array $fields */
	public function setFields(array $fields)
		{ $this->_fields = $fields; }

	/** @param array $pictureTypes */
	public function setPictureTypes(array $pictureTypes)
		{ $this->_pictureTypes = $pictureTypes; }

	/** @param int $filterId */
	public function setFilterId(int $filterId)
		{ $this->_filterId = $filterId; }

	/** @param string $sortorder */
	public function setSortorder(string $sortorder)
		{ $this->_sortorder = $sortorder; }

	/** @param string $sortby */
	public function setSortby(string $sortby)
		{ $this->_sortby = $sortby; }

	/** @param bool $showStatus */
	public function setShowStatus(bool $showStatus)
		{ $this->_showStatus = $showStatus; }

	/** @param string $listType */
	public function setListType(string $listType)
		{ $this->_listType = $listType; }

	/** @param string $template */
	public function setTemplate(string $template)
		{ $this->_template = $template; }

	/** @param string $expose */
	public function setExpose(string $expose)
		{ $this->_expose = $expose; }

	/** @param int $recordsPerPage */
	public function setRecordsPerPage(int $recordsPerPage)
		{ $this->_recordsPerPage = $recordsPerPage; }

	/** @param array $addressFields */
	public function setAddressFields(array $addressFields)
		{ $this->_addressFields = $addressFields; }

	/** @return array */
	public function getFilterableFields(): array
		{ return $this->_filterableFields; }

	/** @param array $filterableFields */
	public function setFilterableFields(array $filterableFields)
		{ $this->_filterableFields = $filterableFields; }

	/** @return array */
	public function getHiddenFields(): array
		{ return $this->_hiddenFields; }

	/** @param array $hiddenFields */
	public function setHiddenFields(array $hiddenFields)
		{ $this->_hiddenFields = $hiddenFields; }

	/** @return string */
	public function getModule(): string
		{ return onOfficeSDK::MODULE_ESTATE; }

	/** @param array $availableOptions */
	public function setAvailableOptions(array $availableOptions)
		{ $this->_availableOptions = $availableOptions; }

	/** @return array */
	public function getAvailableOptions(): array
		{ return $this->_availableOptions; }

	/** @param array $geoFields */
	public function setGeoFields(array $geoFields)
		{ $this->_geoFields = $geoFields; }

	/** @return array */
	public function getGeoFields(): array
		{ return $this->_geoFields; }

	/** @return string */
	public function getViewType(): string
		{ return $this->_listType; }

	/** @param int $sortBySetting */
	public function setSortBySetting(int $sortBySetting)
		{ $this->_sortBySetting = $sortBySetting; }

	/** @return int */
	public function getSortBySetting(): int
		{ return $this->_sortBySetting;	}

	/** @param array $values */
	public function setSortByUserValues(array $values)
		{ $this->_sortbyuservalues = $values; }

	/** @return array */
	public function getSortByUserValues(): array
		{ return $this->_sortbyuservalues; }

	/** @param string $value */
	public function setSortByUserDefinedDefault(string $value)
		{ $this->_sortByUserDefinedDefault = $value; }

	/** @return string */
	public function getSortByUserDefinedDefault(): string
		{ return $this->_sortByUserDefinedDefault; }

	/** @param int $direction */
	public function setSortByUserDefinedDirection(int $direction)
		{ $this->_sortByUserDefinedDirection = $direction; }

	/** @return int */
	public function getSortByUserDefinedDirection(): int
		{ return $this->_sortByUserDefinedDirection; }

	/**
	 * @param bool $adjustable
	 */
	public function setAdjustableSorting(bool $adjustable)
	{
		$this->_adjustableSorting = $adjustable;
	}

	/**
	 * @return bool
	 */
	public function isAdjustableSorting(): bool
	{
		return $this->_adjustableSorting;
	}

	/**
	 * @return bool
	 */
	public function getShowReferenceStatus(): bool
	{
		return $this->_showReferenceStatus;
	}

	/**
	 * @param bool $showReferenceStatus
	 */
	public function setShowReferenceStatus(bool $showReferenceStatus)
	{
		$this->_showReferenceStatus = $showReferenceStatus;
	}
}
