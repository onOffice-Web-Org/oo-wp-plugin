<?php

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataViewAddress;
use onOffice\WPlugin\API\APIClientActionGeneric;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;

class EstateLoader
{
    private $_pApiClientAction;
    private $_formatOutput;
    private $_records = [];
    private $_recordsRaw = [];
    private $_pEnvironment;

    public function __construct(APIClientActionGeneric $pApiClientAction, bool $formatOutput, $pEnvironment)
    {
        $this->_pApiClientAction = $pApiClientAction;
        $this->_formatOutput = $formatOutput;
        $this->_pEnvironment = $pEnvironment;
    }

    /**
     * @param int $currentPage
     * @param array $estateIds
     * @param DataViewAddress $addressDataView
     * @throws DependencyException
     * @throws NotFoundException
     * @throws API\ApiClientException
     */
    public function loadEstateByAddressId(int $currentPage, array $estateIds, DataViewAddress $addressDataView)
    {
        $estateParameters = $this->getEstateAddressOwnerParameters($currentPage, $this->_formatOutput, $estateIds, $addressDataView);
        $this->_pApiClientAction->setParameters($estateParameters);
        $this->_pApiClientAction->addRequestToQueue();

        $estateParametersRaw = $this->getEstateAddressOwnerParameters($currentPage, false, $estateIds, $addressDataView);
        $estateParametersRaw['data'] = $this->_pEnvironment->getEstateStatusLabel()->getFieldsByPrio();
        $estateParametersRaw['data'] []= 'vermarktungsart';
        $estateParametersRaw['data'] []= 'preisAufAnfrage';
        $pApiClientActionRawValues = clone $this->_pApiClientAction;
        $pApiClientActionRawValues->setParameters($estateParametersRaw);
        $pApiClientActionRawValues->addRequestToQueue()->sendRequests();

        $this->_records = $this->_pApiClientAction->getResultRecords();
        $recordsRaw = $pApiClientActionRawValues->getResultRecords();
        $this->_recordsRaw = array_combine(array_column($recordsRaw, 'id'), $recordsRaw);
    }

    /**
     * @param int $currentPage
     * @param bool $formatOutput
     * @return array
     */
    private function getEstateAddressOwnerParameters(int $currentPage, bool $formatOutput, array $estateIds, DataViewAddress $addressDataView)
    {
        $filter = [
            'veroeffentlichen' => [
                ['op' => '=', 'val' => 1],
            ],
            'Id' => [
                ['op' => 'IN', 'val' => $estateIds],
            ],
        ];
        $pFieldModifierHandler = new ViewFieldModifierHandler($addressDataView->getEstateFields(), onOfficeSDK::MODULE_ESTATE);

        $requestParams = [
            'filter' => $filter,
            'data' => $pFieldModifierHandler->getAllAPIFields(),
            'outputlanguage' => Language::getDefault(),
            'formatoutput' => $formatOutput
        ];

        $offset = ( $currentPage - 1 ) * $addressDataView->getRecordsPerPage();
        $requestParams += [
            'listoffset' => $offset
        ];

        if (!empty($addressDataView->getFilter())) {
            $requestParams['filterid'] = $addressDataView->getFilter();
        }
        return $requestParams;
    }

    public function getRecords()
    {
        return $this->_records;
    }

    public function getRecordsRaw()
    {
        return $this->_recordsRaw;
    }
}