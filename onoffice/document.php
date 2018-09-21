<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

// set up WP environment
require '../../../wp-load.php';

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\PdfDocument;
use onOffice\WPlugin\SDKWrapper;

$estateId = filter_input(INPUT_GET, 'estateid', FILTER_VALIDATE_INT);
$language = filter_input(INPUT_GET, 'language');
$configIndex = filter_input(INPUT_GET, 'configindex');

if (null === $estateId || null === $configIndex) {
	exit();
}

try {
	$pDataDetailViewHandler = new DataDetailViewHandler();
	$pView = $pDataDetailViewHandler->getDetailView();
	$isDetailView = $configIndex === $pView->getName();
	if (!$isDetailView) {
		$pEstateConfigFactory = new DataListViewFactory();
		$pView = $pEstateConfigFactory->getListViewByName($configIndex);
	}
} catch (Exception $pEx) {
	exit();
}

$estateIdOriginal = $estateId;

if ($isDetailView) {
	$pDefaultFilterBuilder = new DefaultFilterBuilderDetailView();
	$pDefaultFilterBuilder->setEstateId($estateId);
	$filter = $pDefaultFilterBuilder->buildFilter();
} else {
	$pDefaultFilterBuilder = new DefaultFilterBuilderListView($pView);
	$filter = $pDefaultFilterBuilder->buildFilter();
	$filter['Id'][] = array('op' => '=', 'val' => $estateId);
}

// append ID to filter in order to make sure viewing this document is allowed
$parametersGetEstate = array(
	'data' => array('Id'),
	'filter' => $filter,
	'estatelanguage' => $language,
	'formatoutput' => 0,
);

$pSDKWrapper = new SDKWrapper();
$pApiClientAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate');
$pApiClientAction->setParameters($parametersGetEstate);
$pApiClientAction->addRequestToQueue();

$pSDKWrapper->sendRequests();

$records = $pApiClientAction->getResultRecords();

if ($pApiClientAction->getResultStatus() && count($records) === 1) {
	$row = array_shift($records);
	$estateId = $row['id'];
} else {
	global $wp_query;
	$wp_query->is_404 = true;
	$wp_query->is_single = false;
	$wp_query->is_page = false;

	include( get_query_template( '404' ) );
	exit();
}

$pPdfDocument = new PdfDocument($estateId, $language, $pView->getExpose());
if ($pPdfDocument->fetch()) {
	$binary = $pPdfDocument->getDocumentBinary();
	$type = $pPdfDocument->getMimeType();
	$typeParts = explode('/', $type);

	$fileEnding = $typeParts[1];

	header('Content-Type: '.$type);
	header('Content-Disposition: attachment; filename="document_'.$estateIdOriginal.'.'.$fileEnding.'"');

	echo $binary;
}