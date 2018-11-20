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

namespace onOffice\WPlugin\Types;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * Build a collection of fields by a fixed definition
 *
 */

class LocalFieldsCollectionFactory
{
	/**
	 *
	 * @var array
	 * "label" key needs to be translated
	 *
	 */

	private $_apiReadOnlyFields = [
		onOfficeSDK::MODULE_ADDRESS => [
			'imageUrl' => [
				'type' => FieldTypes::FIELD_TYPE_TEXT,
				'length' => null,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Image',
			],
			'phone' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 40,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Phone',
			],
			'email' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 80,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'E-Mail',
			],
			'fax' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 40,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Fax',
			],
			'mobile' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 40,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Mobile',
			],
			'defaultphone' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 40,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Phone',
			],
			'defaultemail' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 80,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'E-Mail',
			],
			'defaultfax' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 40,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Fax',
			],
			'newsletter' => [
				'type' => FieldTypes::FIELD_TYPE_BOOLEAN,
				'length' => 0,
				'permittedvalues' => [],
				'default' => false,
				'label' => 'Newsletter',
			],
		],
		onOfficeSDK::MODULE_SEARCHCRITERIA => [
			'krit_bemerkung_oeffentlich' => [
				'type' => FieldTypes::FIELD_TYPE_TEXT,
				'length' => null,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Comment',
			],
			GeoPosition::FIELD_GEO_POSITION => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Geo Position',
				'content' => 'Search Criteria',
			],
		],
		onOfficeSDK::MODULE_ESTATE => [
			GeoPosition::FIELD_GEO_POSITION => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Geo Position',
				'content' => 'Geografische-Angaben',
			],

			GeoPosition::ESTATE_LIST_SEARCH_COUNTRY => [
				'type' => FieldTypes::FIELD_TYPE_SINGLESELECT,
				'length' => 250,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Land',
				'content' => 'Geografische-Angaben',
				'module' => onOfficeSDK::MODULE_ESTATE,
			],
			GeoPosition::ESTATE_LIST_SEARCH_RADIUS => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 5,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Radius (km]',
				'content' => 'Geografische-Angaben',
				'module' => onOfficeSDK::MODULE_ESTATE,
			],
			GeoPosition::ESTATE_LIST_SEARCH_STREET => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Strasse',
				'content' => 'Geografische-Angaben',
				'module' => onOfficeSDK::MODULE_ESTATE,
			],
			GeoPosition::ESTATE_LIST_SEARCH_ZIP => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 10,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'PLZ',
				'content' => 'Geografische-Angaben',
				'module' => onOfficeSDK::MODULE_ESTATE,
			],
		],
		'' => [
			'message' => [
				'type' => FieldTypes::FIELD_TYPE_TEXT,
				'length' => null,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Message',
			],
		],
	];


	/**
	 *
	 * @param string $module
	 *
	 */

	public function produceCollection(string $module): FieldsCollection
	{
		$fields = $this->_apiReadOnlyFields[$module] ?? [];
		$pCollection = new FieldsCollection($module);

		foreach ($fields as $name => $properties) {
			$pLocalField = new Field($name, __($properties['label'] ?? '', 'onoffice'));
			$pLocalField->setDefault($properties['default']);
			$pLocalField->setLength($properties['length'] ?? 0);
			$pLocalField->setPermittedvalues($properties['permittedvalues']);
			$pLocalField->setCategory(__($properties['category'] ?? '', 'onoffice'));
			$pLocalField->setType($properties['type']);
			$pCollection->addField($pLocalField);
		}

		return $pCollection;
	}
}
