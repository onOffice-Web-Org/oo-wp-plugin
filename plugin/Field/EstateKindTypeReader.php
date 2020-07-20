<?php

namespace onOffice\WPlugin\Field;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;

class EstateKindTypeReader
{
	/** @var APIClientActionGeneric */
	private $_pApiClientAction;

	/**
	 * @param APIClientActionGeneric $pApiClientAction
	 */
	public function __construct(APIClientActionGeneric $pApiClientAction)
	{
		$this->_pApiClientAction = $pApiClientAction;
	}

	/**
	 * @return array
	 * @throws ApiClientException
	 */
	public function read(): array
	{
		$pApiClientAction = $this->_pApiClientAction->withActionIdAndResourceType
			(onOfficeSDK::ACTION_ID_GET, 'estateCategories');
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$result = [];
		$typeRecord = $this->findEstateTypeRecord($pApiClientAction->getResultRecords());

		foreach ($typeRecord as $typeKey => $possibleTypeValues) {
			$result[$typeKey] = array_column($possibleTypeValues, 'id');
		}

		return $result;
	}

	/**
	 * @param array $records
	 * @return array
	 */
	private function findEstateTypeRecord(array $records): array
	{
		$id = array_search('objekttyp', array_column($records, 'id'), true);
		return $records[$id]['elements'];
	}
}