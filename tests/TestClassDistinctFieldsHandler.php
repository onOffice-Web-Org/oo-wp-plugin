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
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;
use function json_decode;

/**
 * Test for class DistinctFieldsHandler
 */
class TestClassDistinctFieldsHandler
	extends WP_UnitTestCase
{
	/**
	 * @param DataListView $pListView
	 * @return DistinctFieldsHandler
	 */
	public function buildSubject(DataListView $pListView): DistinctFieldsHandler
	{
		$pDefaultFilterBuilder = $this->getMockBuilder(DefaultFilterBuilderListView::class)
			->setConstructorArgs([$pListView, $this->buildFieldsCollectionBuilderShort()])
			->getMock();
		$pDefaultFilterBuilder->expects($this->atLeastOnce())->method('buildFilter')
			->willReturn([
				'veroeffentlichen' => [['op' => '=', 'val' => '1']],
			]);
		$pDefaultFilterBuilderFactory = $this->getMockBuilder(DefaultFilterBuilderFactory::class)
			->setConstructorArgs([$this->buildFieldsCollectionBuilderShort()])
			->getMock();
		$pDefaultFilterBuilderFactory->expects($this->atLeastOnce())->method('buildDefaultListViewFilter')
			->willReturn($pDefaultFilterBuilder);
		$pModelBuilder = new DistinctFieldsHandlerModelBuilder($pDefaultFilterBuilderFactory);

		$pApiClientAction = new APIClientActionGeneric($this->buildSDKWrapper(), '', '');
		return new DistinctFieldsHandler($pApiClientAction, $pModelBuilder);
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

	/**
	 * @return FieldsCollection
	 */
	private function buildFieldsCollection(): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pFieldObjektart = new Field('objektart', onOfficeSDK::MODULE_ESTATE);
		$pFieldObjektart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldsCollection->addField($pFieldObjektart);

		$pFieldObjekttyp = new Field('objekttyp', onOfficeSDK::MODULE_ESTATE);
		$pFieldObjekttyp->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldsCollection->addField($pFieldObjekttyp);

		$pFieldNutzungsart = new Field('nutzungsart', onOfficeSDK::MODULE_ESTATE);
		$pFieldNutzungsart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldsCollection->addField($pFieldNutzungsart);
		return $pFieldsCollection;
	}

	/**
	 * @return FieldsCollection
	 */
	private function buildFieldsCollectionReference(): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pFieldObjektart = new Field('objektart', onOfficeSDK::MODULE_ESTATE);
		$pFieldObjektart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldObjektart->setPermittedvalues([
			'haus' => 'House',
			'wohnung' => 'Apartments',
			'buero_praxen' => 'Offices',
			'zimmer' => 'Rooms',
		]);
		$pFieldsCollection->addField($pFieldObjektart);

		$pFieldObjekttyp = new Field('objekttyp', onOfficeSDK::MODULE_ESTATE);
		$pFieldObjekttyp->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldObjekttyp->setPermittedvalues([
			'doppelhaushaelfte' => 'Semi-detached house',
			'hochparterre' => 'Mezzanine floor',
			'penthouse' => 'Penthouse',
			'souterrain' => 'Basement',
			'einfamilienhaus' => 'Single family house',
			'etage' => 'Apartment',
			'bungalow' => 'Bungalow',
			'reihenhaus' => 'Terraced house',
			'villa' => 'Villa',
		]);
		$pFieldsCollection->addField($pFieldObjekttyp);

		$pFieldNutzungsart = new Field('nutzungsart', onOfficeSDK::MODULE_ESTATE);
		$pFieldNutzungsart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldNutzungsart->setPermittedvalues(['wohnen' => 'Residential']);
		$pFieldsCollection->addField($pFieldNutzungsart);
		return $pFieldsCollection;
	}

	/**
	 * @return SDKWrapperMocker
	 */
	private function buildSDKWrapper(): SDKWrapperMocker
	{
		$pSDKWrapperMocker = new SDKWrapperMocker();

		$parametersEstatesObjektart = [
			'language' => 'ENG',
			'module' => 'estate',
			'field' => 'objektart',
			'filter' => [
				'veroeffentlichen' => [['op' => '=','val' => '1']],
			],
			'filterid' => 1337,
		];

		$fields = [
			'objektart' => 'ApiResponseDistinctFieldsHandlerEstateObjektart.json',
			'objekttyp' => 'ApiResponseDistinctFieldsHandlerEstateObjekttyp.json',
			'nutzungsart' => 'ApiResponseDistinctFieldsHandlerEstateNutzungsart.json',
		];

		foreach ($fields as $field => $apiResponseFile) {
			$parametersEstatesThisField = $parametersEstatesObjektart;
			$parametersEstatesThisField['field'] = $field;
			$responseEstatesOThisField = file_get_contents
				(__DIR__.'/resources/Field/'.$apiResponseFile);
			$responseEstatesFields = json_decode($responseEstatesOThisField, true);
			$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'distinctValues',
				'', $parametersEstatesThisField, null, $responseEstatesFields);
		}
		return $pSDKWrapperMocker;
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	public function testModifyFieldsCollectionForEstate()
	{
		$pDataListView = new DataListView(123, 'testname');
		$pDataListView->setFilterableFields(['objektart', 'objekttyp', 'nutzungsart']);
		$pDataListView->setAvailableOptions(['objektart', 'objekttyp', 'nutzungsart']);
		$pDataListView->setFilterId(1337);
		$pFieldsCollection = $this->buildFieldsCollection();
		$pSubject = $this->buildSubject($pDataListView);
		$pNewFieldsCollection = $pSubject->modifyFieldsCollectionForEstate($pDataListView, $pFieldsCollection);
		$pFieldsCollectionReference = $this->buildFieldsCollectionReference();
		$this->assertEquals($pFieldsCollectionReference, $pNewFieldsCollection);
	}
}