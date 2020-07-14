<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

class TestClassDistinctFieldsHandlerModelBuilder
	extends WP_UnitTestCase
{
	/**
	 * @param DataListView $pListView
	 * @return DistinctFieldsHandlerModelBuilder
	 */
	public function buildSubject(DataListView $pListView): DistinctFieldsHandlerModelBuilder
	{
		$pDefaultFilterBuilder = $this->getMockBuilder(DefaultFilterBuilderListView::class)
			->setConstructorArgs([$pListView, $this->buildFieldsCollectionBuilderShort()])
			->getMock();
		$pDefaultFilterBuilder->method('buildFilter')
			->willReturn([
				'veroeffentlichen' => [['op' => '=', 'val' => '1']],
			]);
		$pDefaultFilterBuilderFactory = $this->getMockBuilder(DefaultFilterBuilderFactory::class)
			->setConstructorArgs([$this->buildFieldsCollectionBuilderShort()])
			->getMock();
		$pDefaultFilterBuilderFactory->method('buildDefaultListViewFilter')
			->willReturn($pDefaultFilterBuilder);
		return new DistinctFieldsHandlerModelBuilder($pDefaultFilterBuilderFactory);
	}

	/**
	 * @return FieldsCollectionBuilderShort
	 */
	private function buildFieldsCollectionBuilderShort(): FieldsCollectionBuilderShort
	{
		$pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria'])
			->setConstructorArgs([new Container])
			->getMock();

		$pFieldsCollectionBuilderShort->method('addFieldsAddressEstate')
			->with($this->anything())
			->willReturnCallback(function (FieldsCollection $pFieldsCollection)
			use ($pFieldsCollectionBuilderShort): FieldsCollectionBuilderShort
			{
				$pFieldObjektart = new Field('objektart', onOfficeSDK::MODULE_ESTATE);
				$pFieldObjektart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldObjektart);

				$pFieldObjekttyp = new Field('objekttyp', onOfficeSDK::MODULE_ESTATE);
				$pFieldObjekttyp->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldObjekttyp);

				$pFieldNutzungsart = new Field('nutzungsart', onOfficeSDK::MODULE_ESTATE);
				$pFieldNutzungsart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldNutzungsart);

				return $pFieldsCollectionBuilderShort;
			});

		$pFieldsCollectionBuilderShort->method('addFieldsSearchCriteria')
			->with($this->anything())
			->willReturnCallback(function (FieldsCollection $pFieldsCollection)
			use ($pFieldsCollectionBuilderShort): FieldsCollectionBuilderShort
			{
				$pField1 = new Field('objektart', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField1);

				return $pFieldsCollectionBuilderShort;
			});

		return $pFieldsCollectionBuilderShort;
	}

	public function testForEstate()
	{
		$pDatalistView = new DataListView(123, 'testList');
		$pDatalistView->setAvailableOptions(['nutzungsart', 'objektart']);
		$pDatalistView->setFilterId(12);

		$pSubject = $this->buildSubject($pDatalistView);
		$pResultModel = $pSubject->buildDataModelForEstate($pDatalistView);

		$this->assertEquals('estate', $pResultModel->getModule());
		$this->assertEquals(['nutzungsart', 'objektart'], $pResultModel->getDistinctFields());
		$this->assertEmpty($pResultModel->getInputValues());
		$this->assertEquals([
			'veroeffentlichen' => [['op' => '=', 'val' => '1']],
		], $pResultModel->getFilterExpression());
		$this->assertEquals(12, $pResultModel->getFilterId());
	}

	public function testForSearchcriteria()
	{
		$pDataFormConfiguration = new DataFormConfigurationApplicantSearch;
		$pDataFormConfiguration->setAvailableOptionsFields(['objektart', 'objekttyp']);
		$pDataFormConfiguration->setFormName('applsearchform');
		$pDataFormConfiguration->setId(1);

		$pSubject = $this->buildSubject(new DataListView(123, 'abcdummy'));
		$pResultModel = $pSubject->buildDataModelForSearchCriteria($pDataFormConfiguration);

		$this->assertEquals('searchcriteria', $pResultModel->getModule());
		$this->assertEquals(['objektart', 'objekttyp'], $pResultModel->getDistinctFields());
		$this->assertEmpty($pResultModel->getInputValues());
		$this->assertEmpty($pResultModel->getFilterExpression());
		$this->assertEquals(0, $pResultModel->getFilterId());
	}
}