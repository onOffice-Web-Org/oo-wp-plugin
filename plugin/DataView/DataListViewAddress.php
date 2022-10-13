<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\WPlugin\DataView;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\ViewProperty;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DataListViewAddress
	implements DataViewFilterableFields, ViewProperty
{
	/** */
	const FIELDS = 'fields';

	/** @var int */
	private $_id = 0;

	/** @var string */
	private $_name = '';

	/** @var string[] */
	private $_fields = [];

	/** @var bool */
	private $_showPhoto = false;

	/** @var string */
	private $_filterId = 0;

	/** @var string */
	private $_sortorder = '';

	/** @var string */
	private $_sortby = '';

	/** @var int */
	private $_recordsPerPage = 5;

	/** @var string */
	private $_template = '';

	/** @var array */
	private $_filterableFields = [];

	/** @var array */
	private $_filterableHiddenFields = [];


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

	/** @return string[] */
	public function getFields(): array
		{ return $this->_fields; }

	/** @return bool */
	public function getShowPhoto(): bool
		{ return $this->_showPhoto; }

	/** @return int */
	public function getFilterId(): int
		{ return $this->_filterId; }

	/** @return string */
	public function getSortorder(): string
		{ return $this->_sortorder; }

	/** @return string */
	public function getSortby(): string
		{ return $this->_sortby; }

	/** @return int */
	public function getRecordsPerPage(): int
		{ return $this->_recordsPerPage; }

	/** @return string */
	public function getTemplate(): string
		{ return $this->_template; }

	/** @param array $fields */
	public function setFields(array $fields)
		{ $this->_fields = $fields; }

	/** @param bool $showPhoto */
	public function setShowPhoto(bool $showPhoto)
		{ $this->_showPhoto = $showPhoto; }

	/** @param int $filterId */
	public function setFilterId(int $filterId)
		{ $this->_filterId = $filterId; }

	/** @param string $sortorder */
	public function setSortorder(string $sortorder)
		{ $this->_sortorder = $sortorder; }

	/** @param string $sortby */
	public function setSortby(string $sortby)
		{ $this->_sortby = $sortby; }

	/** @param int $recordsPerPage */
	public function setRecordsPerPage(int $recordsPerPage)
		{ $this->_recordsPerPage = $recordsPerPage; }

	/** @param string $template */
	public function setTemplate(string $template)
		{ $this->_template = $template; }

	/** @return array */
	public function getFilterableFields(): array
		{ return $this->_filterableFields; }

	/** @param array $filterableFields */
	public function setFilterableFields(array $filterableFields)
		{ $this->_filterableFields = $filterableFields; }

	/** @return array */
	public function getHiddenFields(): array
		{ return $this->_filterableHiddenFields; }

	/** @param array $filterableHiddenFields */
	public function setHiddenFields(array $filterableHiddenFields)
		{ $this->_filterableHiddenFields = $filterableHiddenFields; }

	/** @return string */
	public function getModule(): string
		{ return onOfficeSDK::MODULE_ADDRESS; }

	/** @return string */
	public function getViewType(): string
		{ return ''; }
}
