<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\tests;

use DI\Container;
use DI\ContainerBuilder;
use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelRead;
use onOffice\WPlugin\Field\FieldModuleCollection;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorCustomLabelAddress;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;
use wpdb;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */
class TestClassFieldModuleCollectionDecoratorCustomLabelAddress
	extends WP_UnitTestCase
{
	/** @var RecordManagerReadListViewAddress */
	private $_pRecordManagerReadListViewAddress = null;

	/** @var CustomLabelRead */
	private $_pCustomLabelRead = null;

	/** @var FieldModuleCollection */
	private $_pFieldModuleCollection = null;

	/** @var Container */
	private $_pContainer = null;

	/** @var wpdb */
	private $_pWPDBMock = null;

	/**
	 * @before
	 */

	public function prepare()
	{
		$rows = [
			(object)[
				'customs_labels_id' => 33,
				'locale' => 'de_DE',
				'value' => 'Deutschland',
			]
		];
		$this->_pWPDBMock = $this->getMockBuilder(\wpdb::class)
			->disableOriginalConstructor()
			->setMethods(['get_results'])
			->getMock();
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();

		$this->_pRecordManagerReadListViewAddress = $this->getMockBuilder(RecordManagerReadListViewAddress::class)
			->getMock();
		$this->_pRecordManagerReadListViewAddress->method('readFieldconfigByListviewId')->will($this->returnValueMap([
			[1, $this->getBasicFieldsArray(1)]
		]));
		$this->_pRecordManagerReadListViewAddress->method('getRowByName')->will($this->returnValue(
			$this->getBaseRow(1)
		));
		$this->_pContainer->set(RecordManagerReadListViewAddress::class, $this->_pRecordManagerReadListViewAddress);
		$pFieldsCollectionByFormIds = $this->_pContainer->get(RecordManagerReadListViewAddress::class)->readFieldconfigByListviewId(1);

		$this->_pFieldModuleCollection = new FieldsCollection();
		foreach ($pFieldsCollectionByFormIds as $pFieldsCollectionByFormId) {
			$this->_pFieldModuleCollection->addField(new Field($pFieldsCollectionByFormId['fieldname'],
				onOfficeSDK::MODULE_ADDRESS));
			$this->_pWPDBMock->method('get_results')->will($this->returnValue($rows));
			$this->_pCustomLabelRead = new CustomLabelRead($this->_pWPDBMock);
			$this->_pCustomLabelRead->readCustomLabelByFormIdAndFieldName(1, $pFieldsCollectionByFormId['fieldname'],
				'de_DE','oo_plugin_fieldconfig_address_customs_labels','oo_plugin_fieldconfig_address_translated_labels');
		}
		$this->_pContainer->set(CustomLabelRead::class, $this->_pCustomLabelRead);
	}


	/**
	 *
	 */

	public function testGetAllFields()
	{
		$pDecoratorCustomLabel = new FieldModuleCollectionDecoratorCustomLabelAddress($this->_pFieldModuleCollection,
			'testListViewId1', $this->_pContainer);
		$fieldCustomLabels = $pDecoratorCustomLabel->getFieldCustomLabels();
		$cloneFields = array();

		foreach ($pDecoratorCustomLabel->getAllFields() as $key => $field) {
			$cloneFields[$key] = clone $field;
			$module = $cloneFields[$key]->getModule();
			$name = $cloneFields[$key]->getName();
			$label = $fieldCustomLabels[$module][$name] ?? null;

			if ($label !== null) {
				$this->assertEquals($label, $cloneFields[$key]->getLabel());
			}
		}
	}

	/**
	 *
	 * @param int $addressId
	 * @return array
	 *
	 */

	private function getBasicFieldsArray(int $addressId): array
	{
		$fields = [
			[
				'fieldconfig_id' => '1',
				'listview_id' => $addressId,
				'order' => '1',
				'fieldname' => 'Test 1',
				'fieldlabel' => 'Field label 1',
				'hidden' => '0',
				'availableOptions' => '0',
			],
			[
				'fieldconfig_id' => '1',
				'listview_id' => $addressId,
				'order' => '1',
				'fieldname' => 'Test 3',
				'fieldlabel' => 'Field label 3',
				'hidden' => '0',
				'availableOptions' => '0',
			],
			[
				'fieldconfig_id' => '1',
				'listview_id' => $addressId,
				'order' => '1',
				'fieldname' => 'Test 3',
				'fieldlabel' => 'Field label 3',
				'hidden' => '0',
				'availableOptions' => '0',
			],
		];

		return $fields;
	}

	/**
	 *
	 * @param int $addressId
	 * @return array
	 *
	 */

	private function getBaseRow(int $listviewId): array
	{
		return [
			'listview_address_id' => $listviewId,
			'name' => 'testListViewId' . $listviewId,
			'filterId' => '0',
			'template' => 'testtemplate.php',
			'expose' => '',
			'radius' => '10',
			'fields' => [
				'test1',
				'test2'
			],
			'filterable' => [],
			'hidden' => [],
		];
	}
}
