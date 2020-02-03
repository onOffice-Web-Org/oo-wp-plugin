<?php

namespace onOffice\tests;

use DI\Container;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;

class TestClassDefaultFilterBuilderFactory
	extends WP_UnitTest_Localized
{
	/** @var DefaultFilterBuilderFactory */
	private $_pInstance;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setConstructorArgs([new Container])
			->getMock();

		$this->_pInstance = new DefaultFilterBuilderFactory($pFieldsCollectionBuilderShort);
	}

	/**
	 * @covers \onOffice\WPlugin\Filter\DefaultFilterBuilderFactory::__construct
	 */
	public function testConstruct()
	{
		$this->assertInstanceOf(DefaultFilterBuilderFactory::class, $this->_pInstance);
	}

	/**
	 * @covers \onOffice\WPlugin\Filter\DefaultFilterBuilderFactory::buildDefaultListViewFilter
	 */
	public function testBuildDefaultListViewFilter()
	{
		$pDataListView = $this->getMockBuilder(DataListView::class)
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			DefaultFilterBuilderListView::class,
			$this->_pInstance->buildDefaultListViewFilter($pDataListView));
	}
}