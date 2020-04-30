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

namespace onOffice\WPlugin\Form;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Form;

/**
 *
 */

class FormBuilder
{
	/** @var Container */
	private $_pContainer;

	/**
	 * @param Container $pContainer
	 */
	public function __construct(Container $pContainer)
	{
		$this->_pContainer = $pContainer;
	}

	/**
	 * @param string $formName
	 * @param string $type
	 * @return Form
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFormException
	 */
	public function build(string $formName, string $type): Form
	{
		// @codeCoverageIgnoreStart
		return new Form($formName, $type, $this->_pContainer);
	} // @codeCoverageIgnoreEnd
}
