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

namespace onOffice\WPlugin\Record;

/**
 *
 */

class RecordManagerUpdateTemplateColumn
	extends RecordManager
{
	/** @var string */
	private $_templateUrl = null;

	/** @var string */
	private $_customTemplateUrl = null;

	/** @var string */
	private $_mainTable = '';

	/**
	 * @param string $templateUrl
	 */
	public function setTemplateUrl(string $templateUrl)
	{
		$this->_templateUrl = $templateUrl;
	}

	/**
	 * @param string $customTemplateUrl
	 */
	public function setCustomTemplateUrl(string $customTemplateUrl)
	{
		$this->_customTemplateUrl = $customTemplateUrl;
	}

	/**
	 * @return bool
	 */
	public function updateDataTemplateColumn(): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$whereTemplateUrl = ['template' => $this->_templateUrl];
		$suppressErrors = $pWpDb->suppress_errors();
		$result = $pWpDb->update($prefix.$this->getMainTable(), ['template' => $this->_customTemplateUrl], $whereTemplateUrl);
		$pWpDb->suppress_errors($suppressErrors);

		return $result !== false;
	}

	/** @return string */
	public function getMainTable(): string
		{ return $this->_mainTable; }

	/** @param string $mainTable */
	public function setMainTable(string $mainTable)
	{ 
		switch ($mainTable) {
			case RecordManagerFactory::TYPE_ADDRESS:
				$this->_mainTable = self::TABLENAME_LIST_VIEW_ADDRESS; 
				break;
			case RecordManagerFactory::TYPE_ESTATE:
				$this->_mainTable = self::TABLENAME_LIST_VIEW; 
				break;
			case RecordManagerFactory::TYPE_FORM:
				$this->_mainTable = self::TABLENAME_FORMS; 
				break;
			default:
				break;
		}
	}
}
