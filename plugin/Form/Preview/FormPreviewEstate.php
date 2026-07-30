<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Form\Preview;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;


class FormPreviewEstate
{
	/** @var DataListViewFactory */
	private $_pDataListViewFactory;

	/** @var EstateListFactory */
	private $_pEstateListFactory;

	/** @var DefaultFilterBuilderFactory */
	private $_pDefaultFilterBuilderFactory;

	/**
	 * @param DataListViewFactory $pDataListViewFactory
	 * @param EstateListFactory $pEstateListFactory
	 * @param DefaultFilterBuilderFactory $pDefaultFilterBuilderFactory
	 */
	public function __construct(
		DataListViewFactory $pDataListViewFactory,
		EstateListFactory $pEstateListFactory,
		DefaultFilterBuilderFactory $pDefaultFilterBuilderFactory)
	{
		$this->_pDataListViewFactory = $pDataListViewFactory;
		$this->_pEstateListFactory = $pEstateListFactory;
		$this->_pDefaultFilterBuilderFactory = $pDefaultFilterBuilderFactory;
	}

	/**
	 * Return the number of estates that match the given list configuration.
	 *
	 * The preview does not issue its own isolated API request. Instead it builds the
	 * estate list exactly as the front-end does and resolves the count through the
	 * regular DataListView cache lifecycle: on a cache hit the value is read from the
	 * shared list cache entry without any API call, and on a cache miss the regular
	 * data-creation process runs, warms the list cache and returns the count.
	 *
	 * @param string $listName
	 * @return int
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws ApiClientException
	 * @throws UnknownViewException
	 * @throws Exception
	 */
	public function preview(string $listName): int
	{
		/** @var DataListView $pListView */
		$pListView = $this->_pDataListViewFactory->getListViewByName($listName);

		$pEstateList = $this->_pEstateListFactory->createEstateList($pListView);
		$pEstateList->setDefaultFilterBuilder(
			$this->_pDefaultFilterBuilderFactory->buildDefaultListViewFilter($pListView)
		);

		return $pEstateList->getEstateOverallCountFromCache();
	}
}