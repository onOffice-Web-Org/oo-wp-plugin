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

declare(strict_types=1);

namespace onOffice\WPlugin\Filter;

use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\Types\GeoCoordinates;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FilterConfigurationSimilarEstates
{
	/** @var DataViewSimilarEstates */
	private $_pDataViewSimilarEstates = null;

	/** @var string */
	private $_postalCode = '';

    /** @var string */
    private $_showArchived = '';

	/** @var GeoCoordinates */
	private $_pGeoCoordinates = null;

	/** @var string */
	private $_street = '';

	/** @var string */
	private $_city = '';

	/** @var string */
	private $_country = '';

	/** @var string */
	private $_estateKind = '';

	/** @var string */
	private $_marketingMethod = '';


	/**
	 *
	 * @param DataViewSimilarEstates $pDataViewSimilarEstates
	 *
	 */

	public function __construct(DataViewSimilarEstates $pDataViewSimilarEstates)
	{
		$this->_pDataViewSimilarEstates = $pDataViewSimilarEstates;
		$this->_pGeoCoordinates = new GeoCoordinates();
	}


	/**
	 *
	 * @param GeoCoordinates $pGeoCoordinates
	 *
	 */

	public function setGeoCoordinates(GeoCoordinates $pGeoCoordinates)
	{
		$this->_pGeoCoordinates = $pGeoCoordinates;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getPostalCode(): string
	{
		return $this->_postalCode;
	}

    /**
     *
     * @return string
     *
     */

    public function getShowArchived(): string
    {
        return $this->_showArchived;
    }


	/**
	 *
	 * @return int
	 *
	 */

	public function getRadius(): int
	{
		return $this->_pDataViewSimilarEstates->getRadius();
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getAmount(): int
	{
		return $this->_pDataViewSimilarEstates->getRecordsPerPage();
	}


	/**
	 *
	 * @return GeoCoordinates
	 *
	 */

	public function getGeoCoordinates(): GeoCoordinates
	{
		return $this->_pGeoCoordinates;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getEstateKind(): string
	{
		return $this->_estateKind;
	}


	/**
	 *
	 * @param string $estateKind
	 *
	 */

	public function setEstateKind(string $estateKind)
	{
		$this->_estateKind = $estateKind;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getMarketingMethod(): string
	{
		return $this->_marketingMethod;
	}


	/**
	 *
	 * @param string $marketingMethod
	 *
	 */

	public function setMarketingMethod(string $marketingMethod)
	{
		$this->_marketingMethod = $marketingMethod;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getStreet(): string
	{
		return $this->_street;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getCity(): string
	{
		return $this->_city;
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
	 * @param string $street
	 *
	 */

	public function setStreet(string $street)
	{
		$this->_street = $street;
	}


	/**
	 *
	 * @param string $city
	 *
	 */

	public function setCity(string $city)
	{
		$this->_city = $city;
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
	 * @param string $postalCode
	 *
	 */

	public function setPostalCode(string $postalCode)
	{
		$this->_postalCode = $postalCode;
	}

    /**
     *
     * @param string $showArchived
     *
     */

    public function setShowArchive(string $showArchived)
    {
        $this->_showArchived= $showArchived;
    }


	/**
	 *
	 * @return DataViewSimilarEstates
	 *
	 */

	public function getDataViewSimilarEstates(): DataViewSimilarEstates
	{
		return $this->_pDataViewSimilarEstates;
	}
}
