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

use onOffice\WPlugin\Types\ImageTypes;

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
	const TEMPLATE = 'template';

	/** */
	const PICTURES = 'pictures';

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
	private $_pictureTypes = [
		ImageTypes::PASSPORTPHOTO
	];

	/** @var string */
	private $_template = '';

	/** @var int */
	private $_pageId = 0;

	/** @var array */
	private $_pageIdsHaveDetailShortCode = [];

	/** @var array */
	private $_customLabel = [];

	/** @var string */
	private $_shortCodeForm = '';

	/** @var string */
	private $_shortCodeActiveEstate = '';

	/** @var string */
	private $_shortCodeReferenceEstate = '';

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

	/** @return string */
	public function getShortCodeForm(): string
	{ return $this->_shortCodeForm; }

	/** @param string $shortCodeForm */
	public function setShortCodeForm(string $shortCodeForm)
	{ $this->_shortCodeForm = $shortCodeForm; }

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

	/** @return string */
	public function getShortCodeActiveEstate(): string
		{ return $this->_shortCodeActiveEstate; }

	/** @param string $shortCodeActiveEstate */
	public function setShortCodeActiveEstate(string $shortCodeActiveEstate)
		{ $this->_shortCodeActiveEstate = $shortCodeActiveEstate; }

	/** @return string */
	public function getShortCodeReferenceEstate(): string
		{ return $this->_shortCodeReferenceEstate; }

	/** @param string $shortCodeReferenceEstate */
	public function setShortCodeReferenceEstate(string $shortCodeReferenceEstate)
		{ $this->_shortCodeReferenceEstate = $shortCodeReferenceEstate; }
}
