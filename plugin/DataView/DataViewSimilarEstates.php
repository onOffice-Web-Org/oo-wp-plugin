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

use onOffice\WPlugin\Types\ImageTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataViewSimilarEstates
	implements DataView
{
	/** */
	const FIELDS = 'fields';

	/** @var string[] */
	private $_fields = [
		'Id',
		'objekttitel',
		'objektnr_extern',
		'regionaler_zusatz',
		'kaufpreis',
		'wohnflaeche',
		'anzahl_zimmer',
		'kaltmiete',
		'ort',
		'plz',
		'grundstuecksflaeche',
		'nutzflaeche'
	];

	/** */
	const FIELD_SAME_KIND = 'same_kind';

	/** */
	const FIELD_SAME_MARKETING_METHOD = 'same_maketing_method';

	/** */
	const FIELD_DO_NOT_SHOW_ARCHIVED = 'do_not_show_archived';

	/** */
	const FIELD_DO_NOT_SHOW_REFERENCE = 'do_not_show_reference';

	/** */
	const FIELD_SAME_POSTAL_CODE = 'same_postal_code';

	/** */
	const FIELD_RADIUS = 'radius';

	/** */
	const FIELD_AMOUNT = 'amount';

	/** */
	const FIELD_SIMILAR_ESTATES_TEMPLATE = 'similar_estates_template';


	/** @var bool */
	private $_sameEstateKind = true;

	/** @var bool */
	private $_sameMarketingMethod = true;

	/** @var bool */
	private $_samePostalCode = false;

	/** @var bool */
	private $_doNotShowArchived = false;

	/** @var bool */
	private $_doNotShowReference = false;

	/** @var int */
	private $_radius = 10;

	/** @var int */
	private $_recordsPerPage = 5;

	/** @var string */
	private $_template = '';


	/** @param bool $sameEstateKind */
	public function setSameEstateKind(bool $sameEstateKind)
		{ $this->_sameEstateKind = $sameEstateKind; }

	/** @param bool $sameMarketingMethod */
	public function setSameMarketingMethod(bool $sameMarketingMethod)
		{ $this->_sameMarketingMethod = $sameMarketingMethod; }

	/** @param bool $samePostalCode */
	public function setSamePostalCode(bool $samePostalCode)
		{ $this->_samePostalCode = $samePostalCode; }

	/** @param bool $doNotShowArchived */
	public function setDoNotShowArchived(bool $doNotShowArchived)
		{ $this->_doNotShowArchived = $doNotShowArchived; }

	/** @param bool $doNotShowReference */
	public function setDoNotShowReference(bool $doNotShowReference)
		{ $this->_doNotShowReference = $doNotShowReference; }

	/** @param int $radius */
	public function setRadius(int $radius)
		{ $this->_radius = $radius; }

	/** @param int $recordsPerPage */
	public function setRecordsPerPage(int $recordsPerPage)
		{ $this->_recordsPerPage = $recordsPerPage; }

	/** @return bool */
	public function getSameEstateKind(): bool
		{ return $this->_sameEstateKind; }

	/** @return bool */
	public function getSameMarketingMethod(): bool
		{ return $this->_sameMarketingMethod; }

	/** @return bool */
	public function getSamePostalCode(): bool
		{ return $this->_samePostalCode; }

	/** @return bool */
	public function getDoNotShowArchived(): bool
		{ return $this->_doNotShowArchived; }

	/** @return bool */
	public function getDoNotShowReference(): bool
		{ return $this->_doNotShowReference; }

	/** @return int */
	public function getRadius(): int
		{ return $this->_radius; }

	/** @return int */
	public function getRecordsPerPage(): int
		{ return $this->_recordsPerPage; }

	/** @return array */
	public function getAddressFields(): array
		{ return []; }

	/** @return string */
	public function getExpose(): string
		{ return ''; }

	/** @return array */
	public function getFields(): array
	{ return $this->_fields;}

	/** @param array $fields */
	public function setFields(array $fields)
		{ $this->_fields = $fields; }

	/** @return string */
	public function getName(): string
		{ return 'SimilarEstates'; }

	/** @return array */
	public function getPictureTypes(): array
		{ return [ImageTypes::TITLE]; }

	/** @return string */
	public function getTemplate(): string
		{ return $this->_template; }

	/** @param string $template */
	public function setTemplate(string $template)
		{ $this->_template = $template; }

	/** @return array */
	public function getSortBy()
		{ return ['Id' => 'ASC']; }

	/** @return string */
	public function getSortOrder()
		{ return null; }

	/** @return int */
	public function getFilterId()
		{ return null; }

	/** @return bool */
	public function getRandom(): bool
		{ return false;	}
}