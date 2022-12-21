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
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorCustomLabelEstate;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;
use wpdb;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */
class TestClassFieldModuleCollectionDecoratorCustomLabelEstate
	extends WP_UnitTestCase
{
	/** @var RecordManagerReadListViewEstate */
	private $_pRecordManagerReadListViewEstate = null;

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

		$this->_pRecordManagerReadListViewEstate = $this->getMockBuilder(RecordManagerReadListViewEstate::class)
			->getMock();
		$this->_pRecordManagerReadListViewEstate->method('getFieldconfigByListviewId')->will($this->returnValueMap([
			[1, $this->getBasicFieldsArray(1)]
		]));
		$this->_pRecordManagerReadListViewEstate->method('getRowByName')->will($this->returnValue(
			$this->getBaseRow(1)
		));
		$this->_pContainer->set(RecordManagerReadListViewEstate::class, $this->_pRecordManagerReadListViewEstate);
		$pFieldsCollectionByFormIds = $this->_pContainer->get(RecordManagerReadListViewEstate::class)->getFieldconfigByListviewId(1);

		$this->_pFieldModuleCollection = new FieldsCollection();
		foreach ($pFieldsCollectionByFormIds as $pFieldsCollectionByFormId) {
			$this->_pFieldModuleCollection->addField(new Field($pFieldsCollectionByFormId['fieldname'],
				onOfficeSDK::MODULE_ESTATE));
			$this->_pWPDBMock->method('get_results')->will($this->returnValue($rows));
			$this->_pCustomLabelRead = new CustomLabelRead($this->_pWPDBMock);
			$this->_pCustomLabelRead->readCustomLabelByFormIdAndFieldName(1, $pFieldsCollectionByFormId['fieldname'],
				'de_DE','oo_plugin_fieldconfig_estate_customs_labels','oo_plugin_fieldconfig_estate_translated_labels');
		}
		$this->_pContainer->set(CustomLabelRead::class, $this->_pCustomLabelRead);
	}


	/**
	 *
	 */

	public function testGetAllFields()
	{
		$pDecoratorCustomLabel = new FieldModuleCollectionDecoratorCustomLabelEstate($this->_pFieldModuleCollection,
			'testListViewId1', 'default', $this->_pContainer);
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
	 * @param int $estateId
	 * @return array
	 *
	 */

	private function getBasicFieldsArray(int $estateId): array
	{
		$fields = [
			[
				'fieldconfig_id' => '1',
				'listview_id' => $estateId,
				'order' => '1',
				'fieldname' => 'Test 1',
				'fieldlabel' => 'Field label 1',
				'hidden' => '0',
				'availableOptions' => '0',
			],
			[
				'fieldconfig_id' => '1',
				'listview_id' => $estateId,
				'order' => '1',
				'fieldname' => 'Test 3',
				'fieldlabel' => 'Field label 3',
				'hidden' => '0',
				'availableOptions' => '0',
			],
			[
				'fieldconfig_id' => '1',
				'listview_id' => $estateId,
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
	 * @param int $estateId
	 * @return array
	 *
	 */

	private function getBaseRow(int $listviewId): array
	{
		return [
			'listview_id' => $listviewId,
			'name' => 'testListViewId' . $listviewId,
			'filterId' => '0',
			'sortby' => 'test',
			'sortorder' => 'ASC',
			'show_status' => '0',
			'list_type' => 'default',
			'template' => 'testtemplate.php',
			'expose' => '',
			'recordsPerPage' => '20',
			'random' => '0',
			'country_active' => '0',
			'zip_active' => '1',
			'city_active' => '0',
			'street_active' => '0',
			'radius_active' => '1',
			'radius' => '10',
			'geo_order' => 'street,zip,city,country,radius',
			'sortBySetting' => '',
			'sortByUserDefinedDefault' => '',
			'sortByUserDefinedDirection' => '0',
			'pictures' => [],
			'sortbyuservalues' => [],
			'fields' => [
				'test1',
				'test2'
			],
			'filterable' => [],
			'hidden' => [],
			'availableOptions' => []
		];
	}
}
