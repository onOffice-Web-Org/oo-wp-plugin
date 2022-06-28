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
	const ADDRESS_FIELDS_MASTER_DATA = [
		'phone'    => [
			'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
			'length'          => 40,
			'default'         => null,
			'permittedvalues' => null,
			'label'           => 'All non-mobile phone numbers',
			'tablename'       => 'Stammdaten',
			'module'          => 'address',
			"content"         => "Master data"
		],
		'fax'      => [
			'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
			'length'          => 40,
			'default'         => null,
			'permittedvalues' => null,
			'label'           => 'All fax numbers',
			'tablename'       => 'Stammdaten',
			'module'          => 'address',
			"content"         => "Master data"
		],
		'mobile'   => [
			'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
			'length'          => 40,
			'default'         => null,
			'permittedvalues' => null,
			'label'           => 'All mobile numbers',
			'tablename'       => 'Stammdaten',
			'module'          => 'address',
			"content"         => "Master data"
		],
	];

	/** @var array */
	const ADDRESS_FIELDS_CONTACT = [
		'email' => [
			'type'            => FieldTypes::FIELD_TYPE_VARCHAR,
			'length'          => 80,
			'default'         => null,
			'permittedvalues' => null,
			'label'           => 'All e-mail addresses',
			'tablename'       => 'Kontakt',
			'module'          => 'address',
			"content"         => "Contact"
		],
	];

	/** @var array */
	const ADDRESS_FIELDS_NO_CATEGORY = [
		'imageUrl' => [
			'type'   => FieldTypes::FIELD_TYPE_TEXT,
			'length' => null,
			'default'         => null,
			'permittedvalues' => null,
			'label'  => 'Image',
			'module' => 'address',
		],
	];


	/**
	 *
	 * @return array
	 *
	 */

	public function getAllFields(): array
	{
		$newFields = [];
		foreach ($this->getNewAddressFieldsAfterFormat() as $name => $row) {
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
		if ($module === onOfficeSDK::MODULE_ADDRESS) {
			$row = $this->getNewAddressFieldsAfterFormat()[$name] ?? null;
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
		return ($module === onOfficeSDK::MODULE_ADDRESS &&
			isset($this->getNewAddressFieldsAfterFormat()[$name])) ||
			parent::containsFieldByModule($module, $name);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public static function getNewAddressFields(): array
	{
		return self::ADDRESS_FIELDS_NO_CATEGORY + self::ADDRESS_FIELDS_CONTACT + self::ADDRESS_FIELDS_MASTER_DATA;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public static function getNewAddressFieldsWithTableNameKey(): array
	{
		return [
			''           => self::ADDRESS_FIELDS_NO_CATEGORY,
			'Kontakt'    => self::ADDRESS_FIELDS_CONTACT,
			'Stammdaten' => self::ADDRESS_FIELDS_MASTER_DATA,
		];
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getNewAddressFieldsAfterFormat(): array
	{
		return $this->formatFieldContent(self::getNewAddressFieldsWithTableNameKey());
	}


	/**
	 *
	 * @return array
	 *
	 */

	public static function getNewAddressFieldsTranslatedLabel(): array
	{
		return [
			'All non-mobile phone numbers' => __( 'All non-mobile phone numbers', 'onoffice-for-wp-websites' ),
			'All fax numbers'              => __( 'All fax numbers', 'onoffice-for-wp-websites' ),
			'All mobile numbers'           => __( 'All mobile numbers', 'onoffice-for-wp-websites' ),
			'All e-mail addresses'         => __( 'All e-mail addresses', 'onoffice-for-wp-websites' ),
			'Image'                        => __( 'Image', 'onoffice-for-wp-websites' ),
		];
	}
}
