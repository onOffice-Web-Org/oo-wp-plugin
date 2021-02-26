<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
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

namespace onOffice\WPlugin\Field\CustomLabel\ModelToOutputConverter;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

/**
 *
 */
class CustomLabelModelToOutputConverterFactory
{
	/** @var Container */
	private $_pContainer;

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
	 * @return CustomLabelModelToOutputConverterField
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	public function createForField(): CustomLabelModelToOutputConverterField
	{
		return $this->_pContainer->get(CustomLabelModelToOutputConverterField::class);
	}

}