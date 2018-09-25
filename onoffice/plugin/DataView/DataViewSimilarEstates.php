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

class DataViewSimilarEstates
{
	/** */
	const FIELD_SAME_KIND = 'same_kind';

	/** */
	const FIELD_SAME_MARKETING_METHOD = 'same_maketing_method';

	/** */
	const FIELD_SAME_POSTAL_CODE = 'same_postal_code';

	/** */
	const FIELD_RADIUS = 'radius';

	/** */
	const FIELD_AMOUNT = 'amount';


	/** @var bool */
	private $_sameEstateKind = false;

	/** @var bool */
	private $_sameMarketingMethod = false;

	/** @var bool */
	private $_samePostalCode = false;

	/** @var int */
	private $_radius = 10;

	/** @var int */
	private $_amount = 5;


	/** @param bool $sameEstateKind */
	public function setSameEstateKind(bool $sameEstateKind)
		{ $this->_sameEstateKind = $sameEstateKind; }

	/** @param bool $sameMarketingMethod */
	public function setSameMarketingMethod(bool $sameMarketingMethod)
		{ $this->_sameMarketingMethod = $sameMarketingMethod; }

	/** @param bool $samePostalCode */
	public function setSamePostalCode(bool $samePostalCode)
		{ $this->_samePostalCode = $samePostalCode; }

	/** @param int $radius */
	public function setRadius(int $radius)
		{ $this->_radius = $radius; }

	/** @param bool $amount */
	public function setAmount(int $amount)
		{ $this->_amount = $amount; }

	/** @return bool */
	public function getSameEstateKind(): bool
		{ return $this->_sameEstateKind; }

	/** @return bool */
	public function getSameMarketingMethod(): bool
		{ return $this->_sameMarketingMethod; }

	/** @return bool */
	public function getSamePostalCode(): bool
		{ return $this->_samePostalCode; }

	/** @return int */
	public function getRadius(): int
		{ return $this->_radius; }

	/** @return int */
	public function getAmount(): int
		{ return $this->_amount; }
}
