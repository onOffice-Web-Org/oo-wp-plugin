<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

namespace onOffice\WPlugin\DataView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class DataAddressDetailView
	implements DataViewAddress
{
	/** */
	const FIELDS = 'fields';

	/** */
	const ESTATE_FIELDS = 'estateFields';

	/** */
	const TEMPLATE = 'template';

	/** */
	const PICTURES = 'pictures';

	/** */
	const SHOW_ESTATES_STATUS = 'showEstatesStatus';

	/** */
	const ENABLE_LINKED_ESTATES = 'enableLinkedEstates';

	/** */
	const REFERENCE_ESTATES = 'referenceEstates';

	/** */
	const INPUT_FILTERID = 'filterId';

	/** */
	const RECORDS_PER_PAGE = 'recordsPerPage';

	/** */
	const SHOW_PRICE_ON_REQUEST = 'showPriceOnRequest';

	/** */
	const SHOW_ESTATES_MAP = 'showMap';

	/** */
	const FIELD_CUSTOM_LABEL = 'oo_plugin_fieldconfig_address_translated_labels';

	/** @var string[] */
	private $_fields = [
		'Anrede',
		'Vorname',
		'Name',
		'Zusatz1',
		'Email',
		'Telefon1',
		'Telefax1',
	];

	/** @var string[] */
	private $_estateFields = [
		'objekttitel',
		'objektart',
		'objekttyp',
		'vermarktungsart',
		'plz',
		'ort',
		'objektnr_extern',
		'wohnflaeche',
		'grundstuecksflaeche',
		'nutzflaeche',
		'anzahl_zimmer',
		'anzahl_badezimmer',
		'kaufpreis',
		'kaltmiete',
		'objektbeschreibung',
		'lage',
		'ausstatt_beschr',
		'sonstige_angaben',
		'baujahr',
		'endenergiebedarf',
		'energieverbrauchskennwert',
		'energieausweistyp',
		'energieausweis_gueltig_bis',
		'energyClass',
		'aussen_courtage',
		'kaution',
	];

	/** @var string[] */
	private $_pictureTypes = [];

	/** @var string */
	private $_template = '';

	/** @var int */
	private $_pageId = 0;

	/** @var array */
	private $_pageIdsHaveDetailShortCode = [];

	/** @var array */
	private $_customLabel = [];

	/** @var bool */
	private $_enableLinkedEstates = false;

	/** @var string */
	private $_showReferenceEstates = '0';

	/** @var int */
	private $_filterId = 0;

	/** @var int */
	private $_numberRecordsPerPage = 12;

	/** @var bool */
	private $_showEstateStatus = false;

	/** @var bool */
	private $_showPriceOnRequest = false;

	/** @var bool */
	private $_showEstatesMap = false;

	/** @return int */
	public function getPageId(): int
		{ return $this->_pageId; }

	/** @param int $pageId */
	public function setPageId(int $pageId)
		{ $this->_pageId = $pageId; }

	/** @return string */
	public function getName(): string
		{ return 'detail'; }

	/** @return string[] */
	public function getFields(): array
		{ return $this->_fields; }

	/** @return string[] */
	public function getEstateFields(): array
		{ return $this->_estateFields; }

	/** @param array $estateFields */
	public function setEstateFields(array $estateFields)
		{ $this->_estateFields = $estateFields; }

	/** @return array */
	public function getPictureTypes(): array
		{ return $this->_pictureTypes; }

	/** @return string */
	public function getTemplate(): string
		{ return $this->_template; }

	/** @param array $fields */
	public function setFields(array $fields)
		{ $this->_fields = $fields; }

	/** @param array $pictureTypes */
	public function setPictureTypes(array $pictureTypes)
		{ $this->_pictureTypes = $pictureTypes; }

	/** @param string $template */
	public function setTemplate(string $template)
		{ $this->_template = $template; }

	/** @return array */
	public function getPageIdsHaveDetailShortCode(): array
		{ return $this->_pageIdsHaveDetailShortCode; }

	/** @param int $pageId */
	public function addToPageIdsHaveDetailShortCode(int $pageId)
		{ $this->_pageIdsHaveDetailShortCode[$pageId] = $pageId; }

	/** @param int $pageId */
	public function removeFromPageIdsHaveDetailShortCode(int $pageId)
		{ unset($this->_pageIdsHaveDetailShortCode[$pageId]); }

	/** @return array */
	public function getCustomLabels()
		{ return $this->_customLabel; }

	/** @param array $customLabel */
	public function setCustomLabels(array $customLabel)
		{ $this->_customLabel = $customLabel; }

	/** @return bool */
	public function getEnableLinkedEstates(): bool
		{ return $this->_enableLinkedEstates; }

	/** @param bool $enableLinkedEstates */
	public function setEnableLinkedEstates(bool $enableLinkedEstates)
		{ $this->_enableLinkedEstates = $enableLinkedEstates; }

	/** @return string */
	public function getShowReferenceEstate(): string
	{ return $this->_showReferenceEstates; }

	/** @param string $referenceEstate */
	public function setShowReferenceEstate(string $referenceEstate)
		{ $this->_showReferenceEstates = $referenceEstate; }

	/** @return int */
	public function getFilterId(): int
		{ return $this->_filterId; }

	/** @param int $filterId */
	public function setFilterId(int $filterId)
		{ $this->_filterId = $filterId; }

	/** @return int */
	public function getRecordsPerPage(): int
		{ return $this->_numberRecordsPerPage; }

	/** @param int $numberRecords */
	public function setRecordsPerPage(int $numberRecords)
		{ $this->_numberRecordsPerPage = $numberRecords; }

	/** @return bool */
	public function getShowEstateStatus(): bool
		{ return $this->_showEstateStatus;}

	/** @param bool $estateStatus */
	public function setShowEstateStatus(bool $estateStatus)
		{ $this->_showEstateStatus = $estateStatus; }

	/** @return bool */
	public function getShowPriceOnRequest(): bool
		{ return $this->_showPriceOnRequest; }

	/** @param bool $showPriceOnRequest */
	public function setShowPriceOnRequest(bool $showPriceOnRequest)
		{ $this->_showPriceOnRequest = $showPriceOnRequest; }

	/** @return bool */
	public function getShowEstateMap(): bool
		{ return $this->_showEstatesMap; }

	/** @param bool $showEstatesMap */
	public function setShowEstateMap(bool $showEstatesMap)
		{ $this->_showEstatesMap = $showEstatesMap; }
}
