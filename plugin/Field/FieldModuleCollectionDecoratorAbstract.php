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

use DI\ContainerBuilder;
use onOffice\WPlugin\Field\Collection\FieldLoaderGeneric;
use onOffice\WPlugin\Types\Field;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

abstract class FieldModuleCollectionDecoratorAbstract implements FieldModuleCollection
{
	/** @var FieldModuleCollection */
	private $_pFieldModuleCollection = null;

	/** @var array */
	protected $_allAddressEstateField = [];


	/**
	 *
	 * @param  FieldModuleCollection  $pFieldModuleCollection
	 *
	 */

	public function __construct(FieldModuleCollection $pFieldModuleCollection)
	{
		$this->_pFieldModuleCollection = $pFieldModuleCollection;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAllFields(): array
	{
		return $this->_pFieldModuleCollection->getAllFields();
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
		return $this->_pFieldModuleCollection->containsFieldByModule($module, $name);
	}


	/**
	 *
	 * @param Field $pField
	 *
	 */

	public function addField(Field $pField)
	{
		$this->_pFieldModuleCollection->addField($pField);
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
		return $this->_pFieldModuleCollection->getFieldByModuleAndName($module, $name);
	}


	/**
	 *
	 * @param array $fieldsByModule
	 * @return array
	 *
	 */

	protected function generateListOfMergedFieldsByModule(array $fieldsByModule): array
	{
		$newFields = [];

		foreach ($fieldsByModule as $module => $fields) {
			foreach ($fields as $name => $row) {
				$row['module'] = $module;
				$newFields []= Field::createByRow($name, $row);
			}
		}

		return $newFields;
	}


	/**
	 *
	 * @return FieldModuleCollection
	 *
	 */

	protected function getFieldModuleCollection(): FieldModuleCollection
	{
		return $this->_pFieldModuleCollection;
	}


	/**
	 * @param $newFields
	 *
	 * @return array
	 */

	protected function formatFieldContent( $newFields ): array
	{
		$this->getAddressEstateField();
		$newFieldFormat = [];

		foreach ( $newFields[''] as $addressFieldName => $addressFieldProperties ) {
			$addressFieldProperties['content']   = __( 'Special Fields', 'onoffice-for-wp-websites' );
			$newFieldFormat[ $addressFieldName ] = $addressFieldProperties;
		}
		unset( $newFields[''] );

		foreach ( $this->_allAddressEstateField as $name => $fieldProperties ) {
			if ( isset( $newFields[ $fieldProperties['tablename'] ] ) ) {
				foreach ( $newFields[ $fieldProperties['tablename'] ] as $addressFieldName => $addressFieldProperties ) {
					$addressFieldProperties['content']   = $fieldProperties['content'];
					$newFieldFormat[ $addressFieldName ] = $addressFieldProperties;
				}
				unset( $newFields[ $fieldProperties['tablename'] ] );

				if ( empty( $newFields ) ) {
					return $newFieldFormat;
				}
			}
		}
		foreach ( $newFields as $field ) {
			$newFieldFormat += $field;
		}

		return $newFieldFormat;
	}


	/**
	 *
	 *
	 */

	protected function getAddressEstateField() {
		$pDIContainerBuilder          = new ContainerBuilder;
		$pContainer                   = $pDIContainerBuilder->addDefinitions( ONOFFICE_DI_CONFIG_PATH )->build();
		$pFieldLoader                 = $pContainer->get( FieldLoaderGeneric::class );
		$result                       = $pFieldLoader->sendRequest();
		$this->_allAddressEstateField = [];
		foreach ( $result as $fieldModule ) {
			unset( $fieldModule['elements']['label'] );
			$this->_allAddressEstateField += $fieldModule['elements'];
		}
	}


	/**
	 * @param $allAddressEstateField
	 */

	protected function setAllAddressEstateField($allAddressEstateField)
	{
		$this->_allAddressEstateField = $allAddressEstateField;
	}
}
