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

namespace onOffice\WPlugin\DataView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DataListViewAddress
{
	/** @var int */
	private $_id = null;

	/** @var string */
	private $_name = null;

	/** @var string[] */
	private $_fields = array();

	/** @var bool */
	private $_showPhoto = false;

	/** @var string */
	private $_filterId = null;

	/** @var string */
	private $_sortorder = null;

	/** @var string */
	private $_sortby = null;

	/** @var int */
	private $_recordsPerPage = 5;

	/** @var string */
	private $_template = '';


	/**
	 *
	 * @param int $id
	 * @param string $name
	 *
	 */

	public function __construct($id, $name)
	{
		$this->_id = $id;
		$this->_name = $name;
	}


	/** @return int */
	public function getId()
		{ return $this->_id; }

	/** @return string */
	public function getName()
		{ return $this->_name; }

	/** @return string[] */
	public function getFields()
		{ return $this->_fields; }

	/** @return bool */
	public function getShowPhoto()
		{ return $this->_showPhoto; }

	/** @return int */
	public function getFilterId()
		{ return $this->_filterId; }

	/** @return string */
	public function getSortorder()
		{ return $this->_sortorder; }

	/** @return string */
	public function getSortby()
		{ return $this->_sortby; }

	/** @return int */
	public function getRecordsPerPage()
		{ return $this->_recordsPerPage; }

	/** @return string */
	public function getTemplate()
		{ return $this->_template; }

	/** @param array $fields */
	public function setFields(array $fields)
		{ $this->_fields = $fields; }

	/** @param bool $showPhoto */
	public function setShowPhoto($showPhoto)
		{ $this->_showPhoto = (bool)$showPhoto; }

	/** @param int $filterId */
	public function setFilterId($filterId)
		{ $this->_filterId = $filterId; }

	/** @param string $sortorder */
	public function setSortorder($sortorder)
		{ $this->_sortorder = $sortorder; }

	/** @param string $sortby */
	public function setSortby($sortby)
		{ $this->_sortby = $sortby; }

	/** @param int $recordsPerPage */
	public function setRecordsPerPage($recordsPerPage)
		{ $this->_recordsPerPage = $recordsPerPage; }

	/** @param string $template */
	public function setTemplate($template)
		{ $this->_template = $template; }
}
