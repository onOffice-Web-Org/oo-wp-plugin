<?php

namespace onOffice\WPlugin\Controller\SortList;

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\Renderer\SortListRenderer;

class SortListDropDownGenerator
{
	/** @var SortListRenderer */
	private $_pRenderer;

	/** @var SortListBuilder */
	private $_pListBuilder;

	/** @var DataListViewFactory  */
	private $_pListViewFactory;

	/**
	 * @param SortListBuilder $pListBuilder
	 * @param SortListRenderer $pRenderer
	 * @param DataListViewFactory $pListViewFactory
	 */
	public function __construct(SortListBuilder $pListBuilder, SortListRenderer $pRenderer, DataListViewFactory $pListViewFactory)
	{
		$this->_pListBuilder = $pListBuilder;
		$this->_pRenderer = $pRenderer;
		$this->_pListViewFactory = $pListViewFactory;
	}

	/**
	 * @param string $listViewName
	 * @return string
	 * @throws \onOffice\WPlugin\DataView\UnknownViewException
	 * @throws \onOffice\WPlugin\Field\UnknownFieldException
	 */
	public function generate(string $listViewName): string
	{
		$pListView = $this->_pListViewFactory->getListViewByName($listViewName);
		$pModel = $this->_pListBuilder->build($pListView);
		return $this->_pRenderer->createHtmlSelector($pModel, $pListView->getId());
	}
}