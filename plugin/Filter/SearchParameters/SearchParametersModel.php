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

namespace onOffice\WPlugin\Filter\SearchParameters;


class SearchParametersModel
{
	/** @var array */
	private $_parameters = array();

	/** @var array */
	private $_allowedGetParameters = array();

	/** @var bool */
	private $_filter = true;


	/**
	 *
	 * @param array $parameters
	 *
	 */

	public function setParameters(array $parameters)
	{
		$this->_parameters = $parameters;
	}


	/**
	 *
	 * @param string $key
	 * @param string $value
	 *
	 */

	public function setParameter($key, $value)
	{
		$this->_parameters[$key] = $value;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getParameters(): array
	{
		$parameters = $this->_parameters;

		if ($this->_filter) {
			$parameters = array_filter($this->filterParameters( $parameters ));
		}

		return $parameters;
	}


	/**
	 *
	 * @param array $parameters
	 * @return array
	 *
	 */

	private function filterParameters(array $parameters): array
	{
		$whitelist = array_merge($this->_allowedGetParameters, ['oo_formid', 'oo_formno']);
		$whitelistKey = array_flip($whitelist);

		return array_intersect_key($parameters, $whitelistKey);
	}


	/**  @param array $parameters */
	public function setAllowedGetParameters(array $parameters)
		{ $this->_allowedGetParameters = $parameters; }


	/** @param string $key */
	public function addAllowedGetParameter($key)
		{ $this->_allowedGetParameters []= $key;}


	/** @param bool $enable */
	public function enableFilter($enable)
		{ $this->_filter = (bool) $enable;}


	/** @return array */
	public function getAllowedGetParameters(): array
		{ return $this->_allowedGetParameters;	}


	/** @return bool */
	public function getFilter(): bool
		{ return $this->_filter; }

}