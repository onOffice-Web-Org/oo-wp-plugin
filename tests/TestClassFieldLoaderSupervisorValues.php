<?php

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldLoaderGeneric;
use onOffice\WPlugin\Field\Collection\FieldLoaderSupervisorValues;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

class TestClassFieldLoaderSupervisorValues extends WP_UnitTestCase
{
	/** @var FieldLoaderGeneric */
	private $_pFieldLoader = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$parametersGetFieldList = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'fieldList' => ['benutzer'],
			'language' => Language::getDefault(),
			'modules' => [onOfficeSDK::MODULE_ESTATE],
			'realDataTypes' => true
		];

		$pSDKWrapper = new SDKWrapperMocker();
		$responseSupervisorFieldJson = file_get_contents(__DIR__ . '/resources/ApiResponseSupervisorFields.json');
		$responseSupervisorField = json_decode($responseSupervisorFieldJson, true);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
			$parametersGetFieldList, null, $responseSupervisorField);
		$responseListSupervisorsJson = file_get_contents(__DIR__ . '/resources/ApiResponseListSupervisors.json');
		$responseListSupervisors = json_decode($responseListSupervisorsJson, true);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'users', '',
			[], null, $responseListSupervisors);

		$this->_pFieldLoader = new FieldLoaderSupervisorValues($pSDKWrapper);
	}

	/*
	 *
	 */
	public function testLoadFieldSupervisor()
	{
		$result = iterator_to_array($this->_pFieldLoader->load());
		$expectation = [
			"benutzer" => [
				"type" => FieldTypes::FIELD_TYPE_SINGLESELECT,
				"length" => null,
				"permittedvalues" => [
					69 => 'aa, aa',	
					45 => 'Test first name',
					42 => 'Test last name',
					30 => '(Test Username)'
				],
				"default" => null,
				"label"	=> 'Supervisor',
				"tablename" => 'ObjTech',
				"content" => __('Search Criteria', 'onoffice-for-wp-websites'),
				"module" => onOfficeSDK::MODULE_SEARCHCRITERIA,
			]
		];

		$this->assertCount(1, $result);
		$this->assertEquals($expectation, $result);
	}
}