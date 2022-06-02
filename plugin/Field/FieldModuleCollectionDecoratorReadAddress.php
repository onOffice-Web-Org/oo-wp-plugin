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

namespace onOffice\WPlugin\Field;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FieldModuleCollectionDecoratorReadAddress
	extends FieldModuleCollectionDecoratorAbstract
{
	/** @var array */
	private $_addressFields = [
		'imageUrl' => [
			'type'   => FieldTypes::FIELD_TYPE_TEXT,
			'length' => null,
			'label'  => 'Image',
		],
		'email'    => [
			'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
			'length'          => 80,
			'default'         => null,
			'permittedvalues' => null,
			'label'           => 'all e-mail address',
			'tablename'       => 'Contact',
			"content"         => "Contact"
		],
		'phone'    => [
			'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
			'length'          => 40,
			'default'         => null,
			'permittedvalues' => null,
			'label'           => 'All non-mobile phone numbers',
			'tablename'       => 'Master data',
			"content"         => "Master data"
		],
		'fax'      => [
			'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
			'length'          => 40,
			'default'         => null,
			'permittedvalues' => null,
			'label'           => 'All fax numbers',
			'tablename'       => 'Master data',
			"content"         => "Master data"
		],
		'mobile'   => [
			'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
			'length'          => 40,
			'default'         => null,
			'permittedvalues' => null,
			'label'           => 'All mobile numbers',
			'tablename'       => 'Master data',
			"content"         => "Master data"
		],
		'Telefon1' => [
			'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
			'length'          => 40,
			'default'         => null,
			'permittedvalues' => null,
			'label'           => 'Default phone number',
			'module'          => 'address',
			'tablename'       => 'Master data',
			"content"         => "Master data"
		],
		'Telefax1' => [
			'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
			'length'          => 40,
			'default'         => null,
			'permittedvalues' => null,
			'label'           => 'Default fax number',
			'module'          => 'address',
			'tablename'       => 'Master data',
			"content"         => "Master data"
		],
	];

	private $_deLanguageAddressFields = [
			'imageUrl' => [
					'type'   => FieldTypes::FIELD_TYPE_TEXT,
					'length' => null,
					'label'  => 'Image tieng duc',
			],
			'email'    => [
					'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
					'length'          => 80,
					'default'         => null,
					'permittedvalues' => null,
					'label'           => 'all e-mail address  tieng duc',
					'tablename'       => 'Kontakt',
					"content"         => "Kontakt"
			],
			'phone'    => [
					'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
					'length'          => 40,
					'default'         => null,
					'permittedvalues' => null,
					'label'           => 'All non-mobile phone numbers tieng duc',
					'tablename'       => 'Stammdaten',
					"content"         => "Stammdaten"
			],
			'fax'      => [
					'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
					'length'          => 40,
					'default'         => null,
					'permittedvalues' => null,
					'label'           => 'All fax numbers tieng duc',
					'tablename'       => 'Stammdaten',
					"content"         => "Stammdaten"
			],
			'mobile'   => [
					'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
					'length'          => 40,
					'default'         => null,
					'permittedvalues' => null,
					'label'           => 'All mobile numbers tieng duc',
					'tablename'       => 'Stammdaten',
					"content"         => "Stammdaten"
			],
			'Telefon1' => [
					'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
					'length'          => 40,
					'default'         => null,
					'permittedvalues' => null,
					'label'           => 'Default phone number tieng duc',
					'module'          => 'address',
					'tablename'       => 'Stammdaten',
					"content"         => "Stammdaten"
			],
			'Telefax1' => [
					'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
					'length'          => 40,
					'default'         => null,
					'permittedvalues' => null,
					'label'           => 'Default fax number tieng duc',
					'module'          => 'address',
					'tablename'       => 'Stammdaten',
					"content"         => "Stammdaten"
			],
	];

	public function getAddressFields(){
		$currentLocale = get_locale();
		if($currentLocale == 'de_DE'){
			return $this->_deLanguageAddressFields;
		}else{
			return $this->_addressFields;
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAllFields(): array
	{
		$this->_addressFields = $this->getAddressFields();
		$newFields = [];
		foreach ($this->_addressFields as $name => $row) {
			$row['module'] = onOfficeSDK::MODULE_ADDRESS;
			$newFields []= Field::createByRow($name, $row);
		}
		return array_merge($this->getFieldModuleCollection()->getAllFields(), $newFields);
	}


	/**
	 *
	 * @param string $module
	 * @param string $name
	 * @return Field
	 *
	 */

	public function getFieldByModuleAndName(string $module, string $name): Field
	{
		$this->_addressFields = $this->getAddressFields();
		if ($module === onOfficeSDK::MODULE_ADDRESS) {
			$row = $this->_addressFields[$name] ?? null;
			if ($row !== null) {
				$row['module'] = onOfficeSDK::MODULE_ADDRESS;
				return Field::createByRow($name, $row);
			}
		}
		return parent::getFieldByModuleAndName($module, $name);
	}


	/**
	 *
	 * @param string $module
	 * @param string $name
	 * @return bool
	 *
	 */

	public function containsFieldByModule(string $module, string $name): bool
	{
		$this->_addressFields = $this->getAddressFields();
		return ($module === onOfficeSDK::MODULE_ADDRESS &&
			isset($this->_addressFields[$name])) ||
			parent::containsFieldByModule($module, $name);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getNewAddressFields(): array
	{
		$this->_addressFields = $this->getAddressFields();
		return $this->_addressFields;
	}
}
