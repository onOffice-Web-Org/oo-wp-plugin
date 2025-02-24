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

declare(strict_types=1);

namespace onOffice\WPlugin\DataView;

use onOffice\WPlugin\Types\LinksTypes;
use onOffice\WPlugin\Types\MovieLinkTypes;
use onOffice\WPlugin\Types\ImageTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 * DO NOT MOVE OR RENAME - NAME AND/OR NAMESPACE MAY BE USED IN SERIALIZED DATA
 *
 */

class DataDetailView
	implements DataView
{
	/** */
	const ENABLE_SIMILAR_ESTATES = 'enablesimilarestates';

	/** */
	const PICTURES = 'pictures';

	/** */
	const FIELDS = 'fields';

	/** */
	const ADDRESSFIELDS = 'addressfields';

	/** */
	const FIELD_CUSTOM_LABEL = 'oo_plugin_fieldconfig_estate_translated_labels';

	/** */
	const FIELD_PRICE_ON_REQUEST = 'show_price_on_request';

	/** */
	const FIELD_CONTACT_PERSON = 'contact_person';

	/** @var string */
	const SHOW_ALL_CONTACT_PERSONS = '0';

	/** @var int */
	const SHOW_MAIN_CONTACT_PERSON = '1';

	/** */
	const FIELD_TOTAL_COSTS_CALCULATOR = 'show_total_costs_calculator';

	/** */
	const NOTARY_FEES = 1.5;

	/** */
	const LAND_REGISTER_ENTRY = 0.5;

	/** @var string[] */
	private $_fields = [
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
	private $_addressFields = [
		'imageUrl',
		'Anrede',
		'Vorname',
		'Name',
		'Zusatz1',
		'Strasse',
		'Plz',
		'Ort',
		'Telefon1',
		'mobile',
		'defaultemail',
	];

	/** @var string[] */
	private $_defaultAddressFields = [
		'Anrede',
		'Vorname',
		'Name',
		'Zusatz1',
		'Strasse',
		'Plz',
		'Ort',
		'Telefon1',
		'mobile',
		'defaultemail',
		'imageUrl',
	];

	/** @var string[] */
	const GENERAL_ENERGY_FIELDS = ['energieausweistyp', 'endenergiebedarf', 'energieverbrauchskennwert','energyClass'];

	/** @var string[] */
	const PRIVATE_ENERGY_FIELDS = ['energietraeger', 'baujahr', 'co2ausstoss', 'co2_Emissionsklasse'];

	/** @var string[] */
	const AUSTRIA_ENERGY_FIELDS = ['ea_hwb_at', 'ea_hwb_klasse_at', 'ea_fgee_klasse_at', 'ea_fgee_at'];

	/** @var string */
	const AUSTRIA_LANGUAGE_CODE = 'de_AT';

	/** @var string[] */
	const LANGUAGE_CODE_EU_COUNTRIES = [
		'nl_BE',
		'fr_BE',
		'de_BE',
		'bg_BG',
		'hr_HR',
		'el_CY',
		'tr_CY',
		'cs_CZ',
		'da_DK',
		'et_EE',
		'fi_FI',
		'sv_FI',
		'fr_FR',
		'de_DE',
		'el_GR',
		'hu_HU',
		'en_IE',
		'ga_IE',
		'it_IT',
		'lv_LV',
		'lt_LT',
		'lb_LU',
		'de_LU',
		'fr_LU',
		'mt_MT',
		'en_MT',
		'nl_NL',
		'pl_PL',
		'pt_PT',
		'ro_RO',
		'sk_SK',
		'sl_SI',
		'es_ES',
		'ca_ES',
		'gl_ES',
		'eu_ES',
		'sv_SE',
		'en_GB',
		'gd_GB',
		'cy_GB'
	];

	/** @var string[] */
	private $_pictureTypes = [
		ImageTypes::TITLE,
		ImageTypes::PHOTO,
		ImageTypes::PHOTO_BIG,
		ImageTypes::PANORAMA,
		ImageTypes::GROUNDPLAN,
		ImageTypes::LOCATION_MAP,
		ImageTypes::ENERGY_PASS_RANGE,
	];

	/** @var string */
	private $_template = '';

	/** @var bool */
	private $_accessControls = true;

	/** @var bool */
	private $_restrictAccess = true;

	/** @var string */
	private $_shortCodeForm = '';

	/** @var string */
	private $_expose = '';

	/** @var int */
	private $_pageId = 0;

	/** @var array */
	private $_pageIdsHaveDetailShortCode = [];

	/** @var bool */
	private $_showStatus = 1;

	/** @var int */
	private $_movieLinks = MovieLinkTypes::MOVIE_LINKS_PLAYER;

	/** @var string */
	private $_oguloLinks = LinksTypes::LINKS_EMBEDDED;

	/** @var string */
	private $_objectLinks = LinksTypes::LINKS_DEACTIVATED;

	/** @var string */
	private $_links = LinksTypes::LINKS_DEACTIVATED;

	/** @var bool */
	private $_dataDetailViewActive = false;

	/** @var DataViewSimilarEstates */
	private $_pDataViewSimilarEstates = null;

	/** @var string[] */
	private $_customLabel = [];

	/** @var bool */
	private $_showPriceOnRequest = false;

	/** @var string[] */
	private $_contactImageTypes = [
		ImageTypes::PASSPORTPHOTO
	];

	/** @var bool */
	private $_showTotalCostsCalculator = false;

	/** @var bool */
	private $_showEnergyCertificate = false;

    /** @var string[] */
    private $_priceFields = [
        'kaufpreis',
        'erbpacht',
        'nettokaltmiete',
        'warmmiete',
        'pacht',
        'kaltmiete',
        'miete_pauschal',
        'saisonmiete',
        'wochmietbto',
        'kaufpreis_pro_qm',
        'mietpreis_pro_qm',
        'calculatedPrice'
    ];

	/** @var string[] */
	private $_propertyTransferTax = [
		'Baden-Württemberg' => 5,
		'Bayern' => 3.5,
		'Berlin' => 6,
		'Brandenburg' => 6.5,
		'Bremen' => 5,
		'Hamburg' => 5.5,
		'Hessen' => 6,
		'Mecklenburg-Vorpommern' => 6,
		'Niedersachsen' => 5,
		'Nordrhein-Westfalen' => 6.5,
		'Rheinland-Pfalz' => 5,
		'Saarland' => 6.5,
		'Sachsen' => 5.5,
		'Sachsen-Anhalt' => 5,
		'Schleswig-Holstein' => 6.5,
		'Thüringen' => 5
	];

	/** @var string */
	private $_contactPerson = '0';

	/**
	 *
	 */

	public function __construct()
	{
		$this->_pDataViewSimilarEstates = new DataViewSimilarEstates();
	}

	/**
	 *
	 */

	public function __wakeup()
	{
		if ($this->_pDataViewSimilarEstates === null) {
			$this->_pDataViewSimilarEstates = new DataViewSimilarEstates();
		}
	}

	/** @return array */
	public function getFields(): array
		{ return $this->_fields; }

	/** @return array */
	public function getPictureTypes(): array
		{ return $this->_pictureTypes; }

	/** @return bool */
	public function hasDetailView(): bool
		{ return $this->_accessControls; }

	/** @return bool */
	public function getViewRestrict(): bool
	{ return $this->_restrictAccess; }

	/** @return string */
	public function getTemplate(): string
		{ return $this->_template; }

	/** @return string */
	public function getShortCodeForm(): string
		{ return $this->_shortCodeForm;}

	/** @return string */
	public function getExpose(): string
		{ return $this->_expose; }

	/** @return string */
	public function getName(): string
		{ return 'detail'; }

	/** @return string[] */
	public function getAddressFields(): array
		{ return $this->_addressFields; }

	/** @return string[] */
	public function getDefaultAddressFields(): array
		{ return $this->_defaultAddressFields; }

	/** @return int */
	public function getPageId(): int
		{ return $this->_pageId; }

	/** @param array $fields */
	public function setFields(array $fields)
		{ $this->_fields = $fields; }

	/** @param array $pictureTypes */
	public function setPictureTypes(array $pictureTypes)
		{ $this->_pictureTypes = $pictureTypes; }

	/** @param bool $accessControl */
	public function setHasDetailView(bool $accessControl)
		{ $this->_accessControls = $accessControl; }
    
	/** @param bool $accessControl */
	public function setHasDetailViewRestrict(bool $restrictAccess)
	{ $this->_restrictAccess = $restrictAccess; }

	/** @param string $template */
	public function setTemplate(string $template)
		{ $this->_template = $template; }

	/** @param string $shortCodeForm */
	public function setShortCodeForm(string $shortCodeForm)
	{$this->_shortCodeForm = $shortCodeForm;}

	/** @param string $expose */
	public function setExpose(string $expose)
		{ $this->_expose = $expose; }

	/** @param int $pageId */
	public function setPageId(int $pageId)
		{ $this->_pageId = $pageId; }

	/** @var string[] $addressFields */
	public function setAddressFields(array $addressFields)
		{ $this->_addressFields = $addressFields; }
	/** @return int */
	public function getMovieLinks(): int
		{ return $this->_movieLinks; }

	/** @return string */
	public function getOguloLinks(): string
		{ return $this->_oguloLinks; }

	/** @return string */
	public function getObjectLinks(): string
	{ return $this->_objectLinks; }

	/** @return string */
	public function getLinks(): string
	{ return $this->_links; }

	/** @return DataViewSimilarEstates */
	public function getDataViewSimilarEstates(): DataViewSimilarEstates
	{ return $this->_pDataViewSimilarEstates; }

	/** @param DataViewSimilarEstates $pDataViewSimilarEstates */
	public function setDataViewSimilarEstates(DataViewSimilarEstates $pDataViewSimilarEstates)
	{ $this->_pDataViewSimilarEstates = $pDataViewSimilarEstates; }

	/** @return bool */
	public function getDataDetailViewActive(): bool
	{ return $this->_dataDetailViewActive; }

	/** @param bool $dataDetailViewActive */
	public function setDataDetailViewActive(bool $dataDetailViewActive)
	{ $this->_dataDetailViewActive = $dataDetailViewActive; }

	/** @param int $movieLinks */
	public function setMovieLinks(int $movieLinks)
		{ $this->_movieLinks = $movieLinks; }

	/** @param string $oguloLinks */
	public function setOguloLinks(string $oguloLinks)
	{ $this->_oguloLinks = $oguloLinks; }

	/** @param string $objectLink */
	public function setObjectLinks(string $objectLink)
	{ $this->_objectLinks = $objectLink; }

	/** @param string $links */
	public function setLinks(string $links)
	{ $this->_links = $links; }

	/** @return bool */
	public function getRandom(): bool
		{  return false; }

	/** @return bool */
	public function getShowStatus(): bool
	{ return (bool) $this->_showStatus; }

	/** @param bool $status */
	public function setShowStatus(bool $status)
	{ $this->_showStatus = $status; }

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
	public function getCustomLabels(): array
		{ return $this->_customLabel; }

	/** @param array $customlabel */
	public function setCustomLabels(array $customLabel)
		{ $this->_customLabel = $customLabel; }

	/** @return bool */
	public function getShowPriceOnRequest(): bool
	{ return (bool) $this->_showPriceOnRequest; }

	/** @param bool $priceOnRequest */
	public function setShowPriceOnRequest(bool $priceOnRequest)
	{ $this->_showPriceOnRequest = $priceOnRequest; }

	/** @return bool */
	public function getShowEnergyCertificate(): bool
	{ return $this->_showEnergyCertificate; }

	/** @param bool $showEnergyCertificate */
	public function setShowEnergyCertificate(bool $showEnergyCertificate)
	{ $this->_showEnergyCertificate = $showEnergyCertificate; }

    /**
     * @return array
     */
    public function getListFieldsShowPriceOnRequest(): array
    {
        return $this->_priceFields;
    }

	/** @return array */
	public function getPropertyTransferTax(): array
		{ return $this->_propertyTransferTax; }

	/** @return bool */
	public function getShowTotalCostsCalculator(): bool
		{ return $this->_showTotalCostsCalculator; }

	/** @param bool $costsCalculator */
	public function setShowTotalCostsCalculator(bool $costsCalculator)
		{ $this->_showTotalCostsCalculator = $costsCalculator; }

	/** @return array */
	public function getContactImageTypes(): array
		{ return $this->_contactImageTypes; }

	/** @param array $contactImageTypes */
	public function setContactImageTypes(array $contactImageTypes)
		{ $this->_contactImageTypes = $contactImageTypes; }

	/** @param array $priceFields */
	public function setListFieldsShowPriceOnRequest(array $priceFields)
	{
		$this->_priceFields = $priceFields;
	}

	/** @return string */
	public function getContactPerson(): string
		{ return $this->_contactPerson; }

	/** @param string $contactPerson */
	public function setContactPerson(string $contactPerson)
		{ $this->_contactPerson = $contactPerson; }
}
