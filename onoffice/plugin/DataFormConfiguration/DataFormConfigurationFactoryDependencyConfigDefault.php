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

namespace onOffice\WPlugin\DataFormConfiguration;

use onOffice\WPlugin\Record\RecordManagerReadForm;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DataFormConfigurationFactoryDependencyConfigDefault
	implements DataFormConfigurationFactoryDependencyConfigBase
{
	/** @var RecordManagerReadForm */
	private $_pRecordManagerRead = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pRecordManagerRead = new RecordManagerReadForm();
	}


	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */

	public function getFieldsByFormId(int $formId): array
	{
		return $this->_pRecordManagerRead->readFieldsByFormId($formId);
	}


	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */

	public function getMainRowById(int $formId): array
	{
		return $this->_pRecordManagerRead->getRowById($formId);
	}


	/**
	 *
	 * @param string $name
	 * @return array
	 *
	 */

	public function getMainRowByName(string $name): array
	{
		return $this->_pRecordManagerRead->getRowByName($name);
	}
}
