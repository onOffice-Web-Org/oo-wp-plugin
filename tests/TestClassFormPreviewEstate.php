<?php

declare(strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Form\Preview\FormPreviewEstate;

class TestClassFormPreviewEstate
	extends \WP_UnitTestCase
{
	/**
	 * @return FormPreviewEstate
	 */
	private function buildSubject(): FormPreviewEstate
	{
		$pDataListView = new DataListView(12, 'testList');
		$pDataListView->setFilterId(24);
		$pDataListViewFactory = $this->getMockBuilder(DataListViewFactory::class)
			->onlyMethods(['getListViewByName'])
			->getMock();
		$pDataListViewFactory->expects($this->once())->method('getListViewByName')
			->with('testList', null)->willReturn($pDataListView);

		$pDefaultFilterBuilder = $this->getMockBuilder(DefaultFilterBuilderListView::class)
			->disableOriginalConstructor()
			->getMock();
		$pDefaultFilterBuilderFactory = $this->getMockBuilder(DefaultFilterBuilderFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pDefaultFilterBuilderFactory
			->expects($this->once())
			->method('buildDefaultListViewFilter')
			->with($pDataListView)
			->willReturn($pDefaultFilterBuilder);

		// The preview no longer issues its own API request: it builds the estate list and
		// resolves the count through the regular DataListView cache lifecycle.
		$pEstateList = $this->getMockBuilder(EstateList::class)
			->disableOriginalConstructor()
			->onlyMethods(['setDefaultFilterBuilder', 'getEstateOverallCountFromCache'])
			->getMock();
		$pEstateList
			->expects($this->once())
			->method('setDefaultFilterBuilder')
			->with($pDefaultFilterBuilder);
		$pEstateList
			->expects($this->once())
			->method('getEstateOverallCountFromCache')
			->willReturn(5);

		$pEstateListFactory = $this->getMockBuilder(EstateListFactory::class)
			->disableOriginalConstructor()
			->onlyMethods(['createEstateList'])
			->getMock();
		$pEstateListFactory
			->expects($this->once())
			->method('createEstateList')
			->with($pDataListView)
			->willReturn($pEstateList);

		return new FormPreviewEstate($pDataListViewFactory, $pEstateListFactory, $pDefaultFilterBuilderFactory);
	}

	public function testPreview()
	{
		$pSubject = $this->buildSubject();
		$this->assertEquals(5, $pSubject->preview('testList'));
	}
}
