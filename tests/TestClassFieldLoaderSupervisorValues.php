<?php

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldLoaderGeneric;
use onOffice\WPlugin\Field\Collection\FieldLoaderSupervisorValues;
use onOffice\WPlugin\Language;
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

		$parameterUser = [
			"data" => ["Vorname", "Nachname", "Name", "Kuerzel"],
			"filter" => [
				"Nr" => [["op" => "in", "val" => [69, 45]]]
			]
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
		$responseReadUsersJson = file_get_contents(__DIR__ . '/resources/ApiResponseGetUsers.json');
		$responseReadUsers = json_decode($responseReadUsersJson, true);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'user', '',
			$parameterUser, null, $responseReadUsers);

		$this->_pFieldLoader = new FieldLoaderSupervisorValues($pSDKWrapper);
	}

	/*
	 *
	 */
	public function testLoadFieldSupervisor()
	{
		$result = iterator_to_array($this->_pFieldLoader->load());
		$this->assertCount(1, $result);
		foreach ($result as $fieldname => $fieldProperties) {
			$this->assertIsString($fieldname);
			$this->assertArrayHasKey('module', $fieldProperties);
			$this->assertArrayHasKey('label', $fieldProperties);
			$this->assertArrayHasKey('type', $fieldProperties);
			$this->assertArrayHasKey('default', $fieldProperties);
			$this->assertArrayHasKey('length', $fieldProperties);
			$this->assertArrayHasKey('permittedvalues', $fieldProperties);
			$this->assertArrayHasKey('content', $fieldProperties);
		}
	}
}