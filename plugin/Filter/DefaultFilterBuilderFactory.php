<?php

namespace onOffice\WPlugin\Filter;

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;

class DefaultFilterBuilderFactory
{
	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort;


	/**
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 */

	public function __construct(FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort)
	{
		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
	}


	/**
	 * @param DataListView $pDataListView
	 * @return DefaultFilterBuilderListView
	 */
	public function buildDefaultListViewFilter(DataListView $pDataListView): DefaultFilterBuilderListView
	{
		$pEnvironment = new DefaultFilterBuilderListViewEnvironmentDefault();
		return new DefaultFilterBuilderListView($pDataListView, $this->_pFieldsCollectionBuilderShort, $pEnvironment);
	}
}