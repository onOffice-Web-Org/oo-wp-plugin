<?php

// set up WP environment
require '../../../wp-load.php';

use onOffice\WPlugin\PdfDocument;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\ConfigWrapper;
use onOffice\SDK\onOfficeSDK;

$estateId = filter_input(INPUT_GET, 'estateid', FILTER_VALIDATE_INT);
$templateId = filter_input(INPUT_GET, 'documentid', FILTER_VALIDATE_INT);
$language = filter_input(INPUT_GET, 'language');
$configIndex = filter_input(INPUT_GET, 'configindex');

if (null === $estateId || null === $templateId || null === $configIndex) {
	exit();
}

$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );

if ( null === $estateConfig || ! array_key_exists( $configIndex, $estateConfig) )  {
	exit();
}


// check if the estate is accessible by the given config index so only documents are
// accessible if they are free for the web.
$listConfig = $estateConfig[$configIndex];
$filter = $listConfig['filter'];
$filter['Id'][] = array('op' => '=', 'val' => $estateId);

$parametersGetEstate = array(
	'data' => array('Id'),
	'filter' => $filter,
	'estatelanguage' => $language,
	'formatoutput' => 0,
);

$pSdkWrapper = new SDKWrapper();
$estateHandle = $pSdkWrapper->addRequest(
	onOfficeSDK::ACTION_ID_READ, 'estate', $parametersGetEstate );

$pSdkWrapper->sendRequests();
$response = $pSdkWrapper->getRequestResponse( $estateHandle );

$found = false;
if (isset($response['data']['records'])) {
	$records = $response['data']['records'];

	foreach ($records as $row) {
		if ( $row['id'] === $estateId ) {
			$found = true;
			break;
		}
	}
}

if ( ! $found ) {
	global $wp_query;
	$wp_query->is_404 = true;
	$wp_query->is_single = false;
	$wp_query->is_page = false;

	include( get_query_template( '404' ) );
	exit();
}

$documents = $listConfig['documents'];
$templateName = array_key_exists( $templateId, $documents ) ? $documents[$templateId] : null;

$pPdfDocument = new PdfDocument( $estateId, $language, $templateName );
$pPdfDocument->fetch();
$binary = $pPdfDocument->getDocumentBinary();
$type = $pPdfDocument->getMimeType();
$typeParts = explode('/', $type);

$fileEnding = $typeParts[1];

header( 'Content-Type: '.$type );
header( 'Content-Disposition: attachment; filename="document_'.$estateId.'.'.$fileEnding.'"' );

echo $binary;