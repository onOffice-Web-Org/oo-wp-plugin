<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Template\TemplateCall;
use WP_UnitTestCase;
use function json_decode;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassTemplateCall
	extends WP_UnitTestCase
{
	/** @var array */
	private $_expectationPdf = [
		'urn:onoffice-de-ns:smart:2.5:pdf:expose:kurz:design01Aushang' => 'Design01Aushang',
		'urn:onoffice-de-ns:smart:2.5:pdf:expose:kurz:design01Expose' => 'Design01Expose (kurz)',
		'urn:onoffice-de-ns:smart:2.5:pdf:expose:lang:design01Expose' => 'Design01Expose (lang)',
		'urn:onoffice-de-ns:smart:2.5:pdf:expose:kurz:design02Aushang' => 'Design02Aushang',
		'urn:onoffice-de-ns:smart:2.5:pdf:expose:kurz:design02Expose' => 'Design02Expose (kurz)',
		'urn:onoffice-de-ns:smart:2.5:pdf:expose:lang:design02Expose' => 'Design02Expose (lang)',
		'urn:onoffice-de-ns:smart:2.5:pdf:expose:kurz:objektnachweis' => 'Exposé Objektnachweis',
		'urn:onoffice-de-ns:smart:2.5:pdf:expose:kurz:landscape' => 'Exposé Querformat (kurz)',
		'urn:onoffice-de-ns:smart:2.5:pdf:expose:lang:landscape' => 'Exposé Querformat (lang)',
	];

	/** @var array */
	private $_expectationTemplatePath = [
		1 => [
			'path' => [
				'...\wordpress\wp-content\plugins/onoffice-themes/templates/address/SearchFormAddress.php' => "SearchFormAddress.php",
				'...\wordpress\wp-content\plugins/onoffice-themes/templates/address/default.php' => "default.php"
			],
			'title' => "Personalized (Theme)",
			'folder' => "/onoffice-theme/templates/address/"
		],
		2 => [
			'path' => [
				'...\wordpress\wp-content\plugins/onoffice-personalized/templates/address/SearchFormAddress.php' => "SearchFormAddress.php",
				'...\wordpress\wp-content\plugins/onoffice-personalized/templates/address/default.php' => "default.php"
			],
			'title' => "Personalized (Plugin)",
			'folder' => "onoffice-personalized/templates/address/"
		],
		3 => [
			'path' => [
				'...\wordpress\wp-content\plugins\oo-wp-plugin/templates.dist/address/SearchFormAddress.php' => "SearchFormAddress.php",
				'.\wordpress\wp-content\plugins\oo-wp-plugin/templates.dist/address/default.php' => "default.php",
			],
			'title' => "Default",
			'folder' => "oo-wp-plugin/templates.dist/address/"
		]
	];


	/**
	 *
	 */

	public function testConstruct()
	{
		$pTemplateCall1 = new TemplateCall();
		$this->assertEquals($pTemplateCall1->getTemplateType(), TemplateCall::TEMPLATE_TYPE_EXPOSE);
		$this->assertInstanceOf(SDKWrapper::class, $pTemplateCall1->getSDKWrapper());

		$pTemplateCall2 = new TemplateCall(TemplateCall::TEMPLATE_TYPE_MAIL);
		$this->assertEquals($pTemplateCall2->getTemplateType(), TemplateCall::TEMPLATE_TYPE_MAIL);
		$this->assertInstanceOf(SDKWrapper::class, $pTemplateCall2->getSDKWrapper());
	}


	/**
	 *
	 */

	public function testLoadPdf()
	{
		$pSDKWrapperMocker = new SDKWrapperMocker();
		$responseJson = file_get_contents(__DIR__.'/resources/ApiResponseGetTemplatesTypePdf.json');
		$response = json_decode($responseJson, true);

		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'templates', '',
			['type' => TemplateCall::TEMPLATE_TYPE_EXPOSE], null, $response);

		$pTemplateCall = new TemplateCall(TemplateCall::TEMPLATE_TYPE_EXPOSE, $pSDKWrapperMocker);
		$pTemplateCall->loadTemplates();

		$this->assertEquals($this->_expectationPdf, $pTemplateCall->getTemplates());
	}


	/**
	 *
	 */

	public function testReadTemplates()
	{
		$pTemplateCall = new TemplateCall(TemplateCall::TEMPLATE_TYPE_EXPOSE);

		$templateGlobFiles = [
			"...\wordpress\wp-content\plugins\oo-wp-plugin/templates.dist/address/SearchFormAddress.php",
			".\wordpress\wp-content\plugins\oo-wp-plugin/templates.dist/address/default.php"
		];
		$templateLocalFiles = [
			"...\wordpress\wp-content\plugins/onoffice-personalized/templates/address/SearchFormAddress.php",
			"...\wordpress\wp-content\plugins/onoffice-personalized/templates/address/default.php"
		];
		$templateThemeFiles = [
			"...\wordpress\wp-content\plugins/onoffice-themes/templates/address/SearchFormAddress.php",
			"...\wordpress\wp-content\plugins/onoffice-themes/templates/address/default.php"
		];

		$templatesAll = array_merge($templateGlobFiles, $templateLocalFiles, $templateThemeFiles);
		$templatePath = $pTemplateCall->readTemplates($templatesAll, 'address');
		$this->assertEquals($this->_expectationTemplatePath, $templatePath);
	}
}
