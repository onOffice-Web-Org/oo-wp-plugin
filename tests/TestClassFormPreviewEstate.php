<?php

declare(strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;
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
			->setMethods(['getListViewByName'])
			->getMock();
		$pDataListViewFactory->expects($this->once())-> method('getListViewByName')
			->with('testList', null)->willReturn($pDataListView);
		$pApiClientAction = $this->getMockBuilder(APIClientActionGeneric::class)
			->disableOriginalConstructor()
			->getMock();

		$pDefaultFilterBuilder = $this->getMockBuilder(DefaultFilterBuilderListView::class)
			->disableOriginalConstructor()
			->getMock();
		$pDefaultFilterBuilder->expects($this->once())->method('buildFilter')->willReturn([
			'veroeffentlichen' => [['op' => '=', 'val' => '1']],
		]);

		$pDefaultFilterBuilderFactory = $this->getMockBuilder(DefaultFilterBuilderFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pDefaultFilterBuilderFactory
			->expects($this->once())
			->method('buildDefaultListViewFilter')
			->with($pDataListView)
			->willReturn($pDefaultFilterBuilder);
		$pApiClientAction
			->expects($this->once())
			->id("1")
			->method('withActionIdAndResourceType')
			->with(onOfficeSDK::ACTION_ID_READ, 'estate')
			->willReturnSelf();
		$pApiClientAction
			->expects($this->once())
			->after(1)
			->id("2")
			->method('setParameters')
			->with(['listlimit' => 0, 'filter' => ['veroeffentlichen' => [['op' => '=', 'val' => 1]]], 'filterid' => 24])
			->willReturnSelf();
		$pApiClientAction
			->expects($this->once())
			->after(2)
			->id("3")
			->method('addRequestToQueue')
			->willReturnSelf();
		$pApiClientAction
			->expects($this->once())
			->after(3)
			->id("4")
			->method('sendRequests')
			->willReturnSelf();
		$pApiClientAction
			->expects($this->once())
			->after(4)
			->id("5")
			->method('getResultMeta')
			->willReturn(['cntabsolute' => 5]);
		return new FormPreviewEstate($pDataListViewFactory, $pApiClientAction, $pDefaultFilterBuilderFactory);
	}

	public function testPreview()
	{
		$pSubject = $this->buildSubject();
		$this->assertEquals(5, $pSubject->preview('testList'));
	}
}