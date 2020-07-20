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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;
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
	public function buildSubjectEstate(DataListView $pListView): DistinctFieldsHandler
	{
		$pDefaultFilterBuilder = $this->getMockBuilder(DefaultFilterBuilderListView::class)
			->setConstructorArgs([$pListView, $this->buildFieldsCollectionBuilderShort(onOfficeSDK::MODULE_ESTATE)])
			->getMock();
		$pDefaultFilterBuilder->expects($this->atLeastOnce())->method('buildFilter')
			->willReturn([
				'veroeffentlichen' => [['op' => '=', 'val' => '1']],
			]);
		$pDefaultFilterBuilderFactory = $this->getMockBuilder(DefaultFilterBuilderFactory::class)
			->setConstructorArgs([$this->buildFieldsCollectionBuilderShort(onOfficeSDK::MODULE_ESTATE)])
			->getMock();
		$pDefaultFilterBuilderFactory->expects($this->atLeastOnce())->method('buildDefaultListViewFilter')
			->willReturn($pDefaultFilterBuilder);
		$pModelBuilder = new DistinctFieldsHandlerModelBuilder($pDefaultFilterBuilderFactory);

		$pApiClientAction = new APIClientActionGeneric($this->buildSDKWrapper('estate'), '', '');
		return new DistinctFieldsHandler($pApiClientAction, $pModelBuilder);
	}

	public function buildSubjectSearchCriteria(): DistinctFieldsHandler
	{
		$pDefaultFilterBuilderFactory = $this->getMockBuilder(DefaultFilterBuilderFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pModelBuilder = new DistinctFieldsHandlerModelBuilder($pDefaultFilterBuilderFactory);

		$pApiClientAction = new APIClientActionGeneric($this->buildSDKWrapper('searchcriteria'), '', '');
		return new DistinctFieldsHandler($pApiClientAction, $pModelBuilder);
	}

	/**
	 * @return FieldsCollectionBuilderShort
	 */
	private function buildFieldsCollectionBuilderShort(string $module): FieldsCollectionBuilderShort
	{
		$pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria'])
			->setConstructorArgs([new Container])
			->getMock();

		$pFieldsCollectionBuilderShort->method('addFieldsAddressEstate')
			->with($this->anything())
			->willReturnCallback(static function(FieldsCollection $pFieldsCollection)
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
	 * @param string $module
	 * @return FieldsCollection
	 */
	private function buildFieldsCollection(string $module): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pFieldObjektart = new Field('objektart', $module);
		$pFieldObjektart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldsCollection->addField($pFieldObjektart);

		$pFieldObjekttyp = new Field('objekttyp', $module);
		$pFieldObjekttyp->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldsCollection->addField($pFieldObjekttyp);

		$pFieldNutzungsart = new Field('nutzungsart', $module);
		$pFieldNutzungsart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldsCollection->addField($pFieldNutzungsart);
		return $pFieldsCollection;
	}

	/**
	 * @param string $module
	 * @return FieldsCollection
	 */
	private function buildFieldsCollectionReferenceEstate(): FieldsCollection
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

	private function buildFieldsCollectionReferenceSearchCriteria(): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pFieldObjektart = new Field('objektart', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pFieldObjektart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldObjektart->setPermittedvalues([
			'wohnung' => 'Apartments',
			'haus' => 'House',
			'zimmer' => 'Rooms',
		]);
		$pFieldsCollection->addField($pFieldObjektart);

		$pFieldObjekttyp = new Field('objekttyp', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pFieldObjekttyp->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldObjekttyp->setPermittedvalues([
			'landhaus' => 'Cottage',
			'reiheneck' => 'Corner townhouse',
			'reihenend' => 'End townhouse',
			'souterrain' => 'Basement',
			'einfamilienhaus' => 'Single family house',
			'villa' => 'Villa',
			'schloss' => 'Castle',
			'hochparterre' => 'Mezzanine floor',
			'mehrfamilienhaus' => 'Apartment building',
			'ferienhaus' => 'Holiday home',
			'chalet' => 'Chalet',
			'bungalow' => 'Bungalow',
			'bauernhaus' => 'Farmhouse',
			'zweifamilienhaus' => 'Two-family-house',
			'wohn_und_geschaeftshaus' => 'Business premises with living accomodation',
			'praxisflaeche' => 'Facility space',
		]);
		$pFieldsCollection->addField($pFieldObjekttyp);

		$pFieldNutzungsart = new Field('nutzungsart', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pFieldNutzungsart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldNutzungsart->setPermittedvalues(['wohnen' => 'Residential']);
		$pFieldsCollection->addField($pFieldNutzungsart);
		return $pFieldsCollection;
	}

	/**
	 * @param string $module
	 * @return SDKWrapperMocker
	 */
	private function buildSDKWrapper(string $module): SDKWrapperMocker
	{
		$parameters = [
			'language' => 'ENG',
			'module' => $module,
			'field' => 'X',
			'filter' => [],
			'filterid' => 0,
		];

		if ($module === 'estate') {
			$parameters['filter'] = [
				'veroeffentlichen' => [['op' => '=','val' => '1']],
			];
			$parameters['filterid'] = 1337;
		}

		$fields = [
			'estate' => [
				'objektart' => 'ApiResponseDistinctFieldsHandlerEstateObjektart.json',
				'objekttyp' => 'ApiResponseDistinctFieldsHandlerEstateObjekttyp.json',
				'nutzungsart' => 'ApiResponseDistinctFieldsHandlerEstateNutzungsart.json',
			],
			'searchcriteria' => [
				'objektart' => 'ApiResponseDistinctFieldsHandlerSearchCriteriaObjektart.json',
				'objekttyp' => 'ApiResponseDistinctFieldsHandlerSearchCriteriaObjekttyp.json',
				'nutzungsart' => 'ApiResponseDistinctFieldsHandlerSearchCriteriaNutzungsart.json',
			],
		];
		$pSDKWrapperMocker = new SDKWrapperMocker();

		foreach ($fields[$module] as $field => $apiResponseFile) {
			$parametersEstatesThisField = $parameters;
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
		$pFieldsCollection = $this->buildFieldsCollection(onOfficeSDK::MODULE_ESTATE);
		$pSubject = $this->buildSubjectEstate($pDataListView);
		$pNewFieldsCollection = $pSubject->modifyFieldsCollectionForEstate($pDataListView, $pFieldsCollection);
		$pFieldsCollectionReference = $this->buildFieldsCollectionReferenceEstate();
		$this->assertEquals($pFieldsCollectionReference, $pNewFieldsCollection);
	}

	/**
	 * @throws UnknownFieldException
	 */
	public function testModifyFieldsCollectionForSearchCriteria()
	{
		$pDataFormConfiguration = new DataFormConfigurationApplicantSearch();
		$pDataFormConfiguration->setAvailableOptionsFields(['objektart', 'objekttyp', 'nutzungsart']);
		$pFieldsCollection = $this->buildFieldsCollection(onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pSubject = $this->buildSubjectSearchCriteria();
		$pNewFieldsCollection = $pSubject->modifyFieldsCollectionForSearchCriteria($pDataFormConfiguration, $pFieldsCollection);
		$pFieldsCollectionReference = $this->buildFieldsCollectionReferenceSearchCriteria();
		$this->assertEquals($pFieldsCollectionReference, $pNewFieldsCollection);
	}
}