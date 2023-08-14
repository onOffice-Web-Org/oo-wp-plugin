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

namespace onOffice\WPlugin\ScriptLoader;

/**
 *
 */

class IncludeFileModel
{
	const TYPE_SCRIPT = 'script';

	const TYPE_STYLE = 'style';

	const LOAD_ASYNC = 'async';

	const LOAD_DEFER = 'defer';

	/** @var string */
	private $_identifier = '';

	/** @var string */
	private $_filePath = '';

	/** @var array */
	private $_dependencies = [];

	/** @var bool */
	private $_loadInFooter = false;

	/** @var string */
	private $_type = '';


	/**
	 *
	 * @param string $identifier
	 * @param string $filePath
	 *
	 */

	public function __construct(string $type, string $identifier, string $filePath)
	{
		$this->_identifier = $identifier;
		$this->_filePath = $filePath;
		$this->_type = $type;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getIdentifier(): string
	{
		return $this->_identifier;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getFilePath(): string
	{
		return $this->_filePath;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getDependencies(): array
	{
		return $this->_dependencies;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getLoadInFooter(): bool
	{
		return $this->_loadInFooter;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getType(): string
	{
		return $this->_type;
	}


	/**
	 *
	 * @param array $dependencies
	 * @return $this
	 *
	 */

	public function setDependencies(array $dependencies): self
	{
		$this->_dependencies = $dependencies;
		return $this;
	}


	/**
	 *
	 * @param bool $loadInFooter
	 * @return $this
	 *
	 */

	public function setLoadInFooter(bool $loadInFooter): self
	{
		$this->_loadInFooter = $loadInFooter;
		return $this;
	}
}