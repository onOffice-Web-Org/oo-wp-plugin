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

namespace onOffice\WPlugin\Field\CustomLabel;

use onOffice\WPlugin\Types\Field;

/**
 *
 */

abstract class CustomLabelModelBase
{
	/** @var Field */
	private $_pField = null;

	/** @var int */
	private $_formId = 0;

	/** @var int */
	private $_defaultsId = 0;


	/**
	 *
	 * @param int $formId
	 * @param Field $pField
	 *
	 */

	public function __construct(int $formId, Field $pField)
	{
		$this->_pField = $pField;
		$this->_formId = $formId;
	}


	/**
	 *
	 * @return Field
	 *
	 */

	public function getField(): Field
	{
		return $this->_pField;
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getFormId(): int
	{
		return $this->_formId;
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getDefaultsId(): int
	{
		return $this->_defaultsId;
	}


	/**
	 *
	 * @param int $defaultsId
	 *
	 */

	public function setDefaultsId(int $defaultsId)
	{
		$this->_defaultsId = $defaultsId;
	}
}