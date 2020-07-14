<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

namespace onOffice\WPlugin\Region;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

class Region
{
	/** @var string */
	private $_id = null;

	/** @var string */
	private $_name = '';

	/** @var string */
	private $_description = '';

	/** @var string */
	private $_language = '';

	/** @var int[] */
	private $_postalCodes = [];

	/** @var string */
	private $_state = '';

	/** @var string */
	private $_country = '';

	/** @var Region[] */
	private $_children = [];

	/** @var null|Region */
	private $_pParent = null;

	/**
	 *
	 * @param string $id
	 * @param string $language
	 *
	 */

	public function __construct(string $id, string $language)
	{
		$this->_id = $id;
		$this->_language = $language;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getId(): string
	{
		return $this->_id;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getName(): string
	{
		return $this->_name;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getDescription(): string
	{
		return $this->_description;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getLanguage(): string
	{
		return $this->_language;
	}


	/**
	 *
	 * @return int[]
	 *
	 */

	public function getPostalCodes(): array
	{
		return $this->_postalCodes;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getState(): string
	{
		return $this->_state;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getCountry(): string
	{
		return $this->_country;
	}


	/**
	 *
	 * @return Region[]
	 *
	 */

	public function getChildren(): array
	{
		return $this->_children;
	}


	/**
	 *
	 * @param string $name
	 *
	 */

	public function setName(string $name)
	{
		$this->_name = $name;
	}


	/**
	 *
	 * @param string $description
	 *
	 */

	public function setDescription(string $description)
	{
		$this->_description = $description;
	}


	/**
	 *
	 * @param array $postalcodes
	 *
	 */

	public function setPostalCodes(array $postalcodes)
	{
		$this->_postalCodes = $postalcodes;
	}


	/**
	 *
	 * @param string $state
	 *
	 */

	public function setState(string $state)
	{
		$this->_state = $state;
	}


	/**
	 *
	 * @param string $country
	 *
	 */

	public function setCountry(string $country)
	{
		$this->_country = $country;
	}


	/**
	 *
	 * @param Region[] $children
	 *
	 */

	public function setChildren(array $children)
	{
		$this->_children = $children;
	}

	/**
	 * @return Region|null
	 */
	public function getParent()
	{
		return $this->_pParent;
	}

	/**
	 * @param Region|null $pParent
	 */
	public function setParent(Region $pParent)
	{
		$this->_pParent = $pParent;
	}

}
