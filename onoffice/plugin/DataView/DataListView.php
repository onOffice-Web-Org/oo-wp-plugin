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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataListView
{
	/** @var int */
	private $_id = null;

	/** @var string */
	private $_name = null;

	/** @var string[] */
	private $_fields = array();

	/** @var string[] */
	private $_pictureTypes = array();

	/** @var string */
	private $_filtername = null;

	/** @var string */
	private $_sortorder = null;

	/** @var string */
	private $_sortby = null;

	/** @var int */
	private $_recordsPerPage = 5;

	/** @var bool */
	private $_showStatus = false;

	/** @var bool */
	private $_isReference = false;

	/** @var string */
	private $_template = 'default';

	/** @var string */
	private $_expose = null;


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

	/** @return array */
	public function getFields()
		{ return $this->_fields; }

	/** @return array */
	public function getPictureTypes()
		{ return $this->_pictureTypes; }

	/** @return string */
	public function getFiltername()
		{ return $this->_filtername; }

	/** @return string */
	public function getSortorder()
		{ return $this->_sortorder; }

	/** @return string */
	public function getSortby()
		{ return $this->_sortby; }

	/** @return bool */
	public function getShowStatus()
		{ return $this->_showStatus; }

	/** @return bool */
	public function getIsReference()
		{ return $this->_isReference; }

	/** @return itn */
	public function getRecordsPerPage()
		{ return $this->_recordsPerPage; }

	/** @return string */
	public function getTemplate()
		{ return $this->_template; }

	/** @return string */
	public function getExpose()
		{ return $this->_expose; }

	/** @param array $fields */
	public function setFields(array $fields)
		{ $this->_fields = $fields; }

	/** @param array $pictureTypes */
	public function setPictureTypes(array $pictureTypes)
		{ $this->_pictureTypes = $pictureTypes; }

	/** @param string $filtername */
	public function setFiltername($filtername)
		{ $this->_filtername = $filtername; }

	/** @param string $sortorder */
	public function setSortorder($sortorder)
		{ $this->_sortorder = $sortorder; }

	/** @param string $sortby */
	public function setSortby($sortby)
		{ $this->_sortby = $sortby; }

	/** @param string $showStatus */
	public function setShowStatus($showStatus)
		{ $this->_showStatus = $showStatus; }

	/** @param string $isReference */
	public function setIsReference($isReference)
		{ $this->_isReference = $isReference; }

	/** @param string $template */
	public function setTemplate($template)
		{ $this->_template = $template; }

	/** @param string $expose */
	public function setExpose($expose)
		{ $this->_expose = $expose; }

	/** @param int $recordsPerPage */
	public function setRecordsPerPage($recordsPerPage)
		{ $this->_recordsPerPage = $recordsPerPage; }
}
