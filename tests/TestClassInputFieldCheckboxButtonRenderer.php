<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use DI\Container;
use DI\ContainerBuilder;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\Renderer\InputFieldCheckboxButtonRenderer;
use onOffice\WPlugin\Renderer\InputFieldRenderer;
use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\WP\WPOptionWrapperTest;

class TestClassInputFieldCheckboxButtonRenderer
    extends \WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/**
	 * @before
	 */
	public function prepare()
	{
		$fieldParameters = [
				'labels' => true,
				'showContent' => true,
				'showTable' => true,
				'language' => 'ENG',
				'modules' => ['address'],
				'realDataTypes' => true
		];
		$pSDKWrapper = new SDKWrapperMocker();
		$responseGetFields = json_decode
		(file_get_contents(__DIR__.'/resources/ApiResponseGetFieldsAddress.json'), true);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
				$fieldParameters, null, $responseGetFields);
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$this->_pContainer->set(SDKWrapper::class, $pSDKWrapper);
	}

    public function testAttributes()
    {
        InputFieldRenderer::resetGuiId();
        $pRenderer = new InputFieldCheckboxButtonRenderer(null, 'testIdentifier');
        $pRenderer->setLabel('testLabel');
        $this->assertEquals('testLabel', $pRenderer->getLabel());
        $pRenderer->setId('idTest');
        $this->assertEquals('idTest', $pRenderer->getId());
    }

    /**
     *
     */
    public function testRenderWithValues()
    {
        $pRenderer = new InputFieldCheckboxButtonRenderer(null, 'testIdentifier');
        $pRenderer->setValue('John Doe');
        ob_start();
        global $wpdb;
        $dataDetailView = new DataDetailView();
        $dataDetailView->setAddressFields(['ind_1472_Feld_adressen2']);

        $pWpOption = new WPOptionWrapperTest();
        $pWpOption->addOption('onoffice-default-view', $dataDetailView);
        $pDbChanges = new DatabaseChanges($pWpOption, $wpdb, $this->_pContainer);
        $pDbChanges->install();
        $pRenderer->render();
        $output = ob_get_clean();
        $this->assertEquals('<input type="checkbox" name="" value="John Doe" id="checkbox_2"><p>'
            .'<input type="button" class="inputFieldCheckboxButton button" name="" value="Add to List &gt;&gt;" data-onoffice-category="">'
            .'</p>', $output);
    }

    /**
     *
     */
    public function testRenderEmptyValues()
    {
        $pSubject = new InputFieldCheckboxButtonRenderer('testRenderer',true);
        ob_start();
        global $wpdb;

        $dataDetailView = new DataDetailView();
        $dataDetailView->setAddressFields(['ind_1472_Feld_adressen2']);

        $pWpOption = new WPOptionWrapperTest();
        $pWpOption->addOption('onoffice-default-view', $dataDetailView);
        $pDbChanges = new DatabaseChanges($pWpOption, $wpdb, $this->_pContainer);
        $pDbChanges->install();
        $pSubject->render();
        $output = ob_get_clean();
        $this->assertEquals('<input type="checkbox" name="testRenderer" value="1" id="checkbox_3"><p>'
            .'<input type="button" class="inputFieldCheckboxButton button" name="" value="Add to List &gt;&gt;" data-onoffice-category="">'
            .'</p>', $output);
    }
}